<?php

declare(strict_types=1);

namespace KnowledgeBasePlugin;

use PDO;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Public storefront helpers for /kb wiki navigation.
 */
final class KnowledgeBaseTwigExtension extends AbstractExtension
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('kb_public_enabled', $this->publicEnabled(...)),
            new TwigFunction('kb_wiki_sections', $this->wikiSections(...)),
        ];
    }

    public function publicEnabled(): bool
    {
        return KnowledgeBaseSettings::isStorefrontWikiActive($this->pdo);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function wikiSections(?string $currentSlug = null): array
    {
        return (new KnowledgeBaseWikiIndex($this->pdo))->groupedForPublic($currentSlug);
    }
}
