<?php

namespace Everblock\PrettyBlocks\Blocks;

use Everblock\PrettyBlocks\BlockDefinitionContext;
use Everblock\PrettyBlocks\BlockProviderInterface;

class GuidedSelectorBlockProvider implements BlockProviderInterface
{
    public function getBlocks(BlockDefinitionContext $definitionContext): array
    {
        $module = $definitionContext->getModule();
        $variables = $definitionContext->getVariables();
        $defaultLogo = $variables['defaultLogo'];
        $guidedSelectorTemplate = $variables['guidedSelectorTemplate'];

        return [
            [
                            'name' => $module->l('Guided product selector'),
                            'description' => $module->l('Ask a few questions and redirect to a matching category'),
                            'code' => 'everblock_guided_selector',
                            'tab' => 'general',
                            'icon_path' => $defaultLogo,
                            'need_reload' => true,
                            'templates' => [
                                'default' => $guidedSelectorTemplate,
                            ],
                            'config' => [
                                'fields' => [
                                    'fallback_shortcode' => [
                                        'type' => 'editor',
                                        'label' => $module->l('Fallback content (shortcodes allowed)'),
                                        'default' => '[evercontactform_open][evercontact type="text" label="' . $module->l('Your name') . '"][evercontact type="email" label="' . $module->l('Your email') . '"][evercontact type="textarea" label="' . $module->l('Message') . '"][evercontact type="submit" label="' . $module->l('Send') . '"][evercontactform_close]',
                                    ],
                                ],
                            ],
                            'repeater' => [
                                'name' => 'Question',
                                'nameFrom' => 'question',
                                'groups' => [
                                    'question' => [
                                        'type' => 'text',
                                        'label' => $module->l('Question'),
                                        'default' => '',
                                    ],
                                    'answers' => [
                                        'type' => 'textarea',
                                        'label' => $module->l('Answers (one per line: "Answer label|Answer link")'),
                                        'default' => '',
                                    ],
                                ],
                            ],
                        
            ],
        ];
    }
}
