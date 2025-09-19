<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class SpacerBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $spacerTemplate = $variables['spacerTemplate'];

        return [
            [
                            'name' => $module->l('Spacer'),
                            'description' => $module->l('Add a vertical space'),
                            'code' => 'everblock_spacer',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $spacerTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'space_top' => [
                                        'type' => 'text',
                                        'label' => $module->l('Space top (rem)'),
                                        'default' => '0',
                                    ],
                                    'space_bottom' => [
                                        'type' => 'text',
                                        'label' => $module->l('Space bottom (rem)'),
                                        'default' => '0',
                                    ],
                                    'css_class' => [
                                        'type' => 'text',
                                        'label' => $module->l('Custom CSS class'),
                                        'default' => '',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
