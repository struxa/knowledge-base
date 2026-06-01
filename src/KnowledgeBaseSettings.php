<?php

declare(strict_types=1);

namespace KnowledgeBasePlugin;

use App\Settings;
use App\Settings\SettingsRepository;
use PDO;

final class KnowledgeBaseSettings
{
    public const KEY_PUBLIC_VISIBLE = 'plugin.knowledge_base_plugin.public_visible';

    public const KEY_WIKI_SEEDED = 'plugin.knowledge_base_plugin.wiki_seeded';

    public const CONTENT_TYPE_SLUG = 'kb';

    public static function isPublicVisible(?PDO $pdo = null): bool
    {
        $raw = Settings::get(self::KEY_PUBLIC_VISIBLE, '0');
        if ($raw === null && $pdo !== null) {
            $repo = new SettingsRepository($pdo);
            $all = $repo->allKeyValues();

            return ($all[self::KEY_PUBLIC_VISIBLE] ?? '0') === '1';
        }

        return $raw === '1';
    }

    public static function isWikiSeeded(?PDO $pdo = null): bool
    {
        $raw = Settings::get(self::KEY_WIKI_SEEDED, '0');
        if ($raw === null && $pdo !== null) {
            $repo = new SettingsRepository($pdo);
            $all = $repo->allKeyValues();

            return ($all[self::KEY_WIKI_SEEDED] ?? '0') === '1';
        }

        return $raw === '1';
    }

    public static function setPublicVisible(PDO $pdo, bool $visible): void
    {
        $repo = new SettingsRepository($pdo);
        $repo->upsert(self::KEY_PUBLIC_VISIBLE, $visible ? '1' : '0', true);
        Settings::reload($pdo);
    }

    public static function markWikiSeeded(PDO $pdo): void
    {
        $repo = new SettingsRepository($pdo);
        $repo->upsert(self::KEY_WIKI_SEEDED, '1', true);
        Settings::reload($pdo);
    }
}
