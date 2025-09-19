<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class TocBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $tocTemplate = $variables['tocTemplate'];

        return [
            [
                            'name' => $module->l('Table of contents'),
                            'description' => $module->l('Display a summary with anchored sections'),
                            'code' => 'everblock_toc',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $tocTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Summary title'),
                                        'default' => $module->l('Summary'),
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Section',
                                'nameFrom' => 'title',
                                'groups' => [
                                    'anchor' => [
                                        'type' => 'text',
                                        'label' => $module->l('Anchor ID'),
                                        'default' => 'section-1',
                                    ],
                                    'category' => [
                                        'type' => 'text',
                                        'label' => $module->l('Category'),
                                        'default' => '',
                                    ],
                                    'subcategory' => [
                                        'type' => 'text',
                                        'label' => $module->l('Sub-category'),
                                        'default' => '',
                                    ],
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Title'),
                                        'default' => $module->l('Section 1'),
                                    ],
                                    'content' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Content'),
                                        'default' => '[llorem]',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
