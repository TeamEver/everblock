<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class LookbookBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $lookbookTemplate = $variables['lookbookTemplate'];

        return [
            [
                            'name' => $module->l('Lookbook'),
                            'description' => $module->l('Display looks with associated products'),
                            'code' => 'everblock_lookbook',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $lookbookTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Look title'),
                                        'default' => '',
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Look image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'columns' => [
                                        'type' => 'select',
                                        'label' => $module->l('Columns on desktop'),
                                        'default' => '1',
                                        'choices' => [
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                        ],
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Product',
                                'nameFrom' => 'product',
                                'groups' => [
                                    'product' => [
                                        'type' => 'selector',
                                        'label' => $module->l('Choose a product'),
                                        'collection' => 'Product',
                                        'selector' => '{id} - {name}',
                                        'default' => '',
                                    ],
                                    'top' => [
                                        'type' => 'text',
                                        'label' => $module->l('Top position (e.g., 50%)'),
                                        'default' => '0%',
                                    ],
                                    'left' => [
                                        'type' => 'text',
                                        'label' => $module->l('Left position (e.g., 50%)'),
                                        'default' => '0%',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
