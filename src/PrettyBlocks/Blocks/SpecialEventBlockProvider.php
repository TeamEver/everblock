<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class SpecialEventBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $specialEventTemplate = $variables['specialEventTemplate'];

        return [
            [
                            'name' => $module->l('Special event'),
                            'description' => $module->l('Display a special event with countdown and CTA'),
                            'code' => 'everblock_special_event',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $specialEventTemplate,
                            ],
                            'repeater' => [
                                'name' => 'Event',
                                'nameFrom' => 'title',
                                'groups' => [
                                    'title' => [
                                        'type' => 'editor',
                                        'label' => 'Title',
                                        'default' => $module->l('Winter sale'),
                                    ],
                                    'background_image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Background image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'cta_text' => [
                                        'type' => 'text',
                                        'label' => $module->l('CTA Button Text'),
                                        'default' => $module->l('Shop now'),
                                    ],
                                    'cta_link' => [
                                        'type' => 'text',
                                        'label' => $module->l('CTA Link'),
                                        'default' => '#',
                                    ],
                                    'target_date' => [
                                        'type' => 'text',
                                        'label' => $module->l('Target date (YYYY-MM-DD HH:MM:SS)'),
                                        'default' => '',
                                    ],
                                    'product_ids' => [
                                        'type' => 'text',
                                        'label' => $module->l('Product IDs'),
                                        'default' => '',
                                    ],
                                    'background_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Block background color'),
                                    ],
                                    'text_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Block text color'),
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
