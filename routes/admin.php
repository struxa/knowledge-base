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
use Slim\Exception\HttpNotFoundException;
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
        $provisioner->syncWikiArticlesFromCatalog();

        return $provisioner;
    };

    $wikiContext = static function (Request $request) use ($pdo): array {
        $parser = RouteContext::fromRequest($request)->getRouteParser();
        $publicVisible = KnowledgeBaseSettings::isPublicVisible($pdo);
        $typeSlug = KnowledgeBaseSettings::CONTENT_TYPE_SLUG;

        $editUrlBuilder = static function (int $typeId, int $entryId) use ($parser): string {
            return $parser->urlFor('admin.content_types.entries.edit', [
                'id' => (string) $typeId,
                'entryId' => (string) $entryId,
            ]);
        };

        $readUrlBuilder = static function (string $slug) use ($parser): string {
            return $parser->urlFor('plugin.knowledge_base_plugin.read', ['slug' => $slug]);
        };

        $index = new KnowledgeBaseWikiIndex($pdo);
        $sections = $index->groupedForAdmin($editUrlBuilder, $publicVisible, $readUrlBuilder);

        return [
            'sections' => $sections,
            'public_visible' => $publicVisible,
            'content_type_slug' => $typeSlug,
            'article_count' => array_sum(array_map(
                static fn (array $s): int => count($s['articles']),
                $sections
            )),
            'edit_url_builder' => $editUrlBuilder,
        ];
    };

    $app->group('/admin', function (\Slim\Routing\RouteCollectorProxy $group) use (
        $ctx,
        $twig,
        $pdo,
        $adminView,
        $ensureWiki,
        $wikiContext
    ): void {
        $group->get('/knowledge-base-plugin', function (Request $request, Response $response) use (
            $twig,
            $adminView,
            $ensureWiki,
            $wikiContext
        ): Response {
            $ensureWiki();
            $wiki = $wikiContext($request);

            return $twig->render($response, '@plugin_knowledge_base_plugin/admin/index.twig', $adminView($request, array_merge($wiki, [
                'kb_page' => 'home',
            ])));
        })->setName('plugin.knowledge_base_plugin.index');

        $group->get('/knowledge-base-plugin/a/{slug}', function (Request $request, Response $response, array $args) use (
            $twig,
            $pdo,
            $adminView,
            $ensureWiki,
            $wikiContext
        ): Response {
            $ensureWiki();
            $slug = isset($args['slug']) && is_string($args['slug']) ? trim($args['slug']) : '';
            if ($slug === '') {
                throw new HttpNotFoundException($request);
            }

            $wiki = $wikiContext($request);
            $index = new KnowledgeBaseWikiIndex($pdo);
            $publicVisible = $wiki['public_visible'];
            $article = $index->articleBySlug($slug, $wiki['edit_url_builder'], $publicVisible);
            if ($article === null) {
                throw new HttpNotFoundException($request);
            }

            return $twig->render($response, '@plugin_knowledge_base_plugin/admin/article.twig', $adminView($request, array_merge($wiki, [
                'kb_page' => 'article',
                'kb_current_slug' => $slug,
                'article' => $article,
            ])));
        })->setName('plugin.knowledge_base_plugin.read');

        $group->get('/knowledge-base-plugin/settings', function (Request $request, Response $response) use (
            $twig,
            $adminView,
            $ensureWiki,
            $wikiContext
        ): Response {
            $ensureWiki();
            $wiki = $wikiContext($request);

            return $twig->render($response, '@plugin_knowledge_base_plugin/admin/settings.twig', $adminView($request, array_merge($wiki, [
                'kb_page' => 'settings',
            ])));
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
