<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class CardBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $cardTemplate = $variables['cardTemplate'];
        $defaultLogo = $variables['defaultLogo'];

        return [
            [
                            'name' => $module->l('Bootstrap cards'),
                            'description' => $module->l('Display content cards'),
                            'code' => 'everblock_card',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $cardTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'center_cards' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Center cards in container'),
                                        'default' => 0,
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Card',
                                'nameFrom' => 'title',
                                'groups' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Card title'),
                                        'default' => '',
                                    ],
                                    'content' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Card content'),
                                        'default' => '',
                                    ],
                                    'button_text' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button text'),
                                        'default' => $module->l('En savoir plus'),
                                    ],
                                    'button_link' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button link'),
                                        'default' => '#',
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => 'Image',
                                        'accept' => 'image/svg+xml,image/webp,image/png,image/jpeg,image/gif',
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'alt' => [
                                        'type' => 'text',
                                        'label' => $module->l('Image alt text'),
                                        'default' => '',
                                    ],
                                    'image_width' => [
                                        'type' => 'text',
                                        'label' => $module->l('Image width'),
                                        'default' => '',
                                    ],
                                    'image_height' => [
                                        'type' => 'text',
                                        'label' => $module->l('Image height'),
                                        'default' => '',
                                    ],
                                    'css_class' => [
                                        'type' => 'text',
                                        'label' => $module->l('Custom CSS class'),
                                        'default' => '',
                                    ],
                                    'padding_left' => [
                                        'type' => 'text',
                                        'label' => $module->l('Padding left (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'padding_right' => [
                                        'type' => 'text',
                                        'label' => $module->l('Padding right (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'padding_top' => [
                                        'type' => 'text',
                                        'label' => $module->l('Padding top (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'padding_bottom' => [
                                        'type' => 'text',
                                        'label' => $module->l('Padding bottom (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_left' => [
                                        'type' => 'text',
                                        'label' => $module->l('Margin left (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_right' => [
                                        'type' => 'text',
                                        'label' => $module->l('Margin right (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_top' => [
                                        'type' => 'text',
                                        'label' => $module->l('Margin top (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_bottom' => [
                                        'type' => 'text',
                                        'label' => $module->l('Margin bottom (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
