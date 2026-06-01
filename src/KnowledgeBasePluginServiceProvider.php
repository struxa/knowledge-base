<?php

declare(strict_types=1);

namespace KnowledgeBasePlugin;

use App\Plugin\PluginBootContext;
use App\Plugin\PluginServiceProviderInterface;

final class KnowledgeBasePluginServiceProvider implements PluginServiceProviderInterface
{
    public function boot(PluginBootContext $context): void
    {
        if (KnowledgeBaseSettings::isPublicVisible($context->pdo())) {
            $context->registerPluginReservedSlugs([KnowledgeBaseSettings::CONTENT_TYPE_SLUG]);
        }

        $context->registerAdminNavItem('Browse wiki', 'plugin.knowledge_base_plugin.index');
        $context->registerAdminNavItem('Settings', 'plugin.knowledge_base_plugin.settings');
    }
}
