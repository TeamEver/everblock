<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class ExitIntentBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $exitIntentTemplate = $variables['exitIntentTemplate'];

        return [
            [
                            'name' => $module->l('Exit intent offer'),
                            'description' => $module->l('Display popup when leaving the page'),
                            'code' => 'everblock_exit_intent',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $exitIntentTemplate,
                            ],
                            'repeater' => [
                                'name' => $module->l('Offer'),
                                'nameFrom' => 'title',
                                'groups' => [
                                    'title' => [
                                        'type' => 'text',
                                        'label' => $module->l('Title'),
                                        'default' => '',
                                    ],
                                    'message' => [
                                        'type' => 'textarea',
                                        'label' => $module->l('Message'),
                                        'default' => '',
                                    ],
                                    'cta_label' => [
                                        'type' => 'text',
                                        'label' => $module->l('CTA label'),
                                        'default' => '',
                                    ],
                                    'cta_url' => [
                                        'type' => 'text',
                                        'label' => $module->l('CTA URL'),
                                        'default' => '#',
                                    ],
                                    'image' => [
                                        'type' => 'fileupload',
                                        'label' => $module->l('Image'),
                                        'default' => [
                                            'url' => '',
                                        ],
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
