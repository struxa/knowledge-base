<?php

declare(strict_types=1);

namespace KnowledgeBasePlugin;

use PDO;

/**
 * Supplies KB wiki variables for core content routes when the plugin is on disk
 * (does not rely on Twig functions from service provider boot).
 */
final class KnowledgeBasePublicBridge
{
    public const CONTENT_TYPE_SLUG = 'kb';

    /**
     * @return array{kb_storefront_enabled: bool, kb_wiki_sections: list<array<string, mixed>>}
     */
    public static function indexViewData(PDO $pdo): array
    {
        return self::viewData($pdo, '');
    }

    /**
     * @return array{kb_storefront_enabled: bool, kb_wiki_sections: list<array<string, mixed>>}
     */
    public static function showViewData(PDO $pdo, string $currentSlug): array
    {
        return self::viewData($pdo, $currentSlug);
    }

    /**
     * @return array{kb_storefront_enabled: bool, kb_wiki_sections: list<array<string, mixed>>}
     */
    private static function viewData(PDO $pdo, string $currentSlug): array
    {
        if (!class_exists(KnowledgeBaseWikiIndex::class)) {
            return [
                'kb_storefront_enabled' => false,
                'kb_wiki_sections' => [],
            ];
        }

        $enabled = KnowledgeBaseSettings::isStorefrontWikiActive($pdo);
        if (!$enabled) {
            return [
                'kb_storefront_enabled' => false,
                'kb_wiki_sections' => [],
            ];
        }

        return [
            'kb_storefront_enabled' => true,
            'kb_wiki_sections' => (new KnowledgeBaseWikiIndex($pdo))->groupedForPublic($currentSlug),
        ];
    }
}
