<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class CategoryHighlightBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $featuredCategoryTemplate = $variables['featuredCategoryTemplate'];
        $context = $definitionContext->getContext();

        return [
            [
                            'name' => $module->l('Featured category'),
                            'description' => $module->l('Add featured category'),
                            'code' => 'everblock_category_highlight',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $featuredCategoryTemplate
                            ],
                            'config' => [
                                'fields' => [
                                    'desktop_columns' => [
                                        'type' => 'select',
                                        'label' => $module->l('Desktop columns'),
                                        'default' => '2',
                                        'choices' => [
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                            '6' => '6',
                                        ],
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Menu',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'name' => [
                                        'type' => 'text',
                                        'label' => 'Featured category title',
                                        'default' => '',
                                    ],
                                    'category' => [
                                        'type' => 'selector',
                                        'label' => $module->l('Featured category'),
                                        'collection' => 'Category',
                                        'selector' => '{id} - {name}',
                                        'default' => \HelperBuilder::getRandomCategory((int) $context->language->id, (int) $context->shop->id),
                                        'force_default_value' => true,
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => 'Featured category image',
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'image_width' => [
                                        'type' => 'text',
                                        'label' => 'Image width (e.g., 100px or 50%)',
                                        'default' => '100%',
                                    ],
                                    'image_height' => [
                                        'type' => 'text',
                                        'label' => 'Image height (e.g., 100px)',
                                        'default' => 'auto',
                                    ],
                                    'link' => [
                                        'type' => 'text',
                                        'label' => 'Layout link',
                                        'default' => '',
                                    ],
                                    'obfuscate' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Obfuscate link'),
                                        'default' => '0',
                                    ],
                                    'target_blank' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Open in new tab (only if not obfuscated)'),
                                        'default' => '0',
                                    ],
                                    'title_position_desktop' => [
                                        'type' => 'select',
                                        'label' => $module->l('Title position (desktop)'),
                                        'choices' => [
                                            'center' => $module->l('Center'),
                                            'top' => $module->l('Top'),
                                            'bottom' => $module->l('Bottom'),
                                            'left' => $module->l('Left'),
                                            'right' => $module->l('Right'),
                                        ],
                                        'default' => 'center',
                                    ],
                                    'title_position_mobile' => [
                                        'type' => 'select',
                                        'label' => $module->l('Title position (mobile)'),
                                        'choices' => [
                                            'center' => $module->l('Center'),
                                            'top' => $module->l('Top'),
                                            'bottom' => $module->l('Bottom'),
                                            'left' => $module->l('Left'),
                                            'right' => $module->l('Right'),
                                        ],
                                        'default' => 'center',
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
                                    'margin_left_mobile' => [
                                        'type' => 'text',
                                        'label' => $module->l('Mobile margin left (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_right_mobile' => [
                                        'type' => 'text',
                                        'label' => $module->l('Mobile margin right (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_top_mobile' => [
                                        'type' => 'text',
                                        'label' => $module->l('Mobile margin top (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                    'margin_bottom_mobile' => [
                                        'type' => 'text',
                                        'label' => $module->l('Mobile margin bottom (Please specify the unit of measurement)'),
                                        'default' => '',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
