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

        $context->registerAdminNavItem('Knowledge Base', 'plugin.knowledge_base_plugin.index');
        $context->registerAdminNavItem('Knowledge Base settings', 'plugin.knowledge_base_plugin.settings');
    }
}
