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

namespace Everblock\Tools\Grid\Query;

use Context;
use DbQuery;

if (!defined('_PS_VERSION_') && php_sapi_name() !== 'cli') {
    exit;
}

class ShortcodeQueryBuilder
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function buildQuery(array $filters = []): DbQuery
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $query = new DbQuery();
        $query->select('s.id_everblock_shortcode, s.shortcode, sl.title, sl.content');
        $query->from('everblock_shortcode', 's');
        $query->leftJoin(
            'everblock_shortcode_lang',
            'sl',
            sprintf(
                's.id_everblock_shortcode = sl.id_everblock_shortcode AND sl.id_lang = %d AND sl.id_shop = %d',
                $idLang,
                $idShop
            )
        );
        $query->where('s.id_shop = ' . $idShop);

        foreach ($filters as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            switch ($key) {
                case 'id_everblock_shortcode':
                    $query->where('s.id_everblock_shortcode = ' . (int) $value);
                    break;
                case 'shortcode':
                    $query->where('s.shortcode LIKE "%' . pSQL((string) $value) . '%"');
                    break;
                case 'title':
                case 'content':
                    $query->where(sprintf('sl.%s LIKE "%%%s%%"', pSQL($key), pSQL((string) $value)));
                    break;
            }
        }

        $query->orderBy('s.id_everblock_shortcode DESC');

        return $query;
    }
}
