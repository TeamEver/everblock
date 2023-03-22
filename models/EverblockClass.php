<?php
/**
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class EverBlockClass extends ObjectModel
{
    public $id_everblock;
    public $name;
    public $content;
    public $custom_code;
    public $only_home;
    public $only_category;
    public $id_hook;
    public $device;
    public $groups;
    public $background;
    public $css_class;
    public $bootstrap_class;
    public $id_shop;
    public $categories;
    public $date_start;
    public $date_end;
    public $active;

    public static $definition = [
        'table' => 'everblock',
        'primary' => 'id_everblock',
        'multilang' => true,
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => true,
            ],
            'id_hook' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'only_home' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'only_category' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            'device' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'categories' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isJson',
                'required' => false,
            ],
            'groups' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isJson',
                'required' => false,
            ],
            'background' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isColor',
                'required' => false,
            ],
            'css_class' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false,
            ],
            'bootstrap_class' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'date_start' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDateFormat',
                'required' => false,
            ],
            'date_end' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDateFormat',
                'required' => false,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ],
            // lang fields
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false,
            ],
            'custom_code' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isAnything',
                'required' => false,
            ],
        ],
    ];

    public static function getAllBlocks($id_lang, $id_shop)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('everblock', 'eb');
        $sql->leftJoin('everblock_lang', 'ebl', 'eb.id_everblock = ebl.id_everblock');
        $sql->where('ebl.id_lang = ' . (int)$id_lang);
        $sql->where('eb.id_shop = ' . (int)$id_shop);
        $sql->orderBy('eb.position ASC');

        $allBlocks = Db::getInstance()->executeS($sql);

        return $allBlocks;
    }

    public static function getHeaderBlocks($idLang, $idShop)
    {
        $cacheId = 'EverBlockClass::getHeaderBlocks_'
        . (int) $idLang
        . '_'
        . (int) $idShop;
        if (!Cache::isStored($cacheId)) {
            $return = [];
            $customerGroups = Customer::getGroupsStatic(
                (int) Context::getContext()->customer->id
            );
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('everblock', 'eb');
            $sql->leftJoin('everblock_lang', 'ebl', 'eb.id_everblock = ebl.id_everblock');
            $sql->where('ebl.id_lang = ' . (int) $idLang);
            $sql->where('eb.id_shop = ' . (int) $idShop);
            $sql->where('eb.active = 1');
            $sql->where('ebl.custom_code IS NOT NULL');
            $sql->orderBy('eb.position ASC');

            $allBlocks = Db::getInstance()->executeS($sql);
            $now = date('Y-m-d H:i:s');
            foreach ($allBlocks as $block) {
                // Date start management
                if ($block['date_start'] && $block['date_start'] != '0000-00-00 00:00:00') {
                    if ($block['date_start'] > $now) {
                        continue;
                    }
                }
                // Date end management
                if ($block['date_end'] && $block['date_end'] != '0000-00-00 00:00:00') {
                    if ($block['date_end'] < $now) {
                        continue;
                    }
                }
                // Allowed groups
                $allowedGroups = $block['groups'];
                if ($allowedGroups) {
                    $allowedGroups = json_decode($allowedGroups);
                    if (!array_intersect($allowedGroups, $customerGroups)) {
                        continue;
                    }
                }
                Hook::exec('actionGetEverBlockBefore', [
                    'id_lang' => (int) $idLang,
                    'id_shop' => (int) $idShop,
                    'block' => &$block,
                ]);
                $return[] = $block;
            }
            Cache::store($cacheId, $return);
            return $return;
        }
        return Cache::retrieve($cacheId);
    }

    public static function getBlocks(int $idHook, int $idLang, int $idShop): array
    {
        $cacheId = sprintf('EverBlockClass::getBlocks_%d_%d_%d', $idHook, $idLang, $idShop);
        if (!Cache::isStored($cacheId)) {
            $return = [];
            $now = new DateTime();
            $now = $now->format('Y-m-d H:i:s');
            $customerGroups = Customer::getGroupsStatic((int) Context::getContext()->customer->id);
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('everblock', 'eb');
            $sql->leftJoin('everblock_lang', 'ebl', 'eb.id_everblock = ebl.id_everblock');
            $sql->where('eb.id_hook = ' . (int) $idHook);
            $sql->where('ebl.id_lang = ' . (int) $idLang);
            $sql->where('eb.id_shop = ' . (int) $idShop);
            $sql->where('eb.active = 1');
            $sql->orderBy('eb.position ASC');
            $allBlocks = Db::getInstance()->executeS($sql);
            foreach ($allBlocks as $block) {
                if (!empty($block['date_start']) && $block['date_start'] !== '0000-00-00 00:00:00' && $block['date_start'] > $now) {
                    continue;
                }
                if (!empty($block['date_end']) && $block['date_end'] !== '0000-00-00 00:00:00' && $block['date_end'] < $now) {
                    continue;
                }
                $allowedGroups = json_decode($block['groups'], true);
                if (!empty($allowedGroups) && !array_intersect($allowedGroups, $customerGroups)) {
                    continue;
                }
                $block['bootstrap_class'] = self::getBootstrapColClass(
                    $block['bootstrap_class']
                );
                Hook::exec('actionGetEverBlockBefore', [
                    'id_hook' => $idHook,
                    'id_lang' => $idLang,
                    'id_shop' => $idShop,
                    'block' => &$block,
                ]);
                $return[] = $block;
            }
            Cache::store($cacheId, $return);
            return $return;
        }
        return Cache::retrieve($cacheId);
    }

    public static function getBootstrapColClass($colNumber) {
        // die(var_dump($colNumber));
        $class = 'col-';
        switch ($colNumber) {
            case 1:
                $class .= '12';
                break;
            case 2:
                $class .= '6';
                break;
            case 3:
                $class .= '4';
                break;
            case 4:
                $class .= '3';
                break;
            case 6:
                $class .= '2';
                break;
            default:
                $class .= '12';
                break;
        }
        $class .= ' col-md-';
        switch ($colNumber) {
            case 1:
                $class .= '12';
                break;
            case 2:
                $class .= '6';
                break;
            case 3:
                $class .= '4';
                break;
            case 4:
                $class .= '3';
                break;
            case 6:
                $class .= '2';
                break;
            default:
                $class .= '12';
                break;
        }
        return $class;
    }
}
