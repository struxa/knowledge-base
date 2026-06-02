#!/usr/bin/env python3
"""Regenerate KB article JSON with expanded feature examples. Run from plugin root."""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ARTICLES = ROOT / "data" / "articles"

FEATURE_GUIDE = {
    "title": "What each Struxa feature does",
    "slug": "what-each-feature-does",
    "section": "getting-started",
    "summary": "Plain-language guide to Struxa capabilities—with concrete examples of what you can build.",
    "body": r"""<h2>How to use this guide</h2>
<p>Each section explains <strong>what a feature is</strong>, <strong>what you can do with it</strong>, and <strong>where to configure it</strong> in the admin. For step-by-step workflows, open the linked topic in this Knowledge Base sidebar.</p>

<h2>Content types and custom fields</h2>
<p><strong>Content types</strong> are reusable models for your site—like “Blog post”, “Product”, “Team member”, or “Documentation article”. Each type has its own fields (text, rich text, numbers, media, entry links, …) instead of one generic “post” table.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Run a blog with excerpts, authors, and categories separate from product SKUs and prices.</li>
<li>Publish a review site where each review scores a product via an <code>entry_refs</code> field and a numeric <code>score</code> field.</li>
<li>Ship a knowledge base (like this one) with summary + HTML body and section taxonomy.</li>
<li>Expose any type on the web at <code>/{typeSlug}</code> and <code>/{typeSlug}/{entrySlug}</code> when <strong>public route</strong> is enabled.</li>
<li>Turn on SEO, featured images, or block builder per type—not globally.</li>
</ul>
<p><strong>Configure:</strong> <strong>Content → Content types</strong> (<code>/admin/content-types</code>). See <em>Understanding content types</em>.</p>

<h2>Content lists (saved queries)</h2>
<p><strong>Content lists</strong> are saved queries over content entries—similar to “views” in other CMSs. Filter by status, taxonomy term, and custom fields; sort and paginate; expose on the site, in Twig, or via the public API.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li><strong>Homepage “Top reviews”</strong> — List slug <code>top-reviews</code>: type <em>Review</em>, field filter <code>score</code> ≥ 4, sort <code>field:score</code> descending, embed in home Twig with <code>content_list('top-reviews', 6)</code>.</li>
<li><strong>Category spotlight</strong> — Filter by taxonomy term “Case studies” and show the six newest on a landing page block—no duplicate manual curation.</li>
<li><strong>Staff picks</strong> — Boolean field <code>featured</code> = true; list only featured guides; sort by <code>updated_at</code>.</li>
<li><strong>Public directory</strong> — Enable <strong>Public page</strong> at <code>/lists/partners</code> for a filterable partner grid without building a custom content type archive.</li>
<li><strong>Headless/mobile feed</strong> — Enable REST on the same list; your app calls <code>GET /api/v1/content-lists/featured-guides</code> with a read-scoped API key.</li>
<li><strong>Related products</strong> — On a blog post template, embed a list filtered by a shared tag term to cross-sell without hard-coding product IDs.</li>
</ul>
<p><strong>Configure:</strong> <strong>Content → Content lists</strong> (<code>/admin/content-lists</code>). See <em>Content lists</em> for operators, Twig, and API detail.</p>

<h2>Taxonomies (categories and tags)</h2>
<p><strong>Taxonomies</strong> group entries within a content type—categories, tags, regions, documentation sections, or any custom vocabulary.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Organize blog posts under hierarchical categories (News → Product updates).</li>
<li>Tag reviews with product lines and build taxonomy archives at <code>/review/category/{term}</code>.</li>
<li>Drive KB sidebar navigation from the <em>KB section</em> taxonomy (Getting started, Editors, Developers).</li>
<li>Combine taxonomies with content lists (“latest in Category X”).</li>
</ul>
<p><strong>Configure:</strong> per type under <strong>Content types → Edit → Taxonomies</strong>, or <strong>Content → Taxonomies</strong>.</p>

<h2>Entry references (entry_refs)</h2>
<p><strong>Entry links</strong> fields let one entry point at other published entries—related articles, linked products, author profiles, locations.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>On a review, link to the product entry it discusses (single or multi-select).</li>
<li>On a case study, link to three testimonial entries and render them as cards via <code>entry_refs_resolve()</code>.</li>
<li>Require linked targets to be public before publish—editorial guardrail for go-live.</li>
<li>Expose resolved links in REST entry payloads as <code>referenced_entries</code> for mobile apps.</li>
</ul>
<p><strong>Configure:</strong> add an <code>entry_refs</code> field on the content type. See <em>Entry references</em>.</p>

<h2>Block builder</h2>
<p>The <strong>block builder</strong> composes pages and entries from reusable sections—heroes, feature grids, FAQs, pricing tables, rich text—without writing HTML.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Build a marketing homepage on a <strong>Page</strong> with hero + testimonials + CTA blocks.</li>
<li>Add a long-form case study layout on a <em>Case study</em> content type while keeping structured metadata fields.</li>
<li>Save a pricing block as a <strong>pattern</strong> and reuse it across landing pages.</li>
<li>Mix block sections with custom fields on the same entry (SEO title in fields, layout in blocks).</li>
</ul>
<p><strong>Configure:</strong> enable block builder on the type or use <strong>Pages</strong> → open builder at <code>/admin/pages/{id}/builder</code>.</p>

<h2>Pages vs content entries</h2>
<p><strong>Pages</strong> are one-off URLs (<code>/p/about</code>) with block builder—ideal for About, Contact, legal. <strong>Content entries</strong> are repeating items with archives and APIs.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Set a CMS page as the public homepage (Settings) while blog posts live under <code>/blog</code>.</li>
<li>Keep legal and contact pages out of content-type archives.</li>
<li>Use entries when you need fifty similar items, taxonomies, or commerce fields.</li>
</ul>

<h2>Revisions, drafts, and scheduling</h2>
<p>Every save can snapshot history; schedule future publish and unpublish dates processed by background jobs.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Compare two versions of a page before restoring yesterday’s hero copy.</li>
<li>Keep drafts invisible while editors collaborate; move to <strong>In review</strong> if your workflow uses it.</li>
<li>Schedule a product launch for midnight without staying online to click Publish.</li>
</ul>
<p><strong>Configure:</strong> entry editor sidebar → <strong>Revisions</strong>; publish dates on the entry aside.</p>

<h2>Blueprints and config sync</h2>
<p><strong>Blueprints</strong> import whole site packages (types, menus, sample entries, media seeds). <strong>Config sync</strong> moves schema between environments with a diff preview—no sample content.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Spin up a review-site or product-store demo on staging in one import.</li>
<li>Promote content-type field changes from production to staging without copying customer orders.</li>
<li>Document required plugins in a blueprint so activation warns if Forum or Commerce is missing.</li>
</ul>
<p><strong>Configure:</strong> <strong>Tools → Blueprints</strong> and <strong>Tools → Config sync</strong> (needs <code>manage_portability</code>).</p>

<h2>Media library</h2>
<p>Central storage for images and files used in featured images, richtext, block builder heroes, and digital products.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Upload once, reuse across many entries via the media picker.</li>
<li>Organize assets in folders for large teams.</li>
<li>Attach a file as a commerce <strong>digital download</strong> delivered after purchase.</li>
</ul>
<p><strong>Configure:</strong> <strong>Media</strong> (<code>/admin/media</code>).</p>

<h2>SEO and discoverability</h2>
<p>Per-entry and global SEO fields feed meta tags, Open Graph, Twitter cards, sitemaps, and optional schema JSON.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Customize share previews per blog post while keeping a site-wide default description.</li>
<li>Mark thank-you pages <code>noindex</code> while keeping them public for customers.</li>
<li>Rebuild sitemaps after a large import so search engines pick up new URLs.</li>
</ul>

<h2>Commerce (Stripe)</h2>
<p>Sell <strong>published product entries</strong> via Stripe Checkout—cart, coupons, tax, shipping zones, digital fulfillment.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Run a digital shop with secure file downloads after payment.</li>
<li>Sell physical goods with stock tracking and shipping zones by country.</li>
<li>Offer cart-wide coupons while excluding Stripe Price ID products from discount rules.</li>
<li>Let customers view order history and download links when logged in.</li>
</ul>
<p><strong>Configure:</strong> <strong>Commerce → Commerce settings</strong>, products as content entries. See Commerce section articles.</p>

<h2>REST and GraphQL APIs</h2>
<p>Headless access to entries, pages, and content lists with scoped API keys.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Power a React or mobile app from <code>GET /api/v1/content-types/blog/entries</code>.</li>
<li>Feed a static site generator from content lists without public list pages.</li>
<li>Enrich API JSON from a plugin via the <code>api.entry.response</code> filter.</li>
</ul>
<p><strong>Configure:</strong> <strong>System → API keys</strong>.</p>

<h2>Plugins and themes</h2>
<p><strong>Plugins</strong> add admin screens, public routes, migrations, and hooks. <strong>Themes</strong> control storefront HTML/CSS and template overrides.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Install Forum, Knowledge Base, or mailing-list plugins from the catalog.</li>
<li>Ship a white-label theme with its own <code>theme.json</code> accent color and layouts.</li>
<li>Override <code>content/blog/show.twig</code> in a child theme without forking core.</li>
</ul>
<p><strong>Configure:</strong> <strong>Extensions → Plugins</strong>, <strong>Appearance → Themes</strong>.</p>

<h2>Users, roles, and permissions</h2>
<p>Staff accounts use roles to gate admin areas—editors see Content but not Plugins.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Give freelancers <code>edit_content</code> only on Blog and Media.</li>
<li>Restrict blueprint import to agency leads with <code>manage_portability</code>.</li>
<li>Keep commerce refunds limited to shop managers.</li>
</ul>
<p><strong>Configure:</strong> <strong>System → Users</strong> and <strong>Roles</strong>.</p>

<h2>Background jobs and maintenance</h2>
<p>Cron-driven jobs handle scheduled publish, media compression, sitemap warming, and plugin async work.</p>
<p><strong>What you can do:</strong></p>
<ul>
<li>Publish hundreds of scheduled posts overnight without manual clicks.</li>
<li>Keep uploads optimized with batch compression when GD is available.</li>
<li>Run plugin rebuild tasks (search index, forum counters) off the web request path.</li>
</ul>
<p><strong>Configure:</strong> server cron for <code>jobs:dispatch</code> and <code>jobs:work</code>; <strong>Tools → Maintenance</strong> for cache and retention.</p>""",
}

CONTENT_LISTS_EXAMPLES = r"""
<h2>Example scenarios</h2>
<p>These patterns show what content lists are for in real projects—not just the admin fields.</p>

<h3>Homepage “Top reviews” block</h3>
<p><strong>Goal:</strong> Show the six highest-scoring published reviews on the home page.</p>
<ol>
<li>Create list slug <code>top-reviews</code> for content type <em>Review</em>.</li>
<li>Status: <strong>Published</strong> only.</li>
<li>Field filter: <code>score</code> <strong>gte</strong> <code>4</code> (number field).</li>
<li>Sort: <code>field:score</code> descending.</li>
<li>In <code>page/home.twig</code>: <code>{% set pack = content_list('top-reviews', 6) %}</code> and loop <code>pack.entries</code> into your card partial.</li>
</ol>

<h3>“Latest from Product news” taxonomy strip</h3>
<p><strong>Goal:</strong> A blog sidebar with only posts in the Product news category.</p>
<ol>
<li>List slug <code>product-news-latest</code>, type <em>Blog</em>.</li>
<li>Taxonomy filter: term <em>Product news</em>.</li>
<li>Sort: <code>published_at</code> descending.</li>
<li>Embed with <code>content_list('product-news-latest', 5)</code>—no public list page required.</li>
</ol>

<h3>Staff-curated “Featured guides”</h3>
<p><strong>Goal:</strong> Editors tick a boolean <code>featured</code> on guide entries; marketing displays only those.</p>
<ol>
<li>Add boolean field <code>featured</code> on the Guide type.</li>
<li>List filter: <code>featured</code> <strong>eq</strong> <code>1</code>.</li>
<li>Enable <strong>Public page</strong> at <code>/lists/featured-guides</code> for a shareable directory, or keep theme-only.</li>
</ol>

<h3>Partner directory (public list URL)</h3>
<p><strong>Goal:</strong> A standalone <code>/lists/partners</code> page without custom PHP.</p>
<ol>
<li>Type <em>Partner</em> with logo media field and summary text.</li>
<li>List: published only, sort by <code>title</code> ascending.</li>
<li>Enable <strong>Public page</strong>; optional theme override <code>content_lists/partners.twig</code>.</li>
</ol>

<h3>Mobile app feed via REST</h3>
<p><strong>Goal:</strong> Same query as the website, consumed by an iOS app.</p>
<ol>
<li>Enable <strong>REST API</strong> on list <code>homepage-feed</code>.</li>
<li>Create API key with <code>read</code> scope.</li>
<li>App calls <code>GET /api/v1/content-lists/homepage-feed?page=1</code> and maps <code>data</code> to native cells.</li>
</ol>

<h3>Preview before launch</h3>
<p><strong>Goal:</strong> Verify filters include drafts during QA.</p>
<ol>
<li>Include <strong>Draft</strong> in status filter temporarily.</li>
<li>Click <strong>Preview</strong> on the list edit screen—preview uses admin rules so drafts appear.</li>
<li>Remove draft status before go-live; storefront embeds always use published visibility.</li>
</ol>
"""

FEATURE_OVERVIEW_INTRO = r"""<h2>How to read this overview</h2>
<p>Below is a map of Struxa capabilities. For <strong>what each feature does in practice</strong> (with concrete examples), read <strong>What each Struxa feature does</strong> in this section first.</p>
"""


def patch_content_lists(editors: list) -> None:
    for article in editors:
        if article.get("slug") != "content-lists":
            continue
        article["summary"] = (
            "Saved queries over entries—filter, sort, paginate; "
            "use on pages, in Twig, or via the API, with real-world examples."
        )
        body = article["body"]
        if "<h2>Example scenarios</h2>" not in body:
            body = body.replace("<h2>Troubleshooting</h2>", CONTENT_LISTS_EXAMPLES + "\n<h2>Troubleshooting</h2>")
        article["body"] = body
        return


def patch_feature_overview(articles: list) -> None:
    for article in articles:
        if article.get("slug") != "feature-overview":
            continue
        article["summary"] = (
            "Capabilities across admin, storefront, commerce, and APIs—"
            "with links to detailed examples."
        )
        body = article["body"]
        if "What each Struxa feature does" not in body:
            body = FEATURE_OVERVIEW_INTRO + body
        article["body"] = body
        return


def main() -> None:
    guide_path = ARTICLES / "feature-guide.json"
    guide_path.write_text(
        json.dumps([FEATURE_GUIDE], indent=2, ensure_ascii=False) + "\n",
        encoding="utf-8",
    )

    editors_path = ARTICLES / "editors.json"
    editors = json.loads(editors_path.read_text(encoding="utf-8"))
    patch_content_lists(editors)
    editors_path.write_text(json.dumps(editors, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")

    getting_started_path = ARTICLES / "getting-started.json"
    getting_started = json.loads(getting_started_path.read_text(encoding="utf-8"))
    patch_feature_overview(getting_started)
    getting_started_path.write_text(
        json.dumps(getting_started, indent=2, ensure_ascii=False) + "\n",
        encoding="utf-8",
    )

    print("Updated feature-guide.json, editors.json (content-lists), getting-started.json (feature-overview)")


if __name__ == "__main__":
    main()
