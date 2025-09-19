<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class CountersBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $countersTemplate = $variables['countersTemplate'];
        $defaultLogo = $variables['defaultLogo'];

        return [
            [
                            'name' => $module->l('Counters'),
                            'description' => $module->l('Display animated counters'),
                            'code' => 'everblock_counters',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $countersTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Counter',
                                'nameFrom' => 'label',
                                'groups' => [
                                    'icon' => [
                                        'type' => 'select',
                                        'label' => $module->l('Select an icon'),
                                        'choices' => EverblockTools::getAvailableSvgIcons(),
                                        'default' => 'payment.svg',
                                    ],
                                    'value' => [
                                        'type' => 'text',
                                        'label' => $module->l('Value'),
                                        'default' => '100',
                                    ],
                                    'label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Label'),
                                        'default' => '',
                                    ],
                                    'animation_speed' => [
                                        'type' => 'text',
                                        'label' => $module->l('Animation speed (ms)'),
                                        'default' => '2000',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
