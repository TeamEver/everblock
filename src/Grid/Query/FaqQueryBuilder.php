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

class FaqQueryBuilder
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
        $query->select('f.id_everblock_faq, f.tag_name, fl.title, fl.content, f.position, f.active, f.date_add, f.date_upd');
        $query->from('everblock_faq', 'f');
        $query->leftJoin(
            'everblock_faq_lang',
            'fl',
            sprintf(
                'f.id_everblock_faq = fl.id_everblock_faq AND fl.id_lang = %d AND fl.id_shop = %d',
                $idLang,
                $idShop
            )
        );
        $query->where('f.id_shop = ' . $idShop);

        foreach ($filters as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            switch ($key) {
                case 'id_everblock_faq':
                    $query->where('f.id_everblock_faq = ' . (int) $value);
                    break;
                case 'tag_name':
                    $query->where('f.tag_name LIKE "%' . pSQL((string) $value) . '%"');
                    break;
                case 'title':
                case 'content':
                    $query->where(sprintf('fl.%s LIKE "%%%s%%"', pSQL($key), pSQL((string) $value)));
                    break;
                case 'position':
                    $query->where('f.position = ' . (int) $value);
                    break;
                case 'active':
                    $query->where('f.active = ' . (int) (bool) $value);
                    break;
                case 'date_add':
                case 'date_upd':
                    $query->where(sprintf('f.%s LIKE "%%%s%%"', pSQL($key), pSQL((string) $value)));
                    break;
            }
        }

        $query->orderBy('f.id_everblock_faq DESC');

        return $query;
    }
}
