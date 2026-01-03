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

namespace Everblock\Tools\Controller\Admin;

use Everblock\Tools\Form\Type\EverblockConfigurationType;
use Everblock\Tools\Service\Configuration\EverblockConfigurationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class EverblockConfigurationController extends AbstractController
{
    private EverblockConfigurationManager $configurationManager;
    private TranslatorInterface $translator;

    public function __construct(EverblockConfigurationManager $configurationManager, TranslatorInterface $translator)
    {
        $this->configurationManager = $configurationManager;
        $this->translator = $translator;
    }

    public function index(Request $request): Response
    {
        $configuration = $this->configurationManager->getConfiguration();

        $form = $this->createForm(EverblockConfigurationType::class, $configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->configurationManager->updateFromForm($form->getData());
            $this->addFlash(
                'success',
                $this->translator->trans('Configuration updated successfully.', [], 'Modules.Everblock.Admin')
            );

            return $this->redirectToRoute('everblock_admin_configuration');
        }

        return $this->render(
            '@Modules/everblock/views/templates/admin/symfony/configuration.html.twig',
            [
                'form' => $form->createView(),
                'configuration' => $configuration,
            ]
        );
    }
}
