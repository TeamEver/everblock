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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Validate;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class HookFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_hook', HiddenType::class)
            ->add('name', TextType::class, [
                'label' => $this->trans('Name'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('The hook name is required.'),
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context): void {
                        if (!Validate::isHookName((string) $value)) {
                            $context->addViolation($this->trans('The hook name is invalid.'));
                        }
                    }),
                ],
            ])
            ->add('title', TextType::class, [
                'label' => $this->trans('Title'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('The hook title is required.'),
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context): void {
                        if (!Validate::isCleanHtml((string) $value)) {
                            $context->addViolation($this->trans('The hook title is invalid.'));
                        }
                    }),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->trans('Description'),
                'required' => true,
                'attr' => [
                    'rows' => 3,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('The hook description is required.'),
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context): void {
                        if (!Validate::isCleanHtml((string) $value)) {
                            $context->addViolation($this->trans('The hook description is invalid.'));
                        }
                    }),
                ],
            ])
            ->add('active', SwitchType::class, [
                'label' => $this->trans('Active'),
                'required' => false,
            ]);
    }

    private function trans(string $message): string
    {
        return \Context::getContext()->getTranslator()->trans(
            $message,
            [],
            'Modules.Everblock.Admineverblockcontroller'
        );
    }
}
