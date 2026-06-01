<?php

declare(strict_types=1);

namespace KnowledgeBasePlugin;

/**
 * Default Struxa CMS wiki articles seeded on first admin visit.
 *
 * @internal
 */
final class KnowledgeBaseArticleCatalog
{
    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    public static function articles(): array
    {
        static $cached = null;
        if (is_array($cached)) {
            return $cached;
        }

        $articles = self::loadFromArticlesDirectory();
        if ($articles === []) {
            $articles = self::loadFromLegacyFile();
        }

        $cached = $articles;

        return $cached;
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function loadFromArticlesDirectory(): array
    {
        $dir = dirname(__DIR__) . '/data/articles';
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.json');
        if ($files === false || $files === []) {
            return [];
        }

        sort($files);
        $articles = [];
        foreach ($files as $path) {
            foreach (self::decodeArticleFile($path) as $row) {
                $articles[] = $row;
            }
        }

        return $articles;
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function loadFromLegacyFile(): array
    {
        $path = dirname(__DIR__) . '/data/wiki-articles.json';
        if (!is_readable($path)) {
            return [];
        }

        return self::decodeArticleFile($path);
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function decodeArticleFile(string $path): array
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        $articles = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $slug = isset($row['slug']) && is_string($row['slug']) ? trim($row['slug']) : '';
            $title = isset($row['title']) && is_string($row['title']) ? trim($row['title']) : '';
            $section = isset($row['section']) && is_string($row['section']) ? trim($row['section']) : '';
            $summary = isset($row['summary']) && is_string($row['summary']) ? trim($row['summary']) : '';
            $body = isset($row['body']) && is_string($row['body']) ? $row['body'] : '';
            if ($slug === '' || $title === '') {
                continue;
            }
            $articles[] = [
                'title' => $title,
                'slug' => $slug,
                'section' => $section !== '' ? $section : 'getting-started',
                'summary' => $summary,
                'body' => $body,
            ];
        }

        return $articles;
    }
}
