<?php

declare(strict_types=1);

namespace KnowledgeBasePlugin;

use App\Plugin\PluginBootContext;
use App\Plugin\PluginServiceProviderInterface;

final class KnowledgeBasePluginServiceProvider implements PluginServiceProviderInterface
{
    public function boot(PluginBootContext $context): void
    {
        // Public URLs use the core content-type routes /kb/{slug} (has_public_route on the kb type).
        // Do not registerPluginReservedSlugs(['kb']) — that blocks public_content.php from serving entries.

        $context->registerAdminNavItem('Knowledge Base', 'plugin.knowledge_base_plugin.index');
    }
}
