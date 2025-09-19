<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class MysteryBoxesBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $mysteryBoxesTemplate = $variables['mysteryBoxesTemplate'];

        return [
            [
                            'name' => $module->l('Mystery boxes'),
                            'description' => $module->l('Interactive mystery boxes mini game'),
                            'code' => 'everblock_mystery_boxes',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $mysteryBoxesTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Block title'),
                                        'default' => $module->l('Mystery boxes'),
                                    ],
                                    'instructions' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Instructions text'),
                                        'default' => $module->l('Pick a box to reveal your surprise!'),
                                    ],
                                    'closed_label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Closed state label'),
                                        'default' => '?',
                                    ],
                                    'start_date' => [
                                        'type' => 'text',
                                        'label' => $module->l('Game start date (YYYY-MM-DD HH:MM:SS)'),
                                        'default' => '',
                                    ],
                                    'end_date' => [
                                        'type' => 'text',
                                        'label' => $module->l('Game end date (YYYY-MM-DD HH:MM:SS)'),
                                        'default' => '',
                                    ],
                                    'pre_start_message' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Message before the game starts'),
                                        'default' => '',
                                    ],
                                    'post_end_message' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Message after the game ends'),
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
                            'repeater' => [
                                'name' => 'Mystery box',
                                'nameFrom' => 'label',
                                'groups' => [
                                    'label' => [
                                        'type' => 'text',
                                        'label' => $module->l('Label'),
                                        'default' => '',
                                    ],
                                    'message' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Reveal message'),
                                        'default' => '',
                                    ],
                                    'probability' => [
                                        'type' => 'text',
                                        'label' => $module->l('Probability'),
                                        'default' => '1',
                                    ],
                                    'color' => [
                                        'type' => 'color',
                                        'label' => $module->l('Background color'),
                                        'default' => '#0d6efd',
                                    ],
                                    'text_color' => [
                                        'type' => 'color',
                                        'label' => $module->l('Text color'),
                                        'default' => '#ffffff',
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Optional image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                    'discount' => [
                                        'type' => 'text',
                                        'label' => $module->l('Discount value'),
                                        'default' => '10',
                                    ],
                                    'coupon_name' => [
                                        'type' => 'text',
                                        'label' => $module->l('Coupon name'),
                                        'default' => $module->l('Mystery reward'),
                                    ],
                                    'coupon_prefix' => [
                                        'type' => 'text',
                                        'label' => $module->l('Coupon code prefix'),
                                        'default' => 'MYSTERY',
                                    ],
                                    'coupon_validity' => [
                                        'type' => 'number',
                                        'label' => $module->l('Coupon validity in days'),
                                        'default' => '30',
                                    ],
                                    'minimum_purchase' => [
                                        'type' => 'text',
                                        'label' => $module->l('Minimum purchase amount (tax incl.)'),
                                        'default' => '',
                                    ],
                                    'max_winners' => [
                                        'type' => 'number',
                                        'label' => $module->l('Maximum winners for this box (0 for unlimited)'),
                                        'default' => '0',
                                    ],
                                    'coupon_type' => [
                                        'type' => 'select',
                                        'label' => $module->l('Discount type'),
                                        'default' => 'percent',
                                        'choices' => [
                                            'percent' => $module->l('Percent'),
                                            'amount' => $module->l('Amount'),
                                        ],
                                    ],
                                    'id_categories' => [
                                        'type' => 'selector',
                                        'label' => $module->l('Coupon category restrictions'),
                                        'collection' => 'Category',
                                        'selector' => '{id} - {name}',
                                        'multiple' => true,
                                        'default' => [],
                                    ],
                                    'isWinning' => [
                                        'type' => 'checkbox',
                                        'label' => $module->l('Winning box'),
                                        'default' => false,
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
