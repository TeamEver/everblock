<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class CoverBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $coverTemplate = $variables['coverTemplate'];
        $defaultLogo = $variables['defaultLogo'];

        return [
            [
                            'name' => $module->l('Cover block'),
                            'description' => $module->l('Background image with title, text and two buttons'),
                            'code' => 'everblock_cover',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $coverTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'slider' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Enable slider'),
                                        'default' => 0,
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Cover',
                                'nameFrom' => 'title',
                                'groups' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Title'),
                                        'default' => '',
                                    ],
                                    'title_tag' => [
                                        'type' => 'select',
                                        'label' => $module->l('Heading level'),
                                        'choices' => [
                                            'h1' => 'H1',
                                            'h2' => 'H2',
                                            'h3' => 'H3',
                                            'h4' => 'H4',
                                            'h5' => 'H5',
                                            'h6' => 'H6',
                                        ],
                                        'default' => 'h2',
                                    ],
                                    'title_color' => [
                                        'tab' => 'design',
                                        'type' => 'color',
                                        'label' => $module->l('Title color'),
                                        'default' => '#000000',
                                    ],
                                    'content' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Content'),
                                        'default' => '',
                                    ],
                                    'background_image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Background image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'background_image_mobile' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Mobile background image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'btn1_text' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button 1 text'),
                                        'default' => '',
                                    ],
                                    'btn1_link' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button 1 link'),
                                        'default' => '',
                                    ],
                                    'btn1_type' => [
                                        'type' => 'radio_group',
                                        'label' => $module->l('Button 1 type'),
                                        'default' => 'primary',
                                        'choices' => [
                                            'primary' => 'primary',
                                            'secondary' => 'secondary',
                                            'success' => 'success',
                                            'danger' => 'danger',
                                            'warning' => 'warning',
                                            'info' => 'info',
                                            'light' => 'light',
                                            'dark' => 'dark',
                                            'outline-primary' => 'outline-primary',
                                            'outline-secondary' => 'outline-secondary',
                                            'outline-success' => 'outline-success',
                                            'outline-danger' => 'outline-danger',
                                            'outline-warning' => 'outline-warning',
                                            'outline-info' => 'outline-info',
                                            'outline-light' => 'outline-light',
                                            'outline-dark' => 'outline-dark',
                                        ],
                                    ],
                                    'btn2_text' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button 2 text'),
                                        'default' => '',
                                    ],
                                    'btn2_link' => [
                                        'type' => 'text',
                                        'label' => $module->l('Button 2 link'),
                                        'default' => '',
                                    ],
                                    'btn2_type' => [
                                        'type' => 'radio_group',
                                        'label' => $module->l('Button 2 type'),
                                        'default' => 'primary',
                                        'choices' => [
                                            'primary' => 'primary',
                                            'secondary' => 'secondary',
                                            'success' => 'success',
                                            'danger' => 'danger',
                                            'warning' => 'warning',
                                            'info' => 'info',
                                            'light' => 'light',
                                            'dark' => 'dark',
                                            'outline-primary' => 'outline-primary',
                                            'outline-secondary' => 'outline-secondary',
                                            'outline-success' => 'outline-success',
                                            'outline-danger' => 'outline-danger',
                                            'outline-warning' => 'outline-warning',
                                            'outline-info' => 'outline-info',
                                            'outline-light' => 'outline-light',
                                            'outline-dark' => 'outline-dark',
                                        ],
                                    ],
                                    'content_position_desktop' => [
                                        'type' => 'select',
                                        'label' => $module->l('Content position (desktop)'),
                                        'choices' => [
                                            'center' => $module->l('Center'),
                                            'top' => $module->l('Top'),
                                            'bottom' => $module->l('Bottom'),
                                            'left' => $module->l('Left'),
                                            'right' => $module->l('Right'),
                                        ],
                                        'default' => 'center',
                                    ],
                                    'content_position_mobile' => [
                                        'type' => 'select',
                                        'label' => $module->l('Content position (mobile)'),
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
