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
     * @return list<array{
     *   section_slug: string,
     *   section_label: string,
     *   articles: list<array{entry_id: int, title: string, slug: string, summary: string, edit_url: string|null, public_url: string|null}>
     * }>
     */
    public function groupedForAdmin(?callable $editUrlBuilder = null, bool $publicVisible = false): array
    {
        $types = new ContentTypeRepository($this->pdo);
        $type = $types->findBySlug(KnowledgeBaseSettings::CONTENT_TYPE_SLUG);
        if ($type === null) {
            return [];
        }

        $fields = new ContentFieldRepository($this->pdo);
        $summaryFieldId = null;
        foreach ($fields->forTypeOrdered($type->id) as $field) {
            if ($field->fieldKey === 'summary') {
                $summaryFieldId = $field->id;
                break;
            }
        }

        $entries = new ContentEntryRepository($this->pdo);
        $values = new ContentEntryValueRepository($this->pdo);
        $entryTax = new ContentEntryTaxonomyRepository($this->pdo);
        $taxonomyRepo = new TaxonomyRepository($this->pdo);
        $termRepo = new TaxonomyTermRepository($this->pdo);

        $taxonomy = $taxonomyRepo->findByContentTypeAndSlug($type->id, 'kb-section');
        $termSlugById = [];
        if ($taxonomy !== null) {
            foreach ($termRepo->forTaxonomyOrdered($taxonomy->id) as $term) {
                $termSlugById[$term->id] = $term->slug;
            }
        }

        $bySection = [];
        foreach (self::SECTION_LABELS as $slug => $label) {
            $bySection[$slug] = [
                'section_slug' => $slug,
                'section_label' => $label,
                'articles' => [],
            ];
        }

        $rows = $entries->forTypeOrdered($type->id, 500);
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
                'edit_url' => $editUrlBuilder !== null ? $editUrlBuilder($type->id, $entryId) : null,
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
}
