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
        return array_merge(
            self::gettingStarted(),
            self::generalUsers(),
            self::editors(),
            self::developers(),
            self::pluginDevelopers(),
            self::themeDevelopers(),
            self::commerce(),
        );
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function gettingStarted(): array
    {
        return [
            self::article(
                'getting-started',
                'introduction-to-struxa-cms',
                'Introduction to Struxa CMS',
                'What Struxa is, who it is for, and how the pieces fit together.',
                <<<'HTML'
<h2>What is Struxa?</h2>
<p><strong>Struxa CMS</strong> is a PHP content management system built on <strong>Slim 4</strong> and <strong>Twig</strong>. It targets teams who want structured content (custom types, fields, taxonomies), a full admin UI, optional commerce, headless APIs, and extension through <strong>plugins</strong> and <strong>themes</strong>—without running WordPress.</p>
<h2>Core ideas</h2>
<ul>
<li><strong>Content types</strong> — You define models (blog, product, guide, page-like entries) with typed fields instead of one generic “post” table.</li>
<li><strong>Themes</strong> — Storefront HTML/CSS/JS lives under <code>themes/{slug}/</code> with inheritance from parent themes.</li>
<li><strong>Plugins</strong> — Features ship as isolated packages under <code>plugins/{slug}/</code> with manifests, migrations, and optional routes.</li>
<li><strong>Settings &amp; menus</strong> — Site name, SEO defaults, and navigation are data-driven from the admin.</li>
<li><strong>APIs</strong> — JSON REST and GraphQL expose entries and pages for mobile apps or SPAs.</li>
</ul>
<h2>Typical workflow</h2>
<ol>
<li>Install Struxa and run <code>composer migrate</code>.</li>
<li>Activate the <strong>default</strong> theme (or install another from the catalog).</li>
<li>Create content types or import a <strong>blueprint</strong> (blog, product store).</li>
<li>Publish entries; optional commerce via Stripe.</li>
<li>Extend with plugins (SEO helper, mailing list, Knowledge Base, …).</li>
</ol>
<p>Official developer docs also live in the repository <code>docs/</code> folder; this Knowledge Base mirrors that material for your team inside the admin.</p>
HTML
            ),
            self::article(
                'getting-started',
                'server-requirements',
                'Server requirements',
                'PHP, extensions, database, and web server expectations.',
                <<<'HTML'
<h2>Minimum stack</h2>
<ul>
<li><strong>PHP</strong> 8.2 or newer</li>
<li><strong>MySQL</strong> 8+ (or compatible MariaDB)</li>
<li><strong>Composer</strong> 2.x for dependencies and migrations</li>
</ul>
<h2>Required PHP extensions</h2>
<ul>
<li><code>mbstring</code> — Unicode text handling</li>
<li><code>pdo_mysql</code> — Database access</li>
<li><code>json</code> — APIs and configuration</li>
<li><code>curl</code> — Outbound HTTP (updates, Stripe, AI tools)</li>
<li><code>gd</code> — Image compression and responsive thumbnails in the media library</li>
</ul>
<h2>Web server</h2>
<p>Point the document root at <code>public/</code> with <code>public/index.php</code> as the front controller. For local development you can use:</p>
<pre><code>composer serve</code></pre>
<p>which runs PHP’s built-in server on port 8080.</p>
<h2>Environment</h2>
<p>Copy <code>.env.example</code> to <code>.env</code> and configure database credentials, <code>PHPAUTH_SITE_KEY</code> (32+ characters in production), and <code>PHPAUTH_SITE_URL</code>. Never commit <code>.env</code> to version control.</p>
<h2>First-time database</h2>
<p>Either open <code>/install.php</code> in a browser on an empty database, or run <code>composer migrate</code>, then <strong>remove</strong> <code>public/install.php</code> on production sites.</p>
HTML
            ),
            self::article(
                'getting-started',
                'feature-overview',
                'Feature overview',
                'Extensive list of Struxa capabilities across admin, storefront, and integrations.',
                <<<'HTML'
<h2>Content &amp; editorial</h2>
<ul>
<li>Custom <strong>content types</strong> with many field types (text, textarea, richtext, number, boolean, select, URL, media, entry references, …)</li>
<li><strong>Taxonomies</strong> (categories, tags) per content type</li>
<li><strong>Revisions</strong> with compare and restore</li>
<li><strong>Scheduled</strong> publish and unpublish</li>
<li><strong>Trash</strong> and restore for entries</li>
<li><strong>Block builder</strong> on supported types</li>
<li><strong>Content lists</strong> — saved queries for Twig and APIs</li>
<li><strong>Blueprints</strong> — import/export site structure packages</li>
<li><strong>Config sync</strong> — diff and sync configuration between environments</li>
</ul>
<h2>Pages &amp; presentation</h2>
<ul>
<li>Static <strong>pages</strong> with optional block builder</li>
<li><strong>Theme</strong> catalog install, inheritance, and <code>theme.json</code> settings</li>
<li><strong>Menus</strong> for header/footer navigation</li>
<li>Marketing homepage via theme <code>page/home.twig</code> or a designated CMS page as site front</li>
</ul>
<h2>Media &amp; SEO</h2>
<ul>
<li><strong>Media library</strong> with folders, compression, and responsive variants</li>
<li>Per-entry and global <strong>SEO</strong> (title, description, Open Graph, Twitter, schema JSON)</li>
<li><strong>Sitemaps</strong> and robots.txt</li>
<li>SEO analysis helpers in admin</li>
</ul>
<h2>Users &amp; access</h2>
<ul>
<li>Staff accounts with <strong>roles</strong> and granular <strong>permissions</strong></li>
<li>PHPAuth-powered login, 2FA support, password reset</li>
<li>Customer accounts for commerce order history</li>
</ul>
<h2>Commerce</h2>
<ul>
<li>Sell <strong>content-type products</strong> via Stripe Checkout</li>
<li>Cart, coupons, tax, shipping zones</li>
<li>Digital downloads (file, URL, or unlocked entry)</li>
<li>Order admin, refunds, CSV export</li>
</ul>
<h2>Extensions &amp; integration</h2>
<ul>
<li><strong>Plugin</strong> system with manifests, capabilities, filter hooks, events, background jobs</li>
<li><strong>REST</strong> and <strong>GraphQL</strong> APIs with API keys</li>
<li><strong>Mobile bootstrap</strong> API for companion apps</li>
<li><strong>AI writing assistant</strong> (when configured)</li>
<li><strong>Jobs queue</strong> for async maintenance</li>
</ul>
<h2>Operations</h2>
<ul>
<li>SQL <strong>migrations</strong> (<code>composer migrate</code>)</li>
<li>Site health, maintenance tools, cache clear</li>
<li>Plugin/theme catalog from struxapoint.com</li>
<li>CI-friendly test and PHPStan scripts</li>
</ul>
HTML
            ),
            self::article(
                'getting-started',
                'installation-and-first-login',
                'Installation and first login',
                'Clone, configure, migrate, and sign in to the admin.',
                <<<'HTML'
<h2>Quick start</h2>
<pre><code>git clone https://github.com/struxa/struxa.git
cd struxa
cp .env.example .env
# Edit DB_* and PHPAUTH_* values
composer install
composer plugin-deps:prod
composer migrate</code></pre>
<h2>Web root</h2>
<p>Configure Apache/Nginx/Caddy so the site root is <code>public/</code>. All requests should route to <code>index.php</code>.</p>
<h2>First admin user</h2>
<p>After migrations, register the first staff user via the installer or admin registration flow (depending on your deployment). Assign a role with full permissions (e.g. Administrator).</p>
<h2>Post-install checklist</h2>
<ul>
<li>Set <strong>Settings</strong> → site name, default meta title/description</li>
<li>Activate a <strong>theme</strong></li>
<li>Configure <strong>Menus</strong> for header/footer</li>
<li>Remove <code>public/install.php</code> on production</li>
<li>Run <code>composer test</code> to verify the environment</li>
</ul>
HTML
            ),
        ];
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function generalUsers(): array
    {
        return [
            self::article('general-users', 'admin-navigation-overview', 'Admin navigation overview', 'How the admin sidebar is organized.', <<<'HTML'
<h2>Main areas</h2>
<ul>
<li><strong>Dashboard</strong> — Overview stats and shortcuts</li>
<li><strong>Content</strong> — Entries grouped by content type</li>
<li><strong>Pages</strong> — Standalone site pages</li>
<li><strong>Media</strong> — Uploads and folders</li>
<li><strong>Commerce</strong> — Orders, coupons, settings (when enabled)</li>
<li><strong>Appearance</strong> — Themes and menus</li>
<li><strong>Extensions</strong> — Plugins (install, activate, settings)</li>
<li><strong>Tools</strong> — Blueprints, import/export, maintenance, site health</li>
<li><strong>Settings</strong> — Global site configuration</li>
<li><strong>System</strong> — Users, roles, API keys, updates</li>
</ul>
<p>Plugin screens appear under <strong>Extensions</strong>, often nested under the plugin name when <code>nested_admin_nav</code> is set in <code>plugin.json</code>.</p>
HTML),
            self::article('general-users', 'users-roles-and-permissions', 'Users, roles, and permissions', 'Who can do what in the admin.', <<<'HTML'
<h2>Staff users</h2>
<p>CMS staff sign in at <code>/admin</code>. Each user has one or more <strong>roles</strong>; roles grant <strong>permissions</strong> such as <code>edit_content</code>, <code>manage_content_types</code>, or <code>manage_plugins</code>.</p>
<h2>Best practice</h2>
<ul>
<li>Give editors only content permissions they need</li>
<li>Restrict plugin installation to technical roles</li>
<li>Enable <strong>two-factor authentication</strong> for privileged accounts</li>
</ul>
<h2>Customers</h2>
<p>Storefront customers (commerce) use PHPAuth accounts on the public site for order history—not the CMS staff admin.</p>
HTML),
            self::article('general-users', 'site-settings-and-branding', 'Site settings and branding', 'Global settings that affect SEO and layout.', <<<'HTML'
<h2>Settings screen</h2>
<p><strong>Admin → Settings</strong> stores key/value pairs in <code>cms_settings</code>. Common keys include site name, tagline, default meta title/description, and active theme.</p>
<h2>Branding</h2>
<p>Upload a logo via the media library and reference it in theme settings or templates. Theme-specific options live in <code>theme.json</code> and resolve to <code>theme_settings</code> in Twig.</p>
<h2>Homepage</h2>
<p>You can set a published CMS page as the public homepage, or use the active theme’s <code>page/home.twig</code> when no page is designated.</p>
HTML),
            self::article('general-users', 'menus-and-navigation', 'Menus and navigation', 'Building header and footer links.', <<<'HTML'
<h2>Menu locations</h2>
<p>Under <strong>Appearance → Menus</strong>, assign items to locations such as <code>header</code> and <code>footer</code>. Themes read these via Twig globals.</p>
<h2>Item types</h2>
<ul>
<li>Custom URL</li>
<li>Link to a CMS page</li>
<li>Link to a content entry (when the type has a public route)</li>
</ul>
<p>Plugins may add items through the <code>menu.items</code> filter hook.</p>
HTML),
            self::article('general-users', 'pages-vs-content', 'Pages vs content entries', 'When to use Pages versus Content types.', <<<'HTML'
<h2>Pages</h2>
<p><strong>Pages</strong> are ideal for fixed site structure: About, Contact, legal text. They support block builder layouts and optional SEO fields.</p>
<h2>Content types</h2>
<p><strong>Content types</strong> suit repeating models: blog posts, products, guides, documentation articles. Each type has its own fields and optional archive URL (<code>/{typeSlug}</code>).</p>
<h2>Choosing</h2>
<p>If you need many similar items with custom fields and listings, use a content type. If you need one-off layouts, use a page.</p>
HTML),
            self::article('general-users', 'media-library', 'Media library', 'Uploading and reusing images and files.', <<<'HTML'
<h2>Uploads</h2>
<p>Files land under <code>public/uploads/</code> (excluded from git). The admin media UI supports folders, search, and picking assets for featured images or richtext embeds.</p>
<h2>Processing</h2>
<p>With the <code>gd</code> extension enabled, Struxa can compress uploads and generate responsive image variants for faster storefronts.</p>
<h2>Permissions</h2>
<p>Roles without media upload permission can still select existing assets where editors allow it.</p>
HTML),
            self::article('general-users', 'search-and-sitemaps', 'Search and sitemaps', 'Helping visitors and search engines find content.', <<<'HTML'
<h2>Sitemaps</h2>
<p>Published entries and pages with public routes are included in the XML sitemap. Warm or rebuild sitemaps from maintenance tools when large imports complete.</p>
<h2>SEO defaults</h2>
<p>Set global meta title and description under Settings so empty entries still produce reasonable snippets.</p>
<h2>On-site search</h2>
<p>Core provides search routes; themes can style results. For advanced search, consider a plugin or headless integration.</p>
HTML),
            self::article('general-users', 'backups-and-maintenance', 'Backups and maintenance', 'Keeping the site healthy.', <<<'HTML'
<h2>Backups</h2>
<ul>
<li>Database — regular mysqldump or managed host backups</li>
<li>Files — <code>public/uploads/</code> and any custom <code>storage/</code> data</li>
<li>Code — git for core, themes, and plugins</li>
</ul>
<h2>Maintenance tools</h2>
<p><strong>Admin → Tools → Maintenance</strong> includes cache clear, scheduled job hints, and health checks. Run <code>composer cache:clear</code> from CLI when deploying.</p>
<h2>Updates</h2>
<p>Apply Struxa CMS updates via the in-app updater or documented ZIP merge process; bump only upstream version files when merging a release.</p>
HTML),
        ];
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function editors(): array
    {
        return [
            self::article('editors', 'understanding-content-types', 'Understanding content types', 'The building blocks of structured content.', <<<'HTML'
<h2>Definition</h2>
<p>A <strong>content type</strong> is a template for entries: name, URL slug, flags (public route, SEO, featured image), and a set of <strong>fields</strong>.</p>
<h2>Examples</h2>
<ul>
<li><strong>Blog</strong> — title, excerpt, body, author</li>
<li><strong>Product</strong> — price, SKU, stock, digital delivery fields</li>
<li><strong>Knowledge Base</strong> — summary, body, section taxonomy</li>
</ul>
<p>Manage types under <strong>Content → Content types</strong> if you have the <code>manage_content_types</code> permission.</p>
HTML),
            self::article('editors', 'creating-and-editing-entries', 'Creating and editing entries', 'Day-to-day editorial workflow.', <<<'HTML'
<h2>Create</h2>
<ol>
<li>Go to <strong>Content</strong> and pick a type</li>
<li>Click <strong>New entry</strong></li>
<li>Fill required fields; set status to <strong>Published</strong> when ready</li>
</ol>
<h2>Slug</h2>
<p>Each entry has a URL slug (auto-generated from title). Keep slugs short, lowercase, and hyphenated.</p>
<h2>Featured image</h2>
<p>When the type supports it, pick a media asset for cards and social previews.</p>
HTML),
            self::article('editors', 'taxonomies-categories-and-tags', 'Taxonomies, categories, and tags', 'Organizing entries with terms.', <<<'HTML'
<h2>Per-type taxonomies</h2>
<p>Each content type can have vocabularies (e.g. Categories, Tags). Terms attach to entries in the entry editor.</p>
<h2>Hierarchical categories</h2>
<p>Category-style taxonomies support parent/child terms for nested navigation.</p>
<h2>Public archives</h2>
<p>When the content type has a public route, taxonomy archives may be available at <code>/{type}/{taxonomy}/{term}</code> depending on theme templates.</p>
HTML),
            self::article('editors', 'seo-and-metadata-for-editors', 'SEO and metadata for editors', 'Titles, descriptions, and social cards.', <<<'HTML'
<h2>Per-entry SEO</h2>
<p>On types with <strong>supports_seo</strong>, fill SEO title and description. If left blank, the entry title and summary often serve as fallbacks.</p>
<h2>Social</h2>
<p>Open Graph and Twitter fields control how links appear when shared. Use the featured image when possible.</p>
<h2>Noindex</h2>
<p>Enable <strong>noindex</strong> for thank-you pages or internal-only public URLs you do not want indexed.</p>
HTML),
            self::article('editors', 'revisions-and-workflow', 'Revisions and workflow', 'Drafts, review, and history.', <<<'HTML'
<h2>Statuses</h2>
<ul>
<li><strong>Draft</strong> — not public</li>
<li><strong>Published</strong> — visible when publish date is reached</li>
<li><strong>In review</strong> — optional editorial state (permission-gated)</li>
</ul>
<h2>Revisions</h2>
<p>Open the revisions panel on an entry to compare versions and restore older content. Requires appropriate permissions.</p>
<h2>Scheduling</h2>
<p>Set future <strong>published_at</strong> or <strong>scheduled unpublish</strong> dates; the job worker applies them on schedule.</p>
HTML),
            self::article('editors', 'blueprints-import-export', 'Blueprints import and export', 'Moving structure between sites.', <<<'HTML'
<h2>Blueprints</h2>
<p>JSON packages can describe content types, fields, taxonomies, sample entries, menus, and settings. Import from <strong>Tools → Blueprints</strong>.</p>
<h2>Use cases</h2>
<ul>
<li>Spin up a demo blog or product store quickly</li>
<li>Clone structure from staging to production (entries optional)</li>
</ul>
<p>Always review imported slugs for conflicts with existing types.</p>
HTML),
            self::article('editors', 'content-lists-and-entry-refs', 'Content lists and entry references', 'Relating content and powering dynamic blocks.', <<<'HTML'
<h2>Content lists</h2>
<p>Saved filters (type, taxonomy, sort) usable in Twig and APIs—similar to “views” in other CMSs. See <code>docs/content-lists.md</code>.</p>
<h2>Entry references</h2>
<p>Fields of type <code>entry_refs</code> link entries to other entries (related articles, featured products). The picker searches published entries by type.</p>
HTML),
        ];
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function developers(): array
    {
        return [
            self::article('developers', 'developer-getting-started', 'Developer getting started', 'Where code lives and how requests flow.', <<<'HTML'
<h2>Repository layout</h2>
<ul>
<li><code>public/</code> — Web root</li>
<li><code>src/</code> — Application (<code>App\</code> namespace)</li>
<li><code>routes/</code> — Route groups included from bootstrap</li>
<li><code>templates/</code> — Core admin and marketing Twig</li>
<li><code>themes/</code> — Storefront themes</li>
<li><code>plugins/</code> — Extensions</li>
<li><code>database/migrations/</code> — Core schema</li>
</ul>
<h2>Request lifecycle</h2>
<p><code>public/index.php</code> loads Composer, <code>.env</code>, connects MySQL, boots settings, builds Slim, registers routes (core → plugin public → plugin admin → content catch-alls), then dispatches.</p>
HTML),
            self::article('developers', 'project-structure-and-routing', 'Project structure and routing', 'Slim routes and middleware.', <<<'HTML'
<h2>Route files</h2>
<p>Core groups live in <code>routes/*.php</code>. Plugins add <code>routes/public.php</code> and <code>routes/admin.php</code> receiving <code>(App $app, PluginBootContext $ctx)</code>.</p>
<h2>Middleware</h2>
<p>Staff routes use <code>RequireCmsStaff</code>. CSRF tokens protect POST forms. Permissions gate sensitive actions.</p>
<h2>Example: named route</h2>
<pre><code>$group->get('/my-tool', $handler)-&gt;setName('admin.my_tool');</code></pre>
HTML),
            self::article('developers', 'database-migrations-cli', 'Database migrations and CLI', 'Schema changes and bin/cms.php.', <<<'HTML'
<h2>Migrations</h2>
<p>Ordered <code>.sql</code> files in <code>database/migrations/</code>. Run:</p>
<pre><code>composer migrate</code></pre>
<h2>CLI</h2>
<pre><code>php bin/cms.php</code></pre>
<p>Subcommands include job workers, cache tools, and plugin diagnostics depending on your build.</p>
<h2>Plugin migrations</h2>
<p>Plugin SQL runs on activation into <code>cms_plugin_migrations</code>.</p>
HTML),
            self::article('developers', 'filter-hooks-and-events', 'Filter hooks and events', 'Extending behavior without forking core.', <<<'HTML'
<h2>Filters</h2>
<p>Transform data as it flows through core—SEO meta, menus, API payloads, HTML sanitization. Register in plugin <code>boot()</code>:</p>
<pre><code>use App\Filter\FilterHook;

$context->addFilter(FilterHook::MENU_ITEMS, function (array $items, array $ctx): array {
    $items[] = ['label' => 'Status', 'href' => '/status', 'target' => '', 'css_class' => ''];
    return $items;
}, 10);</code></pre>
<h2>Events</h2>
<p>React to saves and logins with <code>$context->listenEvent(ContentEntrySavedEvent::class, …)</code>. Declare hooks in <code>plugin.json</code> when using strict capabilities.</p>
HTML),
            self::article('developers', 'rest-and-graphql-api', 'REST and GraphQL API', 'Headless access to content.', <<<'HTML'
<h2>API keys</h2>
<p>Create keys under <strong>System → API keys</strong>. Send the key header documented in your deployment.</p>
<h2>REST</h2>
<p>JSON endpoints expose entries, pages, and lists for mobile or JavaScript frontends.</p>
<h2>GraphQL</h2>
<p>Query structured content with the bundled GraphQL server when enabled. Use filters <code>api.entry.response</code> to enrich payloads from plugins.</p>
HTML),
        ];
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function pluginDevelopers(): array
    {
        return [
            self::article('plugin-developers', 'creating-a-plugin', 'Creating a plugin', 'From folder to activation.', <<<'HTML'
<h2>Layout</h2>
<pre><code>plugins/my-plugin/
  plugin.json
  src/MyPluginServiceProvider.php
  routes/admin.php
  routes/public.php   # optional
  views/
  migrations/</code></pre>
<h2>Slug rules</h2>
<p>Directory name must match <code>plugin.json</code> → <code>slug</code> (lowercase, hyphens).</p>
<h2>Activate</h2>
<p><strong>Extensions → Plugins → Activate</strong> runs migrations and calls <code>boot()</code>.</p>
HTML),
            self::article('plugin-developers', 'plugin-manifest-and-capabilities', 'Plugin manifest and capabilities', 'Declaring requirements and permissions.', <<<'HTML'
<h2>Manifest</h2>
<p><code>plugin.json</code> includes <code>requires_cms_version</code>, <code>requires_php</code>, <code>requires_plugins</code>, <code>capabilities</code>, and <code>hooks</code>.</p>
<h2>Capabilities</h2>
<ul>
<li><code>database.read</code> / <code>database.write</code> — PDO access</li>
<li><code>admin.nav</code> — Admin routes and menu items</li>
<li><code>frontend.render</code> — Public routes and reserved slugs</li>
<li><code>settings.write</code> — Persist plugin settings</li>
</ul>
<p>Undeclared plugins run in legacy permissive mode but should still declare hooks they use.</p>
HTML),
            self::article('plugin-developers', 'plugin-routes-and-admin-nav', 'Plugin routes and admin navigation', 'Wiring screens into the admin.', <<<'HTML'
<h2>Admin routes</h2>
<pre><code>return function (App $app, PluginBootContext $ctx): void {
    $app->group('/admin', function ($group) use ($ctx) {
        $group->get('/my-plugin', $handler)-&gt;setName('plugin.my_plugin.admin');
    })-&gt;add(new RequireCmsStaff($ctx->auth(), $ctx->pdo()));
};</code></pre>
<h2>Nav item</h2>
<pre><code>$context->registerAdminNavItem('My plugin', 'plugin.my_plugin.admin');</code></pre>
<h2>Twig</h2>
<p>Render with <code>@plugin_my_plugin/...</code> namespace mapped to <code>views/</code>.</p>
HTML),
            self::article('plugin-developers', 'plugin-migrations-and-jobs', 'Plugin migrations and background jobs', 'SQL on activate and async work.', <<<'HTML'
<h2>Migrations</h2>
<p>Place <code>.sql</code> files in <code>migrations/</code>; they run once per site on activation. Document tables in <code>plugin.json</code> → <code>database.tables</code>.</p>
<h2>Jobs</h2>
<pre><code>$context->registerJobHandler('my-plugin.rebuild', function (Job $job, JobHandlerContext $ctx): array {
    // work…
    return ['ok' => true, 'message' => 'Done'];
});</code></pre>
<p>Cron: <code>php bin/cms.php jobs:dispatch &amp;&amp; php bin/cms.php jobs:work</code></p>
HTML),
        ];
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function themeDevelopers(): array
    {
        return [
            self::article('theme-developers', 'creating-a-theme', 'Creating a theme', 'theme.json, views, and assets.', <<<'HTML'
<h2>Structure</h2>
<pre><code>themes/my-theme/
  theme.json
  views/
  assets/</code></pre>
<h2>Activation</h2>
<p>Install from zip or copy into <code>themes/</code>, then activate under <strong>Appearance → Themes</strong>. Slug in <code>theme.json</code> must match the folder name.</p>
HTML),
            self::article('theme-developers', 'theme-inheritance-and-assets', 'Theme inheritance and assets', 'Parents, theme_asset(), and the storefront boundary.', <<<'HTML'
<h2>Inheritance</h2>
<p>List parent slugs in <code>theme.json</code>. Twig resolves templates child-first, then parents, then core.</p>
<h2>Assets</h2>
<pre><code>&lt;link rel="stylesheet" href="{{ theme_asset('css/app.css') }}" /&gt;</code></pre>
<h2>Storefront boundary</h2>
<p>Never link core marketing <code>/css/styles.css</code> from theme templates. Storefront pages must be portable across theme swaps.</p>
HTML),
            self::article('theme-developers', 'content-type-templates', 'Content type templates', 'Overriding index and show templates.', <<<'HTML'
<h2>Resolution order</h2>
<ul>
<li><code>content/{typeSlug}/show.twig</code></li>
<li><code>content/show.twig</code> (fallback)</li>
</ul>
<p>Same pattern for <code>index.twig</code> and taxonomy archives.</p>
<h2>Variables</h2>
<p>Templates receive the entry, field map, taxonomies, and SEO meta. Use <code>theme_settings</code> for theme options.</p>
HTML),
        ];
    }

    /**
     * @return list<array{title: string, slug: string, section: string, summary: string, body: string}>
     */
    private static function commerce(): array
    {
        return [
            self::article('commerce', 'ecommerce-setup-overview', 'E-commerce setup overview', 'End-to-end commerce checklist.', <<<'HTML'
<h2>Prerequisites</h2>
<ul>
<li>Run <code>composer migrate</code></li>
<li>Import the <strong>Product store</strong> blueprint or create a product content type manually</li>
<li>Stripe account with API keys</li>
</ul>
<h2>Admin steps</h2>
<ol>
<li><strong>Commerce → Settings</strong> — enable commerce, set product type slug, currency</li>
<li>Add Stripe secret, publishable, and webhook signing keys</li>
<li>Configure tax, shipping, and emails as needed</li>
<li>Create products as published entries with price fields</li>
<li>Add webhook endpoint <code>/commerce/stripe/webhook</code> in Stripe</li>
</ol>
HTML),
            self::article('commerce', 'stripe-and-commerce-settings', 'Stripe and commerce settings', 'Keys, webhooks, and configuration.', <<<'HTML'
<h2>Stripe keys</h2>
<p>Use test keys in staging. Store live keys only in production <code>.env</code> or encrypted settings—never in git.</p>
<h2>Webhooks</h2>
<p>Subscribe to <code>checkout.session.completed</code> and <code>checkout.session.expired</code>. Webhooks fulfill inventory, digital grants, and emails if the customer never returns to the success URL.</p>
<h2>Coupons &amp; shipping</h2>
<p>Manage under <strong>Commerce → Coupons</strong> and shipping zones when flat-rate or zone shipping is enabled.</p>
HTML),
            self::article('commerce', 'products-cart-and-checkout', 'Products, cart, and checkout', 'Storefront purchase flow.', <<<'HTML'
<h2>Product fields</h2>
<p>Convention includes <code>price_cents</code>, <code>purchasable</code>, optional <code>stripe_price_id</code>, <code>sku</code>, <code>stock_qty</code>.</p>
<h2>Storefront</h2>
<ul>
<li><code>/shop</code> — product grid</li>
<li><code>/commerce/cart</code> — cart management</li>
<li>POST checkout routes require CSRF tokens</li>
</ul>
<h2>Buy now vs cart</h2>
<p>Single-item Stripe Checkout vs multi-item cart checkout. Coupons apply to cart lines using local <code>price_cents</code> pricing.</p>
HTML),
            self::article('commerce', 'digital-downloads-and-orders', 'Digital downloads and orders', 'Fulfillment after payment.', <<<'HTML'
<h2>Digital products</h2>
<p>Set <code>digital_file</code> (media ID), <code>digital_url</code>, or <code>digital_entry_slug</code>. Priority: file → URL → entry.</p>
<h2>Customer access</h2>
<ul>
<li>Tokenized link <code>/commerce/access/{token}</code></li>
<li>Account order history when logged in</li>
<li>Email links in confirmation messages</li>
</ul>
<h2>Admin</h2>
<p>Refund orders from order detail; grants revoke automatically. Export orders to CSV for accounting.</p>
HTML),
        ];
    }

    /**
     * @param non-empty-string $section
     * @param non-empty-string $slug
     * @return array{title: string, slug: string, section: string, summary: string, body: string}
     */
    private static function article(string $section, string $slug, string $title, string $summary, string $body): array
    {
        return [
            'title' => $title,
            'slug' => $slug,
            'section' => $section,
            'summary' => $summary,
            'body' => $body,
        ];
    }
}
