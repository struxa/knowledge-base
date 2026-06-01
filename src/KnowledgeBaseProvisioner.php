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

final class KnowledgeBaseProvisioner
{
    private readonly ContentTypeRepository $types;

    private readonly ContentFieldRepository $fields;

    private readonly ContentEntryRepository $entries;

    private readonly ContentEntryValueRepository $values;

    private readonly TaxonomyRepository $taxonomies;

    private readonly TaxonomyTermRepository $terms;

    private readonly ContentEntryTaxonomyRepository $entryTaxonomies;

    public function __construct(
        private readonly PDO $pdo,
    ) {
        $this->types = new ContentTypeRepository($pdo);
        $this->fields = new ContentFieldRepository($pdo);
        $this->entries = new ContentEntryRepository($pdo);
        $this->values = new ContentEntryValueRepository($pdo);
        $this->taxonomies = new TaxonomyRepository($pdo);
        $this->terms = new TaxonomyTermRepository($pdo);
        $this->entryTaxonomies = new ContentEntryTaxonomyRepository($pdo);
    }

    /**
     * @return array{seeded: int, skipped: int, errors: list<string>}
     */
    public function seedWikiArticlesIfNeeded(): array
    {
        if (KnowledgeBaseSettings::isWikiSeeded($this->pdo)) {
            return ['seeded' => 0, 'skipped' => 0, 'errors' => []];
        }

        $type = $this->types->findBySlug(KnowledgeBaseSettings::CONTENT_TYPE_SLUG);
        if ($type === null) {
            return ['seeded' => 0, 'skipped' => 0, 'errors' => ['Knowledge Base content type (kb) is missing. Activate the plugin to run migrations.']];
        }

        $fieldIds = $this->fieldIdsForType($type->id);
        if (!isset($fieldIds['body'], $fieldIds['summary'])) {
            return ['seeded' => 0, 'skipped' => 0, 'errors' => ['Knowledge Base fields (body, summary) are missing.']];
        }

        $sectionTermIds = $this->sectionTermIdsBySlug($type->id);
        $seeded = 0;
        $skipped = 0;
        $errors = [];

        foreach (KnowledgeBaseArticleCatalog::articles() as $article) {
            $slug = (string) ($article['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            if ($this->entries->findByTypeAndSlug($type->id, $slug) !== null) {
                ++$skipped;
                continue;
            }

            $sectionSlug = (string) ($article['section'] ?? '');
            $termId = $sectionTermIds[$sectionSlug] ?? null;

            try {
                $entryId = $this->entries->insert(
                    $type->id,
                    (string) ($article['title'] ?? $slug),
                    $slug,
                    'published',
                    null,
                    (string) ($article['title'] ?? $slug),
                    (string) ($article['summary'] ?? ''),
                    null,
                    null,
                    false,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    gmdate('Y-m-d H:i:s'),
                    null,
                    null,
                    null,
                );

                $this->values->upsert($entryId, $fieldIds['summary'], (string) ($article['summary'] ?? ''));
                $this->values->upsert($entryId, $fieldIds['body'], (string) ($article['body'] ?? ''));

                if ($termId !== null) {
                    $this->entryTaxonomies->replaceForEntry($entryId, [$termId]);
                }

                ++$seeded;
            } catch (\Throwable $e) {
                $errors[] = $slug . ': ' . $e->getMessage();
            }
        }

        if ($errors === []) {
            KnowledgeBaseSettings::markWikiSeeded($this->pdo);
        }

        return ['seeded' => $seeded, 'skipped' => $skipped, 'errors' => $errors];
    }

    public function setPublicVisible(bool $visible): void
    {
        $type = $this->types->findBySlug(KnowledgeBaseSettings::CONTENT_TYPE_SLUG);
        if ($type === null) {
            return;
        }

        $this->types->update(
            $type->id,
            $type->name,
            $type->slug,
            $type->icon,
            $type->description,
            $visible,
            $type->supportsSeo,
            $type->supportsFeaturedImage,
            $type->supportsBlockBuilder,
        );

        KnowledgeBaseSettings::setPublicVisible($this->pdo, $visible);
    }

    /**
     * @return array<string, int>
     */
    private function fieldIdsForType(int $typeId): array
    {
        $out = [];
        foreach ($this->fields->forTypeOrdered($typeId) as $field) {
            $out[$field->fieldKey] = $field->id;
        }

        return $out;
    }

    /**
     * @return array<string, int>
     */
    private function sectionTermIdsBySlug(int $typeId): array
    {
        $taxonomy = $this->taxonomies->findByContentTypeAndSlug($typeId, 'kb-section');
        if ($taxonomy === null) {
            return [];
        }

        $out = [];
        foreach ($this->terms->forTaxonomyOrdered($taxonomy->id) as $term) {
            $out[$term->slug] = $term->id;
        }

        return $out;
    }
}
