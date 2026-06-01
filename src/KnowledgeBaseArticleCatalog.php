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

        $path = dirname(__DIR__) . '/data/wiki-articles.json';
        if (!is_readable($path)) {
            $cached = [];

            return $cached;
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $cached = [];

            return $cached;
        }

        if (!is_array($decoded)) {
            $cached = [];

            return $cached;
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

        $cached = $articles;

        return $cached;
    }
}
