<?php

declare(strict_types=1);

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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class EverblockConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pages_base_url', TextType::class, [
                'label' => 'Pages base URL',
                'help' => 'Base path used for the guide pages routes.',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('pages_per_page', IntegerType::class, [
                'label' => 'Items per page',
                'help' => 'Number of guides to display on the listing page.',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0),
                ],
            ])
            ->add('faq_base_url', TextType::class, [
                'label' => 'FAQ base URL',
                'help' => 'Base path used for the FAQ tag routes.',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('faq_per_page', IntegerType::class, [
                'label' => 'FAQ per page',
                'help' => 'Number of FAQs to display for each tag page.',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0),
                ],
            ])
            ->add('google_reviews_cta_label', TranslateType::class, [
                'label' => 'Google reviews CTA label',
                'type' => TextType::class,
                'help' => 'Label used for the Google reviews call-to-action button.',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
