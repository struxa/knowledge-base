# Knowledge Base (Struxa CMS plugin)

Built-in Struxa CMS documentation wiki for staff, with optional public storefront at `/kb`.

## Install

1. Copy or clone this repository into your Struxa site as `plugins/knowledge-base-plugin/` (folder name must match `plugin.json` → `slug`).
2. In **Admin → Extensions → Plugins**, activate **Knowledge Base**.
3. Open **Browse wiki** once to seed the default articles.
4. Optionally enable **Visible on storefront** under **Settings**.

## Requirements

- Struxa CMS 1.1.33+
- PHP 8.2+

## Features

- Creates the `kb` content type with summary/body fields and **KB section** taxonomy
- 35 seeded articles (getting started, editors, developers, plugins, themes, commerce)
- Admin wiki browser with links to edit entries
- Optional public routes at `/kb` and `/kb/{slug}`

## License

Use the same license as your Struxa distribution.
