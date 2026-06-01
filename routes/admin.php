<?php

declare(strict_types=1);

use App\Flash;
use App\Http\Middleware\RequireCmsStaff;
use App\Plugin\PluginBootContext;
use App\Security\CsrfToken;
use KnowledgeBasePlugin\KnowledgeBaseProvisioner;
use KnowledgeBasePlugin\KnowledgeBaseSettings;
use KnowledgeBasePlugin\KnowledgeBaseWikiIndex;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteContext;

return function (App $app, PluginBootContext $ctx): void {
    $middleware = new RequireCmsStaff($ctx->auth(), $ctx->pdo());
    $twig = $ctx->twig();
    $pdo = $ctx->pdo();

    $adminView = static function (Request $request, array $extra = []) use ($ctx): array {
        $cmsUser = $request->getAttribute('cms_user') ?? [];

        return array_merge($ctx->viewData(), [
            'admin_nav' => 'plugin_knowledge_base',
            'cms_user' => is_array($cmsUser) ? $cmsUser : [],
        ], $extra);
    };

    $ensureWiki = static function () use ($pdo): KnowledgeBaseProvisioner {
        $provisioner = new KnowledgeBaseProvisioner($pdo);
        $provisioner->seedWikiArticlesIfNeeded();

        return $provisioner;
    };

    $app->group('/admin', function (\Slim\Routing\RouteCollectorProxy $group) use (
        $ctx,
        $twig,
        $pdo,
        $adminView,
        $ensureWiki
    ): void {
        $group->get('/knowledge-base-plugin', function (Request $request, Response $response) use (
            $twig,
            $pdo,
            $adminView,
            $ensureWiki
        ): Response {
            $ensureWiki();
            $parser = RouteContext::fromRequest($request)->getRouteParser();
            $publicVisible = KnowledgeBaseSettings::isPublicVisible($pdo);

            $editUrlBuilder = static function (int $typeId, int $entryId) use ($parser): string {
                return $parser->urlFor('admin.content_types.entries.edit', [
                    'id' => (string) $typeId,
                    'entryId' => (string) $entryId,
                ]);
            };

            $index = new KnowledgeBaseWikiIndex($pdo);
            $sections = $index->groupedForAdmin($editUrlBuilder, $publicVisible);

            return $twig->render($response, '@plugin_knowledge_base_plugin/admin/index.twig', $adminView($request, [
                'sections' => $sections,
                'public_visible' => $publicVisible,
                'content_type_slug' => KnowledgeBaseSettings::CONTENT_TYPE_SLUG,
                'article_count' => array_sum(array_map(
                    static fn (array $s): int => count($s['articles']),
                    $sections
                )),
            ]));
        })->setName('plugin.knowledge_base_plugin.index');

        $group->get('/knowledge-base-plugin/settings', function (Request $request, Response $response) use (
            $twig,
            $pdo,
            $adminView,
            $ensureWiki
        ): Response {
            $ensureWiki();

            return $twig->render($response, '@plugin_knowledge_base_plugin/admin/settings.twig', $adminView($request, [
                'public_visible' => KnowledgeBaseSettings::isPublicVisible($pdo),
                'content_type_slug' => KnowledgeBaseSettings::CONTENT_TYPE_SLUG,
            ]));
        })->setName('plugin.knowledge_base_plugin.settings');

        $group->post('/knowledge-base-plugin/settings', function (Request $request, Response $response) use (
            $pdo,
            $ensureWiki
        ): Response {
            $body = $request->getParsedBody();
            $body = is_array($body) ? $body : [];
            $token = isset($body['_csrf_token']) && is_string($body['_csrf_token']) ? $body['_csrf_token'] : '';
            if (!CsrfToken::validate($token)) {
                Flash::set('error', 'Invalid security token. Please try again.');
                $parser = RouteContext::fromRequest($request)->getRouteParser();

                return $response
                    ->withHeader('Location', $parser->urlFor('plugin.knowledge_base_plugin.settings'))
                    ->withStatus(302);
            }

            $provisioner = $ensureWiki();
            $wantPublic = !empty($body['public_visible']);
            $provisioner->setPublicVisible($wantPublic);

            Flash::set(
                'success',
                $wantPublic
                    ? 'Knowledge Base is now public at /' . KnowledgeBaseSettings::CONTENT_TYPE_SLUG . '/{article-slug}.'
                    : 'Knowledge Base is admin-only; public URLs are disabled.'
            );

            $parser = RouteContext::fromRequest($request)->getRouteParser();

            return $response
                ->withHeader('Location', $parser->urlFor('plugin.knowledge_base_plugin.settings'))
                ->withStatus(302);
        })->setName('plugin.knowledge_base_plugin.settings.save');
    })->add($middleware);
};
