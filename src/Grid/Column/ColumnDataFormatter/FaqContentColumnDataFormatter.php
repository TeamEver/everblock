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

namespace Everblock\Tools\Grid\Column\ColumnDataFormatter;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class FaqContentColumnDataFormatter
{
    /**
     * @param array<int, array<string, mixed>> $records
     *
     * @return array<int, array<string, mixed>>
     */
    public function format(array $records): array
    {
        foreach ($records as &$record) {
            if (!array_key_exists('content', $record)) {
                continue;
            }

            $content = (string) $record['content'];
            $cleanContent = trim(strip_tags($content));

            if (\Tools::strlen($cleanContent) > 120) {
                $cleanContent = \Tools::substr($cleanContent, 0, 117) . '...';
            }

            $record['content'] = $cleanContent;
        }

        return $records;
    }
}
