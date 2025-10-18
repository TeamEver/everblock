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

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class FaqFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_everblock_faq', HiddenType::class)
            ->add('tag_name', TextType::class, [
                'label' => $this->trans('FAQ tag'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('The FAQ tag is required.'),
                    ]),
                    new Regex([
                        'pattern' => '/^\S+$/u',
                        'message' => $this->trans('The FAQ tag cannot contain spaces.'),
                    ]),
                ],
                'attr' => [
                    'maxlength' => 255,
                    'pattern' => '\\S+',
                ],
            ])
            ->add('title', TranslateType::class, [
                'label' => $this->trans('Title'),
                'locales' => $options['languages'],
                'type' => TextType::class,
                'options' => [
                    'required' => false,
                    'attr' => [
                        'maxlength' => 255,
                    ],
                ],
            ])
            ->add('content', TranslateType::class, [
                'label' => $this->trans('Content'),
                'locales' => $options['languages'],
                'type' => TextareaType::class,
                'options' => [
                    'required' => false,
                    'attr' => [
                        'class' => 'evertranslatable',
                        'data-autoload-rte' => true,
                    ],
                ],
            ])
            ->add('position', IntegerType::class, [
                'label' => $this->trans('Position'),
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => $this->trans('The position must be greater than or equal to zero.'),
                    ]),
                ],
                'empty_data' => '0',
            ])
            ->add('active', SwitchType::class, [
                'label' => $this->trans('Active'),
                'required' => false,
                'constraints' => [
                    new Callback(function ($value, ExecutionContextInterface $context): void {
                        if ($value !== null && !is_bool($value) && $value !== 0 && $value !== 1) {
                            $context->addViolation($this->trans('The active status is invalid.'));
                        }
                    }),
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
