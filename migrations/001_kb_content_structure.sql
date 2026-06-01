-- Knowledge Base plugin — CMS content type, fields, and section taxonomy.
-- Articles are seeded from PHP (KnowledgeBaseProvisioner) on first admin visit.
-- has_public_route starts at 0; enable via Admin → Knowledge Base → Settings.

INSERT INTO cms_content_types
    (name, slug, icon, description, has_public_route, supports_seo, supports_featured_image, supports_block_builder)
SELECT 'Knowledge Base', 'kb', 'book',
       'Struxa CMS documentation articles. Managed by the Knowledge Base plugin; optional public URLs at /kb/{slug}.',
       0, 1, 0, 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM cms_content_types WHERE slug = 'kb');

INSERT INTO cms_content_fields
    (content_type_id, label, field_key, field_type, placeholder, help_text, is_required, sort_order)
SELECT t.id, 'Summary', 'summary', 'textarea',
       NULL,
       'Short excerpt for indexes and meta description fallback.',
       0, 5
FROM cms_content_types t
WHERE t.slug = 'kb'
  AND NOT EXISTS (
    SELECT 1 FROM cms_content_fields f
    WHERE f.content_type_id = t.id AND f.field_key = 'summary'
  );

INSERT INTO cms_content_fields
    (content_type_id, label, field_key, field_type, placeholder, help_text, is_required, sort_order)
SELECT t.id, 'Article body', 'body', 'richtext',
       NULL,
       'Full article HTML. Safe to edit after import.',
       1, 10
FROM cms_content_types t
WHERE t.slug = 'kb'
  AND NOT EXISTS (
    SELECT 1 FROM cms_content_fields f
    WHERE f.content_type_id = t.id AND f.field_key = 'body'
  );

INSERT INTO cms_taxonomies
    (content_type_id, name, slug, description, taxonomy_type, is_hierarchical)
SELECT t.id, 'KB section', 'kb-section',
       'Documentation audience or topic (Getting started, Developers, Editors, …).',
       'category', 1
FROM cms_content_types t
WHERE t.slug = 'kb'
  AND NOT EXISTS (
    SELECT 1 FROM cms_taxonomies tx
    WHERE tx.content_type_id = t.id AND tx.slug = 'kb-section'
  );

INSERT INTO cms_taxonomy_terms (taxonomy_id, name, slug, description, parent_id, sort_order)
SELECT tx.id, 'Getting started', 'getting-started', 'Introduction, requirements, and feature overview.', NULL, 10
FROM cms_taxonomies tx
INNER JOIN cms_content_types t ON t.id = tx.content_type_id AND t.slug = 'kb'
WHERE tx.slug = 'kb-section'
  AND NOT EXISTS (
    SELECT 1 FROM cms_taxonomy_terms tt WHERE tt.taxonomy_id = tx.id AND tt.slug = 'getting-started'
  );

INSERT INTO cms_taxonomy_terms (taxonomy_id, name, slug, description, parent_id, sort_order)
SELECT tx.id, 'General users', 'general-users', 'Day-to-day admin and site management.', NULL, 20
FROM cms_taxonomies tx
INNER JOIN cms_content_types t ON t.id = tx.content_type_id AND t.slug = 'kb'
WHERE tx.slug = 'kb-section'
  AND NOT EXISTS (
    SELECT 1 FROM cms_taxonomy_terms tt WHERE tt.taxonomy_id = tx.id AND tt.slug = 'general-users'
  );

INSERT INTO cms_taxonomy_terms (taxonomy_id, name, slug, description, parent_id, sort_order)
SELECT tx.id, 'Editors', 'editors', 'Content types, entries, taxonomies, SEO, and workflow.', NULL, 30
FROM cms_taxonomies tx
INNER JOIN cms_content_types t ON t.id = tx.content_type_id AND t.slug = 'kb'
WHERE tx.slug = 'kb-section'
  AND NOT EXISTS (
    SELECT 1 FROM cms_taxonomy_terms tt WHERE tt.taxonomy_id = tx.id AND tt.slug = 'editors'
  );

INSERT INTO cms_taxonomy_terms (taxonomy_id, name, slug, description, parent_id, sort_order)
SELECT tx.id, 'Developers', 'developers', 'Core architecture, CLI, APIs, and extension points.', NULL, 40
FROM cms_taxonomies tx
INNER JOIN cms_content_types t ON t.id = tx.content_type_id AND t.slug = 'kb'
WHERE tx.slug = 'kb-section'
  AND NOT EXISTS (
    SELECT 1 FROM cms_taxonomy_terms tt WHERE tt.taxonomy_id = tx.id AND tt.slug = 'developers'
  );

INSERT INTO cms_taxonomy_terms (taxonomy_id, name, slug, description, parent_id, sort_order)
SELECT tx.id, 'Plugin developers', 'plugin-developers', 'Building and shipping Struxa plugins.', NULL, 50
FROM cms_taxonomies tx
INNER JOIN cms_content_types t ON t.id = tx.content_type_id AND t.slug = 'kb'
WHERE tx.slug = 'kb-section'
  AND NOT EXISTS (
    SELECT 1 FROM cms_taxonomy_terms tt WHERE tt.taxonomy_id = tx.id AND tt.slug = 'plugin-developers'
  );

INSERT INTO cms_taxonomy_terms (taxonomy_id, name, slug, description, parent_id, sort_order)
SELECT tx.id, 'Theme developers', 'theme-developers', 'Themes, templates, assets, and storefront layout.', NULL, 60
FROM cms_taxonomies tx
INNER JOIN cms_content_types t ON t.id = tx.content_type_id AND t.slug = 'kb'
WHERE tx.slug = 'kb-section'
  AND NOT EXISTS (
    SELECT 1 FROM cms_taxonomy_terms tt WHERE tt.taxonomy_id = tx.id AND tt.slug = 'theme-developers'
  );

INSERT INTO cms_taxonomy_terms (taxonomy_id, name, slug, description, parent_id, sort_order)
SELECT tx.id, 'Commerce', 'commerce', 'E-commerce, Stripe, products, and orders.', NULL, 70
FROM cms_taxonomies tx
INNER JOIN cms_content_types t ON t.id = tx.content_type_id AND t.slug = 'kb'
WHERE tx.slug = 'kb-section'
  AND NOT EXISTS (
    SELECT 1 FROM cms_taxonomy_terms tt WHERE tt.taxonomy_id = tx.id AND tt.slug = 'commerce'
  );
