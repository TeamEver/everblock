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
 */

namespace Everblock\PrestaShopBundle\Form\Admin\EverBlock;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EverBlockType extends AbstractType
{
    public const TAB_GENERAL = 'general';
    public const TAB_TARGETING = 'targeting';
    public const TAB_DISPLAY = 'display';
    public const TAB_MODAL = 'modal';
    public const TAB_SCHEDULE = 'schedule';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class, [
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('hook_id', ChoiceType::class, [
                'choices' => $this->flipChoices($options['hooks']),
                'placeholder' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Choice(['choices' => array_keys($options['hooks'])]),
                ],
            ])
            ->add('content', CollectionType::class, [
                'entry_type' => TextareaType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'constraints' => [
                    new Callback(function ($value, ExecutionContextInterface $context) use ($options) {
                        if (!is_array($value)) {
                            $context->buildViolation('Invalid translated content.')->addViolation();

                            return;
                        }
                        $defaultLangId = $options['default_language_id'];
                        $defaultValue = isset($value[$defaultLangId]) ? trim((string) $value[$defaultLangId]) : '';
                        if ('' === $defaultValue) {
                            $context->buildViolation('Content is required for the default language.')->addViolation();
                        }
                    }),
                ],
            ])
            ->add('custom_code', CollectionType::class, [
                'entry_type' => TextareaType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('device', ChoiceType::class, [
                'required' => false,
                'choices' => $this->flipChoices($options['devices']),
                'placeholder' => false,
                'constraints' => [
                    new Assert\Choice(['choices' => array_keys($options['devices'])]),
                ],
            ])
            ->add('group_ids', ChoiceType::class, [
                'choices' => $this->flipChoices($options['groups']),
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Choice([
                        'choices' => array_keys($options['groups']),
                        'multiple' => true,
                    ]),
                ],
            ])
            ->add('only_home', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('only_category', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('only_category_product', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('category_ids', ChoiceType::class, [
                'choices' => $this->flipChoices($options['categories']),
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Choice([
                        'choices' => array_keys($options['categories']),
                        'multiple' => true,
                    ]),
                ],
            ])
            ->add('only_manufacturer', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('manufacturer_ids', ChoiceType::class, [
                'choices' => $this->flipChoices($options['manufacturers']),
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Choice([
                        'choices' => array_keys($options['manufacturers']),
                        'multiple' => true,
                    ]),
                ],
            ])
            ->add('only_supplier', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('supplier_ids', ChoiceType::class, [
                'choices' => $this->flipChoices($options['suppliers']),
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Choice([
                        'choices' => array_keys($options['suppliers']),
                        'multiple' => true,
                    ]),
                ],
            ])
            ->add('only_cms_category', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('cms_category_ids', ChoiceType::class, [
                'choices' => $this->flipChoices($options['cms_categories']),
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Choice([
                        'choices' => array_keys($options['cms_categories']),
                        'multiple' => true,
                    ]),
                ],
            ])
            ->add('obfuscate_link', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('add_container', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('lazyload', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('background', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 32]),
                ],
            ])
            ->add('css_class', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('data_attribute', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('bootstrap_class', ChoiceType::class, [
                'required' => false,
                'choices' => $this->flipChoices($options['bootstrap']),
                'placeholder' => false,
                'constraints' => [
                    new Assert\Choice(['choices' => array_keys($options['bootstrap'])]),
                ],
            ])
            ->add('position', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Type('integer'),
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('modal', CheckboxType::class, [
                'required' => false,
                'constraints' => [new Assert\Type('bool')],
            ])
            ->add('delay', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Type('integer'),
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('timeout', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Type('integer'),
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('date_start', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'with_seconds' => true,
                'html5' => false,
            ])
            ->add('date_end', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'with_seconds' => true,
                'html5' => false,
            ])
            ->add('id_shop', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Type('integer'),
                ],
            ])
        ;

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();
                if ($data['date_start'] instanceof \DateTimeInterface && $data['date_end'] instanceof \DateTimeInterface) {
                    if ($data['date_end'] < $data['date_start']) {
                        $form->get('date_end')->addError(new FormError('End date must be greater than start date.'));
                    }
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'allow_extra_fields' => true,
            'hooks' => [],
            'devices' => [],
            'categories' => [],
            'manufacturers' => [],
            'suppliers' => [],
            'cms_categories' => [],
            'bootstrap' => [],
            'groups' => [],
            'default_language_id' => (int) \Configuration::get('PS_LANG_DEFAULT'),
        ]);
    }

    private function flipChoices(array $choices): array
    {
        $flipped = [];
        foreach ($choices as $value => $label) {
            $flipped[$label] = $value;
        }

        return $flipped;
    }
}
