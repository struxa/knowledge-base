<?php

declare(strict_types=1);

namespace KnowledgeBasePlugin;

use App\Content\ContentEntryRepository;
use App\Content\ContentEntryValueRepository;
use App\Content\ContentFieldRepository;
use App\Content\ContentTypeRepository;
use App\Taxonomy\ContentEntryTaxonomyRepository;
use App\Taxonomy\TaxonomyRepository;
use App\Taxonomy\TaxonomyTermRepository;
use PDO;

final class KnowledgeBaseWikiIndex
{
    /** @var array<string, string> */
    private const SECTION_LABELS = [
        'getting-started' => 'Getting started',
        'general-users' => 'General users',
        'editors' => 'Editors',
        'developers' => 'Developers',
        'plugin-developers' => 'Plugin developers',
        'theme-developers' => 'Theme developers',
        'commerce' => 'Commerce',
    ];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{type_id: int, summary_field_id: int|null, body_field_id: int|null, term_slug_by_id: array<int, string>}|null
     */
    private function kbTypeContext(): ?array
    {
        $types = new ContentTypeRepository($this->pdo);
        $type = $types->findBySlug(KnowledgeBaseSettings::CONTENT_TYPE_SLUG);
        if ($type === null) {
            return null;
        }

        $fields = new ContentFieldRepository($this->pdo);
        $summaryFieldId = null;
        $bodyFieldId = null;
        foreach ($fields->forTypeOrdered($type->id) as $field) {
            if ($field->fieldKey === 'summary') {
                $summaryFieldId = $field->id;
            } elseif ($field->fieldKey === 'body') {
                $bodyFieldId = $field->id;
            }
        }

        $taxonomyRepo = new TaxonomyRepository($this->pdo);
        $termRepo = new TaxonomyTermRepository($this->pdo);
        $taxonomy = $taxonomyRepo->findByContentTypeAndSlug($type->id, 'kb-section');
        $termSlugById = [];
        if ($taxonomy !== null) {
            foreach ($termRepo->forTaxonomyOrdered($taxonomy->id) as $term) {
                $termSlugById[$term->id] = $term->slug;
            }
        }

        return [
            'type_id' => $type->id,
            'summary_field_id' => $summaryFieldId,
            'body_field_id' => $bodyFieldId,
            'term_slug_by_id' => $termSlugById,
        ];
    }

    /**
     * @return list<array{
     *   section_slug: string,
     *   section_label: string,
     *   articles: list<array{entry_id: int, title: string, slug: string, summary: string, edit_url: string|null, read_url: string|null, public_url: string|null}>
     * }>
     */
    public function groupedForAdmin(
        ?callable $editUrlBuilder = null,
        bool $publicVisible = false,
        ?callable $readUrlBuilder = null
    ): array {
        $ctx = $this->kbTypeContext();
        if ($ctx === null) {
            return [];
        }

        $typeId = $ctx['type_id'];
        $summaryFieldId = $ctx['summary_field_id'];
        $termSlugById = $ctx['term_slug_by_id'];

        $entries = new ContentEntryRepository($this->pdo);
        $values = new ContentEntryValueRepository($this->pdo);
        $entryTax = new ContentEntryTaxonomyRepository($this->pdo);

        $bySection = [];
        foreach (self::SECTION_LABELS as $slug => $label) {
            $bySection[$slug] = [
                'section_slug' => $slug,
                'section_label' => $label,
                'articles' => [],
            ];
        }

        $rows = $entries->forTypeOrdered($typeId, 500);
        $entryIds = array_map(static fn (array $r): int => (int) $r['id'], $rows);
        $summaries = $summaryFieldId !== null
            ? $values->valuesForFieldAndEntryIds($summaryFieldId, $entryIds)
            : [];

        foreach ($rows as $row) {
            $entryId = (int) $row['id'];
            $slug = (string) $row['slug'];
            $title = (string) $row['title'];
            $sectionSlug = 'getting-started';
            foreach ($entryTax->termIdsForEntry($entryId) as $tid) {
                if (isset($termSlugById[$tid])) {
                    $sectionSlug = $termSlugById[$tid];
                    break;
                }
            }
            if (!isset($bySection[$sectionSlug])) {
                $bySection[$sectionSlug] = [
                    'section_slug' => $sectionSlug,
                    'section_label' => ucfirst(str_replace('-', ' ', $sectionSlug)),
                    'articles' => [],
                ];
            }

            $bySection[$sectionSlug]['articles'][] = [
                'entry_id' => $entryId,
                'title' => $title,
                'slug' => $slug,
                'summary' => $summaries[$entryId] ?? '',
                'edit_url' => $editUrlBuilder !== null ? $editUrlBuilder($typeId, $entryId) : null,
                'read_url' => $readUrlBuilder !== null ? $readUrlBuilder($slug) : null,
                'public_url' => $publicVisible ? '/' . KnowledgeBaseSettings::CONTENT_TYPE_SLUG . '/' . $slug : null,
            ];
        }

        foreach ($bySection as &$section) {
            usort($section['articles'], static fn (array $a, array $b): int => strcmp($a['title'], $b['title']));
        }
        unset($section);

        $ordered = [];
        foreach (array_keys(self::SECTION_LABELS) as $slug) {
            if (isset($bySection[$slug]) && $bySection[$slug]['articles'] !== []) {
                $ordered[] = $bySection[$slug];
            }
        }
        foreach ($bySection as $slug => $section) {
            if (!isset(self::SECTION_LABELS[$slug]) && $section['articles'] !== []) {
                $ordered[] = $section;
            }
        }

        return $ordered;
    }

    /**
     * @return array{
     *   entry_id: int,
     *   title: string,
     *   slug: string,
     *   summary: string,
     *   body: string,
     *   section_slug: string,
     *   section_label: string,
     *   edit_url: string|null,
     *   public_url: string|null,
     *   prev: array{slug: string, title: string}|null,
     *   next: array{slug: string, title: string}|null
     * }|null
     */
    public function articleBySlug(
        string $slug,
        ?callable $editUrlBuilder = null,
        bool $publicVisible = false
    ): ?array {
        $ctx = $this->kbTypeContext();
        if ($ctx === null) {
            return null;
        }

        $entries = new ContentEntryRepository($this->pdo);
        $entry = $entries->findByTypeAndSlug($ctx['type_id'], $slug);
        if ($entry === null) {
            return null;
        }

        $values = new ContentEntryValueRepository($this->pdo);
        $entryTax = new ContentEntryTaxonomyRepository($this->pdo);
        $entryId = $entry->id;

        $fieldValues = $values->valuesByFieldIdForEntry($entryId);
        $summary = $ctx['summary_field_id'] !== null
            ? ($fieldValues[$ctx['summary_field_id']] ?? '')
            : '';
        $body = $ctx['body_field_id'] !== null
            ? ($fieldValues[$ctx['body_field_id']] ?? '')
            : '';

        $sectionSlug = 'getting-started';
        foreach ($entryTax->termIdsForEntry($entryId) as $tid) {
            if (isset($ctx['term_slug_by_id'][$tid])) {
                $sectionSlug = $ctx['term_slug_by_id'][$tid];
                break;
            }
        }

        $sectionLabel = self::SECTION_LABELS[$sectionSlug]
            ?? ucfirst(str_replace('-', ' ', $sectionSlug));

        $prev = null;
        $next = null;
        foreach ($this->groupedForAdmin() as $section) {
            if ($section['section_slug'] !== $sectionSlug) {
                continue;
            }
            $articles = $section['articles'];
            foreach ($articles as $i => $article) {
                if ($article['slug'] !== $slug) {
                    continue;
                }
                if ($i > 0) {
                    $prev = [
                        'slug' => $articles[$i - 1]['slug'],
                        'title' => $articles[$i - 1]['title'],
                    ];
                }
                if ($i < count($articles) - 1) {
                    $next = [
                        'slug' => $articles[$i + 1]['slug'],
                        'title' => $articles[$i + 1]['title'],
                    ];
                }
                break 2;
            }
        }

        return [
            'entry_id' => $entryId,
            'title' => $entry->title,
            'slug' => $entry->slug,
            'summary' => $summary,
            'body' => $body,
            'section_slug' => $sectionSlug,
            'section_label' => $sectionLabel,
            'edit_url' => $editUrlBuilder !== null ? $editUrlBuilder($ctx['type_id'], $entryId) : null,
            'public_url' => $publicVisible ? '/' . KnowledgeBaseSettings::CONTENT_TYPE_SLUG . '/' . $entry->slug : null,
            'prev' => $prev,
            'next' => $next,
        ];
    }
}
