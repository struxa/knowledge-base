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
    private ContentTypeRepository $types;

    private ContentFieldRepository $fields;

    private ContentEntryRepository $entries;

    private ContentEntryValueRepository $values;

    private TaxonomyRepository $taxonomies;

    private TaxonomyTermRepository $terms;

    private ContentEntryTaxonomyRepository $entryTaxonomies;

    public function __construct(
        private PDO $pdo,
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

        $articles = KnowledgeBaseArticleCatalog::articles();
        if ($articles === []) {
            return ['seeded' => 0, 'skipped' => 0, 'errors' => ['Wiki article data is missing (data/wiki-articles.json). Reinstall the plugin from the catalog.']];
        }

        $sectionTermIds = $this->sectionTermIdsBySlug($type->id);
        $seeded = 0;
        $skipped = 0;
        $errors = [];

        foreach ($articles as $article) {
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
                $entryId = $this->insertPublishedEntry(
                    $type->id,
                    (string) ($article['title'] ?? $slug),
                    $slug,
                    (string) ($article['title'] ?? $slug),
                    (string) ($article['summary'] ?? ''),
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

    private function insertPublishedEntry(
        int $contentTypeId,
        string $title,
        string $slug,
        string $seoTitle,
        string $seoDescription,
    ): int {
        $publishedAt = gmdate('Y-m-d H:i:s');
        $args = [
            'contentTypeId' => $contentTypeId,
            'title' => $title,
            'slug' => $slug,
            'status' => 'published',
            'featuredImageId' => null,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'focusKeyphrase' => null,
            'canonicalUrl' => null,
            'seoNoindex' => false,
            'ogTitle' => null,
            'ogDescription' => null,
            'ogImageId' => null,
            'twitterTitle' => null,
            'twitterDescription' => null,
            'twitterImageId' => null,
            'schemaJson' => null,
            'publishedAt' => $publishedAt,
            'scheduledPublishAt' => null,
            'scheduledUnpublishAt' => null,
            'createdBy' => null,
        ];

        $method = new \ReflectionMethod($this->entries, 'insert');
        $ordered = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (!array_key_exists($name, $args)) {
                if ($name === 'focusKeyphrase') {
                    continue;
                }
                throw new \RuntimeException('Unsupported ContentEntryRepository::insert signature.');
            }
            $ordered[] = $args[$name];
        }

        /** @var int $id */
        $id = $method->invoke($this->entries, ...$ordered);

        return $id;
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
