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

namespace Everblock\Tools\Service\Domain;

use Everblock\Tools\Entity\EverBlockFlag;
use Everblock\Tools\Entity\EverBlockModal;
use Everblock\Tools\Entity\EverBlockTab;
use Everblock\Tools\Repository\EverBlockFlagRepository;
use Everblock\Tools\Repository\EverBlockModalRepository;
use Everblock\Tools\Repository\EverBlockTabRepository;
use Everblock\Tools\Service\EverBlockFlagProvider;
use Everblock\Tools\Service\EverBlockModalProvider;
use Everblock\Tools\Service\EverBlockTabProvider;

class LegacyModelMap
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getMappings(): array
    {
        return [
            'EverblockFlagsClass' => [
                'entity' => EverBlockFlag::class,
                'repository' => EverBlockFlagRepository::class,
                'service' => EverBlockFlagProvider::class,
                'usage' => [
                    'everblock.php',
                    'models/EverblockTools.php',
                    'controllers/front/videoproducts.php',
                ],
            ],
            'EverblockTabsClass' => [
                'entity' => EverBlockTab::class,
                'repository' => EverBlockTabRepository::class,
                'service' => EverBlockTabProvider::class,
                'usage' => [
                    'everblock.php',
                    'models/EverblockTools.php',
                    'controllers/front/videoproducts.php',
                ],
            ],
            'EverblockModal' => [
                'entity' => EverBlockModal::class,
                'repository' => EverBlockModalRepository::class,
                'service' => EverBlockModalProvider::class,
                'usage' => [
                    'everblock.php',
                    'models/EverblockTools.php',
                    'controllers/front/modal.php',
                ],
            ],
        ];
    }
}

