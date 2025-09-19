<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class SocialLinksBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $socialLinksTemplate = $variables['socialLinksTemplate'];

        return [
            [
                            'name' => $module->l('Social links'),
                            'description' => $module->l('Display custom links to social networks'),
                            'code' => 'everblock_social_links',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $socialLinksTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'icon_color' => [
                                        'type' => 'color',
                                        'label' => $module->l('Icon color'),
                                        'default' => '',
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Social link',
                                'nameFrom' => 'url',
                                'groups' => [
                                    'url' => [
                                        'type' => 'text',
                                        'label' => $module->l('Link URL'),
                                        'default' => '#',
                                    ],
                                    'icon' => [
                                        'type' => 'select',
                                        'label' => $module->l('Select an icon'),
                                        'choices' => EverblockTools::getAvailableSvgIcons(),
                                        'default' => 'facebook.svg',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
