<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class CtaBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $ctaTemplate = $variables['ctaTemplate'];
        $defaultLogo = $variables['defaultLogo'];

        return [
            [
                            'name' => $module->l('Call to Action'),
                            'description' => $module->l('Display a title, some content and a call-to-action button.'),
                            'code' => 'everblock_cta',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $ctaTemplate,
                            ],
                            'repeater' => [
                                'name' => 'CTA',
                                'nameFrom' => 'name',
                                'groups' => [
                                    'name' => [
                                        'type' => 'editor',
                                        'label' => 'Title',
                                        'default' => Configuration::get('PS_SHOP_NAME'),
                                    ],
                                    'content' => [
                                        'type' => 'editor',
                                        'label' => 'Block content',
                                        'default' => '[llorem]',
                                    ],
                                    'cta_link' => [
                                        'type' => 'text',
                                        'label' => $module->l('CTA Link'),
                                        'default' => '#',
                                    ],
                                    'cta_text' => [
                                        'type' => 'text',
                                        'label' => $module->l('CTA Button Text'),
                                        'default' => $module->l('Discover now'),
                                    ],
                                    'title' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Right column title'),
                                        'default' => 'Le n°1 dans la protection et la santé du sportif',
                                    ],
                                    'description' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Right column description'),
                                        'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec placerat, risus quis lobortis aliquam...',
                                    ],
                                    'text_highlight_1' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Highlight word 1'),
                                        'default' => 'protection',
                                    ],
                                    'text_highlight_2' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Highlight word 2'),
                                        'default' => 'santé',
                                    ],
                                    'background_image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Background image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'parallax' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Parallax mode'),
                                        'default' => false,
                                    ],
                                    'background_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Block background color')
                                    ],
                                    'text_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'default' => '',
                                        'label' => $module->l('Block text color')
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
