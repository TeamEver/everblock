<?php
/**
 * 2019-2021 Team Ever
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
    public $only_home;
    public $only_category;
    public $id_hook;
    public $id_category;
    public $id_shop;
    public $active;

    public static $definition = array(
        'table' => 'everblock',
        'primary' => 'id_everblock',
        'multilang' => true,
        'fields' => array(
            'name' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => true
            ),
            'id_hook' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'only_home' => array(
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ),
            'only_category' => array(
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
            ),
            'id_category' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'position' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool'
            ),
            // lang fields
            'content' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => false
            ),
        )
    );

    public static function getBlocks($id_hook, $id_lang, $id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('everblock', 'eb');
        $sql->leftJoin('everblock_lang', 'ebl', 'eb.id_everblock = ebl.id_everblock');
        $sql->where('eb.id_hook = '.(int)$id_hook);
        $sql->where('ebl.id_lang = '.(int)$id_lang);
        $sql->where('eb.id_shop = '.(int)$id_shop);
        $sql->where('eb.active = 1');
        $sql->orderBy('eb.position ASC');

        return Db::getInstance()->executeS($sql);
    }
}
