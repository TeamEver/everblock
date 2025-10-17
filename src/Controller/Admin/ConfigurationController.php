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

namespace Everblock\Tools\Controller\Admin;

use Everblock\Tools\Service\Admin\StatisticsProvider;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class ConfigurationController extends BaseEverblockController
{
    /**
     * @var StatisticsProvider|null
     */
    private $statisticsProvider;

    public function __construct(
        ?\Context $context = null,
        ?\PrestaShop\PrestaShop\Adapter\Module\Repository\ModuleRepository $moduleRepository = null,
        ?\Symfony\Contracts\Translation\TranslatorInterface $translator = null,
        ?\Everblock\Tools\Service\Admin\NavigationBuilder $navigationBuilder = null,
        ?StatisticsProvider $statisticsProvider = null
    ) {
        parent::__construct($context, $moduleRepository, $translator, $navigationBuilder);
        $this->statisticsProvider = $statisticsProvider;
    }

    public function index(): Response
    {
        $stats = $this->statisticsProvider ? $this->statisticsProvider->getStatistics() : [];

        return $this->renderLayout(
            $this->translate('Configuration'),
            [
                'content_title' => $this->translate('Module configuration'),
                'content_description' => $this->translate('Legacy forms are still available from the Modules manager while the Symfony UI is being finalised.'),
            ],
            '@Modules/everblock/templates/admin/everblock/placeholder.html.twig',
            [
                'page_identifier' => 'configuration',
                'module_stats' => $stats,
            ]
        );
    }
}
