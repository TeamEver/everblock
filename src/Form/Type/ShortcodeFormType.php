<?php

/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Everblock\Tools\Form\Type;

use PrestaShopBundle\Form\Admin\Type\TranslateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class ShortcodeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_everblock_shortcode', HiddenType::class)
            ->add('title', TranslateType::class, [
                'label' => $this->trans('Title'),
                'locales' => $options['languages'],
                'type' => TextType::class,
                'options' => [
                    'required' => true,
                ],
            ])
            ->add('shortcode', TextType::class, [
                'label' => $this->trans('Shortcode'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('The shortcode is required.'),
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context): void {
                        $shortcode = (string) $value;

                        if (strpos($shortcode, ' ') !== false) {
                            $context->addViolation($this->trans('The shortcode cannot contain spaces.'));
                        }

                        if (strpos($shortcode, '[') === false || strpos($shortcode, ']') === false) {
                            $context->addViolation($this->trans('The shortcode must include opening and closing brackets.'));
                        }
                    }),
                ],
            ])
            ->add('content', TranslateType::class, [
                'label' => $this->trans('Content'),
                'locales' => $options['languages'],
                'type' => TextareaType::class,
                'options' => [
                    'required' => true,
                    'attr' => [
                        'class' => 'evertranslatable',
                        'data-autoload-rte' => true,
                    ],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'languages' => [],
        ]);
        $resolver->setAllowedTypes('languages', 'array');
    }

    private function trans(string $message): string
    {
        return \Context::getContext()->getTranslator()->trans(
            $message,
            [],
            'Modules.Everblock.Admin'
        );
    }
}
