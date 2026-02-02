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

namespace Everblock\Tools\Service;

use Address;
use Cart;
use CartRule;
use Category;
use CMS;
use Combination;
use Configuration;
use Context;
use Country;
use Customer;
use Db;
use DbQuery;
use DirectoryIterator;
use Everblock;
use EverblockClass;
use EverblockFaq;
use EverblockShortcode;
use Gender;
use Hook;
use Image;
use ImageManager;
use ImageType;
use Language;
use Link;
use Media;
use Module;
use NewsletterProSubscription;
use ObjectModel;
use Manufacturer;
use PrestaShop\Module\PrestashopCheckout\Order\PaymentStepCheckoutOrderBuilder;
use PrestaShop\PrestaShop\Adapter\Configuration as ConfigurationAdapter;
use PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\StockManager as StockManagerAdapter;
use PrestaShopDatabaseException;
use PrestaShopException;
use PrestaShopLogger;
use Product;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use StockAvailable;
use Store;
use Tools;
use Validate;
use WebP;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverblockTools extends ObjectModel
{
    public static function renderShortcodes(string $txt, Context $context, Everblock $module): string
    {
        Hook::exec('displayBeforeRenderingShortcodes', ['html' => &$txt]);
        $controllerTypes = [
            'front',
            'modulefront',
        ];
        $txt = static::getEverShortcodes($txt, $context);
        $shortcodeHandlers = [
            '[alert' => 'getAlertShortcode',
            '[everfaq_product' => ['method' => 'getProductFaqShortcodes', 'args' => ['context', 'module']],
            '[everfaq' => ['method' => 'getFaqShortcodes', 'args' => ['context', 'module']],
            '[everinstagram]' => ['method' => 'getInstagramShortcodes', 'args' => ['context', 'module']],
            '[product' => ['method' => 'getProductShortcodes', 'args' => ['context', 'module']],
            '[product_image' => ['method' => 'getProductImageShortcodes', 'args' => ['context', 'module']],
            '[productfeature' => ['method' => 'getFeatureProductShortcodes', 'args' => ['context', 'module']],
            '[productfeaturevalue' => ['method' => 'getFeatureValueProductShortcodes', 'args' => ['context', 'module']],
            '[category' => ['method' => 'getCategoryShortcodes', 'args' => ['context', 'module']],
            '[manufacturer' => ['method' => 'getManufacturerShortcodes', 'args' => ['context', 'module']],
            '[brands' => ['method' => 'getBrandsShortcode', 'args' => ['context', 'module']],
            '[storelocator]' => ['method' => 'generateGoogleMap', 'args' => ['context', 'module']],
            '[evermap]' => ['method' => 'getEverMapShortcode', 'args' => ['context', 'module']],
            '{hook h=' => 'replaceHook',
            '[llorem]' => ['method' => 'generateLoremIpsum', 'args' => ['context']],
            '[everblock' => ['method' => 'getEverBlockShortcode', 'args' => ['context']],
            '[subcategories' => ['method' => 'getSubcategoriesShortcode', 'args' => ['context', 'module']],
            '[everstore' => ['method' => 'getStoreShortcode', 'args' => ['context', 'module']],
            '[video' => 'getVideoShortcode',
            '[qcdacf' => ['method' => 'getQcdAcfCode', 'args' => ['context']],
            '[displayQcdSvg' => ['method' => 'getQcdSvgCode', 'args' => ['context']],
            '[everimg' => ['method' => 'getEverImgShortcode', 'args' => ['context', 'module']],
            '[wordpress-posts]' => ['method' => 'getWordpressPostsShortcode', 'args' => ['context', 'module']],
            '[googlereviews' => ['method' => 'getGoogleReviewsShortcode', 'args' => ['context', 'module']],
            '[best-sales' => ['method' => 'getBestSalesShortcode', 'args' => ['context', 'module']],
            '[categorybestsales' => ['method' => 'getCategoryBestSalesShortcode', 'args' => ['context', 'module']],
            '[brandbestsales' => ['method' => 'getBrandBestSalesShortcode', 'args' => ['context', 'module']],
            '[featurebestsales' => ['method' => 'getFeatureBestSalesShortcode', 'args' => ['context', 'module']],
            '[featurevaluebestsales' => ['method' => 'getFeatureValueBestSalesShortcode', 'args' => ['context', 'module']],
            '[last-products' => ['method' => 'getLastProductsShortcode', 'args' => ['context', 'module']],
            '[recently_viewed' => ['method' => 'getRecentlyViewedShortcode', 'args' => ['context', 'module']],
            '[promo-products' => ['method' => 'getPromoProductsShortcode', 'args' => ['context', 'module']],
            '[products_by_tag' => ['method' => 'getProductsByTagShortcode', 'args' => ['context', 'module']],
            '[low_stock' => ['method' => 'getLowStockShortcode', 'args' => ['context', 'module']],
            '[evercart]' => ['method' => 'getCartShortcode', 'args' => ['context', 'module']],
            '[cart_total]' => ['method' => 'getCartTotalShortcode', 'args' => ['context']],
            '[cart_quantity]' => ['method' => 'getCartQuantityShortcode', 'args' => ['context']],
            '[shop_logo]' => ['method' => 'getShopLogoShortcode', 'args' => ['context']],
            '[newsletter_form]' => ['method' => 'getNewsletterFormShortcode', 'args' => ['context', 'module']],
            '[nativecontact]' => ['method' => 'getNativeContactShortcode', 'args' => ['context', 'module']],
            '[evercontactform_open]' => ['method' => 'getFormShortcode', 'args' => ['context', 'module']],
            '[everorderform_open]' => ['method' => 'getOrderFormShortcode', 'args' => ['context', 'module']],
            '[random_product' => ['method' => 'getRandomProductsShortcode', 'args' => ['context', 'module']],
            '[accessories' => ['method' => 'getAccessoriesShortcode', 'args' => ['context', 'module']],
            '[linkedproducts' => ['method' => 'getLinkedProductsShortcode', 'args' => ['context', 'module']],
            '[crosselling' => ['method' => 'getCrossSellingShortcode', 'args' => ['context', 'module']],
            '[widget' => 'getWidgetShortcode',
            '[prettyblocks' => ['method' => 'getPrettyblocksShortcodes', 'args' => ['context', 'module']],
            '[everaddtocart' => ['method' => 'getAddToCartShortcode', 'args' => ['context', 'module']],
            '[cms' => ['method' => 'getCmsShortcode', 'args' => ['context']],
        ];

        foreach ($shortcodeHandlers as $needle => $handler) {
            if (strpos($txt, $needle) === false) {
                continue;
            }

            if (is_string($handler)) {
                $method = $handler;
                $args = [];
            } else {
                $method = $handler['method'];
                $args = $handler['args'] ?? [];
            }

            $callArgs = array_merge([$txt], static::resolveShortcodeArgs($args, $context, $module));
            $txt = forward_static_call_array([static::class, $method], $callArgs);
        }
        if (in_array($context->controller->controller_type, $controllerTypes)) {
            $txt = static::getCustomerShortcodes($txt, $context);
            $txt = static::obfuscateTextByClass($txt);
        }
        $txt = static::renderSmartyVars($txt, $context);
        Hook::exec('displayAfterRenderingShortcodes', ['html' => &$txt]);
        return $txt;
    }

    /**
     * Parse attributes from a shortcode string.
     *
     * @param string $attrStr
     *
     * @return array<string, mixed>
     */
    protected static function parseShortcodeAttrs(string $attrStr): array
    {
        $attrs = [];
        preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $attrStr, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = strtolower($match[1]);
            $value = trim($match[2]);
            if (strpos($value, '|') !== false) {
                $value = array_filter(array_map('trim', explode('|', $value)));
            }
            $attrs[$key] = $value;
        }

        return $attrs;
    }

    /**
     * Build the argument list for a shortcode handler based on configuration values.
     *
     * @param array<int, string> $args
     *
     * @return array<int, mixed>
     */
    protected static function resolveShortcodeArgs(array $args, Context $context, Everblock $module): array
    {
        $resolved = [];

        foreach ($args as $arg) {
            switch ($arg) {
                case 'context':
                    $resolved[] = $context;
                    break;
                case 'module':
                    $resolved[] = $module;
                    break;
            }
        }

        return $resolved;
    }

    /**
     * Replace products_by_tag shortcode with rendered product list.
     *
     * @param string $txt
     * @param Context $context
     * @param Everblock $module
     *
     * @return string
     */
    protected static function getProductsByTagShortcode(string $txt, Context $context, Everblock $module): string
    {
        return (string) preg_replace_callback('/\[products_by_tag\s+([^\]]+)\]/i', function ($matches) use ($context, $module) {
            $attrs = static::parseShortcodeAttrs($matches[1]);

            $tagNames = [];
            if (isset($attrs['tag'])) {
                $tagNames = array_filter(array_map('trim', (array) $attrs['tag']));
            }

            $tagIds = [];
            if (isset($attrs['tag_id'])) {
                $tagIds = array_filter(array_map('intval', (array) $attrs['tag_id']));
            }

            if (empty($tagNames) && empty($tagIds)) {
                return '';
            }

            $match = isset($attrs['match']) && strtolower($attrs['match']) === 'all' ? 'all' : 'any';
            $limit = isset($attrs['limit']) ? max(1, (int) $attrs['limit']) : 12;
            $offset = isset($attrs['offset']) ? max(0, (int) $attrs['offset']) : 0;
            $order = isset($attrs['order']) ? strtolower($attrs['order']) : 'position';
            $way = isset($attrs['way']) ? strtolower($attrs['way']) : 'asc';
            $cols = isset($attrs['cols']) ? (int) $attrs['cols'] : null;
            $visibilityAttr = isset($attrs['visibility']) ? $attrs['visibility'] : 'both|catalog';
            $visibilities = array_filter(array_map('trim', explode('|', $visibilityAttr)));
            if (empty($visibilities)) {
                $visibilities = ['both', 'catalog'];
            }

            $allowedOrders = ['position', 'name', 'price', 'date_add', 'rand'];
            if (!in_array($order, $allowedOrders, true)) {
                $order = 'position';
            }

            $allowedWays = ['asc', 'desc'];
            if (!in_array($way, $allowedWays, true)) {
                $way = 'asc';
            }

            $cacheKey = md5(json_encode([
                $tagNames,
                $tagIds,
                $match,
                $limit,
                $offset,
                $order,
                $way,
                $visibilities,
                (int) $context->shop->id,
                (int) $context->language->id,
            ]));

            static $cache = [];

            if (!isset($cache[$cacheKey])) {
                $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
                $params = [
                    'id_shop' => (int) $context->shop->id,
                    'id_lang' => (int) $context->language->id,
                ];

                $visPlaceholders = [];
                foreach ($visibilities as $i => $vis) {
                    $ph = ':vis' . $i;
                    $visPlaceholders[] = $ph;
                    $params['vis' . $i] = $vis;
                }

                $sql = 'SELECT pt.id_product'
                    . ' FROM ' . _DB_PREFIX_ . 'product_tag pt'
                    . ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps ON (ps.id_product = pt.id_product'
                    . ' AND ps.id_shop = :id_shop AND ps.active = 1'
                    . ' AND ps.visibility IN (' . implode(',', $visPlaceholders) . '))'
                    . ' INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.id_product = pt.id_product'
                    . ' AND pl.id_lang = :id_lang AND pl.id_shop = :id_shop)'
                    . ' INNER JOIN ' . _DB_PREFIX_ . 'product p ON (p.id_product = pt.id_product)';

                $tagNamePlaceholders = [];
                if (!empty($tagNames)) {
                    $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'tag t ON (t.id_tag = pt.id_tag AND t.id_lang = :id_lang)';
                    foreach ($tagNames as $i => $tagName) {
                        $ph = ':tagname' . $i;
                        $tagNamePlaceholders[] = $ph;
                        $params['tagname' . $i] = $tagName;
                    }
                }

                $tagIdPlaceholders = [];
                foreach ($tagIds as $i => $tagId) {
                    $ph = ':tagid' . $i;
                    $tagIdPlaceholders[] = $ph;
                    $params['tagid' . $i] = (int) $tagId;
                }

                $conditions = [];
                if ($tagIdPlaceholders) {
                    $conditions[] = 'pt.id_tag IN (' . implode(',', $tagIdPlaceholders) . ')';
                }
                if ($tagNamePlaceholders) {
                    $conditions[] = 't.name IN (' . implode(',', $tagNamePlaceholders) . ')';
                }

                if ($conditions) {
                    if (count($conditions) > 1) {
                        $sql .= ' WHERE (' . implode(' OR ', $conditions) . ')';
                    } else {
                        $sql .= ' WHERE ' . $conditions[0];
                    }
                }

                $sql .= ' GROUP BY pt.id_product';
                $tagCount = count(array_unique(array_merge($tagIds, $tagNames)));
                if ($match === 'all' && $tagCount > 1) {
                    $sql .= ' HAVING COUNT(DISTINCT pt.id_tag) = ' . (int) $tagCount;
                }

                if ($order === 'rand') {
                    $sql .= ' ORDER BY RAND()';
                } else {
                    switch ($order) {
                        case 'name':
                            $orderBy = 'pl.name';
                            break;
                        case 'price':
                            $orderBy = 'ps.price';
                            break;
                        case 'date_add':
                            $orderBy = 'p.date_add';
                            break;
                        case 'position':
                        default:
                            $orderBy = 'ps.position';
                    }
                    $sql .= ' ORDER BY ' . $orderBy . ' ' . strtoupper($way);
                }

                $sql .= ' LIMIT ' . (int) $offset . ', ' . (int) $limit;

                $rows = $db->executeS($sql, $params);
                $productIds = [];
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $productIds[] = (int) $row['id_product'];
                    }
                }

                $cache[$cacheKey] = static::everPresentProducts($productIds, $context);
            }

            $products = $cache[$cacheKey];

            $context->smarty->assign([
                'products' => $products,
                'cols' => $cols,
                'total' => count($products),
                'params' => $attrs,
            ]);

            $templatePath = static::getTemplatePath('hook/products_by_tag.tpl', $module);

            return $context->smarty->fetch($templatePath);
        }, $txt);
    }

    /**
     * Replace low_stock shortcode with rendered low stock product list.
     *
     * Examples:
     * [low_stock]
     * [low_stock limit="8" threshold="3" order="qty" way="asc"]
     * [low_stock id_category="12|34" days="30" order="date_add" way="desc"]
     *
     * @param string  $txt
     * @param Context $context
     * @param Everblock $module
     *
     * @return string
     */
    protected static function getLowStockShortcode(string $txt, Context $context, Everblock $module): string
    {
        return (string) preg_replace_callback('/\[low_stock(?:\s+([^\]]+))?\]/i', function ($matches) use ($context, $module) {
            $attrs = static::parseShortcodeAttrs($matches[1] ?? '');

            $idShop = (int) $context->shop->id;
            $idShopGroup = (int) $context->shop->id_shop_group;
            $idLang = (int) $context->language->id;

            $limit = isset($attrs['limit']) ? max(1, (int) $attrs['limit']) : 10;
            $offset = isset($attrs['offset']) ? max(0, (int) $attrs['offset']) : 0;
            $threshold = isset($attrs['threshold'])
                ? (int) $attrs['threshold']
                : (int) (Configuration::get('EVERBLOCK_LOW_STOCK_THRESHOLD') ?: 5);

            $match = strtolower($attrs['match'] ?? 'lte');
            $allowedMatch = ['lt', 'lte', 'eq', 'gt', 'gte'];
            if (!in_array($match, $allowedMatch, true)) {
                $match = 'lte';
            }
            $operatorMap = [
                'lt' => '<',
                'lte' => '<=',
                'eq' => '=',
                'gt' => '>',
                'gte' => '>=',
            ];
            $operator = $operatorMap[$match];

            $order = strtolower($attrs['order'] ?? 'qty');
            $allowedOrders = ['qty', 'date_add', 'name', 'price', 'sales', 'rand'];
            if (!in_array($order, $allowedOrders, true)) {
                $order = 'qty';
            }

            $way = strtolower($attrs['way'] ?? 'asc');
            $allowedWays = ['asc', 'desc'];
            if (!in_array($way, $allowedWays, true)) {
                $way = 'asc';
            }
            if ($order === 'rand') {
                $way = 'asc';
            }

            $days = isset($attrs['days']) ? max(0, (int) $attrs['days']) : 0;

            $idCategories = isset($attrs['id_category']) ? array_filter(array_map('intval', (array) $attrs['id_category'])) : [];
            $idManufacturers = isset($attrs['id_manufacturer']) ? array_filter(array_map('intval', (array) $attrs['id_manufacturer'])) : [];

            $visibilityAttr = $attrs['visibility'] ?? 'both,catalog';
            $visibilityList = array_map('trim', explode(',', $visibilityAttr));
            $allowedVis = ['both', 'catalog', 'search', 'none'];
            $visibilities = array_values(array_intersect($visibilityList, $allowedVis));
            if (empty($visibilities)) {
                $visibilities = ['both', 'catalog'];
            }

            $availableOnly = isset($attrs['available_only']) ? (int) $attrs['available_only'] : 1;
            $cols = isset($attrs['cols']) ? (int) $attrs['cols'] : 4;
            $by = strtolower($attrs['by'] ?? 'product');
            if (!in_array($by, ['product', 'combination'], true)) {
                $by = 'product';
            }

            $cacheKey = md5(json_encode([
                $attrs,
                $idShop,
                $idLang,
            ]));

            static $cache = [];

            if (!isset($cache[$cacheKey])) {
                $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
                $params = [
                    'id_shop' => $idShop,
                    'id_shop_group' => $idShopGroup,
                    'id_lang' => $idLang,
                    'threshold' => $threshold,
                    'offset' => $offset,
                    'limit' => $limit,
                ];

                $visPlaceholders = [];
                foreach ($visibilities as $i => $vis) {
                    $ph = ':vis' . $i;
                    $visPlaceholders[] = $ph;
                    $params['vis' . $i] = $vis;
                }

                $sql = 'SELECT p.id_product';
                if ($by === 'combination') {
                    $sql .= ', sa.id_product_attribute';
                }
                if ($order === 'sales') {
                    $sql .= ', COALESCE(s.sold_qty,0) AS sold_qty';
                }
                $sql .= ' FROM ' . _DB_PREFIX_ . 'product p'
                    . ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps ON (ps.id_product = p.id_product AND ps.id_shop = :id_shop)'
                    . ' INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.id_product = p.id_product AND pl.id_shop = :id_shop AND pl.id_lang = :id_lang)'
                    . ' INNER JOIN ' . _DB_PREFIX_ . 'stock_available sa ON (sa.id_product = p.id_product';
                if ($by === 'product') {
                    $sql .= ' AND sa.id_product_attribute = 0';
                } else {
                    $sql .= ' AND sa.id_product_attribute > 0';
                }
                $sql .= ' AND (sa.id_shop = :id_shop OR (sa.id_shop IS NULL AND sa.id_shop_group = :id_shop_group)))';

                if (!empty($idCategories)) {
                    $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'category_product cp ON (cp.id_product = p.id_product)';
                }
                if ($order === 'sales') {
                    if ($by === 'combination') {
                        $sql .= ' LEFT JOIN (SELECT od.product_id, od.product_attribute_id, SUM(od.product_quantity) AS sold_qty'
                            . ' FROM ' . _DB_PREFIX_ . 'order_detail od'
                            . ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order'
                            . ' WHERE o.valid = 1'
                            . ' GROUP BY od.product_id, od.product_attribute_id) s'
                            . ' ON (s.product_id = p.id_product AND s.product_attribute_id = sa.id_product_attribute)';
                    } else {
                        $sql .= ' LEFT JOIN (SELECT od.product_id, SUM(od.product_quantity) AS sold_qty'
                            . ' FROM ' . _DB_PREFIX_ . 'order_detail od'
                            . ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order'
                            . ' WHERE o.valid = 1'
                            . ' GROUP BY od.product_id) s'
                            . ' ON (s.product_id = p.id_product)';
                    }
                }

                $sql .= ' WHERE ps.active = 1'
                    . ' AND ps.visibility IN (' . implode(',', $visPlaceholders) . ')'
                    . ' AND sa.quantity ' . $operator . ' :threshold';

                if ($availableOnly === 1) {
                    $sql .= ' AND ps.available_for_order = 1';
                }
                if ($days > 0) {
                    $sql .= ' AND p.date_add >= :date_limit';
                    $params['date_limit'] = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
                }
                if (!empty($idCategories)) {
                    $catPlaceholders = [];
                    foreach ($idCategories as $i => $idCat) {
                        $ph = ':cat' . $i;
                        $catPlaceholders[] = $ph;
                        $params['cat' . $i] = $idCat;
                    }
                    $sql .= ' AND cp.id_category IN (' . implode(',', $catPlaceholders) . ')';
                }
                if (!empty($idManufacturers)) {
                    $manPlaceholders = [];
                    foreach ($idManufacturers as $i => $idMan) {
                        $ph = ':man' . $i;
                        $manPlaceholders[] = $ph;
                        $params['man' . $i] = $idMan;
                    }
                    $sql .= ' AND p.id_manufacturer IN (' . implode(',', $manPlaceholders) . ')';
                }

                $sql .= ' GROUP BY p.id_product';
                if ($by === 'combination') {
                    $sql .= ', sa.id_product_attribute';
                }

                if ($order === 'rand') {
                    $sql .= ' ORDER BY RAND()';
                } else {
                    $fieldMap = [
                        'qty' => 'sa.quantity',
                        'date_add' => 'p.date_add',
                        'name' => 'pl.name',
                        'price' => 'ps.price',
                        'sales' => 'sold_qty',
                    ];
                    $sql .= ' ORDER BY ' . $fieldMap[$order] . ' ' . strtoupper($way);
                }

                $sql .= ' LIMIT :offset, :limit';

                $rows = $db->executeS($sql, $params);

                $products = [];
                $variants = [];

                if (!empty($rows)) {
                    if ($by === 'product') {
                        $ids = array_map(static function ($row) {
                            return (int) $row['id_product'];
                        }, $rows);
                        $products = static::everPresentProducts($ids, $context);
                    } else {
                        $assembler = new \ProductAssembler($context);
                        $presenterFactory = new \ProductPresenterFactory($context);
                        $presentationSettings = $presenterFactory->getPresentationSettings();
                        $presenter = new ProductListingPresenter(
                            new ImageRetriever($context->link),
                            $context->link,
                            new PriceFormatter(),
                            new ProductColorsRetriever(),
                            $context->getTranslator()
                        );
                        $presentationSettings->showPrices = true;

                        foreach ($rows as $row) {
                            $raw = [
                                'id_product' => (int) $row['id_product'],
                                'id_product_attribute' => (int) $row['id_product_attribute'],
                                'id_lang' => $idLang,
                                'id_shop' => $idShop,
                            ];
                            $assembled = $assembler->assembleProduct($raw);
                            $presented = $presenter->present($presentationSettings, $assembled, $context->language);
                            $products[] = $presented;

                            $combination = new Combination((int) $row['id_product_attribute']);
                            $attrNames = [];
                            if (Validate::isLoadedObject($combination)) {
                                $combAttrs = $combination->getAttributesName($idLang);
                                foreach ($combAttrs as $attr) {
                                    $attrNames[] = $attr['name'];
                                }
                            }
                            $imageId = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                                'SELECT id_image FROM ' . _DB_PREFIX_ . 'product_attribute_image WHERE id_product_attribute = ' . (int) $row['id_product_attribute'] . ' ORDER BY id_image ASC'
                            );
                            $imageUrl = '';
                            if ($imageId && !empty($presented['link_rewrite'])) {
                                $imageUrl = $context->link->getImageLink($presented['link_rewrite'], $imageId);
                            }

                            $variants[] = [
                                'id_product' => (int) $row['id_product'],
                                'id_product_attribute' => (int) $row['id_product_attribute'],
                                'attributes' => $attrNames,
                                'url' => $context->link->getProductLink(
                                    (int) $row['id_product'],
                                    null,
                                    null,
                                    null,
                                    $idLang,
                                    $idShop,
                                    (int) $row['id_product_attribute']
                                ),
                                'image' => $imageUrl,
                            ];
                        }
                    }
                }

                $cache[$cacheKey] = [
                    'products' => $products,
                    'variants' => $variants,
                ];
            }

            $data = $cache[$cacheKey];

            $context->smarty->assign([
                'products' => $data['products'],
                'variants' => $data['variants'],
                'cols' => $cols,
                'params' => $attrs,
            ]);

            $templatePath = static::getTemplatePath('hook/low_stock.tpl', $module);

            return $context->smarty->fetch($templatePath);
        }, $txt);
    }

    public static function getCrossSellingShortcode(string $txt, Context $context, Everblock $module): string
    {

        preg_match_all(
            '/\[crosselling(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+orderby=(\w+))?(?:\s+orderway=(ASC|DESC))?(?:\s+carousel=(true|false))?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $limit = isset($match[1]) && $match[1] !== '' ? (int) $match[1] : (isset($match[2]) ? (int) $match[2] : 4);
            $orderBy = isset($match[3]) ? strtolower($match[3]) : 'id_product';
            $orderWay = isset($match[4]) ? strtoupper($match[4]) : 'ASC';
            $carousel = isset($match[5]) && strtolower($match[5]) === 'true';

            $allowedOrderBy = ['id_product', 'price', 'name', 'date_add', 'position'];
            $allowedOrderWay = ['ASC', 'DESC'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'id_product';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'ASC';
            }

            $cartIds = [];
            if ($context->cart && $context->cart->id) {
                $cartIds = array_map(function ($p) {
                    return (int) $p['id_product'];
                }, $context->cart->getProducts());
            }

            if (empty($cartIds)) {
                $bestIds = static::filterAvailableCrossSellingProducts(
                    static::getBestSellingProductIds($limit, $orderBy, $orderWay),
                    $context
                );
                $everPresentProducts = static::everPresentProducts($bestIds, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'crosselling',
                    ]);
                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);
                } else {
                    $replacement = '';
                }

                $txt = str_replace($match[0], $replacement, $txt);
                continue;
            }

            $cacheId = 'getCrossSellingShortcode_' . md5(json_encode([$cartIds, $limit, $orderBy, $orderWay]));
            if (!EverblockCache::isCacheStored($cacheId)) {
                $sql = new DbQuery();
                $sql->select('DISTINCT p.id_product');
                $sql->from('accessory', 'a');
                $sql->innerJoin('product', 'p', 'p.id_product = a.id_product_2');
                $sql->where('a.id_product_1 IN (' . implode(',', $cartIds) . ')');
                $sql->where('p.active = 1');
                $sql->orderBy('p.' . pSQL($orderBy) . ' ' . pSQL($orderWay));
                $sql->limit($limit * 2);
                $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                EverblockCache::cacheStore($cacheId, $productIds);
            } else {
                $productIds = EverblockCache::cacheRetrieve($cacheId);
            }

            $ids = [];
            foreach ($productIds as $row) {
                $id = (int) $row['id_product'];
                if (!in_array($id, $cartIds) && !in_array($id, $ids)
                    && static::isProductAvailableForCrossSelling($id, $context)
                ) {
                    $ids[] = $id;
                }
                if (count($ids) >= $limit) {
                    break;
                }
            }

            if (count($ids) < $limit) {
                $categoryIds = [];
                foreach ($cartIds as $cartId) {
                    foreach (Product::getProductCategories($cartId) as $cid) {
                        $categoryIds[(int) $cid] = true;
                    }
                }
                foreach (array_keys($categoryIds) as $cid) {
                    if (count($ids) >= $limit) {
                        break;
                    }
                    $categoryProducts = static::getProductsByCategoryId($cid, $limit * 2, $orderBy, $orderWay);
                    foreach ($categoryProducts as $cproduct) {
                        $pid = (int) $cproduct['id_product'];
                        if (!in_array($pid, $cartIds) && !in_array($pid, $ids)
                            && static::isProductAvailableForCrossSelling($pid, $context)
                        ) {
                            $ids[] = $pid;
                        }
                        if (count($ids) >= $limit) {
                            break 2;
                        }
                    }
                }
            }

            if (count($ids) < $limit) {
                $bestIds = static::getBestSellingProductIds($limit * 2, $orderBy, $orderWay);
                foreach ($bestIds as $bid) {
                    if (count($ids) >= $limit) {
                        break;
                    }
                    if (!in_array($bid, $cartIds) && !in_array($bid, $ids)
                        && static::isProductAvailableForCrossSelling($bid, $context)
                    ) {
                        $ids[] = $bid;
                    }
                }
            }

            if (empty($ids)) {
                $bestIds = static::filterAvailableCrossSellingProducts(
                    static::getBestSellingProductIds($limit, $orderBy, $orderWay),
                    $context
                );
                $everPresentProducts = static::everPresentProducts($bestIds, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'crosselling',
                    ]);
                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);
                } else {
                    $replacement = '';
                }

                $txt = str_replace($match[0], $replacement, $txt);
                continue;
            }

            $filteredIds = static::filterAvailableCrossSellingProducts($ids, $context);
            $everPresentProducts = static::everPresentProducts($filteredIds, $context);

            if (!empty($everPresentProducts)) {
                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => $carousel,
                    'shortcodeClass' => 'crosselling',
                ]);
                $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                $replacement = $context->smarty->fetch($templatePath);
                $txt = str_replace($match[0], $replacement, $txt);
            } else {
                $txt = str_replace($match[0], '', $txt);
            }
        }

        return $txt;
    }

    protected static function filterAvailableCrossSellingProducts(array $productIds, Context $context): array
    {
        $filtered = [];

        foreach ($productIds as $productId) {
            $productId = (int) $productId;

            if ($productId <= 0) {
                continue;
            }

            if (static::isProductAvailableForCrossSelling($productId, $context)) {
                $filtered[] = $productId;
            }
        }

        return $filtered;
    }

    protected static function isProductAvailableForCrossSelling(int $productId, Context $context): bool
    {
        static $availabilityCache = [];

        if (isset($availabilityCache[$productId])) {
            return $availabilityCache[$productId];
        }

        if (!Configuration::get('PS_STOCK_MANAGEMENT')) {
            $availabilityCache[$productId] = true;

            return true;
        }

        $product = new Product($productId);

        if (!Validate::isLoadedObject($product)) {
            $availabilityCache[$productId] = false;

            return false;
        }

        $quantity = StockAvailable::getQuantityAvailableByProduct(
            $productId,
            0,
            (int) $context->shop->id
        );

        if ($quantity > 0) {
            $availabilityCache[$productId] = true;

            return true;
        }

        $isAllowedWhenOutOfStock = Product::isAvailableWhenOutOfStock((int) $product->out_of_stock);

        $availabilityCache[$productId] = $isAllowedWhenOutOfStock;

        return $isAllowedWhenOutOfStock;
    }

    public static function addToCartByUrl(Context $context, int $productId, int $productAttributeId = 0, int $quantity = 1)
    {
        try {
            if (!isset($context->cart) || !$context->cart->id) {
                // Création d'un nouveau panier
                $cart = new Cart();
                $cart->id_lang = (int) $context->language->id;
                $cart->id_currency = (int) $context->currency->id;
                $cart->id_shop_group = (int) $context->shop->id_shop_group;
                $cart->id_shop = (int) $context->shop->id;
                $cart->id_customer = (int) $context->customer->id;

                if ($context->customer->id) {
                    $cart->id_address_delivery = (int) Address::getFirstCustomerAddressId($cart->id_customer);
                    $cart->id_address_invoice = (int) $cart->id_address_delivery;
                } else {
                    $cart->id_address_delivery = 0;
                    $cart->id_address_invoice = 0;
                }

                if ($cart->add()) {
                    // Panier créé avec succès, associez-le au contexte
                    $context->cart = $cart;

                    // Sauvegarde du panier dans la session
                    $context->cookie->id_cart = (int) $cart->id;
                    $context->cookie->write(); // Assurez-vous que le cookie est mis à jour

                    // Application des règles de panier si nécessaire
                    CartRule::autoRemoveFromCart($context);
                    CartRule::autoAddToCart($context);
                }
            }
            $updated = $context->cart->updateQty($quantity, $productId, $productAttributeId);
            if ($updated) {
                $module = Module::getInstanceByName('everblock');
                $context->controller->success[] = $module->l('Product added to cart successfully');
                $context->controller->redirectWithNotifications(
                    $context->link->getPageLink('cart', true, null, ['action' => 'show'])
                );
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage());
        }
    }

    public static function getAddToCartShortcode(string $txt, Context $context, Everblock $module): string
    {
        // Expression régulière pour capturer le shortcode avec les paramètres 'ref' et optionnellement 'text'
        $pattern = '/\[everaddtocart\s+ref="([^"]+)"(?:\s+text="([^"]+)")?\]/';

        // Remplacement de chaque occurrence du shortcode par le lien add-to-cart
        $txt = preg_replace_callback($pattern, function ($matches) use ($context, $module) {
            $productReference = $matches[1];
            $customText = isset($matches[2]) ? $matches[2] : $module->l('Add to cart'); // Texte par défaut
            $idProduct = 0;
            $idProductAttribute = 0;

            // Rechercher la référence dans le produit principal
            $idProduct = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT `id_product`
                FROM `' . _DB_PREFIX_ . 'product`
                WHERE `reference` = "' . pSQL($productReference) . '"
            ');

            // Si aucun produit trouvé, vérifier dans les déclinaisons (product_attribute)
            if (!$idProduct) {
                $result = Db::getInstance()->getRow('
                    SELECT pa.`id_product`, pa.`id_product_attribute`
                    FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                    WHERE pa.`reference` = "' . pSQL($productReference) . '"
                ');

                // Si une déclinaison est trouvée, récupérer son id_product et id_product_attribute
                if ($result) {
                    $idProduct = (int) $result['id_product'];
                    $idProductAttribute = (int) $result['id_product_attribute'];
                }
            }
            // Si aucun produit ni déclinaison n'est trouvé, on retourne une chaîne vide
            if (!$idProduct) {
                return ''; // Si aucun produit ou déclinaison n'est trouvé, ne rien afficher
            }

            // Construction de l'URL pour ajouter au panier
            $link = $context->link->getPageLink('index', true, null, [
                'eac' => $idProduct,
                'id_product' => $idProduct,
                'id_product_attribute' => $idProductAttribute, // 0 si pas de déclinaison
                'qty' => 1 // Quantité par défaut
            ]);

            // Générer le lien HTML avec le texte personnalisé ou par défaut
            return '<a href="' . $link . '" class="btn btn-primary ">' . htmlspecialchars($customText) . '</a>';
        }, $txt);

        return $txt;
    }

    public static function getFaqShortcodes(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/faq.tpl', $module);
        $pattern = '/\[everfaq tag="([^"]+)"\]/';

        $txt = preg_replace_callback($pattern, function ($matches) use ($context, $templatePath) {
            $tagName = $matches[1];

            $faqs = EverblockFaq::getFaqByTagName($context->shop->id, $context->language->id, $tagName);

            $context->smarty->assign('everFaqs', $faqs);

            return $context->smarty->fetch($templatePath);

        }, $txt);

        return $txt;
    }

    public static function getProductFaqShortcodes(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/faq.tpl', $module);
        $pattern = '/\[everfaq_product(?:\s+([^\]]+))?\]/';

        $txt = (string) preg_replace_callback($pattern, function ($matches) use ($context, $templatePath) {
            $attrString = isset($matches[1]) ? trim($matches[1]) : '';
            if ($attrString === '') {
                return '';
            }

            $attrs = static::parseShortcodeAttrs($attrString);
            $productId = (int) ($attrs['id_product']
                ?? ($attrs['product_id']
                ?? ($attrs['product']
                ?? ($attrs['id'] ?? 0))));

            if ($productId <= 0) {
                return '';
            }

            $faqIds = EverblockFaq::getFaqIdsByProduct($productId, $context->shop->id);
            if (empty($faqIds)) {
                return '';
            }

            $faqs = EverblockFaq::getByIds($faqIds, $context->language->id, $context->shop->id, true);
            if (empty($faqs)) {
                return '';
            }

            $context->smarty->assign('everFaqs', $faqs);

            return $context->smarty->fetch($templatePath);
        }, $txt);

        return $txt;
    }

    // Todo : trigger shortcode
    public static function getCmsShortcode(string $txt, Context $context): string
    {
        // Regex pour [cms id="X"] ou [evercms id="X"]
        preg_match_all('/\[(?:cms|evercms)\s+id="?(\d+)"?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $cmsId = (int) $match[1];

            // Récupération de la page CMS avec langue et boutique
            $cms = new CMS($cmsId, $context->language->id, $context->shop->id);

            if (Validate::isLoadedObject($cms) && $cms->active) {
                $txt = str_replace($match[0], $cms->content, $txt);
            } else {
                // Si CMS non trouvé ou inactif, on retire le shortcode
                $txt = str_replace($match[0], '', $txt);
            }
        }

        return $txt;
    }

    public static function getInstagramShortcodes(string $txt, Context $context, Everblock $module): string
    {
        $imgs = static::fetchInstagramImages();
        if (!$imgs || count($imgs) <= 0) {
            $txt = str_replace('[everinstagram]', '', $txt);
            return $txt;
        }
        $templatePath = static::getTemplatePath('hook/instagram.tpl', $module);
        $context->smarty->assign([
            'everinsta_shopid' => $context->shop->id,
            'EVERINSTA_ACCESS_TOKEN' => Configuration::get('EVERINSTA_ACCESS_TOKEN'),
            'everinsta_nbr' => 12,
            'everinsta_link' => Configuration::get('EVERINSTA_LINK'),
            'everinsta_show_caption' => Configuration::get('EVERINSTA_SHOW_CAPTION'),
            'insta_imgs' => $imgs,
        ]);

        // Chercher toutes les occurrences du shortcode [everinstagram]
        preg_match_all('/\[everinstagram\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            // Charger et rendre le contenu du template
            $renderedContent = $context->smarty->fetch($templatePath);
            
            // Remplacer chaque occurrence du shortcode par le contenu rendu du template
            $txt = str_replace($match[0], $renderedContent, $txt);
        }

        return $txt;
    }

    public static function getWordpressPostsShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all('/\[wordpress-posts\]/i', $txt, $matches, PREG_SET_ORDER);
        $templatePath = static::getTemplatePath('hook/generated_wp_posts.tpl', $module);

        if (!file_exists(_PS_MODULE_DIR_ . 'everblock/views/templates/hook/generated_wp_posts.tpl')) {
            foreach ($matches as $match) {
                $txt = str_replace($match[0], '', $txt);
            }
            return $txt;
        }

        $generatedDir = _PS_MODULE_DIR_ . 'everblock/views/templates/hook/generated_wp_posts/';
        $storedPosts = [];
        $storedFile = Configuration::get('EVERWP_POSTS_DATA_FILE');
        if ($storedFile) {
            $storedPath = $generatedDir . $storedFile;
            if (is_file($storedPath) && is_readable($storedPath)) {
                $storedContent = Tools::file_get_contents($storedPath);
                if ($storedContent !== false) {
                    $decoded = json_decode($storedContent, true);
                    if (is_array($decoded)) {
                        $storedPosts = $decoded;
                    }
                }
            }
        }

        $backgroundImage = Configuration::get('EVERWP_POSTS_BG_IMAGE');
        $backgroundUrl = '';
        if ($backgroundImage) {
            $backgroundUrl = $context->link->getBaseLink(null, null)
                . 'modules/' . $module->name . '/views/img/' . $backgroundImage;
        }
        $sliderEnabled = Configuration::get('EVERWP_POSTS_SLIDER_ENABLED');
        if ($sliderEnabled === false) {
            $sliderEnabled = true;
        }
        $context->smarty->assign([
            'everblock_wp_posts' => $storedPosts,
            'everblock_wp_blog_url' => Configuration::get('EVERWP_BLOG_URL') ?: '/blog',
            'everblock_wp_background_image' => $backgroundUrl,
            'everblock_wp_posts_slider_enabled' => (bool) $sliderEnabled,
        ]);

        foreach ($matches as $match) {
            $renderedContent = $context->smarty->fetch($templatePath);
            $txt = str_replace($match[0], $renderedContent, $txt);
        }

        return $txt;
    }

    public static function getGoogleReviewsShortcode(string $txt, Context $context, Everblock $module): string
    {
        $pattern = '/\[googlereviews(?:\s+([^\]]+))?\]/i';

        return (string) preg_replace_callback($pattern, function ($matches) use ($context, $module) {
            $attrString = isset($matches[1]) ? trim($matches[1]) : '';
            $attrs = $attrString !== '' ? static::parseShortcodeAttrs($attrString) : [];

            $overrides = [
                'api_key' => $attrs['key'] ?? ($attrs['api_key'] ?? null),
                'place_id' => $attrs['place_id'] ?? ($attrs['id'] ?? null),
                'limit' => $attrs['limit'] ?? ($attrs['max'] ?? null),
                'min_rating' => $attrs['min_rating'] ?? ($attrs['rating'] ?? null),
                'sort' => $attrs['sort'] ?? null,
                'show_rating' => $attrs['show_rating'] ?? null,
                'show_avatar' => $attrs['show_avatar'] ?? null,
                'show_cta' => $attrs['show_cta'] ?? null,
                'cta_label' => $attrs['cta_label'] ?? null,
                'cta_url' => $attrs['cta_url'] ?? null,
                'columns' => $attrs['columns'] ?? ($attrs['cols'] ?? null),
                'css_class' => $attrs['class'] ?? null,
                'heading' => $attrs['title'] ?? ($attrs['heading'] ?? null),
                'intro' => $attrs['intro'] ?? ($attrs['description'] ?? null),
            ];

            $resolved = static::resolveGoogleReviews($overrides);

            if (!$resolved['options']['is_configured']) {
                return '';
            }

            if (!empty($overrides['css_class'])) {
                $resolved['options']['css_class'] = trim(($resolved['options']['css_class'] ?? '') . ' ' . $overrides['css_class']);
            }

            $templatePath = static::getTemplatePath('hook/google_reviews.tpl', $module);

            $context->smarty->assign([
                'googleReviewsHeading' => $resolved['options']['heading'],
                'googleReviewsIntro' => $resolved['options']['intro'],
                'googleReviewsData' => $resolved['data'],
                'googleReviewsOptions' => $resolved['options'],
            ]);

            return $context->smarty->fetch($templatePath);
        }, $txt);
    }

    /**
     * Prepare Google reviews options and fetch data.
     *
     * @param array<string, mixed> $overrides
     *
     * @return array{options: array<string, mixed>, data: array<string, mixed>}
     */
    public static function resolveGoogleReviews(array $overrides = []): array
    {
        $options = static::prepareGoogleReviewsOptions($overrides);

        $data = [
            'name' => '',
            'rating' => null,
            'user_ratings_total' => 0,
            'url' => '',
            'website' => '',
            'reviews' => [],
        ];

        if ($options['is_configured']) {
            $data = static::fetchGooglePlaceReviews(
                $options['api_key'],
                $options['place_id'],
                $options['limit'],
                $options['min_rating'],
                $options['sort']
            );
        }

        $ctaUrl = trim((string) $options['cta_url']);
        if ($ctaUrl === '' && isset($data['url']) && $data['url']) {
            $ctaUrl = (string) $data['url'];
        }
        if ($ctaUrl === '' && isset($data['website']) && $data['website']) {
            $ctaUrl = (string) $data['website'];
        }
        $options['cta_url'] = $ctaUrl;

        // Never expose API credentials to the template layer
        unset($options['api_key']);

        return [
            'options' => $options,
            'data' => $data,
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    protected static function prepareGoogleReviewsOptions(array $overrides = []): array
    {
        $defaultLimit = (int) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_LIMIT');
        if ($defaultLimit <= 0) {
            $defaultLimit = 5;
        }

        $defaultMinRating = Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_MIN_RATING');
        $defaultSort = (string) Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SORT');
        $defaultSort = in_array($defaultSort, ['newest', 'most_relevant'], true) ? $defaultSort : 'most_relevant';

        $defaultShowRating = static::parseBoolean(Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_RATING'));
        if ($defaultShowRating === null) {
            $defaultShowRating = true;
        }
        $defaultShowAvatar = static::parseBoolean(Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_AVATAR'));
        if ($defaultShowAvatar === null) {
            $defaultShowAvatar = true;
        }
        $defaultShowCta = static::parseBoolean(Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_SHOW_CTA'));
        if ($defaultShowCta === null) {
            $defaultShowCta = true;
        }

        $options = [
            'api_key' => trim((string) ($overrides['api_key'] ?? Configuration::get('EVERBLOCK_GOOGLE_API_KEY'))),
            'place_id' => trim((string) ($overrides['place_id'] ?? Configuration::get('EVERBLOCK_GOOGLE_PLACE_ID'))),
            'limit' => (int) ($overrides['limit'] ?? $defaultLimit),
            'min_rating' => (float) ($overrides['min_rating'] ?? ($defaultMinRating !== false ? (float) $defaultMinRating : 0.0)),
            'sort' => (string) ($overrides['sort'] ?? $defaultSort),
            'show_rating' => $defaultShowRating,
            'show_avatar' => $defaultShowAvatar,
            'show_cta' => $defaultShowCta,
            'cta_label' => trim((string) ($overrides['cta_label'] ?? Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_CTA_LABEL'))),
            'cta_url' => trim((string) ($overrides['cta_url'] ?? Configuration::get('EVERBLOCK_GOOGLE_REVIEWS_CTA_URL'))),
            'columns' => (int) ($overrides['columns'] ?? 3),
            'css_class' => trim((string) ($overrides['css_class'] ?? '')),
            'heading' => trim((string) ($overrides['heading'] ?? '')),
            'intro' => $overrides['intro'] ?? '',
            'overrides' => [
                'api_key' => isset($overrides['api_key']) && $overrides['api_key'] !== '',
                'place_id' => isset($overrides['place_id']) && $overrides['place_id'] !== '',
                'limit' => array_key_exists('limit', $overrides),
                'min_rating' => array_key_exists('min_rating', $overrides),
                'sort' => array_key_exists('sort', $overrides),
                'columns' => array_key_exists('columns', $overrides),
            ],
        ];

        $sort = strtolower($options['sort']);
        if (!in_array($sort, ['newest', 'most_relevant'], true)) {
            $options['sort'] = 'most_relevant';
        } else {
            $options['sort'] = $sort;
        }

        if (isset($overrides['show_rating'])) {
            $parsed = static::parseBoolean($overrides['show_rating']);
            if ($parsed !== null) {
                $options['show_rating'] = $parsed;
            }
            $options['overrides']['show_rating'] = true;
        } else {
            $options['overrides']['show_rating'] = false;
        }

        if (isset($overrides['show_avatar'])) {
            $parsed = static::parseBoolean($overrides['show_avatar']);
            if ($parsed !== null) {
                $options['show_avatar'] = $parsed;
            }
            $options['overrides']['show_avatar'] = true;
        } else {
            $options['overrides']['show_avatar'] = false;
        }

        if (isset($overrides['show_cta'])) {
            $parsed = static::parseBoolean($overrides['show_cta']);
            if ($parsed !== null) {
                $options['show_cta'] = $parsed;
            }
            $options['overrides']['show_cta'] = true;
        } else {
            $options['overrides']['show_cta'] = false;
        }

        if ($options['limit'] <= 0) {
            $options['limit'] = 5;
        }

        if ($options['columns'] <= 0) {
            $options['columns'] = 3;
        }
        if ($options['columns'] > 6) {
            $options['columns'] = 6;
        }

        $options['min_rating'] = max(0.0, min(5.0, $options['min_rating']));

        $options['is_configured'] = $options['api_key'] !== '' && $options['place_id'] !== '';

        return $options;
    }

    /**
     * @param mixed $value
     */
    protected static function parseBoolean($value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if ($value === '') {
            return null;
        }

        $value = strtolower((string) $value);

        if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function fetchGooglePlaceReviews(string $apiKey, string $placeId, int $limit, float $minRating, string $sort): array
    {
        $empty = [
            'name' => '',
            'rating' => null,
            'user_ratings_total' => 0,
            'url' => '',
            'website' => '',
            'reviews' => [],
        ];

        if ($apiKey === '' || $placeId === '') {
            return $empty;
        }

        $cacheId = 'everblock_google_reviews_' . md5($placeId . '|' . $limit . '|' . $minRating . '|' . $sort);

        if (!EverblockCache::isCacheStored($cacheId)) {
            $query = [
                'place_id' => $placeId,
                'fields' => 'name,rating,user_ratings_total,reviews,url,website',
                'key' => $apiKey,
                'reviews_sort' => $sort === 'newest' ? 'newest' : 'most_relevant',
                'reviews_no_translations' => 'true',
            ];

            $endpoint = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query($query);
            $response = Tools::file_get_contents($endpoint);

            if ($response === false) {
                PrestaShopLogger::addLog('Everblock Google Reviews: unable to contact Google Places API.', 2);
                EverblockCache::cacheStore($cacheId, $empty);
            } else {
                $payload = json_decode($response, true);

                if (!is_array($payload) || ($payload['status'] ?? '') !== 'OK') {
                    $status = isset($payload['status']) ? (string) $payload['status'] : 'unknown';
                    PrestaShopLogger::addLog('Everblock Google Reviews: API status ' . $status, 2);
                    EverblockCache::cacheStore($cacheId, $empty);
                } else {
                    $result = isset($payload['result']) && is_array($payload['result']) ? $payload['result'] : [];
                    $reviews = isset($result['reviews']) && is_array($result['reviews']) ? $result['reviews'] : [];
                    $filteredReviews = [];

                    foreach ($reviews as $review) {
                        if (!is_array($review)) {
                            continue;
                        }
                        $rating = isset($review['rating']) ? (float) $review['rating'] : 0.0;
                        if ($rating < $minRating) {
                            continue;
                        }

                        $filteredReviews[] = [
                            'author_name' => $review['author_name'] ?? '',
                            'author_url' => $review['author_url'] ?? '',
                            'profile_photo_url' => $review['profile_photo_url'] ?? '',
                            'rating' => $rating,
                            'text' => $review['text'] ?? '',
                            'relative_time_description' => $review['relative_time_description'] ?? '',
                            'time' => isset($review['time']) ? (int) $review['time'] : null,
                        ];
                    }

                    $data = [
                        'name' => $result['name'] ?? '',
                        'rating' => isset($result['rating']) ? (float) $result['rating'] : null,
                        'user_ratings_total' => isset($result['user_ratings_total']) ? (int) $result['user_ratings_total'] : 0,
                        'url' => $result['url'] ?? '',
                        'website' => $result['website'] ?? '',
                        'reviews' => array_slice($filteredReviews, 0, $limit),
                    ];

                    EverblockCache::cacheStore($cacheId, $data);
                }
            }
        }

        $cachedData = EverblockCache::cacheRetrieve($cacheId);

        return is_array($cachedData) ? $cachedData : $empty;
    }

    public static function getProductShortcodes(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
        // Update regex to capture optional carousel parameter
        preg_match_all('/\[product\s+(\d+(?:,\s*\d+)*)(?:\s+carousel=(true|false))?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $productIdsArray = array_map('intval', explode(',', $match[1]));
            $carousel = isset($match[2]) && $match[2] === 'true';
            $everPresentProducts = static::everPresentProducts($productIdsArray, $context);
            
            if (!empty($everPresentProducts)) {
                // Assign products and carousel flag to the template
                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => $carousel,
                    'shortcodeClass' => 'product'
                ]);
                $renderedContent = $context->smarty->fetch($templatePath);
                
                $txt = str_replace($match[0], $renderedContent, $txt);
            }
        }

        return $txt;
    }

    public static function getProductImageShortcodes(string $txt, Context $context, Everblock $module): string
    {
        // Debug: vérifier si le shortcode est détecté
        if (strpos($txt, '[product_image') === false) {
            return $txt; // Pas de shortcode à traiter
        }
        
        $templatePath = static::getTemplatePath('hook/product_image.tpl', $module);
        // Regex pour capturer [product_image id_product] ou [product_image id_product image_number]
        preg_match_all('/\[product_image\s+(\d+)(?:\s+(\d+))?\]/i', $txt, $matches, PREG_SET_ORDER);
        
        // Debug: vérifier si des matches sont trouvés
        if (empty($matches)) {
            // Aucun match trouvé, retourner le texte original avec un debug
            return str_replace('[product_image', '<!-- DEBUG: product_image shortcode detected but no valid matches -->[product_image', $txt);
        }

        foreach ($matches as $match) {
            $productId = (int) $match[1];
            $imageNumber = isset($match[2]) ? (int) $match[2] : 1; // Par défaut, première image
            

            try {
                // Vérifier que le produit existe et est actif
                $product = new Product($productId, true, $context->language->id, $context->shop->id);
                if (!Validate::isLoadedObject($product) || !(bool) $product->active) {
                    // Remplacer par une chaîne vide si le produit n'existe pas
                    $txt = str_replace($match[0], '', $txt);
                    continue;
                }
                
                // Récupérer les images du produit
                $images = Image::getImages($context->language->id, $productId);
                if (empty($images)) {
                    // Pas d'images disponibles
                    $txt = str_replace($match[0], '', $txt);
                    continue;
                }
                
                // Sélectionner l'image demandée (ou la première si le numéro dépasse)
                $imageIndex = min($imageNumber - 1, count($images) - 1); // Index basé sur 0
                $imageIndex = max(0, $imageIndex); // S'assurer que l'index n'est pas négatif
                $selectedImage = $images[$imageIndex];
                
                // Vérifier que nous avons les données nécessaires
                if (!isset($selectedImage['id_image'])) {
                    $txt = str_replace($match[0], '', $txt);
                    continue;
                }
                
                // Construire l'URL de l'image
                $imageType = 'large_default'; // Type d'image par défaut
                $linkRewrite = isset($product->link_rewrite[$context->language->id]) 
                    ? $product->link_rewrite[$context->language->id] 
                    : $product->link_rewrite[Configuration::get('PS_LANG_DEFAULT')];
                    
                $imageUrl = $context->link->getImageLink(
                    $linkRewrite,
                    $selectedImage['id_image'],
                    $imageType
                );
                
                // Préparer le nom du produit
                $productName = isset($product->name[$context->language->id]) 
                    ? $product->name[$context->language->id] 
                    : $product->name[Configuration::get('PS_LANG_DEFAULT')];
                
                // Préparer les données pour le template
                $imageData = [
                    'id_product' => $productId,
                    'product_name' => $productName,
                    'image_url' => $imageUrl,
                    'image_alt' => $productName,
                    'image_number' => $imageNumber,
                    'total_images' => count($images)
                ];
                
                // Assigner les données au template
                $context->smarty->assign([
                    'productImage' => $imageData
                ]);
                
                $renderedContent = $context->smarty->fetch($templatePath);
                $txt = str_replace($match[0], $renderedContent, $txt);
                
            } catch (Exception $e) {
                // En cas d'erreur, remplacer par une chaîne vide
                $txt = str_replace($match[0], '', $txt);
                continue;
            }
        }

        return $txt;
    }

    public static function getFeatureProductShortcodes(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);

        // Regex mise à jour pour capturer les paramètres optionnels orderby et orderway
        preg_match_all(
            '/\[productfeature\s+id=(\d+)(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?\s+carousel=(true|false)(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $featureId = (int) $match[1];
            $productLimit = isset($match[2]) && $match[2] !== '' ? (int) $match[2] : (isset($match[3]) ? (int) $match[3] : 10);
            $carousel = strtolower($match[4]) === 'true';

            $orderBy = isset($match[5]) ? strtolower($match[5]) : 'id_product';
            $orderWay = isset($match[6]) ? strtoupper($match[6]) : 'DESC';

            // Validation des paramètres
            $allowedOrderBy = ['id_product', 'price', 'name', 'date_add', 'position'];
            $allowedOrderWay = ['ASC', 'DESC'];

            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'id_product';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'DESC';
            }

            $featureProducts = static::getProductsByFeature($featureId, $productLimit, $context, $orderBy, $orderWay);
            $productIds = array_column($featureProducts, 'id_product');
            $everPresentProducts = static::everPresentProducts($productIds, $context);

            if (!empty($featureProducts)) {
                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => $carousel,
                    'shortcodeClass' => 'productfeature'
                ]);
                $renderedContent = $context->smarty->fetch($templatePath);
                $txt = str_replace($match[0], $renderedContent, $txt);
            }
        }

        return $txt;
    }

    /**
     * Méthode pour obtenir les produits en fonction de l'ID de la caractéristique et de la limite de produits.
     */
    protected static function getProductsByFeature(int $featureId, int $limit, Context $context, string $orderBy = 'id_product', string $orderWay = 'DESC')
    {
        $cacheId = 'everblock_getProductsByFeature_'
            . $featureId . '_' . $limit . '_' . $context->language->id
            . '_' . $orderBy . '_' . $orderWay;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = new DbQuery();
            $sql->select('p.id_product');
            $sql->from('product', 'p');
            $sql->innerJoin('feature_product', 'fp', 'p.id_product = fp.id_product');
            $sql->where('fp.id_feature = ' . (int) $featureId);
            $sql->where('p.active = 1');
            $sql->orderBy('p.' . pSQL($orderBy) . ' ' . pSQL($orderWay));
            $sql->limit($limit);

            $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            EverblockCache::cacheStore($cacheId, $productIds);
            return $productIds;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getBrandDataById(int $idBrand, Context $context): array
    {
        $cacheId = 'everblock_getBrandDataById_'
            . (int) $context->language->id . '_'
            . (int) $idBrand;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $manufacturer = new Manufacturer($idBrand, $context->language->id);
            if (!Validate::isLoadedObject($manufacturer)) {
                EverblockCache::cacheStore($cacheId, []);
                return [];
            }

            $imageExtensions = ['jpg', 'png', 'webp'];
            $width = null;
            $height = null;
            $logo = false;

            foreach ($imageExtensions as $ext) {
                $imagePath = _PS_MANU_IMG_DIR_ . (int) $idBrand . '-small_default.' . $ext;
                if (file_exists($imagePath)) {
                    $webpLogo = self::convertToWebP($imagePath);
                    if ($webpLogo) {
                        $logo = $webpLogo;
                        $webpPath = self::urlToFilePath($webpLogo);
                        if (file_exists($webpPath)) {
                            [$width, $height] = getimagesize($webpPath);
                        }
                    } else {
                        [$width, $height] = getimagesize($imagePath);
                        $logo = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/m/' . (int) $idBrand . '-small_default.' . $ext;
                    }
                    break;
                }
            }

            if (!$logo) {
                $logo = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/m/default.jpg';
                $width = 150;
                $height = 150;
            }

            $url = $context->link->getManufacturerLink($idBrand);
            $brandData = [
                'id' => (int) $idBrand,
                'name' => $manufacturer->name,
                'logo' => $logo,
                'url' => $url,
                'width' => $width,
                'height' => $height,
            ];
            EverblockCache::cacheStore($cacheId, $brandData);
            return $brandData;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getFeatureValueProductShortcodes(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
        // Mise à jour de la regex pour capturer les paramètres id, nb, limit, carousel, orderby et orderway
        preg_match_all('/\[productfeaturevalue\s+id=(\d+)(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $featureId = intval($match[1]);
            $productLimit = isset($match[2]) && $match[2] !== '' ? intval($match[2]) : (isset($match[3]) ? intval($match[3]) : 10);
            $carousel = isset($match[4]) && $match[4] === 'true';
            $orderBy = isset($match[5]) ? strtolower($match[5]) : 'date_add';
            $orderWay = isset($match[6]) ? strtoupper($match[6]) : 'DESC';

            // Rechercher les produits par caractéristique
            $featureProducts = static::getProductsByFeatureValue($featureId, $productLimit, $context, $orderBy, $orderWay);
            $productIds = array_column($featureProducts, 'id_product');
            $everPresentProducts = static::everPresentProducts($productIds, $context);
            if (!empty($featureProducts)) {
                // Assigner les produits et le flag carousel au template
                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => $carousel,
                    'shortcodeClass' => 'productfeaturevalue'
                ]);
                $renderedContent = $context->smarty->fetch($templatePath);

                $txt = str_replace($match[0], $renderedContent, $txt);
            }
        }

        return $txt;
    }

    /**
     * Méthode pour obtenir les produits en fonction de l'ID de la caractéristique et de la limite de produits.
     */
    protected static function getProductsByFeatureValue(int $featureValueId, int $limit, Context $context, string $orderBy = 'date_add', string $orderWay = 'DESC')
    {
        $cacheId = 'everblock_getProductsByFeatureValue_'
        . (int) $featureValueId
        . '_'
        . (int) $limit
        . '_'
        . (int) $context->language->id
        . '_' . $orderBy . '_' . $orderWay;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = new DbQuery();
            $sql->select('p.id_product');
            $sql->from('product', 'p');
            $sql->innerJoin('feature_product', 'fp', 'p.id_product = fp.id_product');
            $sql->where('fp.id_feature_value = ' . (int) $featureValueId);
            $sql->where('p.active = 1');
            $sql->orderBy('p.' . pSQL($orderBy) . ' ' . pSQL($orderWay));
            $sql->limit($limit);

            $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            EverblockCache::cacheStore($cacheId, $productIds);
            return $productIds;
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getCategoryShortcodes(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);

        // Regex pour capturer : id, nb, carousel, orderBy, orderWay (tous optionnels sauf id et nb)
        preg_match_all(
            '/\[category\s+id="(\d+)"(?:\s+nb="?(\d+)"?)?(?:\s+limit="?(\d+)"?)?(?:\s+carousel=(?:"?(true|false)"?))?(?:\s+orderby="?(id_product|price|name|date_add|position)"?)?(?:\s+orderway="?(ASC|DESC)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $categoryId = (int) $match[1];
            $productCount = isset($match[2]) && $match[2] !== '' ? (int) $match[2] : (isset($match[3]) ? (int) $match[3] : 10);
            $carousel = isset($match[4]) && strtolower($match[4]) === 'true';
            $orderBy = isset($match[5]) ? $match[5] : 'id_product';
            $orderWay = isset($match[6]) ? strtoupper($match[6]) : 'ASC';

            $categoryProducts = static::getProductsByCategoryId($categoryId, $productCount, $orderBy, $orderWay);
            if (!empty($categoryProducts)) {
                $productIds = array_column($categoryProducts, 'id_product');
                $everPresentProducts = static::everPresentProducts($productIds, $context);
                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => $carousel,
                    'shortcodeClass' => 'category',
                ]);
                $renderedHtml = $context->smarty->fetch($templatePath);
                $txt = str_replace($match[0], $renderedHtml, $txt);
            }
        }

        return $txt;
    }

    public static function getProductsByCategoryId(
        $categoryId,
        int $limit,
        string $orderBy = 'id_product',
        string $orderWay = 'ASC',
        bool $includeSubcategories = false
    ): array {
        $categoryId = (int) $categoryId;

        if ($categoryId <= 0) {
            return [];
        }

        $cacheId = 'everblock_getProductsByCategoryId_'
            . $categoryId . '_' . $limit . '_' . $orderBy . '_' . $orderWay . '_' . (int) $includeSubcategories;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $idLang = (int) Context::getContext()->language->id;
            $categoryIds = [$categoryId];

            if ($includeSubcategories) {
                $toProcess = [$categoryId];
                while (!empty($toProcess)) {
                    $current = array_pop($toProcess);
                    $children = Category::getChildren($current, $idLang);
                    foreach ($children as $child) {
                        $childId = (int) $child['id_category'];
                        if (!in_array($childId, $categoryIds)) {
                            $categoryIds[] = $childId;
                            $toProcess[] = $childId;
                        }
                    }
                }
            }

            $products = [];
            $ids = [];
            foreach ($categoryIds as $cid) {
                $category = new Category($cid);
                if (!Validate::isLoadedObject($category)) {
                    continue;
                }
                $catProducts = $category->getProducts(
                    $idLang,
                    1,
                    $limit,
                    $orderBy,
                    $orderWay
                );
                foreach ($catProducts as $product) {
                    $pid = (int) $product['id_product'];
                    if (!in_array($pid, $ids)) {
                        $products[] = $product;
                        $ids[] = $pid;
                    }
                    if (count($products) >= $limit) {
                        break 2;
                    }
                }
            }

            EverblockCache::cacheStore($cacheId, $products);
            return $products;
        }

        $cachedProducts = EverblockCache::cacheRetrieve($cacheId);
        return is_array($cachedProducts) ? $cachedProducts : [];
    }

    public static function getManufacturerShortcodes($message, $context, Everblock $module)
    {
        $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);

        preg_match_all(
            '/\[manufacturer\s+id="(\d+)"(?:\s+nb="?(\d+)"?)?(?:\s+limit="?(\d+)"?)?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $message,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $manufacturerId = (int) $match[1];
            $productCount = isset($match[2]) && $match[2] !== '' ? (int) $match[2] : (isset($match[3]) ? (int) $match[3] : 10);
            $carousel = isset($match[4]) && $match[4] === 'true';
            $orderBy = isset($match[5]) ? strtolower($match[5]) : 'id_product';
            $orderWay = isset($match[6]) ? strtoupper($match[6]) : 'DESC';

            // Validation
            $allowedOrderBy = ['id_product', 'price', 'name', 'date_add', 'position'];
            $allowedOrderWay = ['ASC', 'DESC'];

            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'id_product';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'DESC';
            }

            $manufacturerProducts = static::getProductsByManufacturerId($manufacturerId, $productCount, $orderBy, $orderWay);

            if (!empty($manufacturerProducts)) {
                $productIds = array_column($manufacturerProducts, 'id_product');
                $everPresentProducts = static::everPresentProducts($productIds, $context);

                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => $carousel,
                    'shortcodeClass' => 'manufacturer'
                ]);
                $renderedHtml = $context->smarty->fetch($templatePath);
                $message = str_replace($match[0], $renderedHtml, $message);
            }
        }

        return $message;
    }

    protected static function getProductsByManufacturerId(int $manufacturerId, int $limit, string $orderBy = 'id_product', string $orderWay = 'DESC'): array
    {
        $cacheId = 'everblock_getProductsByManufacturerId_'
            . $manufacturerId . '_' . $limit . '_' . $orderBy . '_' . $orderWay;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $manufacturer = new Manufacturer($manufacturerId);
            $return = [];

            if (Validate::isLoadedObject($manufacturer)) {
                $products = Manufacturer::getProducts(
                    $manufacturer->id,
                    Context::getContext()->language->id,
                    1,
                    $limit,
                    pSQL($orderBy),
                    pSQL($orderWay)
                );
                $return = $products;
            }

            EverblockCache::cacheStore($cacheId, $return);
            return $return;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getBrandsShortcode(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/ever_brand.tpl', $module);

        // Regex modifiée pour capturer un paramètre optionnel `carousel=true|false`
        preg_match_all('/\[brands\s+nb="(\d+)"(?:\s+carousel=(true|false))?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $brandCount = (int) $match[1];
            $carousel = isset($match[2]) && $match[2] === 'true';

            $brands = static::getBrandsData($brandCount, $context);
            if (!empty($brands)) {
                $context->smarty->assign([
                    'brands' => $brands,
                    'carousel' => $carousel,
                ]);

                $renderedHtml = $context->smarty->fetch($templatePath);

                // Reconstruire le shortcode d'origine pour un remplacement précis
                $shortcode = '[brands nb="' . $brandCount . '"' . ($carousel ? ' carousel=true' : '') . ']';
                $txt = str_replace($shortcode, $renderedHtml, $txt);
            }
        }

        return $txt;
    }

    protected static function getBrandsData($limit, $context)
    {
        $cacheId = 'everblock_getBrandsData_'
            . (int) $context->language->id
            . '_'
            . (int) $limit;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $brands = Manufacturer::getLiteManufacturersList(
                (int) $context->language->id
            );
            $limitedBrands = [];

            if (!empty($brands)) {
                $brands = array_slice($brands, 0, $limit);
                foreach ($brands as $brand) {
                    $name = $brand['name'];
                    $imageExtensions = ['jpg', 'png', 'webp'];
                    $width = null;
                    $height = null;
                    $logo = false;

                    // Vérifier tous les formats d'image
                    foreach ($imageExtensions as $ext) {
                        $imagePath = _PS_MANU_IMG_DIR_ . (int) $brand['id'] . '-small_default.' . $ext;
                        if (file_exists($imagePath)) {
                            list($width, $height) = getimagesize($imagePath);
                            $logo = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/m/' . (int) $brand['id'] . '-small_default.' . $ext;
                            break; // Sort dès qu'une image valide est trouvée
                        }
                    }

                    // Image de secours
                    if (!$logo) {
                        $logo = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/m/default.jpg';
                        $width = 150;
                        $height = 150;
                    }
                    $url = $brand['link'];

                    $limitedBrands[] = [
                        'id' => $brand['id'],
                        'name' => $name,
                        'logo' => $logo,
                        'url' => $url,
                        'width' => $width,
                        'height' => $height,
                    ];
                }
            }
            EverblockCache::cacheStore($cacheId, $limitedBrands);
            return $limitedBrands;
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getBestSellingProductIdsForPrettyblock(int $limit, string $orderBy = 'total_quantity', string $orderWay = 'DESC', ?int $days = null): array
    {
        return static::getBestSellingProductIds($limit, $orderBy, $orderWay, $days);
    }

    public static function getBestSellingProductIdsForCategoryPrettyblock(
        int $categoryId,
        int $limit,
        string $orderBy = 'total_quantity',
        string $orderWay = 'DESC',
        ?int $days = null
    ): array {
        return static::getBestSellingProductIdsByCategory($categoryId, $limit, $orderBy, $orderWay, $days);
    }

    protected static function getBestSellingProductIds(int $limit, string $orderBy = 'total_quantity', string $orderWay = 'DESC', ?int $days = null): array
    {
        $context = Context::getContext();
        $cacheId = 'everblock_bestSellingProductIds_'
            . (int) $context->shop->id . '_'
            . $limit . '_'
            . ($days ?? 'all') . '_'
            . $orderBy . '_'
            . $orderWay;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = 'SELECT od.product_id, SUM(od.product_quantity) AS total_quantity'
                . ' FROM ' . _DB_PREFIX_ . 'order_detail od'
                . ' JOIN ' . _DB_PREFIX_ . 'orders o ON od.id_order = o.id_order'
                . ' JOIN ' . _DB_PREFIX_ . 'product_shop ps ON od.product_id = ps.id_product'
                . ' WHERE ps.active = 1';

            if ($days !== null) {
                $dateFrom = date('Y-m-d H:i:s', strtotime("-$days days"));
                $sql .= ' AND o.date_add >= "' . pSQL($dateFrom) . '"';
            }

            $sql .= ' GROUP BY od.product_id'
                . ' ORDER BY ' . pSQL($orderBy) . ' ' . pSQL($orderWay)
                . ' LIMIT ' . (int) $limit;

            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $ids = array_map(function ($row) {
                return (int) $row['product_id'];
            }, $rows);
            EverblockCache::cacheStore($cacheId, $ids);
            return $ids;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    protected static function getBestSellingProductIdsByCategory(
        int $categoryId,
        int $limit,
        string $orderBy = 'total_quantity',
        string $orderWay = 'DESC',
        ?int $days = null
    ): array {
        $context = Context::getContext();
        $shopId = (int) $context->shop->id;

        // Sécurisation orderBy / orderWay (important)
        $allowedOrderBy = ['total_quantity'];
        $allowedOrderWay = ['ASC', 'DESC'];

        if (!in_array($orderBy, $allowedOrderBy, true)) {
            $orderBy = 'total_quantity';
        }
        if (!in_array(strtoupper($orderWay), $allowedOrderWay, true)) {
            $orderWay = 'DESC';
        }

        $cacheId = 'everblock_bestSellingProductIds_category_'
            . $shopId . '_'
            . (int) $categoryId . '_'
            . (int) $limit . '_'
            . ($days ?? 'all') . '_'
            . $orderBy . '_'
            . $orderWay;

        if (EverblockCache::isCacheStored($cacheId)) {
            return EverblockCache::cacheRetrieve($cacheId);
        }

        $sql = '
            SELECT od.product_id, SUM(od.product_quantity) AS total_quantity
            FROM ' . _DB_PREFIX_ . 'orders o
            INNER JOIN ' . _DB_PREFIX_ . 'order_detail od
                ON o.id_order = od.id_order
            INNER JOIN (
                SELECT ps.id_product
                FROM ' . _DB_PREFIX_ . 'product_shop ps
                INNER JOIN ' . _DB_PREFIX_ . 'category_product cp
                    ON cp.id_product = ps.id_product
                WHERE ps.id_shop = ' . $shopId . '
                  AND ps.active = 1
                  AND cp.id_category = ' . (int) $categoryId . '
            ) filtered_products
                ON filtered_products.id_product = od.product_id
            WHERE o.id_shop = ' . $shopId . '
              AND o.valid = 1';

        if ($days !== null) {
            $dateFrom = date('Y-m-d H:i:s', strtotime('-' . (int) $days . ' days'));
            $sql .= ' AND o.date_add >= "' . pSQL($dateFrom) . '"';
        }

        $sql .= '
            GROUP BY od.product_id
            ORDER BY ' . pSQL($orderBy) . ' ' . pSQL($orderWay) . '
            LIMIT ' . (int) $limit;

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['product_id'];
        }

        // Cache long (best-seller ≠ temps réel)
        EverblockCache::cacheStore($cacheId, $ids, 86400); // 24h

        return $ids;
    }

    protected static function getBestSellingProductIdsByBrand(int $brandId, int $limit, string $orderBy = 'total_quantity', string $orderWay = 'DESC', ?int $days = null): array
    {
        $context = Context::getContext();
        $cacheId = 'everblock_bestSellingProductIds_brand_'
            . (int) $context->shop->id . '_'
            . $brandId . '_'
            . $limit . '_'
            . ($days ?? 'all') . '_'
            . $orderBy . '_'
            . $orderWay;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $shopId = (int) $context->shop->id;
            $sql = 'SELECT od.product_id, SUM(od.product_quantity) AS total_quantity'
                . ' FROM ' . _DB_PREFIX_ . 'order_detail od'
                . ' JOIN ' . _DB_PREFIX_ . 'orders o ON od.id_order = o.id_order'
                . ' JOIN ' . _DB_PREFIX_ . 'product_shop ps ON od.product_id = ps.id_product'
                . ' JOIN ' . _DB_PREFIX_ . 'product p ON od.product_id = p.id_product'
                . ' WHERE ps.active = 1'
                . ' AND ps.id_shop = ' . $shopId
                . ' AND o.id_shop = ' . $shopId
                . ' AND p.id_manufacturer = ' . (int) $brandId;

            if ($days !== null) {
                $dateFrom = date('Y-m-d H:i:s', strtotime("-$days days"));
                $sql .= ' AND o.date_add >= "' . pSQL($dateFrom) . '"';
            }

            $sql .= ' GROUP BY od.product_id'
                . ' ORDER BY ' . pSQL($orderBy) . ' ' . pSQL($orderWay)
                . ' LIMIT ' . (int) $limit;

            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $ids = array_map(function ($row) {
                return (int) $row['product_id'];
            }, $rows);
            EverblockCache::cacheStore($cacheId, $ids);
            return $ids;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    protected static function getBestSellingProductIdsByFeature(int $featureId, int $limit, string $orderBy = 'total_quantity', string $orderWay = 'DESC', ?int $days = null): array
    {
        $context = Context::getContext();
        $cacheId = 'everblock_bestSellingProductIds_feature_'
            . (int) $context->shop->id . '_'
            . $featureId . '_'
            . $limit . '_'
            . ($days ?? 'all') . '_'
            . $orderBy . '_'
            . $orderWay;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $shopId = (int) $context->shop->id;
            $sql = 'SELECT od.product_id, SUM(od.product_quantity) AS total_quantity'
                . ' FROM ' . _DB_PREFIX_ . 'order_detail od'
                . ' JOIN ' . _DB_PREFIX_ . 'orders o ON od.id_order = o.id_order'
                . ' JOIN ' . _DB_PREFIX_ . 'product_shop ps ON od.product_id = ps.id_product'
                . ' JOIN ' . _DB_PREFIX_ . 'feature_product fp ON od.product_id = fp.id_product'
                . ' WHERE ps.active = 1'
                . ' AND ps.id_shop = ' . $shopId
                . ' AND o.id_shop = ' . $shopId
                . ' AND fp.id_feature = ' . (int) $featureId;

            if ($days !== null) {
                $dateFrom = date('Y-m-d H:i:s', strtotime("-$days days"));
                $sql .= ' AND o.date_add >= "' . pSQL($dateFrom) . '"';
            }

            $sql .= ' GROUP BY od.product_id'
                . ' ORDER BY ' . pSQL($orderBy) . ' ' . pSQL($orderWay)
                . ' LIMIT ' . (int) $limit;

            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $ids = array_map(function ($row) {
                return (int) $row['product_id'];
            }, $rows);
            EverblockCache::cacheStore($cacheId, $ids);
            return $ids;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    protected static function getBestSellingProductIdsByFeatureValue(int $featureValueId, int $limit, string $orderBy = 'total_quantity', string $orderWay = 'DESC', ?int $days = null): array
    {
        $context = Context::getContext();
        $cacheId = 'everblock_bestSellingProductIds_feature_value_'
            . (int) $context->shop->id . '_'
            . $featureValueId . '_'
            . $limit . '_'
            . ($days ?? 'all') . '_'
            . $orderBy . '_'
            . $orderWay;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $shopId = (int) $context->shop->id;
            $sql = 'SELECT od.product_id, SUM(od.product_quantity) AS total_quantity'
                . ' FROM ' . _DB_PREFIX_ . 'order_detail od'
                . ' JOIN ' . _DB_PREFIX_ . 'orders o ON od.id_order = o.id_order'
                . ' JOIN ' . _DB_PREFIX_ . 'product_shop ps ON od.product_id = ps.id_product'
                . ' JOIN ' . _DB_PREFIX_ . 'feature_product fp ON od.product_id = fp.id_product'
                . ' WHERE ps.active = 1'
                . ' AND ps.id_shop = ' . $shopId
                . ' AND o.id_shop = ' . $shopId
                . ' AND fp.id_feature_value = ' . (int) $featureValueId;

            if ($days !== null) {
                $dateFrom = date('Y-m-d H:i:s', strtotime("-$days days"));
                $sql .= ' AND o.date_add >= "' . pSQL($dateFrom) . '"';
            }

            $sql .= ' GROUP BY od.product_id'
                . ' ORDER BY ' . pSQL($orderBy) . ' ' . pSQL($orderWay)
                . ' LIMIT ' . (int) $limit;

            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $ids = array_map(function ($row) {
                return (int) $row['product_id'];
            }, $rows);
            EverblockCache::cacheStore($cacheId, $ids);
            return $ids;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getWidgetShortcode($txt)
    {
        $txt = preg_replace_callback('/\[widget moduleName="(.+?)" hookName="(.+?)"\]/', function ($matches) {
            $moduleName = $matches[1];
            $hookName = $matches[2];

            if (Module::isInstalled($moduleName) && Module::isEnabled($moduleName)) {
                $module = Module::getInstanceByName($moduleName);
                if (method_exists($module, 'renderWidget')) {
                    return $module->renderWidget($hookName, []);
                } else {
                    return '';
                }
            } else {
                return '';
            }
        }, $txt);
        return $txt;
    }

    public static function getPrettyblocksShortcodes(string $txt, Context $context, Everblock $module): string
    {
        if ((bool) Module::isInstalled('prettyblocks') === true
            && (bool) Module::isEnabled('prettyblocks') === true
            && (bool) static::moduleDirectoryExists('prettyblocks') === true
        ) {
            try {
                // Définir le chemin vers le template
                $templatePath = static::getTemplatePath('hook/prettyblocks.tpl', $module);
                // Regex pour trouver les shortcodes de type [prettyblocks name="mon_nom"]
                $pattern = '/\[prettyblocks name="([^"]+)"\]/';

                // Fonction de remplacement pour traiter chaque shortcode trouvé
                $replacementFunction = function ($matches) use ($context, $templatePath) {
                    $zoneName = $matches[1];

                    try {
                        // Assigner le nom de la zone à Smarty
                        $context->smarty->assign('zone_name', $zoneName);

                        // Récupérer le rendu du template avec Smarty
                        return $context->smarty->fetch($templatePath);
                    } catch (\Throwable $e) {
                        PrestaShopLogger::addLog(
                            sprintf('Prettyblocks shortcode rendering failed for zone "%s": %s', $zoneName, $e->getMessage()),
                            3
                        );
                        return '';
                    }
                };

                // Remplacer tous les shortcodes trouvés par le rendu Smarty correspondant
                $txt = preg_replace_callback($pattern, $replacementFunction, $txt);
            } catch (\Throwable $e) {
                PrestaShopLogger::addLog(
                    sprintf('Prettyblocks shortcode parsing failed: %s', $e->getMessage()),
                    3
                );
            }
        }
        
        return $txt;
    }

    public static function generateFormFromShortcode(
        string $shortcode,
        Context $context,
        Everblock $module
    ) {
        preg_match_all('/(\w+)\s*=\s*"([^"]+)"|(\w+)\s*=\s*([^"\s,]+)/', $shortcode, $matches, PREG_SET_ORDER);
        $attributes = [];
        static $uniqueIdentifier = 0;

        foreach ($matches as $match) {
            $attribute_name = $match[1] ?: $match[3];
            $attribute_value = $match[2] ?: $match[4];
            $attribute_value = trim($attribute_value, "\"");
            $attributes[$attribute_name] = $attribute_value;
        }

        $uid = ++$uniqueIdentifier;
        $rawLabel = isset($attributes['label']) ? $attributes['label'] : '';
        $field = [
            'type' => htmlspecialchars($attributes['type'], ENT_QUOTES),
            'label' => htmlspecialchars($rawLabel, ENT_QUOTES),
            'raw_label' => $rawLabel,
            'value' => $attributes['value'] ?? null,
            'values' => isset($attributes['values']) ? explode(',', $attributes['values']) : [],
            'required' => isset($attributes['required']) && strtolower($attributes['required']) === 'true',
            'class' => isset($attributes['class']) ? htmlspecialchars($attributes['class'], ENT_QUOTES) : '',
            'unique' => $uid,
            'id' => 'everfield_' . $uid,
        ];

        if ($field['type'] === 'sento') {
            $emailCandidates = array_filter(array_map('trim', explode(',', (string) $rawLabel)));
            $validEmails = [];

            foreach ($emailCandidates as $candidate) {
                if (Validate::isEmail($candidate)) {
                    $validEmails[] = $candidate;
                }
            }

            if (!empty($validEmails)) {
                $emailString = implode(',', $validEmails);
                $encodedEmails = base64_encode($emailString);
                $signature = Tools::encrypt($emailString . '|' . (int) $context->shop->id);
                $field['secure_value'] = $encodedEmails . '::' . $signature;
            }
        }

        $context->smarty->assign('field', $field);
        $templatePath = static::getTemplatePath('hook/contact_field.tpl', $module);

        return $context->smarty->fetch($templatePath);
    }

    public static function getFormShortcode(string $txt, Context $context, Everblock $module): string
    {
        // Remplace [evercontactform_open] par le formulaire ouvrant
        $txt = str_replace(
            '[evercontactform_open]',
            '<div class="container"><form method="POST" enctype="multipart/form-data" class="evercontactform" action="#">',
            $txt
        );

        // Remplace [evercontactform_close] par input token + fermeture du form
        $token = Tools::getToken();
        $txt = str_replace(
            '[evercontactform_close]',
            '<input type="hidden" name="token" value="' . $token . '"></form></div>',
            $txt
        );

        // Recherche et remplace tous les shortcodes [evercontact ...]
        $pattern = '/\[evercontact\s[^\]]+\]/';
        $result = preg_replace_callback($pattern, function ($matches) use ($context, $module) {
            return static::generateFormFromShortcode($matches[0], $context, $module);
        }, $txt);

        return $result;
    }

    public static function getOrderFormShortcode(string $txt, Context $context, Everblock $module): string
    {
        $txt = str_replace('[everorderform_open]', '<div class="container">', $txt);
        $txt = str_replace('[everorderform_close]', '</div>', $txt);
        $pattern = '/\[everorderform\s[^\]]+\]/';
        $result = preg_replace_callback($pattern, function ($matches) use ($context, $module) {
            // $matches[0] contient le shortcode trouvé
            return static::generateFormFromShortcode($matches[0], $context, $module);
        }, $txt);
        return $result;
    }

    public static function replaceHook(string $txt): string
    {
        preg_match_all('/\{hook h=\'(.*?)\'\}/', $txt, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $hookName) {
                $hookContent = Hook::exec($hookName, [], null, true);
                $hookContentString = '';
                if (is_array($hookContent)) {
                    foreach ($hookContent as $hcontent) {
                        $hookContentString .= $hcontent;
                    }
                } else {
                    $hookContentString = (string)$hookContent;
                }
                $txt = str_replace("{hook h='$hookName'}", $hookContentString, $txt);
            }
        }
        return $txt;
    }

    public static function getNativeContactShortcode(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/contact.tpl', $module);
        $replacement = $context->smarty->fetch($templatePath);
        $txt = str_replace('[nativecontact]', $replacement, $txt);
        return $txt;
    }

    public static function getCartShortcode(string $txt, Context $context, Everblock $module): string
    {
        $templatePath = static::getTemplatePath('hook/cart.tpl', $module);
        $replacement = $context->smarty->fetch($templatePath);
        $txt = str_replace('[evercart]', $replacement, $txt);
        return $txt;
    }

    public static function getCartTotalShortcode(string $txt, Context $context): string
    {
        $total = 0;
        if (isset($context->cart) && $context->cart->id) {
            $total = $context->cart->getOrderTotal(true, Cart::BOTH);
        }
        $formatted = Tools::displayPrice($total, $context->currency);
        $txt = str_replace('[cart_total]', $formatted, $txt);
        return $txt;
    }

    public static function getCartQuantityShortcode(string $txt, Context $context): string
    {
        $quantity = 0;
        if (isset($context->cart) && $context->cart->id) {
            $quantity = (int) $context->cart->getProductsQuantity();
        }
        $txt = str_replace('[cart_quantity]', (string) $quantity, $txt);
        return $txt;
    }

    public static function getShopLogoShortcode(string $txt, Context $context): string
    {
        $logoName = Configuration::get('PS_LOGO', null, null, (int) $context->shop->id);
        $filePath = _PS_IMG_DIR_ . $logoName;
        if (!$logoName || !file_exists($filePath)) {
            return str_replace('[shop_logo]', '', $txt);
        }

        [$width, $height] = getimagesize($filePath);
        $url = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/' . $logoName;
        $alt = htmlspecialchars($context->shop->name, ENT_QUOTES);

        $imgTag = sprintf(
            '<img src="%s" alt="%s" width="%d" height="%d" />',
            htmlspecialchars($url, ENT_QUOTES),
            $alt,
            (int) $width,
            (int) $height
        );

        return str_replace('[shop_logo]', $imgTag, $txt);
    }

    public static function getAlertShortcode(string $txt): string
    {
        $allowedTypes = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];

        return (string) preg_replace_callback(
            '/\[alert(?:\s+([^\]]+))?\](.*?)\[\/alert\]/is',
            function ($matches) use ($allowedTypes) {
                $attrs = static::parseShortcodeAttrs($matches[1] ?? '');
                $type = strtolower($attrs['type'] ?? 'info');

                if (!in_array($type, $allowedTypes, true)) {
                    $type = 'info';
                }

                $content = $matches[2];
                return '<div class="alert alert-' . $type . '" role="alert">' . $content . '</div>';
            },
            $txt
        );
    }

    public static function getNewsletterFormShortcode(string $txt, Context $context, Everblock $module): string
    {
        if (Module::isInstalled('ps_emailsubscription') && Module::isEnabled('ps_emailsubscription')) {
            $newsletter = Module::getInstanceByName('ps_emailsubscription');
            if (method_exists($newsletter, 'renderWidget')) {
                $replacement = $newsletter->renderWidget('displayFooter', []);

                // Force the newsletter form to submit to the current page instead
                // of the homepage, otherwise inserting the shortcode in a page
                // (e.g. contact form) breaks the form action. When the form is
                // loaded via AJAX (e.g. inside a modal), the request URI points to
                // the AJAX controller. In that case, rely on the origin URL sent
                // from JavaScript.
                $currentUrl = Tools::getValue('everblock_origin_url');
                if (!$currentUrl || !Validate::isUrl($currentUrl)) {
                    $currentUrl = Tools::getHttpHost(true) . $_SERVER['REQUEST_URI'];
                }
                $replacement = preg_replace(
                    '@(<form[^>]*action=")[^"]*#blockEmailSubscription_displayFooter(")@',
                    '$1' . htmlspecialchars($currentUrl, ENT_QUOTES) . '#blockEmailSubscription_displayFooter$2',
                    $replacement
                );

                $txt = str_replace('[newsletter_form]', $replacement, $txt);
            }
        }
        return $txt;
    }

    public static function getEverBlockShortcode(string $txt, Context $context): string
    {
        preg_match_all('/\[everblock\s+(\d+)\]/i', $txt, $matches);

        foreach ($matches[1] as $match) {
            $everblockId = (int) $match;
            $everblock = new EverblockClass(
                (int) $everblockId,
                (int) $context->language->id,
                (int) $context->shop->id
            );
            $shortcode = '[everblock ' . $everblockId . ']';
            if (Validate::isLoadedObject($everblock)) {
                $replacement = $everblock->content;
                $txt = str_replace($shortcode, $replacement, $txt);
            } else {
                $txt = str_replace($shortcode, '', $txt);
            }
        }
        return $txt;
    }

    public static function getRandomProductsShortcode(string $txt, Context $context, Everblock $module): string
    {
        // Update regex to capture optional params nb, limit, carousel, orderby and orderway
        preg_match_all('/\[random_product(?:\s+nb="?(\d+)")?(?:\s+limit="?(\d+)")?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(ASC|DESC)"?)?\]/i', $txt, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $limit = isset($match[1]) && $match[1] !== '' ? (int) $match[1] : (isset($match[2]) ? (int) $match[2] : 8);
            $carousel = isset($match[3]) && $match[3] === 'true';
            $orderBy = isset($match[4]) ? strtolower($match[4]) : '';
            $orderWay = isset($match[5]) ? strtoupper($match[5]) : 'ASC';

            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int) $context->shop->id . '
                    ';
            if ($orderBy) {
                $sql .= 'ORDER BY p.' . pSQL($orderBy) . ' ' . pSQL($orderWay);
            } else {
                $sql .= 'ORDER BY RAND()';
            }
            $sql .= ' LIMIT ' . (int) $limit;
            $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (!empty($productIds)) {
                $productIdsArray = array_map(function ($row) {
                    return (int) $row['id_product'];
                }, $productIds);

                $everPresentProducts = static::everPresentProducts($productIdsArray, $context);

                if (!empty($everPresentProducts)) {
                    // Assign products and carousel flag to the template
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'random_product'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[random_product'];
                    if (isset($match[1])) { $shortcodeParts[] = 'nb="' . $match[1] . '"'; }
                    elseif (isset($match[2])) { $shortcodeParts[] = 'limit="' . $match[2] . '"'; }
                    if (isset($match[3])) { $shortcodeParts[] = 'carousel=' . $match[3]; }
                    if (isset($match[4])) { $shortcodeParts[] = 'orderby=' . $match[4]; }
                    if (isset($match[5])) { $shortcodeParts[] = 'orderway=' . $match[5]; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getLastProductsShortcode(string $txt, Context $context, Everblock $module): string
    {
        // Update regex to capture optional nb, limit, carousel, orderby and orderway
        preg_match_all('/\[last-products(?:\s+(\d+))?(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(ASC|DESC)"?)?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $limit = isset($match[2]) && $match[2] !== '' ? (int) $match[2] : (isset($match[1]) && $match[1] !== '' ? (int) $match[1] : (isset($match[3]) ? (int) $match[3] : 8));
            $carousel = isset($match[4]) && $match[4] === 'true';
            $orderBy = isset($match[5]) ? strtolower($match[5]) : 'date_add';
            $orderWay = isset($match[6]) ? strtoupper($match[6]) : 'DESC';

            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int) $context->shop->id . '
                    AND p.active = 1
                    ORDER BY p.' . pSQL($orderBy) . ' ' . pSQL($orderWay) . '
                    LIMIT ' . (int) $limit;
            $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (!empty($productIds)) {
                $productIdsArray = array_map(function ($row) {
                    return (int) $row['id_product'];
                }, $productIds);

                $everPresentProducts = static::everPresentProducts($productIdsArray, $context);

                if (!empty($everPresentProducts)) {
                    // Assign products and carousel flag to the template
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'last-products'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[last-products'];
                    if (isset($match[2]) && $match[2] !== '') { $shortcodeParts[] = 'nb=' . $match[2]; }
                    elseif (isset($match[1]) && $match[1] !== '') { $shortcodeParts[] = $match[1]; }
                    elseif (isset($match[3])) { $shortcodeParts[] = 'limit=' . $match[3]; }
                    if (isset($match[4])) { $shortcodeParts[] = 'carousel=' . $match[4]; }
                    if (isset($match[5])) { $shortcodeParts[] = 'orderby=' . $match[5]; }
                    if (isset($match[6])) { $shortcodeParts[] = 'orderway=' . $match[6]; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getRecentlyViewedShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all('/\[recently_viewed(?:\s+nb=(\d+))?(?:\s+carousel=(true|false))?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $limit = isset($match[1]) ? (int) $match[1] : 4;
            $carousel = isset($match[2]) && $match[2] === 'true';
            $viewed = isset($context->cookie->viewed) ? (string) $context->cookie->viewed : '';
            $ids = array_filter(array_map('intval', array_reverse(array_unique(explode(',', $viewed)))));

            if (!empty($ids)) {
                $productIds = array_slice($ids, 0, $limit);
                $everPresentProducts = static::everPresentProducts($productIds, $context);
                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'recently-viewed',
                    ]);
                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[recently_viewed'];
                    if (isset($match[1])) { $shortcodeParts[] = 'nb=' . $match[1]; }
                    if (isset($match[2])) { $shortcodeParts[] = 'carousel=' . $match[2]; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getPromoProductsShortcode(string $txt, Context $context, Everblock $module): string
    {
        // Update regex to capture optional nb, limit, carousel, orderby and orderway
        preg_match_all('/\[promo-products(?:\s+(\d+))?(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(ASC|DESC)"?)?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $limit = isset($match[2]) && $match[2] !== '' ? (int) $match[2] : (isset($match[1]) && $match[1] !== '' ? (int) $match[1] : (isset($match[3]) ? (int) $match[3] : 8));
            $carousel = isset($match[4]) && $match[4] === 'true';
            $orderBy = isset($match[5]) ? strtolower($match[5]) : 'date_add';
            $orderWay = isset($match[6]) ? strtoupper($match[6]) : 'DESC';

            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int) $context->shop->id . '
                    AND p.active = 1
                    AND p.on_sale = 1
                    ORDER BY p.' . pSQL($orderBy) . ' ' . pSQL($orderWay) . '
                    LIMIT ' . (int) $limit;
            $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (!empty($productIds)) {
                $productIdsArray = array_map(function ($row) {
                    return (int) $row['id_product'];
                }, $productIds);

                $everPresentProducts = static::everPresentProducts($productIdsArray, $context);

                if (!empty($everPresentProducts)) {
                    // Assign products and carousel flag to the template
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'promo-products'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[promo-products'];
                    if (isset($match[2]) && $match[2] !== '') { $shortcodeParts[] = 'nb=' . $match[2]; }
                    elseif (isset($match[1]) && $match[1] !== '') { $shortcodeParts[] = $match[1]; }
                    elseif (isset($match[3])) { $shortcodeParts[] = 'limit=' . $match[3]; }
                    if (isset($match[4])) { $shortcodeParts[] = 'carousel=' . $match[4]; }
                    if (isset($match[5])) { $shortcodeParts[] = 'orderby=' . $match[5]; }
                    if (isset($match[6])) { $shortcodeParts[] = 'orderway=' . $match[6]; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getBestSalesShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all(
            '/\[best-sales(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+days=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $limit = isset($match[1]) && $match[1] !== '' ? (int)$match[1] : (isset($match[2]) ? (int)$match[2] : 10);
            $days = isset($match[3]) ? (int)$match[3] : null;
            $carousel = isset($match[4]) && $match[4] === 'true';
            $orderBy = isset($match[5]) ? strtolower($match[5]) : 'total_quantity';
            $orderWay = isset($match[6]) ? strtoupper($match[6]) : 'DESC';

            // Validation
            $allowedOrderBy = ['total_quantity', 'product_id'];
            $allowedOrderWay = ['ASC', 'DESC'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'total_quantity';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'DESC';
            }

            $cacheId = 'getBestSalesShortcode_' 
                . (int)$context->shop->id 
                . "_{$limit}_" 
                . ($days ?? 'all') 
                . "_{$orderBy}_{$orderWay}";

            if (!EverblockCache::isCacheStored($cacheId)) {
                $sql = 'SELECT od.product_id, SUM(od.product_quantity) AS total_quantity
                        FROM ' . _DB_PREFIX_ . 'order_detail od
                        JOIN ' . _DB_PREFIX_ . 'orders o ON od.id_order = o.id_order
                        JOIN ' . _DB_PREFIX_ . 'product_shop ps ON od.product_id = ps.id_product
                        WHERE ps.active = 1';

                if ($days !== null) {
                    $dateFrom = date('Y-m-d H:i:s', strtotime("-$days days"));
                    $sql .= ' AND o.date_add >= "' . pSQL($dateFrom) . '"';
                }

                $sql .= ' GROUP BY od.product_id
                          ORDER BY ' . pSQL($orderBy) . ' ' . pSQL($orderWay) . '
                          LIMIT ' . (int)$limit;

                $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                EverblockCache::cacheStore($cacheId, $productIds);
            } else {
                $productIds = EverblockCache::cacheRetrieve($cacheId);
            }
            if (!empty($productIds)) {
                $productIdsArray = array_map(function ($row) {
                    return (int) $row['product_id'];
                }, $productIds);
                $everPresentProducts = static::everPresentProducts($productIdsArray, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'best-sales'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $txt = str_replace($match[0], $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getCategoryBestSalesShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all(
            '/\[categorybestsales\s+id="?(\d+)"?(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+days=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $categoryId = (int)$match[1];
            $limit = isset($match[2]) && $match[2] !== '' ? (int)$match[2] : (isset($match[3]) ? (int)$match[3] : 10);
            $days = isset($match[4]) ? (int)$match[4] : null;
            $carousel = isset($match[5]) && $match[5] === 'true';
            $orderBy = isset($match[6]) ? strtolower($match[6]) : 'total_quantity';
            $orderWay = isset($match[7]) ? strtoupper($match[7]) : 'DESC';

            $allowedOrderBy = ['total_quantity', 'product_id'];
            $allowedOrderWay = ['ASC', 'DESC'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'total_quantity';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'DESC';
            }

            $productIds = static::getBestSellingProductIdsByCategory($categoryId, $limit, $orderBy, $orderWay, $days);

            if (!empty($productIds)) {
                $everPresentProducts = static::everPresentProducts($productIds, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'categorybestsales'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[categorybestsales', 'id=' . $categoryId];
                    if (isset($match[2]) && $match[2] !== '') { $shortcodeParts[] = 'nb=' . $match[2]; }
                    elseif (isset($match[3])) { $shortcodeParts[] = 'limit=' . $match[3]; }
                    if (isset($match[4])) { $shortcodeParts[] = 'days=' . $match[4]; }
                    if (isset($match[5])) { $shortcodeParts[] = 'carousel=' . $match[5]; }
                    if (isset($match[6])) { $shortcodeParts[] = 'orderby=' . $match[6]; }
                    if (isset($match[7])) { $shortcodeParts[] = 'orderway=' . $match[7]; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';

                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getBrandBestSalesShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all(
            '/\[brandbestsales\s+id="?(\d+)"?(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+days=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $brandId = (int)$match[1];
            $limit = isset($match[2]) && $match[2] !== '' ? (int)$match[2] : (isset($match[3]) ? (int)$match[3] : 10);
            $days = isset($match[4]) ? (int)$match[4] : null;
            $carousel = isset($match[5]) && $match[5] === 'true';
            $orderBy = isset($match[6]) ? strtolower($match[6]) : 'total_quantity';
            $orderWay = isset($match[7]) ? strtoupper($match[7]) : 'DESC';

            $allowedOrderBy = ['total_quantity', 'product_id'];
            $allowedOrderWay = ['ASC', 'DESC'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'total_quantity';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'DESC';
            }

            $productIds = static::getBestSellingProductIdsByBrand($brandId, $limit, $orderBy, $orderWay, $days);

            if (!empty($productIds)) {
                $everPresentProducts = static::everPresentProducts($productIds, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'brandbestsales'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[brandbestsales', 'id=' . $brandId];
                    if (isset($match[2]) && $match[2] !== '') { $shortcodeParts[] = 'nb=' . $match[2]; }
                    elseif (isset($match[3])) { $shortcodeParts[] = 'limit=' . $match[3]; }
                    if (isset($match[4])) { $shortcodeParts[] = 'days=' . $match[4]; }
                    if (isset($match[5])) { $shortcodeParts[] = 'carousel=' . $match[5]; }
                    if (isset($match[6])) { $shortcodeParts[] = 'orderby=' . $match[6]; }
                    if (isset($match[7])) { $shortcodeParts[] = 'orderway=' . $match[7]; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';

                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getFeatureBestSalesShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all(
            '/\[featurebestsales\s+id="?(\d+)"?(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+days=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $featureId = (int)$match[1];
            $limit = isset($match[2]) && $match[2] !== '' ? (int)$match[2] : (isset($match[3]) ? (int)$match[3] : 10);
            $days = isset($match[4]) ? (int)$match[4] : null;
            $carousel = isset($match[5]) && $match[5] === 'true';
            $orderBy = isset($match[6]) ? strtolower($match[6]) : 'total_quantity';
            $orderWay = isset($match[7]) ? strtoupper($match[7]) : 'DESC';

            $allowedOrderBy = ['total_quantity', 'product_id'];
            $allowedOrderWay = ['ASC', 'DESC'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'total_quantity';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'DESC';
            }

            $productIds = static::getBestSellingProductIdsByFeature($featureId, $limit, $orderBy, $orderWay, $days);

            if (!empty($productIds)) {
                $everPresentProducts = static::everPresentProducts($productIds, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'featurebestsales'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[featurebestsales', 'id=' . $featureId];
                    if (isset($match[2]) && $match[2] !== '') { $shortcodeParts[] = 'nb=' . $match[2]; }
                    elseif (isset($match[3])) { $shortcodeParts[] = 'limit=' . $match[3]; }
                    if (isset($match[4])) { $shortcodeParts[] = 'days=' . $match[4]; }
                    if (isset($match[5])) { $shortcodeParts[] = 'carousel=' . $match[5]; }
                    if (isset($match[6])) { $shortcodeParts[] = 'orderby=' . $match[6]; }
                    if (isset($match[7])) { $shortcodeParts[] = 'orderway=' . $match[7]; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';

                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getFeatureValueBestSalesShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all(
            '/\[featurevaluebestsales\s+id="?(\d+)"?(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+days=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $featureValueId = (int)$match[1];
            $limit = isset($match[2]) && $match[2] !== '' ? (int)$match[2] : (isset($match[3]) ? (int)$match[3] : 10);
            $days = isset($match[4]) ? (int)$match[4] : null;
            $carousel = isset($match[5]) && $match[5] === 'true';
            $orderBy = isset($match[6]) ? strtolower($match[6]) : 'total_quantity';
            $orderWay = isset($match[7]) ? strtoupper($match[7]) : 'DESC';

            $allowedOrderBy = ['total_quantity', 'product_id'];
            $allowedOrderWay = ['ASC', 'DESC'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'total_quantity';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'DESC';
            }

            $productIds = static::getBestSellingProductIdsByFeatureValue($featureValueId, $limit, $orderBy, $orderWay, $days);

            if (!empty($productIds)) {
                $everPresentProducts = static::everPresentProducts($productIds, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'featurevaluebestsales'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[featurevaluebestsales', 'id=' . $featureValueId];
                    if (isset($match[2]) && $match[2] !== '') { $shortcodeParts[] = 'nb=' . $match[2]; }
                    elseif (isset($match[3])) { $shortcodeParts[] = 'limit=' . $match[3]; }
                    if (isset($match[4])) { $shortcodeParts[] = 'days=' . $match[4]; }
                    if (isset($match[5])) { $shortcodeParts[] = 'carousel=' . $match[5]; }
                    if (isset($match[6])) { $shortcodeParts[] = 'orderby=' . $match[6]; }
                    if (isset($match[7])) { $shortcodeParts[] = 'orderway=' . $match[7]; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';

                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getLinkedProductsShortcode(string $txt, Context $context, Everblock $module): string
    {
        if (!Tools::getValue('id_product')) {
            return $txt;
        }

        preg_match_all(
            '/\[linkedproducts(?:\s+nb="?(\d+)"?)?(?:\s+limit="?(\d+)"?)?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(ASC|DESC)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $limit = isset($match[1]) && $match[1] !== '' ? (int) $match[1] : (isset($match[2]) ? (int) $match[2] : 8);
            $orderBy = isset($match[3]) ? strtolower($match[3]) : 'position';
            $orderWay = isset($match[4]) ? strtoupper($match[4]) : 'ASC';

            $allowedOrderBy = ['id_product', 'price', 'name', 'date_add', 'position'];
            $allowedOrderWay = ['ASC', 'DESC'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'position';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'ASC';
            }

            $productId = (int) Tools::getValue('id_product');
            $cacheId = 'getLinkedProductsShortcode_'
                . (int) $context->shop->id . '_' . $productId . '_' . $limit
                . '_' . $orderBy . '_' . $orderWay;

            if (!EverblockCache::isCacheStored($cacheId)) {
                $sql = new DbQuery();
                $sql->select('p.id_product');
                $sql->from('product', 'p');
                $sql->innerJoin('accessory', 'a', 'p.id_product = a.id_product_2');
                $sql->where('a.id_product_1 = ' . (int) $productId);
                $sql->where('p.active = 1');
                $sql->orderBy('p.' . pSQL($orderBy) . ' ' . pSQL($orderWay));
                $sql->limit($limit);

                $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                EverblockCache::cacheStore($cacheId, $productIds);
            } else {
                $productIds = EverblockCache::cacheRetrieve($cacheId);
            }

            if (!empty($productIds)) {
                $productIdsArray = array_map(function ($row) {
                    return (int) $row['id_product'];
                }, $productIds);
                $everPresentProducts = static::everPresentProducts($productIdsArray, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel_id' => 'linkedProductsCarousel-' . uniqid(),
                    ]);

                    $templatePath = static::getTemplatePath('hook/linkedproducts_carousel.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[linkedproducts'];
                    if (isset($match[1])) { $shortcodeParts[] = 'nb="' . $match[1] . '"'; }
                    elseif (isset($match[2])) { $shortcodeParts[] = 'limit="' . $match[2] . '"'; }
                    if (isset($match[3])) { $shortcodeParts[] = 'orderby="' . $match[3] . '"'; }
                    if (isset($match[4])) { $shortcodeParts[] = 'orderway="' . $match[4] . '"'; }
                    $shortcode = implode(' ', $shortcodeParts) . ']';

                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getAccessoriesShortcode(string $txt, Context $context, Everblock $module): string
    {
        if (!Tools::getValue('id_product')) {
            return $txt;
        }

        preg_match_all(
            '/\[accessories(?:\s+nb="?(\d+)")?(?:\s+limit="?(\d+)")?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(ASC|DESC)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $limit = isset($match[1]) && $match[1] !== '' ? (int) $match[1] : (isset($match[2]) ? (int) $match[2] : 0);
            if ($limit <= 0) {
                continue;
            }
            $orderBy = isset($match[3]) ? strtolower($match[3]) : 'position';
            $orderWay = isset($match[4]) ? strtoupper($match[4]) : 'ASC';

            $allowedOrderBy = ['id_product', 'price', 'name', 'date_add', 'position'];
            $allowedOrderWay = ['ASC', 'DESC'];
            if (!in_array($orderBy, $allowedOrderBy)) {
                $orderBy = 'position';
            }
            if (!in_array($orderWay, $allowedOrderWay)) {
                $orderWay = 'ASC';
            }

            $shortcodeParts = ['[accessories'];
            if (isset($match[1])) { $shortcodeParts[] = 'nb="' . $match[1] . '"'; }
            elseif (isset($match[2])) { $shortcodeParts[] = 'limit="' . $match[2] . '"'; }
            if (isset($match[3])) { $shortcodeParts[] = 'orderby="' . $match[3] . '"'; }
            if (isset($match[4])) { $shortcodeParts[] = 'orderway="' . $match[4] . '"'; }
            $shortcode = implode(' ', $shortcodeParts) . ']';

            $productId = (int) Tools::getValue('id_product');
            $cacheId = 'getAccessoriesShortcode_'
                . (int) $context->shop->id . '_' . $productId . '_' . $limit
                . '_' . $orderBy . '_' . $orderWay;

            if (!EverblockCache::isCacheStored($cacheId)) {
                $sql = new DbQuery();
                $sql->select('p.id_product');
                $sql->from('product', 'p');
                $sql->innerJoin('accessory', 'a', 'p.id_product = a.id_product_2');
                $sql->where('a.id_product_1 = ' . (int) $productId);
                $sql->where('p.active = 1');
                $sql->orderBy('p.' . pSQL($orderBy) . ' ' . pSQL($orderWay));
                $sql->limit($limit);

                $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                EverblockCache::cacheStore($cacheId, $productIds);
            } else {
                $productIds = EverblockCache::cacheRetrieve($cacheId);
            }

            if (!empty($productIds)) {
                $productIdsArray = array_map(function ($row) {
                    return (int) $row['id_product'];
                }, $productIds);
                $everPresentProducts = static::everPresentProducts($productIdsArray, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel_id' => 'accessoriesCarousel-' . uniqid(),
                    ]);

                    $templatePath = static::getTemplatePath('hook/linkedproducts_carousel.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $txt = str_replace($shortcode, $replacement, $txt);
                } else {
                    $txt = str_replace($shortcode, '', $txt);
                }
            } else {
                $txt = str_replace($shortcode, '', $txt);
            }
        }

        return $txt;
    }

    public static function getSubcategoriesShortcode(string $txt, Context $context, Everblock $module): string
    {
        $categoryShortcodes = [];
        preg_match_all('/\[subcategories\s+id="(\d+)"\s+nb="(\d+)"\]/i', $txt, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $categoryId = (int) $match[1];
            $categoryCount = (int) $match[2];
            $category = new Category(
                (int) $categoryId,
                (int) $context->language->id,
                (int) $context->shop->id
            );
            if (!Validate::isLoadedObject($category)
                || (bool) $category->active === false
            ) {
                continue;
            }
            $subCategories = $category->getSubCategories(
                (int) $context->language->id
            );
            if (count($subCategories) > $categoryCount) {
                $subCategories = array_slice($subCategories, 0, $categoryCount);
            }
            foreach ($subCategories as &$subCategory) {
                $imageLink = $context->link->getCatImageLink(
                    ImageType::getFormattedName('category'),
                    (int) $subCategory['id_category']
                );
                $categoryLink = $context->link->getCategoryLink(
                    (int) $subCategory['id_category'],
                    null,
                    $context->language->id
                );
                $subCategory['link'] = $categoryLink;
                $subCategory['image_link'] = $imageLink;
            }
            $context->smarty->assign('everSubCategories', $subCategories);
            $templatePath = static::getTemplatePath('hook/subcategories.tpl', $module);
            $replacement = $context->smarty->fetch($templatePath);
            $txt = str_replace($match[0], $replacement, $txt);
        }
        return $txt;
    }

    public static function getStoreShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all('/\[everstore\s+(\d+)\]/i', $txt, $matches);
        foreach ($matches[1] as $match) {
            $storeIds = explode(',', $match);
            $storeInfo = [];
            foreach ($storeIds as $storeId) {
                $store = new Store(
                    (int) $storeId,
                    (int) $context->language->id,
                    (int) $context->shop->id
                );
                if (!Validate::isLoadedObject($store)) {
                    continue;
                }
                $storeInfo[] = [
                    'id_store' => $store->id,
                    'image_link' => $context->link->getStoreImageLink(ImageType::getFormattedName('medium'), $store->id),
                    'name' => $store->name,
                    'address1' => $store->address1,
                    'address2' => $store->address2,
                    'postcode' => $store->postcode,
                    'city' => $store->city,
                    'latitude' => $store->latitude,
                    'longitude' => $store->longitude,
                    'hours' => $store->hours,
                    'phone' => $store->phone,
                    'fax' => $store->fax,
                    'note' => $store->note,
                    'email' => $store->email,
                    'date_add' => $store->date_add,
                    'date_upd' => $store->date_upd,
                ];
            }
            $context->smarty->assign('storeInfos', $storeInfo);
            $templatePath = static::getTemplatePath('hook/store.tpl', $module);
            $replacement = $context->smarty->fetch($templatePath);
            $txt = preg_replace_callback(
                '/\[everstore\s+' . preg_quote($match) . '\]/i',
                function () use ($replacement) {
                    return $replacement;
                },
                $txt
            );
        }

        return $txt;
    }

    /**
     * Search & replace QCD ACF shortcodes from 410 Gone module
     * @param $txt : full DOM
     * @return $txt : full DOM fixed
    */
    public static function getQcdAcfCode(string $txt, Context $context): string
    {
        if (!Module::isInstalled('qcdacf')
            || !static::moduleDirectoryExists('qcdacf')
        ) {
            return $txt;
        }
        $objectId = 0;
        $objectType = '';
        if (Tools::getValue('id_product')) {
            $objectId = (int) Tools::getValue('id_product');
            $objectType = 'product';
        }
        if (Tools::getValue('id_category')) {
            $objectId = (int) Tools::getValue('id_category');
            $objectType = 'category';
        }
        if (Tools::getValue('id_manufacturer')) {
            $objectId = (int) Tools::getValue('id_manufacturer');
            $objectType = 'manufacturer';
        }
        if (Tools::getValue('id_supplier')) {
            $objectId = (int) Tools::getValue('id_supplier');
            $objectType = 'supplier';
        }
        if (Tools::getValue('id_cms')) {
            $objectId = (int) Tools::getValue('id_cms');
            $objectType = 'cms';
        }
        Module::getInstanceByName('qcdacf');
        $pattern = '/\[qcdacf\s+(\w+)\s+(\w+)\s+(\w+)\]/i';
        $modifiedTxt = preg_replace_callback($pattern, function ($matches) use ($objectType, $objectId, $context) {
            $name = $matches[1];
            $value = qcdacf::getVar($name, $objectType, $objectId, $context->language->id);
            if ($value) {
                return $value;
            }
            return '';
        }, $txt);
        return $modifiedTxt;
    }

    public static function getEverImgShortcode(string $txt, Context $context, Everblock $module): string
    {
        // 🔹 Regex robuste : compatible avec carousel=true ou carousel="true"
        preg_match_all(
            '/\[everimg\s+name="([^"]*?)"\s*(?:class="([^"]*)")?(?:\s+carousel=(?:"(true|false)"|(true|false)))?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            // Match structure :
            // 1 => filenames, 2 => class, 3|4 => carousel value
            $filenames = array_map('trim', explode(',', $match[1]));
            $class = isset($match[2]) && $match[2] !== '' ? trim($match[2]) : 'img-fluid';
            $carousel = (isset($match[3]) && $match[3] === 'true') || (isset($match[4]) && $match[4] === 'true');

            $images = [];

            foreach ($filenames as $filename) {
                $safeFilename = basename($filename);
                $filepath = _PS_IMG_DIR_ . 'cms/' . $safeFilename;

                if (!is_file($filepath)) {
                    continue;
                }

                $size = @getimagesize($filepath);
                if ($size === false || count($size) < 2) {
                    continue;
                }

                [$width, $height] = $size;
                $alt = htmlspecialchars(pathinfo($safeFilename, PATHINFO_FILENAME), ENT_QUOTES);
                $classAttr = htmlspecialchars($class, ENT_QUOTES);

                // 🔹 Construction du chemin public (compatible CLI)
                $domain = method_exists('Tools', 'getShopDomainSsl')
                    ? Tools::getShopDomainSsl(true)
                    : Tools::getHttpHost(true);
                $baseUri = defined('PS_BASE_URI') ? PS_BASE_URI : '/';
                $webPath = rtrim($domain, '/') . $baseUri . 'img/cms/' . $safeFilename;

                $images[] = [
                    'src' => htmlspecialchars($webPath, ENT_QUOTES),
                    'width' => (int) $width,
                    'height' => (int) $height,
                    'alt' => $alt,
                    'class' => $classAttr,
                ];
            }

            $replacement = '';
            if (!empty($images)) {
                // 🔹 Mode carousel Bootstrap
                if ($carousel && count($images) > 1 && isset($context->smarty, $module)) {
                    $context->smarty->assign([
                        'images' => $images,
                        'carousel_id' => 'everImgCarousel-' . uniqid(),
                    ]);

                    $templatePath = _PS_MODULE_DIR_ . $module->name . '/views/templates/hook/ever_img_carousel.tpl';

                    if (is_file($templatePath)) {
                        $replacement = $context->smarty->fetch($templatePath);
                    }
                }
                // 🔹 Mode simple (une ou plusieurs images statiques)
                else {
                    $html = [];
                    foreach ($images as $img) {
                        $imgTag = sprintf(
                            '<img src="%s" width="%d" height="%d" alt="%s" loading="lazy" class="%s" />',
                            $img['src'],
                            $img['width'],
                            $img['height'],
                            $img['alt'],
                            $img['class']
                        );
                        $html[] = count($images) > 1 ? '<div class="col">' . $imgTag . '</div>' : $imgTag;
                    }
                    $replacement = count($images) > 1
                        ? '<div class="row">' . implode('', $html) . '</div>'
                        : $html[0];
                }
            }

            // 🔹 Remplacement dans le texte
            $txt = str_replace($match[0], $replacement, $txt);
        }

        return $txt;
    }

    public static function getQcdSvgCode(string $txt, Context $context): string
    {
        if (!Module::isInstalled('qcdsvg') || !static::moduleDirectoryExists('qcdsvg')) {
            return $txt;
        }

        $module = Module::getInstanceByName('qcdsvg');
        if (!is_callable([$module, 'hookDisplayQcdSvg'])) {
            return $txt;
        }

        // Capture les shortcodes [displayQcdSvg name="calendar" class="..." inline=true]
        $pattern = '/\[displayQcdSvg\s+([^\]]+)\]/i';

        $txt = preg_replace_callback($pattern, function ($matches) use ($module) {
            $attrString = $matches[1];
            $params = [];

            // Parse les attributs du shortcode
            preg_match_all('/(\w+)=("([^"]*)"|\'([^\']*)\'|(\w+))/', $attrString, $attrMatches, PREG_SET_ORDER);
            foreach ($attrMatches as $attr) {
                $key = $attr[1];
                $val = $attr[3] ?? $attr[4] ?? $attr[5]; // support "value", 'value', value

                // Type casting basique
                if (strtolower($val) === 'true') {
                    $val = true;
                } elseif (strtolower($val) === 'false') {
                    $val = false;
                }

                $params[$key] = $val;
            }

            // Retourne le rendu SVG (inline ou img)
            return $module->hookDisplayQcdSvg($params);
        }, $txt);

        return $txt;
    }

    public static function renderSmartyVars(string $txt, Context $context): string
    {
        $controllerTypes = [
            'front',
            'modulefront',
        ];
        if (!in_array($context->controller->controller_type, $controllerTypes)) {
            return $txt;
        }
        $templateVars = [
            'customer' => $context->controller->getTemplateVarCustomer(),
            'currency' => $context->controller->getTemplateVarCurrency(),
            'shop' => $context->controller->getTemplateVarShop(),
            'urls' => $context->controller->getTemplateVarUrls(),
            'configuration' => $context->controller->getTemplateVarConfiguration(),
            'breadcrumb' => $context->controller->getBreadcrumb(),
        ];
        foreach ($templateVars as $key => $value) {
            $search = '$' . $key;
            if (is_array($value)) {
                $txt = static::renderSmartyVarsInArray($txt, $search, $value);
            } elseif (is_string($value)) {
                $txt = str_replace($search, $value, $txt);
            }
        }
        return $txt;
    }

    private static function renderSmartyVarsInArray(string $txt, string $search, array $array): string
    {
        foreach ($array as $key => $value) {
            $elementSearch = $search . '.' . $key;
            if (is_array($value)) {
                $txt = static::renderSmartyVarsInArray($txt, $elementSearch, $value);
            } else {
                $replacement = is_scalar($value) ? (string) $value : '';
                $txt = str_replace($elementSearch, $replacement, $txt);
            }
        }
        return $txt;
    }

    /**
     * Search & replace string on tables, can be any string (URL or simple text)
     * @param string $oldUrl
     * @param string $newUrl
    * @param int $id_shop
    * @return array of success/error messages
    */
    public static function migrateUrls(?string $oldUrl, ?string $newUrl, int $id_shop): array
    {
        $postErrors = [];
        $querySuccess = [];

        if (!$oldUrl || !$newUrl) {
            return ['postErrors' => ['Missing search or replace string'], 'querySuccess' => []];
        }

        try {
            $db = \Db::getInstance();
            $tables = $db->executeS('SHOW TABLES');
            foreach ($tables as $tableRow) {
                $table = reset($tableRow);
                $columns = $db->executeS('SHOW COLUMNS FROM ' . bqSQL($table));
                foreach ($columns as $column) {
                    $field = $column['Field'];
                    $type = $column['Type'];

                    if (!preg_match('/char|text|blob/i', $type)) {
                        continue;
                    }

                    $sql = 'UPDATE ' . bqSQL($table)
                        . " SET `" . bqSQL($field) . "` = REPLACE(`" . bqSQL($field)
                        . "`, '" . pSQL($oldUrl, true) . "', '" . pSQL($newUrl, true)
                        . "') WHERE `" . bqSQL($field) . "` LIKE '%" . pSQL($oldUrl, true) . "%'";

                    if ($db->execute($sql)) {
                        $querySuccess[] = $table . '.' . $field;
                    }
                }
            }
        } catch (\Exception $e) {
            $postErrors[] = $e->getMessage();
        }

        if ((bool) EverblockCache::getModuleConfiguration('EVERPSCSS_CACHE') === true) {
            \Tools::clearAllCache();
        }

        return [
            'postErrors' => $postErrors,
            'querySuccess' => $querySuccess,
        ];
    }
    
    public static function getVideoShortcode(string $txt): string
    {
        preg_match_all('/\[video\s+(.*?)\]/i', $txt, $videoMatches);
        foreach ($videoMatches[0] as $shortcode) {
            $videoUrl = preg_replace('/\[video\s+|\]/i', '', $shortcode);
            $iframe = static::detectVideoSite($videoUrl);
            if ($iframe) {
                $txt = str_replace($shortcode, $iframe, $txt);
            }
        }
        return $txt;
    }

    public static function detectVideoSite(string $url): string
    {
        $patterns = [
            'youtube' => '/^(?:https?:\/\/)?(?:www\.)?youtu(?:be\.com\/watch\?v=|\.be\/)([\w\-\_]+)(?:\S+)?$/',
            'youtube_embed' => '/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/embed\/([\w\-\_]+)(?:\S+)?$/',
            'youtube_live' => '/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/live\/([\w\-\_]+)(?:\S+)?$/',
            'vimeo' => '/^(?:https?:\/\/)?(?:www\.)?vimeo\.com\/([0-9]+)$/i',
            'dailymotion' => '/^(?:https?:\/\/)?(?:www\.)?dailymotion\.com\/video\/([a-z0-9]+)$/i',
            'vidyard' => '/^(?:https?:\/\/)?(?:embed\.)?vidyard.com\/(?:watch\/)?([a-zA-Z0-9\-\_]+)$/'
        ];
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                switch ($key) {
                    case 'youtube':
                    case 'youtube_embed':
                    case 'youtube_live':
                        return '<iframe width="100%" height="315" src="https://www.youtube.com/embed/' . $matches[1] . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                    case 'vimeo':
                        return '<iframe src="https://player.vimeo.com/video/' . $matches[1] . '?color=ffffff&title=0&byline=0&portrait=0" width="100%" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                    case 'dailymotion':
                        return '<iframe frameborder="0" width="100%" height="270" src="//www.dailymotion.com/embed/video/' . $matches[1] . '" allowfullscreen></iframe>';
                    case 'vidyard':
                        return '<iframe src="https://play.vidyard.com/' . $matches[1] . '.html?v=3.1.1&type=lightbox" width="100%" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                }
            }
        }
        return '';
    }

    public static function getStoreLocatorData()
    {
        $context = Context::getContext();
        $id_lang = (int) $context->language->id;
        $id_shop = (int) $context->shop->id;
        $stores = Store::getStores($id_lang);
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        foreach ($stores as &$store) {
            $storeShopId = isset($store['id_shop']) ? (int) $store['id_shop'] : $id_shop;
            $timezone = Configuration::get('PS_TIMEZONE', null, null, $storeShopId);
            if (empty($timezone)) {
                $timezone = Configuration::get('PS_TIMEZONE');
            }
            $now = new \DateTime('now', new \DateTimeZone($timezone));
            $todayIndex = (int) $now->format('N') - 1; // 0 = lundi
            $currentTime = $now->format('H:i');
            $todayDate = $now->format('Y-m-d');
            $frenchHolidays = self::getFrenchHolidays((int) $now->format('Y'));
            $isHoliday = in_array($todayDate, $frenchHolidays);

            $id_store = (int) $store['id_store'];
            $cms_id = (int) Configuration::get('QCD_ASSOCIATED_CMS_PAGE_ID_STORE_' . $id_store, null, null, $storeShopId);
            $cms_link = null;
            $storeHolidayHours = self::getStoreHolidayHoursConfig($id_store);
            $todayStoreHolidaySlot = $storeHolidayHours[$todayDate] ?? null;
            if ($todayStoreHolidaySlot) {
                $todayStoreHolidaySlot = trim($todayStoreHolidaySlot);
            }

            if ($cms_id > 0) {
                $cms = new CMS($cms_id, $id_lang, $storeShopId);
                if (Validate::isLoadedObject($cms)) {
                    $link = new Link();
                    $cms_link = $link->getCMSLink($cms);
                }
            }

            $store['cms_id'] = $cms_id;
            $store['cms_link'] = $cms_link;

            $decodedHours = json_decode($store['hours'], true);
            $store['hours_display'] = [];
            $store['is_open'] = false;
            $store['open_until'] = null;
            $store['opens_at'] = null;

            foreach ($days as $i => $day) {
                $slots = isset($decodedHours[$i]) ? $decodedHours[$i] : [];
                if ($i === $todayIndex && $isHoliday && $todayStoreHolidaySlot) {
                    $slots = [$todayStoreHolidaySlot];
                }
                $hoursFormatted = [];

                foreach ($slots as $slot) {
                    if (empty($slot)) {
                        continue;
                    }

                    // Plusieurs créneaux ? → on découpe
                    // Patterns autorisés :
                    //   - "10h - 12h" ou "10h – 12h" (plage unique)
                    //   - "10h - 12h / 14h - 18h" (plages multiples)
                    //   - "10h / 16h" (début et fin séparés par un slash)
                    $normalizedSlot = self::normalizeDashes($slot);
                    $subSlots = explode(' / ', $slot);
                    $normalizedSubSlots = explode(' / ', $normalizedSlot);

                    foreach ($subSlots as $subSlot) {
                        $hoursFormatted[] = trim($subSlot);
                    }

                    if (($i === $todayIndex) && (!$isHoliday || $todayStoreHolidaySlot)) {
                        $rangeHandled = false;
                        foreach ($normalizedSubSlots as $normalizedSubSlot) {
                            if (strpos($normalizedSubSlot, '-') !== false) {
                                $parts = preg_split('/\s*-\s*/', $normalizedSubSlot);
                                $startRaw = $parts[0] ?? '';
                                $endRaw = $parts[1] ?? '';
                                $start = self::normalizeTime($startRaw);
                                $end = self::normalizeTime($endRaw);
                                if ($start && $end) {
                                    if ($currentTime >= $start && $currentTime <= $end) {
                                        $store['is_open'] = true;
                                        $store['open_until'] = $end;
                                    } elseif ($currentTime < $start) {
                                        if ($store['opens_at'] === null || $start < $store['opens_at']) {
                                            $store['opens_at'] = $start;
                                        }
                                    }
                                }
                                $rangeHandled = true;
                            }
                        }

                        if (!$rangeHandled && count($normalizedSubSlots) === 2) {
                            $start = self::normalizeTime($normalizedSubSlots[0]);
                            $end = self::normalizeTime($normalizedSubSlots[1]);
                            if ($start && $end) {
                                if ($currentTime >= $start && $currentTime <= $end) {
                                    $store['is_open'] = true;
                                    $store['open_until'] = $end;
                                } elseif ($currentTime < $start) {
                                    if ($store['opens_at'] === null || $start < $store['opens_at']) {
                                        $store['opens_at'] = $start;
                                    }
                                }
                            }
                        }
                    }
                }

                $label = count($hoursFormatted) > 0 ? implode(' / ', $hoursFormatted) : 'Fermé';
                if ($isHoliday && $i === $todayIndex) {
                    if ($todayStoreHolidaySlot) {
                        $label .= ' (jour férié)';
                    } else {
                        $label = 'Fermé (jour férié)';
                    }
                }

                $store['hours_display'][] = [
                    'day' => $day,
                    'hours' => $label,
                ];
            }

            if ($isHoliday && !$todayStoreHolidaySlot) {
                $store['is_open'] = false;
                $store['open_until'] = null;
                $store['opens_at'] = null;
            }
        }

        return $stores;
    }

    protected static function normalizeTime($str)
    {
        $str = trim($str);

        // Cas : "9h30", "10h00", "20h", "20h00", etc.
        if (preg_match('/^(\d{1,2})h(\d{2})?$/', $str, $matches)) {
            $hour = (int) $matches[1];
            $minute = isset($matches[2]) ? (int) $matches[2] : 0;
            return sprintf('%02d:%02d', $hour, $minute);
        }

        // Cas : déjà formaté type "9:30"
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $str, $matches)) {
            return sprintf('%02d:%02d', $matches[1], $matches[2]);
        }

        // Cas : juste "9" ou "20"
        if (preg_match('/^(\d{1,2})$/', $str, $matches)) {
            return sprintf('%02d:00', $matches[1]);
        }

        return null;
    }

    protected static function normalizeDashes($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $result = preg_replace('/[\x{2010}-\x{2015}\x{2212}]/u', '-', $value);

        return is_string($result) ? $result : $value;
    }


    public static function getFrenchHolidays($year)
    {
        $easterDate = easter_date($year);
        $holidays = [
            // Jours fixes
            sprintf('%s-01-01', $year), // Jour de l'an
            sprintf('%s-05-01', $year), // Fête du travail
            sprintf('%s-05-08', $year), // Victoire 1945
            sprintf('%s-07-14', $year), // Fête nationale
            sprintf('%s-08-15', $year), // Assomption
            sprintf('%s-11-01', $year), // Toussaint
            sprintf('%s-11-11', $year), // Armistice
            sprintf('%s-12-25', $year), // Noël

            // Jours mobiles (basés sur Pâques)
            date('Y-m-d', $easterDate), // Pâques
            date('Y-m-d', strtotime('+39 days', $easterDate)), // Ascension
            date('Y-m-d', strtotime('+49 days', $easterDate)), // Pentecôte
            date('Y-m-d', strtotime('+50 days', $easterDate)), // Lundi de Pentecôte
        ];

        return $holidays;
    }

    protected static function getStoreHolidayHoursConfig(int $storeId): array
    {
        $result = [];
        $holidays = self::getFrenchHolidays((int) date('Y'));
        foreach ($holidays as $date) {
            $hoursKey = 'EVERBLOCK_HOLIDAY_HOURS_' . (int) $storeId . '_' . $date;
            $hours = Configuration::get($hoursKey);
            if ($hours) {
                $result[$date] = trim($hours);
            }
        }
        return $result;
    }

    public static function getStoreCoordinates(int $storeId): array
    {
        $cacheId = 'store_coordinates_' . (int) $storeId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $store = new Store((int) $storeId);
            if (Validate::isLoadedObject($store)) {
                $coordinates = [
                    'latitude' => (float) $store->latitude,
                    'longitude' => (float) $store->longitude
                ];
                EverblockCache::cacheStore($cacheId, $coordinates);
            } else {
                return [];
            }
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function generateGoogleMap(string $txt, Context $context, Everblock $module): string
    {
        $apiKey = Configuration::get('EVERBLOCK_GMAP_KEY');
        if (!$apiKey) {
            return str_replace('[storelocator]', '', $txt);
        }
        $stores = static::getStoreLocatorData();
        if (!empty($stores)) {
            $smarty = $context->smarty;
            $templatePath = static::getTemplatePath('hook/storelocator.tpl', $module);
            $hasPrettyblocks = Module::isInstalled('prettyblocks')
                && Module::isEnabled('prettyblocks')
                && static::moduleDirectoryExists('prettyblocks');
            $smarty->assign([
                'everblock_stores' => $stores,
                'has_prettyblocks' => $hasPrettyblocks,
                'everblock_show_map_toggle' => (bool) Configuration::get('EVERBLOCK_STORELOCATOR_TOGGLE'),
            ]);
            $storeLocatorContent = $smarty->fetch($templatePath);
            $txt = str_replace('[storelocator]', $storeLocatorContent, $txt);
        }
        return $txt;
    }


    public static function generateGoogleMapScript($markers)
    {
        if (!$markers) {
            return;
        }
        // Convertir les latitudes et longitudes en nombres flottants
        foreach ($markers as &$marker) {
            $marker['lat'] = (float) $marker['lat'];
            $marker['lng'] = (float) $marker['lng'];
        }
        $googleMapCode = '
            (function () {
                var map;
                var infoWindow;
                var markers = ' . json_encode($markers) . ';
                var markerMap = {};
                var storeList = document.getElementById("everblock-storelist");
                var originalItems = storeList ? Array.from(storeList.children) : [];
                var defaultCenter = { lat: markers[0].lat, lng: markers[0].lng };

                function renderContent(marker) {
                    var phone = marker.phone ? `<div>${marker.phone}</div>` : "";
                    var address2 = marker.address2 ? marker.address2 + "<br>" : "";
                    var directions = `<a href="https://www.google.com/maps/dir/?api=1&destination=${marker.lat},${marker.lng}" target="_blank" rel="noopener noreferrer" class="btn btn-primary w-100">${marker.directions_label}</a>`;
                    var title = marker.cms_link ? `<a href="${marker.cms_link}" class="text-dark text-decoration-none">${marker.title}</a>` : marker.title;
                    return `
                        <div class="everblock-marker-info row g-3 mx-0">
                            <div class="col-4">
                                <img src="${marker.img}" alt="${marker.title}" style="width:80px;height:80px;object-fit:cover;" class="rounded w-100 ms-2">
                            </div>
                            <div class="col-8 ps-3">
                                <strong>${title}</strong><br>
                                ${marker.address1}<br>
                                ${address2}
                                ${marker.postcode} ${marker.city}<br>
                                ${phone}
                                <div>${marker.status}</div>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#storeHoursModal${marker.id}"><u>${marker.hours_label}</u> &gt;</a>
                                <div class="mt-2">${directions}</div>
                            </div>
                        </div>
                    `;
                }

                function findClosestMarker(userLocation) {
                    var closestMarker = null;
                    var closestDistance = Number.MAX_VALUE;

                    markers.forEach(function (marker) {
                        var markerLocation = new google.maps.LatLng(marker.lat, marker.lng);
                        var distance = google.maps.geometry.spherical.computeDistanceBetween(userLocation, markerLocation);

                        if (distance < closestDistance) {
                            closestDistance = distance;
                            closestMarker = marker;
                        }
                    });

                    return closestMarker;
                }

                function filterStores(userLocation) {
                    var items = document.querySelectorAll("#everblock-storelist .everblock-store-item");
                    var distances = [];
                    items.forEach(function (el) {
                        var lat = parseFloat(el.getAttribute("data-lat"));
                        var lng = parseFloat(el.getAttribute("data-lng"));
                        var distance = google.maps.geometry.spherical.computeDistanceBetween(userLocation, new google.maps.LatLng(lat, lng));
                        distances.push({el: el, distance: distance});
                    });
                    distances.sort(function (a, b) { return a.distance - b.distance; });
                    items.forEach(function (el) { el.style.display = "none"; });
                    distances.forEach(function (item, index) {
                        if (index < 5) {
                            item.el.style.display = "";
                            item.el.parentNode.appendChild(item.el);
                        }
                    });
                }

                function resetDisplay() {
                    map.panTo(defaultCenter);
                    map.setZoom(13);
                    if (infoWindow) {
                        infoWindow.close();
                    }
                    if (storeList) {
                        originalItems.forEach(function (el) {
                            storeList.appendChild(el);
                            el.style.display = "";
                        });
                    }
                }

                function applySearch(userLocation) {
                    map.panTo(userLocation);
                    map.setZoom(15);
                    var closestMarker = findClosestMarker(userLocation);
                    filterStores(userLocation);
                    if (closestMarker) {
                        var markerObj = markerMap[closestMarker.id];
                        infoWindow.setContent(renderContent(closestMarker));
                        infoWindow.open(map, markerObj);
                    }
                }

                function initMap() {
                    map = new google.maps.Map(document.getElementById("everblock-storelocator"), {
                        center: defaultCenter,
                        zoom: 13
                    });
                    infoWindow = new google.maps.InfoWindow();

                    markers.forEach(function (marker) {
                        var markerOptions = {
                            position: { lat: marker.lat, lng: marker.lng },
                            map: map,
                            title: marker.title
                        };
                        if (marker.icon) {
                            markerOptions.icon = marker.icon;
                        }
                        var markerObj = new google.maps.Marker(markerOptions);
                        markerMap[marker.id] = markerObj;
                        markerObj.addListener("click", function () {
                            infoWindow.setContent(renderContent(marker));
                            infoWindow.open(map, markerObj);
                        });
                    });

                    document.getElementById("everblock-storelocator").style.height = "500px";
                }

                function initAutocomplete() {
                    var geocoder = new google.maps.Geocoder();
                    var searchBtn = document.getElementById("store_search_btn");
                    var searchInput = document.getElementById("store_search");
                    var autocomplete;

                    if (!searchInput) {
                        return;
                    }

                    function handlePlaceSelection(place) {
                        if (!place) {
                            return;
                        }
                        var location = place.geometry && place.geometry.location ? place.geometry.location : place.location;
                        if (location) {
                            applySearch(location);
                        }
                    }

                    if (google.maps.places && google.maps.places.PlaceAutocompleteElement) {
                        autocomplete = new google.maps.places.PlaceAutocompleteElement({
                            inputElement: searchInput
                        });

                        if (autocomplete.addEventListener) {
                            autocomplete.addEventListener("gmp-placeselect", function (event) {
                                handlePlaceSelection(event.detail && event.detail.place);
                            });
                        }

                        if (autocomplete.addListener && typeof autocomplete.getPlace === "function") {
                            autocomplete.addListener("place_changed", function () {
                                handlePlaceSelection(autocomplete.getPlace());
                            });
                        }
                    } else if (google.maps.places && google.maps.places.Autocomplete) {
                        autocomplete = new google.maps.places.Autocomplete(searchInput);

                        autocomplete.addListener("place_changed", function () {
                            handlePlaceSelection(autocomplete.getPlace());
                        });
                    }

                    function performSearch() {
                        var address = searchInput.value;
                        if (!address.trim()) {
                            resetDisplay();
                            return;
                        }
                        geocoder.geocode({ address: address }, function (results, status) {
                            if (status === "OK" && results[0].geometry && results[0].geometry.location) {
                                applySearch(results[0].geometry.location);
                            }
                        });
                    }

                    if (searchBtn) {
                        searchBtn.addEventListener("click", performSearch);
                    }

                    searchInput.addEventListener("keydown", function (e) {
                        if (e.key === "Enter") {
                            e.preventDefault();
                            performSearch();
                        }
                    });

                    searchInput.addEventListener("input", function () {
                        if (!this.value) {
                            resetDisplay();
                        }
                    });
                }

                document.addEventListener("DOMContentLoaded", function () {
                    storeList = document.getElementById("everblock-storelist");
                    if (storeList) {
                        originalItems = Array.from(storeList.children);
                    }
                    var mapTabBtn = document.getElementById("tab-map");
                    if (mapTabBtn) {
                        mapTabBtn.addEventListener("shown.bs.tab", function () {
                            if (typeof google !== "undefined" && map) {
                                google.maps.event.trigger(map, "resize");
                            }
                        });
                    }

                    var storeListEl = document.getElementById("everblock-storelist");
                    if (storeListEl) {
                        storeListEl.addEventListener("click", function (e) {
                            var nameEl = e.target.closest("h6");
                            if (nameEl && window.innerWidth >= 768) {
                                e.preventDefault();
                                var itemEl = nameEl.closest(".everblock-store-item");
                                var id = parseInt(itemEl.getAttribute("data-id"));
                                var marker = markers.find(function (m) { return m.id === id; });
                                var markerObj = markerMap[id];
                                if (marker && markerObj) {
                                    map.panTo({ lat: marker.lat, lng: marker.lng });
                                    map.setZoom(15);
                                    infoWindow.setContent(renderContent(marker));
                                    infoWindow.open(map, markerObj);
                                }
                            }
                        });
                    }

                    var mapToggleBtn = document.getElementById("store_toggle_map");
                    if (mapToggleBtn) {
                        var wrapper = document.getElementById("everblock-storelocator-wrapper");
                        var mapPane = document.getElementById("pane-map");
                        var listPane = document.getElementById("pane-list");
                        var tabs = document.getElementById("storeLocatorTabs");
                        var navMapBtn = document.getElementById("tab-map");
                        var navListBtn = document.getElementById("tab-list");
                        var hideLabel = mapToggleBtn.getAttribute("data-label-hide") || "";
                        var showLabel = mapToggleBtn.getAttribute("data-label-show") || "";

                        function setMapVisibility(visible) {
                            if (!wrapper || !mapPane || !listPane) {
                                return;
                            }
                            if (visible) {
                                wrapper.classList.remove("map-hidden");
                                if (hideLabel) {
                                    mapToggleBtn.textContent = hideLabel;
                                }
                                mapToggleBtn.setAttribute("aria-expanded", "true");
                                mapToggleBtn.dataset.state = "visible";
                                if (tabs) {
                                    tabs.classList.remove("d-none");
                                }
                                if (mapPane.classList.contains("fade")) {
                                    mapPane.classList.add("show", "active");
                                    listPane.classList.remove("show", "active");
                                }
                                if (navMapBtn && navListBtn) {
                                    navMapBtn.classList.add("active");
                                    navMapBtn.setAttribute("aria-selected", "true");
                                    navListBtn.classList.remove("active");
                                    navListBtn.setAttribute("aria-selected", "false");
                                }
                                if (typeof google !== "undefined" && map) {
                                    setTimeout(function () {
                                        google.maps.event.trigger(map, "resize");
                                    }, 50);
                                }
                            } else {
                                wrapper.classList.add("map-hidden");
                                if (showLabel) {
                                    mapToggleBtn.textContent = showLabel;
                                }
                                mapToggleBtn.setAttribute("aria-expanded", "false");
                                mapToggleBtn.dataset.state = "hidden";
                                if (tabs) {
                                    tabs.classList.add("d-none");
                                }
                                if (mapPane.classList.contains("fade")) {
                                    mapPane.classList.remove("show", "active");
                                    listPane.classList.add("show", "active");
                                }
                                if (navMapBtn && navListBtn) {
                                    navMapBtn.classList.remove("active");
                                    navMapBtn.setAttribute("aria-selected", "false");
                                    navListBtn.classList.add("active");
                                    navListBtn.setAttribute("aria-selected", "true");
                                }
                            }
                        }

                        mapToggleBtn.addEventListener("click", function () {
                            var isVisible = mapToggleBtn.dataset.state !== "hidden";
                            setMapVisibility(!isVisible);
                        });
                    }
                });

                google.maps.event.addDomListener(window, "load", initAutocomplete);
                google.maps.event.addDomListener(window, "load", initMap);
            })();
        ';
        return $googleMapCode;
    }

    public static function getEverMapShortcode(string $txt, Context $context, Everblock $module): string
    {
        $apiKey = Configuration::get('EVERBLOCK_GMAP_KEY');
        if (!$apiKey) {
            $message = $module->l('Please set a Google Maps API key in the module configuration.', 'EverblockTools');
            return str_replace('[evermap]', $message, $txt);
        }

        $address1 = Configuration::get('PS_SHOP_ADDR1');
        $postcode = Configuration::get('PS_SHOP_CODE');
        $city = Configuration::get('PS_SHOP_CITY');
        $idCountry = (int) Configuration::get('PS_SHOP_COUNTRY_ID');
        $country = $idCountry ? new Country($idCountry, (int) $context->language->id) : null;
        $countryName = $country ? $country->name : '';

        if (!$address1 || !$postcode || !$city || !$countryName) {
            $message = $module->l('Please fill the postal address of the shop.', 'EverblockTools');
            return str_replace('[evermap]', $message, $txt);
        }

        $address2 = Configuration::get('PS_SHOP_ADDR2');
        $fullAddress = trim($address1 . ' ' . $address2 . ' ' . $postcode . ' ' . $city . ' ' . $countryName);

        $coords = static::getCoordinatesFromAddress($fullAddress, $apiKey);
        if (!$coords) {
            $message = $module->l('Unable to geocode the store address.', 'EverblockTools');
            return str_replace('[evermap]', $message, $txt);
        }

        $mapHtml = '<div id="everblock-gmap" style="width:100%;height:300px;"></div>';
        $mapHtml .= '<script>function initEverblockGmap(){var c={lat:' . $coords['lat'] . ',lng:' . $coords['lng'] . '};var m=new google.maps.Map(document.getElementById("everblock-gmap"),{zoom:15,center:c});new google.maps.Marker({position:c,map:m});}</script>';
        $mapHtml .= '<script src="https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '&callback=initEverblockGmap" async defer></script>';

        return str_replace('[evermap]', $mapHtml, $txt);
    }

    public static function getCoordinatesFromAddress(string $address, string $apiKey)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . $apiKey;
        $response = Tools::file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['results'][0]['geometry']['location'])) {
                return $data['results'][0]['geometry']['location'];
            }
        }
        return false;
    }

    public static function getAllProducts(int $shopId, int $langId, $start = null, $limit = null, $orderBy = null, $orderWay = null): array
    {
        $cacheId = 'EverblockTools::getAllProducts_' . (int) $shopId . '_' . $langId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = 'SELECT p.id_product, pl.name
                    FROM ' . _DB_PREFIX_ . 'product_shop AS p
                    LEFT JOIN ' . _DB_PREFIX_ . 'product_lang AS pl ON (p.id_product = pl.id_product AND pl.id_lang = ' . (int) $langId . ')
                    WHERE p.id_shop = ' . (int) $shopId;

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $products = [];

            if ($result) {
                foreach ($result as $row) {
                    $products[$row['id_product']] = (int) $row['id_product'] . ' - ' . $row['name'];
                }
            }
            EverblockCache::cacheStore($cacheId, $products);
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getAllManufacturers(int $shopId, int $langId): array
    {
        $cacheId = 'EverblockTools::getAllManufacturers_' . (int) $shopId . '_' . $langId;
        
        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = 'SELECT m.id_manufacturer, m.name
                    FROM ' . _DB_PREFIX_ . 'manufacturer AS m
                    LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer_lang AS ml ON (m.id_manufacturer = ml.id_manufacturer AND ml.id_lang = ' . (int) $langId . ')
                    WHERE m.id_manufacturer IN (
                        SELECT id_manufacturer
                        FROM ' . _DB_PREFIX_ . 'product
                        WHERE id_product IN (
                            SELECT id_product
                            FROM ' . _DB_PREFIX_ . 'product_shop
                            WHERE id_shop = ' . (int) $shopId . '
                        )
                    )';

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $manufacturers = [];

            if ($result) {
                foreach ($result as $row) {
                    $manufacturers[$row['id_manufacturer']] = (int) $row['id_manufacturer'] . ' - ' . $row['name'];
                }
            }
            EverblockCache::cacheStore($cacheId, $manufacturers);
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getAllSuppliers(int $shopId, int $langId): array
    {
        $cacheId = 'EverblockTools::getAllSuppliers_' . (int) $shopId . '_' . $langId;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = 'SELECT m.id_supplier, m.name
                    FROM ' . _DB_PREFIX_ . 'supplier AS m
                    LEFT JOIN ' . _DB_PREFIX_ . 'supplier_lang AS ml ON (m.id_supplier = ml.id_supplier AND ml.id_lang = ' . (int) $langId . ')
                    WHERE m.id_supplier IN (
                        SELECT id_supplier
                        FROM ' . _DB_PREFIX_ . 'product
                        WHERE id_product IN (
                            SELECT id_product
                            FROM ' . _DB_PREFIX_ . 'product_shop
                            WHERE id_shop = ' . (int) $shopId . '
                        )
                    )';

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $suppliers = [];

            if ($result) {
                foreach ($result as $row) {
                    $suppliers[$row['id_supplier']] = (int) $row['id_supplier'] . ' - ' . $row['name'];
                }
            }
            EverblockCache::cacheStore($cacheId, $suppliers);
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getProductIdsBySupplier(int $supplierId, $start = null, $limit = null, $orderBy = null, $orderWay = null): array
    {
        $sql = new DbQuery();
        $sql->select('id_product');
        $sql->from('product');
        $sql->where('id_supplier = ' . (int) $supplierId);
        if ($limit) {
            $sql->limit($limit);
        }
        $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        return array_column($productIds, 'id_product');
    }

    public static function getProductIdsByManufacturer(int $manufacturerId, $start = null, $limit = null, $orderBy = null, $orderWay = null): array
    {
        $sql = new DbQuery();
        $sql->select('id_product');
        $sql->from('product');
        $sql->where('id_manufacturer = ' . (int) $manufacturerId);
        if ($limit) {
            $sql->limit((int) $limit);
        }
        $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        return array_column($productIds, 'id_product');
    }

    public static function addLazyLoadToImages(string $text): string
    {
        // Rechercher toutes les balises <img> dans le texte
        $pattern = '/<img\s+([^>]*?)\bsrc="([^"]*?)"([^>]*)>/i';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        // Parcourir les correspondances et ajouter la classe 'lazyload' et l'attribut 'loading="lazy"'
        foreach ($matches as $match) {
            $beforeSrcAttributes = trim($match[1]); // Attributs avant src
            $imageUrl = $match[2]; // URL de l'image
            $afterSrcAttributes = trim($match[3]); // Attributs après src

            $allAttributes = trim($beforeSrcAttributes . ' ' . $afterSrcAttributes); // Tous les attributs

            // Vérifier et modifier l'attribut class
            if (preg_match('/\bclass="([^"]*)"/i', $allAttributes, $classMatch)) {
                $newClassAttribute = 'class="' . trim($classMatch[1] . ' lazyload') . '"';
                $allAttributes = str_replace($classMatch[0], $newClassAttribute, $allAttributes);
            } else {
                $allAttributes .= ' class="lazyload"';
            }

            // Vérifier si loading="lazy" est déjà présent
            if (!preg_match('/\bloading\s*=\s*".*?"/i', $allAttributes)) {
                $allAttributes .= ' loading="lazy"';
            }

            // Construire la nouvelle balise <img> avec tous les attributs modifiés
            $newTag = '<img ' . trim($allAttributes) . ' src="' . $imageUrl . '">';
            $text = str_replace($match[0], $newTag, $text);
        }

        return $text;
    }

    public static function obfuscateTextByClass(string $text): string
    {
        if ((bool) Configuration::get('EVERBLOCK_USE_OBF') === false) {
            return $text;
        }
        // Capturer uniquement <a ...obfme...>CONTENU</a>
        $pattern = '/<a([^>]*)class=("|\')[^"\']*\bobfme\b[^"\']*\2([^>]*)>(.*?)<\/a>/is';

        return preg_replace_callback($pattern, function ($m) {

            $attrsBefore = trim($m[1]);
            $quote = $m[2];
            $attrsAfter  = trim($m[3]);
            $innerHtml = $m[4];

            $attributes = trim($attrsBefore . ' ' . $attrsAfter);

            // Extraire href
            preg_match('/href=("|\')(.*?)\1/i', $attributes, $hrefMatch);
            $href = $hrefMatch[2] ?? '';
            $encoded = base64_encode($href);

            // Ajouter class obflink
            $attributes = preg_replace(
                '/class=("|\')([^"\']*)(\1)/i',
                'class=$1$2 obflink$3',
                $attributes
            );

            // Remplacer href par data-obflink
            $attributes = preg_replace(
                '/href=("|\')(.*?)\1/i',
                'data-obflink="' . $encoded . '"',
                $attributes
            );

            return '<span ' . $attributes . '>' . $innerHtml . '</span>';
        }, $text);
    }

    public static function obfuscateText(string $text): string
    {
        // Rechercher toutes les balises <a href> dans le texte
        $pattern = '/<a\s+(?:[^>]*)href="([^"]*)"([^>]*)>/i';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
        // Parcourir les correspondances et remplacer les balises <a> par des balises <span>
        foreach ($matches as $match) {
            $linkUrl = $match[1];
            $linkAttributes = $match[2];
            $encodedLink = base64_encode($linkUrl);
            // Obtenir les classes existantes de la balise <a>
            preg_match('/class="([^"]*)"/i', $match[0], $classMatches);
            $existingClasses = !empty($classMatches[1]) ? $classMatches[1] : '';
            // Ajouter la classe 'obflink' aux classes existantes
            $classesWithObflink = $existingClasses . ' obflink';
            // Construire la nouvelle balise <span> avec les classes existantes et les attributs de lien
            $newTag = '<span class="' . $classesWithObflink . '" data-obflink="' . $encodedLink . '"' . $linkAttributes . '>';
            $text = str_replace($match[0], $newTag, $text);
        }
        return $text;
    }

    public static function getCustomerShortcodes(string $txt, Context $context): string
    {
        $entityShortcodes = [];
        $customer = new Customer((int) $context->customer->id);
        $gender = new Gender((int) $customer->id_gender, (int) $context->language->id);
        $entityShortcodes = [
            '[entity_lastname]' => $customer->lastname,
            '[entity_firstname]' => $customer->firstname,
            '[entity_company]' => $customer->company,
            '[entity_siret]' => $customer->siret,
            '[entity_ape]' => $customer->ape,
            '[entity_birthday]' => $customer->birthday,
            '[entity_website]' => $customer->website,
            '[entity_gender]' => $gender->name,
        ];
        foreach ($entityShortcodes as $key => $value) {
            $replacement = $value ?? '';
            $txt = str_replace($key, (string) $replacement, $txt);
        }
        return $txt;
    }

    public static function getEverShortcodes(string $txt, Context $context): string
    {
        $customShortcodes = EverblockShortcode::getAllShortcodes(
            $context->shop->id,
            $context->language->id
        );
        $returnedShortcodes = [];
        foreach ($customShortcodes as $sc) {
            $content = $sc->content ?? '';
            $txt = str_replace($sc->shortcode, (string) $content, $txt);
        }
        return $txt;
    }

    public static function generateLoremIpsum(string $txt, Context $context): string
    {
        $cacheId = 'generateLoremIpsum_' . (int) $context->shop->id;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $lloremParagraphNum = (int) EverblockCache::getModuleConfiguration('EVERPSCSS_P_LLOREM_NUMBER');
            if ($lloremParagraphNum <= 0) {
                $lloremParagraphNum = 5;
            }
            $lloremSentencesNum = (int) EverblockCache::getModuleConfiguration('EVERPSCSS_S_LLOREM_NUMBER');
            if ($lloremSentencesNum <= 0) {
                $lloremSentencesNum = 5;
            }
            $paragraphs = [];
            $sentences = [
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
                'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            ];
            for ($i = 0; $i < $lloremParagraphNum; $i++) {
                $paragraph = '<p>';
                for ($j = 0; $j < $lloremSentencesNum; $j++) {
                    $sentence = $sentences[array_rand($sentences)];
                    $paragraph .= $sentence . ' ';
                }
                $paragraph .= '</p>';
                $paragraphs[] = $paragraph;
            }
            $llorem = implode("\n\n", $paragraphs);
            EverblockCache::cacheStore($cacheId, $llorem);
        } else{
            $llorem = EverblockCache::cacheRetrieve($cacheId);
        }
        $txt = str_replace('[llorem]', $llorem, $txt);
        return $txt;
    }

    public static function checkAndFixDatabase()
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $dbMaster = Db::getInstance();
        $tableNames = [
            _DB_PREFIX_ . 'everblock',
            _DB_PREFIX_ . 'everblock_lang',
            _DB_PREFIX_ . 'everblock_shortcode',
            _DB_PREFIX_ . 'everblock_shortcode_lang',
            _DB_PREFIX_ . 'everblock_faq',
            _DB_PREFIX_ . 'everblock_faq_lang',
            _DB_PREFIX_ . 'everblock_faq_product',
            _DB_PREFIX_ . 'everblock_tabs',
            _DB_PREFIX_ . 'everblock_tabs_lang',
            _DB_PREFIX_ . 'everblock_flags',
            _DB_PREFIX_ . 'everblock_flags_lang',
            _DB_PREFIX_ . 'everblock_modal',
            _DB_PREFIX_ . 'everblock_modal_lang',
            _DB_PREFIX_ . 'everblock_game_play',
            _DB_PREFIX_ . 'everblock_page',
            _DB_PREFIX_ . 'everblock_page_lang',
        ];
        $missingTableDetected = false;
        foreach ($tableNames as $tableName) {
            if (!static::ifTableExists($tableName)) {
                $missingTableDetected = true;
                break; // Pas besoin de vérifier les autres tables
            }
        }

        if ($missingTableDetected) {
            include _PS_MODULE_DIR_ . 'everblock/sql/install.php';
        }
        // Ajoute les colonnes manquantes à la table ps_everblock
        $columnsToAdd = [
            'only_home' => 'int(10) unsigned DEFAULT NULL',
            'id_hook' => 'int(10) unsigned NOT NULL',
            'only_category' => 'int(10) unsigned DEFAULT NULL',
            'only_category_product' => 'int(10) unsigned DEFAULT NULL',
            'only_manufacturer' => 'int(10) unsigned DEFAULT NULL',
            'only_supplier' => 'int(10) unsigned DEFAULT NULL',
            'only_cms_category' => 'int(10) unsigned DEFAULT NULL',
            'obfuscate_link' => 'int(10) unsigned DEFAULT NULL',
            'add_container' => 'int(10) unsigned DEFAULT NULL',
            'lazyload' => 'int(10) unsigned DEFAULT NULL',
            'device' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'id_shop' => 'int(10) unsigned NOT NULL DEFAULT 1',
            'position' => 'int(10) unsigned DEFAULT 0',
            'categories' => 'text DEFAULT NULL',
            'manufacturers' => 'text DEFAULT NULL',
            'suppliers' => 'text DEFAULT NULL',
            'cms_categories' => 'text DEFAULT NULL',
            'groups' => 'text DEFAULT NULL',
            'background' => 'varchar(255) DEFAULT NULL',
            'css_class' => 'varchar(255) DEFAULT NULL',
            'data_attribute' => 'varchar(255) DEFAULT NULL',
            'bootstrap_class' => 'varchar(255) DEFAULT NULL',
            'delay' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'timeout' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'modal' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'date_start' => 'DATETIME DEFAULT NULL',
            'date_end' => 'DATETIME DEFAULT NULL',
            'active' => 'int(10) unsigned NOT NULL DEFAULT 1',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock',
            $columnsToAdd,
            'Unable to update Ever Block database'
        );
        // Ajoute les colonnes manquantes à la table ps_everblock_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'content' => 'text DEFAULT NULL',
            'custom_code' => 'text DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_lang',
            $columnsToAdd,
            'Unable to update Ever Block database'
        );
        // Ajoute les colonnes manquantes à la table everblock_shortcode
        $columnsToAdd = [
            'shortcode' => 'text DEFAULT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL DEFAULT 1',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_shortcode',
            $columnsToAdd,
            'Unable to update Ever Block database'
        );
        // Ajoute les colonnes manquantes à la table everblock_shortcode_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'title' => 'text DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_shortcode_lang',
            $columnsToAdd,
            'Unable to update Ever Block database'
        );
        // Ajoute les colonnes manquantes à la table everblock_faq
        $columnsToAdd = [
            'tag_name' => 'text DEFAULT NULL',
            'position' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'active' => 'int(10) unsigned NOT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL DEFAULT 1',
            'date_add' => 'DATETIME DEFAULT NULL',
            'date_upd' => 'DATETIME DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_faq',
            $columnsToAdd,
            'Unable to update Ever Block database'
        );
        // Ajoute les colonnes manquantes à la table everblock_faq_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'title' => 'text DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_faq_lang',
            $columnsToAdd,
            'Unable to update Ever Block database'
        );
        // Ajoute les colonnes manquantes à la table everblock_faq_product
        $columnsToAdd = [
            'id_everblock_faq' => 'int(10) unsigned NOT NULL',
            'id_product' => 'int(10) unsigned NOT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL',
            'position' => 'int(10) unsigned NOT NULL DEFAULT 0',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_faq_product',
            $columnsToAdd,
            'Unable to update Ever Block FAQ product database'
        );
        // Ajoute les colonnes manquantes à la table everblock_tabs
        $columnsToAdd = [
            'id_product' => 'int(10) unsigned NOT NULL',
            'id_tab' => 'int(10) unsigned DEFAULT 0',
            'id_shop' => 'int(10) unsigned DEFAULT 1',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_tabs',
            $columnsToAdd,
            'Unable to update Ever Block tabs database'
        );
        // Ajoute les colonnes manquantes à la table everblock_tabs_lang
        $columnsToAdd = [
            'id_everblock_tabs' => 'int(10) unsigned NOT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
            'title' => 'varchar(255) DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_tabs_lang',
            $columnsToAdd,
            'Unable to update Ever Block tabs lang database'
        );
        // Ajoute les colonnes manquantes à la table everblock_flags
        $columnsToAdd = [
            'id_product' => 'int(10) unsigned NOT NULL',
            'id_flag' => 'int(10) unsigned DEFAULT 0',
            'id_shop' => 'int(10) unsigned DEFAULT 0',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_flags',
            $columnsToAdd,
            'Unable to update Ever Block flags database'
        );
        // Ajoute les colonnes manquantes à la table everblock_flags_lang
        $columnsToAdd = [
            'id_everblock_flags' => 'int(10) unsigned NOT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
            'title' => 'varchar(255) DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_flags_lang',
            $columnsToAdd,
            'Unable to update Ever Block flags lang database'
        );
        // Ajoute les colonnes manquantes à la table everblock_modal
        $columnsToAdd = [
            'id_product' => 'int(10) unsigned NOT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL',
            'file' => 'varchar(255) DEFAULT NULL',
            'button_file' => 'varchar(255) DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_modal',
            $columnsToAdd,
            'Unable to update Ever Block modal database'
        );
        // Ajoute les colonnes manquantes à la table everblock_modal_lang
        $columnsToAdd = [
            'id_everblock_modal' => 'int(10) unsigned NOT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
            'content' => 'text DEFAULT NULL',
            'button_label' => 'text DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_modal_lang',
            $columnsToAdd,
            'Unable to update Ever Block modal lang database'
        );
        // Ajoute les colonnes manquantes à la table everblock_game_play
        $columnsToAdd = [
            'id_prettyblocks' => 'int(10) unsigned NOT NULL',
            'id_customer' => 'int(10) unsigned NOT NULL',
            'ip_address' => 'varchar(45) DEFAULT NULL',
            'result' => 'varchar(255) DEFAULT NULL',
            'is_winner' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'date_add' => 'DATETIME DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_game_play',
            $columnsToAdd,
            'Unable to update Ever Block game play database'
        );
        $pageTable = _DB_PREFIX_ . 'everblock_page';
        $columnExists = $dbMaster->executeS('SHOW COLUMNS FROM `' . $pageTable . '` LIKE "id_employee"');
        if (!$columnExists) {
            $dbMaster->execute('ALTER TABLE `' . $pageTable . '` ADD `id_employee` int(10) unsigned DEFAULT NULL AFTER `id_shop`');
        }
        // Ajoute les colonnes manquantes à la table everblock_page
        $columnsToAdd = [
            'id_shop' => 'int(10) unsigned NOT NULL DEFAULT 1',
            'id_employee' => 'int(10) unsigned DEFAULT NULL',
            'groups' => 'text DEFAULT NULL',
            'cover_image' => 'varchar(255) DEFAULT NULL',
            'active' => 'int(10) unsigned NOT NULL DEFAULT 1',
            'position' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'date_add' => 'DATETIME DEFAULT NULL',
            'date_upd' => 'DATETIME DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_page',
            $columnsToAdd,
            'Unable to update Ever Block page database'
        );
        // Ajoute les colonnes manquantes à la table everblock_page_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'name' => 'varchar(255) DEFAULT NULL',
            'title' => 'varchar(255) DEFAULT NULL',
            'meta_description' => 'text DEFAULT NULL',
            'short_description' => 'text DEFAULT NULL',
            'link_rewrite' => 'varchar(255) DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];
        static::addMissingColumns(
            $db,
            _DB_PREFIX_ . 'everblock_page_lang',
            $columnsToAdd,
            'Unable to update Ever Block page lang database'
        );
        static::cleanObsoleteFiles();
    }

    private static function addMissingColumns(Db $db, string $tableName, array $columnsToAdd, string $logMessage): void
    {
        if (!static::ifTableExists($tableName)) {
            return;
        }

        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . $tableName . '` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . $tableName . '` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog($logMessage);
                }
            }
        }
    }

    public static function everPresentProducts(array $result, Context $context): array
    {
        $products = [];
        $cacheEnabled = (bool) EverblockCache::getModuleConfiguration('EVERBLOCK_EVER_PRESENT_CACHE');
        $cacheKey = '';

        if ($cacheEnabled) {
            $productIds = array_values(array_map('intval', $result));
            $cacheContext = [
                'products' => $productIds,
                'lang' => (int) $context->language->id,
                'shop' => (int) $context->shop->id,
                'currency' => isset($context->currency) ? (int) $context->currency->id : 0,
                'customer' => isset($context->customer) ? (int) $context->customer->id : 0,
                'customer_group' => isset($context->customer) ? (int) $context->customer->id_default_group : 0,
                'country' => isset($context->country) ? (int) $context->country->id : 0,
            ];
            $cacheKey = 'everblock_everPresentProducts_' . md5(json_encode($cacheContext));
            if (EverblockCache::isCacheStored($cacheKey)) {
                $cachedProducts = EverblockCache::cacheRetrieve($cacheKey);
                return is_array($cachedProducts) ? $cachedProducts : [];
            }
        }

        if (!empty($result)) {
            $assembler = new \ProductAssembler($context);
            $presenterFactory = new \ProductPresenterFactory($context);
            $presentationSettings = $presenterFactory->getPresentationSettings();

            // compatibilité PS 8 et PS 9
            if (class_exists(\PrestaShop\PrestaShop\Core\Product\ProductListingPresenter::class)) {
                // PS 1.7 / 8
                $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                    new ImageRetriever($context->link),
                    $context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $context->getTranslator()
                );
            } elseif (class_exists(\PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter::class)) {
                // PS 9
                $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter(
                    new ImageRetriever($context->link),
                    $context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $context->getTranslator()
                );
            } else {
                throw new \Exception('No suitable product presenter class found for this PrestaShop version.');
            }

            $presentationSettings->showPrices = true;

            foreach ($result as $productId) {
                $psProduct = new Product((int) $productId);

                if (!Validate::isLoadedObject($psProduct) || !(bool) $psProduct->active) {
                    continue;
                }

                $rawProduct = [
                    'id_product' => $productId,
                    'id_lang'   => $context->language->id,
                    'id_shop'   => $context->shop->id,
                ];

                $pproduct = $assembler->assembleProduct($rawProduct);

                if (Product::checkAccessStatic((int) $productId, (int) $context->customer->id)) {
                    $products[] = $presenter->present(
                        $presentationSettings,
                        $pproduct,
                        $context->language
                    );
                }
            }
        }

        if ($cacheEnabled && $cacheKey !== '') {
            EverblockCache::cacheStore($cacheKey, $products);
        }

        return $products;
    }
    public static function dropUnusedLangs(): array
    {
        $postErrors = [];
        $querySuccess = [];
        $pstable = [
            _DB_PREFIX_ . 'category_lang',
            _DB_PREFIX_ . 'product_lang',
            _DB_PREFIX_ . 'image_lang',
            _DB_PREFIX_ . 'cms_lang',
            _DB_PREFIX_ . 'meta_lang',
            _DB_PREFIX_ . 'manufacturer_lang',
            _DB_PREFIX_ . 'supplier_lang',
            _DB_PREFIX_ . 'group_lang',
            _DB_PREFIX_ . 'gender_lang',
            _DB_PREFIX_ . 'feature_lang',
            _DB_PREFIX_ . 'feature_value_lang',
            _DB_PREFIX_ . 'customization_field_lang',
            _DB_PREFIX_ . 'country_lang',
            _DB_PREFIX_ . 'cart_rule_lang',
            _DB_PREFIX_ . 'carrier_lang',
            _DB_PREFIX_ . 'attachment_lang',
            _DB_PREFIX_ . 'attribute_lang',
            _DB_PREFIX_ . 'attribute_group_lang',
        ];
        foreach ($pstable as $tableName) {
            $table = bqSQL(trim($tableName));
            $sql = 'DELETE FROM ' . $table . '
            WHERE id_lang NOT IN
            (SELECT id_lang FROM ' . _DB_PREFIX_ . 'lang)';
            try {
                Db::getInstance()->Execute($sql);
                $querySuccess[] = 'Unknown lang dropped from table ' . $table;
            } catch (Exception $e) {
                $postErrors[] = $e->getMessage();
            }
        }
        return [
            'postErrors' => $postErrors,
            'querySuccess' => $querySuccess,
        ];
    }

    /**
     * Exporte les données des tables de module dans un fichier SQL.
     * 
     * @return bool True en cas de succès, sinon False.
     */
    public static function exportModuleTablesSQL(): bool
    {
        // Liste des tables de module sans préfixe
        $tables = [
            _DB_PREFIX_ . 'everblock',
            _DB_PREFIX_ . 'everblock_lang',
            _DB_PREFIX_ . 'everblock_shortcode',
            _DB_PREFIX_ . 'everblock_shortcode_lang',
            _DB_PREFIX_ . 'everblock_faq',
            _DB_PREFIX_ . 'everblock_faq_lang',
            _DB_PREFIX_ . 'everblock_tabs',
            _DB_PREFIX_ . 'everblock_tabs_lang',
        ];
        // Valider et nettoyer les noms de table (vous pouvez ajouter d'autres vérifications ici)
        $validTables = [];
        foreach ($tables as $table) {
            $table = bqSQL(trim($table));
            if (!empty($table)) {
                if (static::ifTableExists($table)) {
                    $validTables[] = pSQL($table);
                }
            }
        }
        if (empty($validTables)) {
            return false;
        }
        // Générer une requête SQL pour extraire les tables spécifiées
        $tablesString = implode(',', $validTables);
        // Exécutez la requête SQL pour récupérer les données de la base de données PrestaShop
        $db = Db::getInstance();
        $sqlData = '';
        foreach ($validTables as $tableName) {
            $tableName = bqSQL(trim($tableName));
            // Obtenir la structure de la table (inclut les contraintes et les index)
            $createTableSql = static::getTableStructure($tableName);
            // Ajoutez DROP TABLE
            $sqlData .= "DROP TABLE IF EXISTS `$tableName`;\n";
            // Ajoutez CREATE TABLE avec la structure
            $sqlData .= "$createTableSql;\n";
            // Exécutez la requête SQL pour extraire les données de la table
            $sql = 'SELECT * FROM `' . $tableName . '`';
            $result = $db->executeS($sql);
            if ($result) {
                // Ajoutez INSERT INTO
                foreach ($result as $row) {
                    $sqlData .= "INSERT INTO `$tableName` (";
                    $escapedKeys = array_map([Db::getInstance(), 'escape'], array_keys($row));
                    $escapedKeys = array_map(function ($key) {
                        return "`$key`";
                    }, $escapedKeys); // Ajout des backticks aux noms de colonnes
                    $sqlData .= implode(',', $escapedKeys);
                    $sqlData .= ") VALUES (";
                    // Échappez et formatez correctement les valeurs
                    $escapedValues = [];
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $escapedValues[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $escapedValues[] = (int) $value;
                        } else {
                            $escapedValues[] = "'" . pSQL($value) . "'";
                        }
                    }
                    $sqlData .= implode(',', $escapedValues);
                    $sqlData .= ");\n";
                }
            }
        }
        $filePath = _PS_MODULE_DIR_ . 'everblock/dump.sql';
        if (file_put_contents($filePath, $sqlData)) {
            return true;
        }
        return false;
    }

    /**
     * Exporte la table de configuration dans un fichier SQL.
     *
     * @return bool True en cas de succès, sinon False.
     */
    public static function exportConfigurationSQL(): bool
    {
        $tableName = _DB_PREFIX_ . 'configuration';
        if (!static::ifTableExists($tableName)) {
            return false;
        }
        $db = Db::getInstance();
        $sqlData = '';
        $tableName = bqSQL(trim($tableName));
        $createTableSql = static::getTableStructure($tableName);
        $sqlData .= "DROP TABLE IF EXISTS `$tableName`;\n";
        $sqlData .= "$createTableSql;\n";
        $sql = 'SELECT * FROM `' . $tableName . '`';
        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as $row) {
                $sqlData .= "INSERT INTO `$tableName` (";
                $escapedKeys = array_map([Db::getInstance(), 'escape'], array_keys($row));
                $escapedKeys = array_map(function ($key) {
                    return "`$key`";
                }, $escapedKeys);
                $sqlData .= implode(',', $escapedKeys);
                $sqlData .= ") VALUES (";
                $escapedValues = [];
                foreach ($row as $value) {
                    if (is_null($value)) {
                        $escapedValues[] = 'NULL';
                    } elseif (is_numeric($value)) {
                        $escapedValues[] = (int) $value;
                    } else {
                        $escapedValues[] = "'" . pSQL($value) . "'";
                    }
                }
                $sqlData .= implode(',', $escapedValues);
                $sqlData .= ");\n";
            }
        }
        $filePath = _PS_MODULE_DIR_ . 'everblock/configuration.sql';
        if (file_put_contents($filePath, $sqlData)) {
            return true;
        }
        return false;
    }

    /**
     * Récupère la structure d'une table dans la base de données.
     * @param string $tableName Nom de la table.
     * @return string|null Structure de la table en SQL, ou null en cas d'erreur.
     */
    protected static function getTableStructure(string $tableName)
    {
        $db = Db::getInstance();
        $sql ='SHOW CREATE TABLE ' . $tableName;
        $result = $db->executeS($sql);
        if ($result && isset($result[0]['Create Table'])) {
            return $result[0]['Create Table'];
        }
        return null;
    }

    /**
     * Vérifie si une table existe dans la base de données.
     * @param string $tableName Nom de la table à vérifier.
     * @return bool True si la table existe, sinon False.
     */
    protected static function ifTableExists(string $tableName)
    {
        $db = Db::getInstance();
        $result = $db->executeS('SHOW TABLES LIKE "' . $tableName . '"');
        return !empty($result);
    }

    /**
     * Teste si le fichier SQL de sauvegarde existe et restaure les tables et données si possible.
     * @return bool
     */
    public static function restoreModuleTablesFromBackup(): bool
    {
        // Chemin du fichier de sauvegarde
        $filePath = _PS_MODULE_DIR_ . 'everblock/dump.sql';
        if (file_exists($filePath)) {
            try {
                // Exécute les requêtes SQL du fichier de sauvegarde
                $sqlContent = Tools::file_get_contents($filePath);
                $db = Db::getInstance();
                $queries = preg_split("/;\n/", $sqlContent);
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        $db->execute($query);
                    }
                }
                unlink($filePath);
                return true;
            } catch (Exception $e) {
                // En cas d'erreur, log l'erreur dans PrestaShop Logger
                PrestaShopLogger::addLog('Error during Ever Block module tables restoration: ' . $e->getMessage(), 3);
                return false;
            }
        }
        return false;
    }

    /**
     * Export a single HTML block as a SQL string.
     * Generates DELETE and INSERT statements for the block
     * in everblock and everblock_lang tables.
     *
     * @param int $idBlock Block identifier
     * @return string SQL content or empty string on failure
     */
    public static function exportBlockSQL(int $idBlock): string
    {
        $db = Db::getInstance();

        $block = $db->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'everblock` WHERE id_everblock = ' . (int) $idBlock
        );
        if (!$block) {
            return '';
        }

        $sqlData = 'DELETE FROM `' . _DB_PREFIX_ . 'everblock` WHERE `id_everblock` = ' . (int) $idBlock . ';' . PHP_EOL;
        $columns = array_keys($block);
        $escapedCols = array_map(function ($col) { return '`' . bqSQL($col) . '`'; }, $columns);
        $values = [];
        foreach ($block as $value) {
            if (is_null($value)) {
                $values[] = 'NULL';
            } elseif (is_numeric($value)) {
                $values[] = (int) $value;
            } else {
                $values[] = "'" . pSQL($value) . "'";
            }
        }
        $sqlData .= 'INSERT INTO `' . _DB_PREFIX_ . 'everblock` (' . implode(',', $escapedCols) . ') VALUES (' . implode(',', $values) . ');' . PHP_EOL;

        $rows = $db->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'everblock_lang` WHERE id_everblock = ' . (int) $idBlock
        );
        if ($rows) {
            $sqlData .= 'DELETE FROM `' . _DB_PREFIX_ . 'everblock_lang` WHERE `id_everblock` = ' . (int) $idBlock . ';' . PHP_EOL;
            foreach ($rows as $row) {
                $cols = array_keys($row);
                $cols = array_map(function ($c) { return '`' . bqSQL($c) . '`'; }, $cols);
                $vals = [];
                foreach ($row as $val) {
                    if (is_null($val)) {
                        $vals[] = 'NULL';
                    } elseif (is_numeric($val)) {
                        $vals[] = (int) $val;
                    } else {
                        $vals[] = "'" . pSQL($val) . "'";
                    }
                }
                $sqlData .= 'INSERT INTO `' . _DB_PREFIX_ . 'everblock_lang` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ');' . PHP_EOL;
            }
        }

        return $sqlData;
    }

    /**
     * Create fake products
     * @param shop id
     * @return bool
    */
    public static function generateProducts(int $idShop): bool
    {
        $numProducts = (int) Configuration::get('EVERPS_DUMMY_NBR');
        if ($numProducts <= 0) {
            $numProducts = 5;
        }
        try {
            for ($i = 0; $i < $numProducts; $i++) {
                $product = new Product();
                $product->id_shop_default = $idShop;
                $product->price = rand(10, 100);
                $product->quantity = rand(1, 100);
                // Générez d'autres propriétés de produit fictives selon vos besoins
                $product->name = 'Product ' . ($i + 1);
                $product->description = 'Description for Product ' . ($i + 1);
                $product->reference = 'PRD_DUMMY' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                if ($product->add()) {
                    $categories = [2];
                    $product->addToCategories($categories);
                    StockAvailable::setQuantity($product->id, 0, $product->quantity);

                    $image = new Image();
                    $image->id_product = $product->id;
                    $image->position = Image::getHighestPosition($product->id) + 1;
                    $image->cover = 1;
                    if ($image->add()) {
                        $image->associateTo([$idShop]);
                        $tmpFile = tempnam(_PS_TMP_IMG_DIR_, 'ever');
                        $fakeUrl = 'https://picsum.photos/600/600?random=' . mt_rand();
                        if (!Tools::copy($fakeUrl, $tmpFile)) {
                            Tools::copy(_PS_MODULE_DIR_ . 'everblock/logo.png', $tmpFile);
                        }
                        $path = (defined('_PS_PRODUCT_IMG_DIR_') ? _PS_PRODUCT_IMG_DIR_ : _PS_PROD_IMG_DIR_) . $image->getExistingImgPath() . '.jpg';
                        ImageManager::resize($tmpFile, $path);
                        $types = ImageType::getImagesTypes('products');
                        foreach ($types as $type) {
                            $pathToType = (defined('_PS_PRODUCT_IMG_DIR_') ? _PS_PRODUCT_IMG_DIR_ : _PS_PROD_IMG_DIR_) . $image->getExistingImgPath() . '-' . $type['name'] . '.jpg';

                            ImageManager::resize(
                                $tmpFile,
                                $pathToType,
                                $type['width'],
                                $type['height']
                            );
                        }
                        unlink($tmpFile);
                    }
                } else {
                    PrestaShopLogger::addLog('Failed to create Product ' . ($i + 1), 3); // Niveau d'erreur : 3 (erreur)
                    return false;
                }
            }
            return true;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Error: ' . $e->getMessage(), 3); // Niveau d'erreur : 3 (erreur)
            return false;
        }
    }

    public static function getPhpLicenceHeader(): string
    {
        return '<?php' . PHP_EOL .
            '/**' . PHP_EOL .
            ' * 2019-2025 Team Ever' . PHP_EOL .
            ' *' . PHP_EOL .
            ' * NOTICE OF LICENSE' . PHP_EOL .
            ' *' . PHP_EOL .
            ' * This source file is subject to the Academic Free License (AFL 3.0)' . PHP_EOL .
            ' * that is bundled with this package in the file LICENSE.txt.' . PHP_EOL .
            ' * It is also available through the world-wide-web at this URL:' . PHP_EOL .
            ' * http://opensource.org/licenses/afl-3.0.php' . PHP_EOL .
            ' * If you did not receive a copy of the license and are unable to' . PHP_EOL .
            ' * obtain it through the world-wide-web, please send an email' . PHP_EOL .
            ' * to license@prestashop.com so we can send you a copy immediately.' . PHP_EOL .
            ' *' . PHP_EOL .
            ' *  @author    Team Ever <https://www.team-ever.com/>' . PHP_EOL .
            ' *  @copyright 2019-2025 Team Ever' . PHP_EOL .
            ' *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)' . PHP_EOL .
            ' */' . PHP_EOL .
            'if (!defined(\'_PS_VERSION_\')) {' . PHP_EOL .
            '    exit;' . PHP_EOL .
            '}';
    }

    public static function getUpgradeMethod($version)
    {
        return 'function upgrade_module_' . str_replace('.', '_', $version) . '($module)' . PHP_EOL .
            '{' . PHP_EOL .
            '    EverblockTools::checkAndFixDatabase();' . PHP_EOL .
            '    $module->checkHooks();' . PHP_EOL .
            '    return true;' . PHP_EOL .
            '}';
    }

    public static function setLog(string $logKey, string $logValue)
    {
        $logFilePath = _PS_ROOT_DIR_ . '/var/logs/' . $logKey . '.log';
        $logValue = trim($logValue);
        if ($logValue === '') {
            if (file_exists($logFilePath)) {
                unlink($logFilePath);
            }
            return;
        }
        file_put_contents($logFilePath, $logValue);
    }

    public static function getLog(string $logKey)
    {
        $logFilePath = _PS_ROOT_DIR_ . '/var/logs/' . $logKey . '.log';
        if (file_exists($logFilePath)) {
            return Tools::file_get_contents($logFilePath);
        }
        return '';
    }

    public static function dropLog(string $logKey)
    {
        $logFilePath = _PS_ROOT_DIR_ . '/var/logs/' . $logKey . '.log';
        if (file_exists($logFilePath)) {
            unlink($logFilePath);
        }
    }

    public static function purgeNativePrestashopLogsTable()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'log');
        ;
    }

    /**
     * Check if module is on disk (PS issue when module has been deleted manually)
     * @param module name
     * @return bool
    */
    public static function moduleDirectoryExists(string $moduleName): bool
    {
        $moduleDirPath = _PS_MODULE_DIR_ . $moduleName;
        return is_dir($moduleDirPath);
    }

    public static function secureModuleFoldersWithApache(): array
    {
        $postErrors = [];
        $querySuccess = [];
        try {
            $modulesDir = _PS_MODULE_DIR_;
            $sourceHtaccessFile = $modulesDir . 'everblock/.htaccess';
            if (!file_exists($sourceHtaccessFile)) {
                $postErrors[] = 'The .htaccess file is not present in the Everblock module.';
                return ['postErrors' => $postErrors, 'querySuccess' => $querySuccess];
            }
            $directories = new DirectoryIterator($modulesDir);
            foreach ($directories as $directory) {
                if ($directory->isDot() || !$directory->isDir()) {
                    continue;
                }
                $moduleDirPath = $directory->getPathname();
                $htaccessFilePath = $moduleDirPath . '/.htaccess';
                if (!file_exists($htaccessFilePath)) {
                    if (!copy($sourceHtaccessFile, $htaccessFilePath)) {
                        $postErrors[] = 'Failed to copy .htaccess file to ' . $directory->getFilename();
                    } else {
                        $querySuccess[] = 'The .htaccess file has been successfully copied to ' . $directory->getFilename();
                    }
                }
            }
        } catch (Exception $e) {
            // En cas d'exception, ajouter le message d'erreur à postErrors
            $postErrors[] = 'An error occurred: ' . $e->getMessage();
            // Log de l'erreur
            PrestaShopLogger::addLog($e->getMessage());
        }
        return [
            'postErrors' => $postErrors,
            'querySuccess' => $querySuccess,
        ];
    }

    public static function cleanObsoleteFiles(): void
    {
        $moduleDir = _PS_MODULE_DIR_ . 'everblock/';
        $allowedFile = $moduleDir . 'config/allowed_files.php';
        if (!file_exists($allowedFile)) {
            return;
        }
        $allowedFiles = include $allowedFile;
        if (!is_array($allowedFiles)) {
            return;
        }
        $allowed = array_flip(array_map('trim', $allowedFiles));

        $ignorePatterns = ['views/img/*'];
        $gitignore = $moduleDir . '.gitignore';
        if (file_exists($gitignore)) {
            $lines = file($gitignore, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0) {
                    continue;
                }
                $ignorePatterns[] = $line;
            }
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($moduleDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $executableExtensions = ['php', 'phtml', 'phar'];

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            $relativePath = str_replace('\\', '/', substr($fileInfo->getPathname(), strlen($moduleDir)));
            // 🔒 Ignore generated WP posts templates
            if (strpos($relativePath, 'views/templates/hook/generated_wp_posts/') === 0) {
                continue;
            }
            $extension = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
            if (in_array($extension, $executableExtensions, true)) {
                if (basename($relativePath) === 'index.php') {
                    continue;
                }
                if (!isset($allowed[$relativePath])) {
                    @unlink($fileInfo->getPathname());
                }

                continue;
            }
            $skip = false;
            foreach ($ignorePatterns as $pattern) {
                if (fnmatch($pattern, $relativePath)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }
            if (!isset($allowed[$relativePath])) {
                @unlink($fileInfo->getPathname());
            }
        }
    }

    public static function fetchInstagramImages()
    {
        $cacheId = 'fetchInstagramImages';
        if (!EverblockCache::isCacheStored($cacheId)) {
            $request = static::getInstagramRequest();
            // $request = Tools::file_get_contents('https://graph.instagram.com/me/media?access_token=IGQWRNTDdaUnFyaFNway14eTJ0NFpiSDlSZAlNNemV0U3hwNmlma3laMC01WUVxdVlucnJOM2JReF9Oblg2SmdHRlVwLXdPWXRPNVNLb1RZASjMtN0JHMW4zemNnYzZA6MVpYSGEwcHEtOG5MQQZDZD&fields=id,caption,media_type,media_url,permalink,thumbnail_url,username,timestamp');
            $result = json_decode($request, true);
            $imgs = [];
            $baseDir = _PS_IMG_DIR_ . 'cms/instagram/';
            if (!is_dir($baseDir)) {
                @mkdir($baseDir, 0755, true);
            }
            if ($result && isset($result['data']) && $result['data']) {
                foreach ($result['data'] as $post) {
                    $mediaUrl = isset($post['thumbnail_url']) ? $post['thumbnail_url'] : $post['media_url'];
                    $extension = pathinfo(parse_url($mediaUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (!$extension) {
                        $extension = 'jpg';
                    }
                    $fileName = $post['id'] . '.' . $extension;
                    $filePath = $baseDir . $fileName;
                    $webPath = _PS_BASE_URL_ . __PS_BASE_URI__ . 'img/cms/instagram/' . $fileName;

                    if (!file_exists($filePath)) {
                        $content = Tools::file_get_contents($mediaUrl);
                        if ($content !== false) {
                            file_put_contents($filePath, $content);
                        }
                    }

                    $width = 0;
                    $height = 0;
                    if (is_file($filePath)) {
                        $imageSize = @getimagesize($filePath);
                        if ($imageSize) {
                            $width = (int) $imageSize[0];
                            $height = (int) $imageSize[1];
                        }
                    }

                    if ($width <= 0 || $height <= 0) {
                        $width = 320;
                        $height = 320;
                    }

                    $imgs[] = [
                        'id' => isset($post['id']) ? $post['id'] : $post['id'],
                        'permalink' => isset($post['permalink']) ? $post['permalink'] : '',
                        'low_resolution' => $webPath,
                        'thumbnail' => $webPath,
                        'standard_resolution' => $webPath,
                        'caption' => isset($post['caption']) ? $post['caption'] : '',
                        'is_video' => strpos($mediaUrl, '.mp4') !== false,
                        'width' => $width,
                        'height' => $height,
                    ];
                }
            }
            static::refreshInstagramToken();
            EverblockCache::cacheStore($cacheId, $imgs);
            return $imgs;
        }
        return EverblockCache::cacheRetrieve($cacheId);
    }

    public static function getInstagramRequest()
    {
        $instaToken = Configuration::get('EVERINSTA_ACCESS_TOKEN');
        $fields = '&fields=id,caption,media_type,media_url,permalink,thumbnail_url,username,timestamp';
        $url = "https://graph.instagram.com/me/media?access_token=" . $instaToken . $fields;
        return Tools::file_get_contents($url);
    }

    public static function refreshInstagramToken()
    {
        $instaToken = Configuration::get('EVERINSTA_ACCESS_TOKEN');
        $url = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $instaToken;
        $result = Tools::file_get_contents($url);
        $json = json_decode($result, true);
        if (isset($json['access_token'])) {
            Configuration::updateValue('EVERINSTA_ACCESS_TOKEN', $json['access_token']);
            return $json['access_token'];
        }
        return null;
    }

    public static function fetchWordpressPosts(): bool
    {
        $apiUrl = trim(Configuration::get('EVERWP_API_URL'));
        if (!$apiUrl) {
            return false;
        }
        $limit = (int) Configuration::get('EVERWP_POST_NBR');
        if ($limit < 1) {
            $limit = 3;
        }
        $requestUrl = rtrim($apiUrl, '/');
        $queryParams = [
            'per_page' => $limit,
            '_embed' => 1,
        ];
        $separator = (strpos($requestUrl, '?') === false) ? '?' : '&';
        $requestUrl .= $separator . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        $response = Tools::file_get_contents($requestUrl);
        $posts = json_decode($response, true);
        if (!$posts || !is_array($posts)) {
            return false;
        }
        $generatedDir = _PS_MODULE_DIR_ . 'everblock/views/templates/hook/generated_wp_posts/';
        if (!is_dir($generatedDir)) {
            if (!@mkdir($generatedDir, 0755, true) && !is_dir($generatedDir)) {
                return false;
            }
        }

        $apiBaseUrl = self::getWordpressApiBase($apiUrl);
        $preparedPosts = [];
        foreach ($posts as $post) {
            $postId = (int) ($post['id'] ?? 0);
            $title = html_entity_decode(
                strip_tags($post['title']['rendered'] ?? ''),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
            $link = $post['link'] ?? '#';
            $excerpt = html_entity_decode(
                strip_tags($post['excerpt']['rendered'] ?? ''),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

            $mediaData = self::resolveWordpressFeaturedMedia($post, $apiBaseUrl);
            $featuredImageUrl = $mediaData['url'] ?? '';
            $width = isset($mediaData['width']) ? (int) $mediaData['width'] : null;
            $height = isset($mediaData['height']) ? (int) $mediaData['height'] : null;
            $featuredImage = '';
            $featuredWidth = $width;
            $featuredHeight = $height;

            if ($featuredImageUrl) {
                $importedImage = self::importWordpressFeaturedImage($featuredImageUrl, $postId, $title, $width, $height);
                if ($importedImage !== null) {
                    $featuredImage = $importedImage['url'];
                    $featuredWidth = $importedImage['width'];
                    $featuredHeight = $importedImage['height'];
                }
            }

            $preparedPosts[] = [
                'id' => $postId,
                'title' => $title,
                'link' => $link,
                'excerpt' => $excerpt,
                'featured_image' => $featuredImage,
                'featured_image_width' => $featuredWidth ?? 0,
                'featured_image_height' => $featuredHeight ?? 0,
            ];
        }

        $tmpFile = tempnam($generatedDir, 'wp_posts_');
        if ($tmpFile === false) {
            return false;
        }

        $finalFile = $tmpFile . '.json';
        if (!@rename($tmpFile, $finalFile)) {
            @unlink($tmpFile);
            return false;
        }

        if (file_put_contents($finalFile, json_encode($preparedPosts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) === false) {
            @unlink($finalFile);
            return false;
        }

        $previousFile = Configuration::get('EVERWP_POSTS_DATA_FILE');
        if ($previousFile) {
            $previousPath = $generatedDir . $previousFile;
            if (is_file($previousPath) && $previousPath !== $finalFile) {
                @unlink($previousPath);
            }
        }

        Configuration::updateValue('EVERWP_POSTS_DATA_FILE', basename($finalFile));

        $legacyFile = Configuration::get('EVERWP_POSTS_TEMPLATE_FILE');
        if ($legacyFile) {
            $legacyPath = $generatedDir . $legacyFile;
            if (is_file($legacyPath)) {
                @unlink($legacyPath);
            }
            Configuration::deleteByName('EVERWP_POSTS_TEMPLATE_FILE');
        }
        return true;
    }

    public static function isBot()
    {
        $userAgent = '';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        if ($userAgent === '') {
            return false;
        }
        $botUserAgents = array(
            'Googlebot',
            'Bingbot',
            'Slackbot',
            'Twitterbot',
            'Facebookbot',
            'Pinterestbot',
            'LinkedInBot',
            'WhatsApp',
            'SkypeUriPreview',
            'TelegramBot',
            'Discordbot',
            'Slurp',
            'DuckDuckGo',
            'YandexBot',
            'Baiduspider',
            'Sogou',
            'Exabot',
            'AhrefsBot',
            'SemrushBot',
            'MJ12bot',
            'DotBot',
            'BLEXBot',
            'Rambler',
            'ia_archiver',
            'archive.org_bot',
            'BazQuxBot',
            'UptimeRobot',
            'Pingdom',
            'TurnitinBot',
            'Screaming Frog SEO Spider',
            'Mediapartners-Google',
            'AdsBot-Google',
            'MJ12bot',
            'rogerbot',
            'Ezooms',
            'ICC-Crawler',
            'Y!J-BRW',
            'UnwindFetchor',
            'blekkobot',
            'ia_archiver',
            'semanticbot',
            'PaperLiBot',
            'GTmetrix',
            // Ajoutez d'autres bots si nécessaire
        );

        foreach ($botUserAgents as $botAgent) {
            if (stripos($userAgent, $botAgent) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function convertImagesToWebP($htmlContent)
    {
        if ((bool) Configuration::get('EVERBLOCK_DISABLE_WEBP') === true) {
            return $htmlContent;
        }
        // Regular expression to find img tags and their src attributes
        $pattern = '/<img\s+([^>]*src="([^"]+)"[^>]*)>/i';
        $shopName = Configuration::get('PS_SHOP_NAME');

        $htmlContent = preg_replace_callback($pattern, function ($matches) use ($shopName) {
            $imgTag = $matches[0];
            $imgAttributes = $matches[1];
            $src = $matches[2];

            // Convert the image to WebP
            $webpSrc = self::convertToWebP($src);
            
            if ($webpSrc) {
                // Replace the src with the new WebP src
                $imgTag = str_replace($src, $webpSrc, $imgTag);

                // Get the image dimensions, only if the image exists
                $imagePath = self::urlToFilePath($webpSrc);
                if (file_exists($imagePath)) {
                    list($width, $height) = getimagesize($imagePath);

                    // Add width and height attributes if they don't already exist
                    if (strpos($imgAttributes, 'width=') === false && strpos($imgAttributes, 'height=') === false) {
                        $imgTag = str_replace('<img ', '<img width="' . $width . '" height="' . $height . '" ', $imgTag);
                    } else {
                        $imgTag = preg_replace('/width="\d*"/i', 'width="' . $width . '"', $imgTag);
                        $imgTag = preg_replace('/height="\d*"/i', 'height="' . $height . '"', $imgTag);
                    }
                }

                // Add alt attribute if it doesn't exist
                if (strpos($imgAttributes, 'alt=') === false) {
                    // Ajoute l'attribut alt juste après <img
                    $imgTag = preg_replace('/<img\s+/i', '<img alt="' . htmlspecialchars($shopName, ENT_QUOTES) . '" ', $imgTag);
                }
            }

            return $imgTag;
        }, $htmlContent);

        return $htmlContent;
    }

    public static function convertAllPrettyblocksImagesToWebP(): int
    {
        $db = Db::getInstance();
        $results = $db->executeS('SELECT id_prettyblocks, state FROM ' . _DB_PREFIX_ . 'prettyblocks WHERE state IS NOT NULL');

        $updatedCount = 0;

        foreach ($results as $row) {
            $id = (int) $row['id_prettyblocks'];
            $state = json_decode($row['state'], true);

            if (!is_array($state)) {
                continue;
            }

            $converted = self::convertRepeaterImagesToWebP($state);

            // Si des modifications ont été faites, on met à jour
            if (json_encode($state) !== json_encode($converted)) {
                $db->update(
                    'prettyblocks',
                    ['state' => pSQL(json_encode($converted))],
                    'id_prettyblocks = ' . $id
                );
                $updatedCount++;
            }
        }

        return $updatedCount; // nombre de blocs modifiés
    }

    public static function convertRepeaterImagesToWebP(array $repeaterData): array
    {
        foreach ($repeaterData as &$group) {
            foreach (['image', 'background_image'] as $imageKey) {
                if (
                    isset($group[$imageKey]['value']['url']) &&
                    is_string($group[$imageKey]['value']['url']) &&
                    !empty($group[$imageKey]['value']['url'])
                ) {
                    $originalUrl = $group[$imageKey]['value']['url'];
                    $webpUrl = self::convertToWebP($originalUrl);

                    if ($webpUrl) {
                        $group[$imageKey]['value']['url'] = $webpUrl;
                        $group[$imageKey]['value']['extension'] = 'webp';
                        $group[$imageKey]['value']['filename'] = pathinfo($webpUrl, PATHINFO_BASENAME);

                        // Mettre à jour les dimensions si pertinents
                        $webpPath = self::urlToFilePath($webpUrl);
                        if (file_exists($webpPath)) {
                            $dimensions = getimagesize($webpPath);
                            if ($dimensions) {
                                list($width, $height) = $dimensions;

                                $widthKey = $imageKey . '_width';
                                $heightKey = $imageKey . '_height';

                                if (isset($group[$widthKey])) {
                                    $group[$widthKey]['value'] = $width . 'px';
                                }
                                if (isset($group[$heightKey])) {
                                    $group[$heightKey]['value'] = $height . 'px';
                                }
                            }
                        }
                    }
                }
            }
        }

        return $repeaterData;
    }

    public static function moveAllPrettyblocksMediasToCms(): int
    {
        $db = Db::getInstance();
        $destinationDir = _PS_IMG_DIR_ . 'cms/prettyblocks/';
        if (!is_dir($destinationDir)) {
            @mkdir($destinationDir, 0755, true);
        }
        $results = $db->executeS('SELECT id_prettyblocks, state FROM ' . _DB_PREFIX_ . 'prettyblocks WHERE state IS NOT NULL');

        $updatedCount = 0;

        foreach ($results as $row) {
            $id = (int) $row['id_prettyblocks'];
            $state = json_decode($row['state'], true);

            if (!is_array($state)) {
                continue;
            }

            $migrated = self::moveMediasRecursive($state, $destinationDir);

            if (json_encode($state) !== json_encode($migrated)) {
                $db->update(
                    'prettyblocks',
                    ['state' => pSQL(json_encode($migrated))],
                    'id_prettyblocks = ' . $id
                );
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    private static function moveMediasRecursive(array $data, string $destinationDir): array
    {
        foreach ($data as &$item) {
            if (is_array($item)) {
                if (($item['type'] ?? null) === 'fileupload' && isset($item['value']['url'])) {
                    $item = self::moveSingleMediaField($item, $destinationDir);
                } else {
                    $item = self::moveMediasRecursive($item, $destinationDir);
                }
            }
        }

        return $data;
    }

    private static function moveSingleMediaField(array $field, string $destinationDir): array
    {
        $url = $field['value']['url'] ?? '';
        if ($url) {
            $sourcePath = self::urlToFilePath($url);
            $filename = $field['value']['filename'] ?? basename($sourcePath);
            $extension = strtolower($field['value']['extension'] ?? pathinfo($filename, PATHINFO_EXTENSION));
            $mimeType = strtolower($field['value']['mime'] ?? '');
            $isSvg = in_array($extension, ['svg', 'svg+xml', 'svgz'], true) || strpos($mimeType, 'image/svg') === 0;
            if (file_exists($sourcePath)) {
                if (!is_dir($destinationDir)) {
                    @mkdir($destinationDir, 0755, true);
                }
                $destinationPath = $destinationDir . $filename;
                if ($sourcePath !== $destinationPath) {
                    @rename($sourcePath, $destinationPath);
                }
                $publicUrl = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/cms/prettyblocks/' . $filename;
                $field['value']['url'] = $publicUrl;
                $field['value']['filename'] = $filename;

                if ($isSvg) {
                    $field['value']['extension'] = 'svg';
                } else {
                    $webpUrl = self::convertToWebP($publicUrl);
                    if ($webpUrl) {
                        $field['value']['url'] = $webpUrl;
                        $webpPath = parse_url($webpUrl, PHP_URL_PATH);
                        $field['value']['filename'] = $webpPath ? basename($webpPath) : basename($webpUrl);
                        $field['value']['extension'] = 'webp';
                    } elseif (!isset($field['value']['extension'])) {
                        $field['value']['extension'] = $extension ?: pathinfo($filename, PATHINFO_EXTENSION);
                    }
                }
            }
        }
        $field['path'] = '$/img/cms/prettyblocks/';

        return $field;
    }

    private static function getWordpressApiBase(string $apiUrl): string
    {
        $apiUrl = trim($apiUrl);
        if ($apiUrl === '') {
            return '';
        }

        $requestUrl = rtrim($apiUrl, '/');
        $mediaBase = preg_replace('#/posts(?:/|$)#', '', $requestUrl);
        if (is_string($mediaBase) && $mediaBase !== $requestUrl) {
            return $mediaBase;
        }

        $parsed = parse_url($requestUrl);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            return $requestUrl;
        }

        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = $parsed['path'] ?? '';
        $basePath = rtrim(dirname($path), '/\\');
        if ($basePath === '.' || $basePath === '/') {
            $basePath = ''; // dirname('/') renvoie '/'
        }

        return $parsed['scheme'] . '://' . $parsed['host'] . $port . $basePath;
    }

    private static function resolveWordpressFeaturedMedia(array $post, string $apiBaseUrl): array
    {
        $mediaId = isset($post['featured_media']) ? (int) $post['featured_media'] : 0;
        if ($mediaId > 0) {
            $media = self::fetchWordpressMedia($apiBaseUrl, $mediaId);
            if (is_array($media) && isset($media['source_url'])) {
                return [
                    'url' => $media['source_url'],
                    'width' => $media['media_details']['width'] ?? null,
                    'height' => $media['media_details']['height'] ?? null,
                ];
            }
        }

        if (isset($post['_embedded']['wp:featuredmedia'][0]['source_url'])) {
            $embedded = $post['_embedded']['wp:featuredmedia'][0];
            return [
                'url' => $embedded['source_url'],
                'width' => $embedded['media_details']['width'] ?? null,
                'height' => $embedded['media_details']['height'] ?? null,
            ];
        }

        if (isset($post['yoast_head_json']['og_image'][0]['url'])) {
            $ogImage = $post['yoast_head_json']['og_image'][0];
            return [
                'url' => $ogImage['url'],
                'width' => $ogImage['width'] ?? null,
                'height' => $ogImage['height'] ?? null,
            ];
        }

        return [];
    }

    private static function fetchWordpressMedia(string $apiBaseUrl, int $mediaId): ?array
    {
        if ($mediaId <= 0 || $apiBaseUrl === '') {
            return null;
        }

        $endpoint = rtrim($apiBaseUrl, '/') . '/media/' . $mediaId;
        $response = Tools::file_get_contents($endpoint);
        if ($response === false) {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private static function importWordpressFeaturedImage(string $imageUrl, int $postId, string $title, ?int $width, ?int $height): ?array
    {
        $imageUrl = trim($imageUrl);
        if ($imageUrl === '') {
            return null;
        }

        if (!self::ensureCmsDirectoryExists()) {
            return null;
        }

        $parsed = parse_url($imageUrl);
        $path = $parsed['path'] ?? '';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = 'jpg';
        }

        $filenameBase = pathinfo($path, PATHINFO_FILENAME);
        if (!$filenameBase) {
            $filenameBase = method_exists('Tools', 'str2url')
                ? Tools::str2url($title)
                : Tools::link_rewrite($title);
            $filenameBase = $filenameBase ?: 'image';
        }
        $filenameBase = self::sanitizeFileName($filenameBase);
        if ($filenameBase === '') {
            $filenameBase = 'image';
        }

        $postIdentifier = $postId > 0 ? $postId : time();
        $fileName = sprintf('blog-%d-%s.%s', $postIdentifier, $filenameBase, $extension);
        $destination = _PS_IMG_DIR_ . 'cms/' . $fileName;

        try {
            $downloadSuccess = Tools::copy($imageUrl, $destination);
        } catch (Exception $e) {
            $downloadSuccess = false;
        }

        if (!$downloadSuccess) {
            return null;
        }

        $dimensions = @getimagesize($destination);
        if (is_array($dimensions)) {
            $width = (int) $dimensions[0];
            $height = (int) $dimensions[1];
        }

        $width = $width ?: 600;
        $height = $height ?: 338;

        return [
            'path' => $destination,
            'url' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/cms/' . $fileName,
            'width' => $width,
            'height' => $height,
            'filename' => $fileName,
        ];
    }

    private static function ensureCmsDirectoryExists(): bool
    {
        $directory = _PS_IMG_DIR_ . 'cms/';
        if (is_dir($directory)) {
            return true;
        }

        return @mkdir($directory, 0755, true) || is_dir($directory);
    }

    private static function sanitizeFileName(string $fileName): string
    {
        $normalized = method_exists('Tools', 'str2url')
            ? Tools::str2url($fileName)
            : Tools::link_rewrite($fileName);
        $normalized = preg_replace('/[^a-z0-9\-]+/i', '-', (string) $normalized);
        $normalized = preg_replace('/-+/', '-', (string) $normalized);

        return trim((string) $normalized, '-');
    }

    public static function convertToWebP($imagePath)
    {
        // Si déjà en webp, on ne fait rien
        if (strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) === 'webp') {
            return $imagePath;
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $imagePath = self::urlToFilePath($imagePath);
        } else {
            $imagePath = self::relativeToAbsolutePath($imagePath);
        }

        $imagePath = str_replace(Tools::getHttpHost(true) . __PS_BASE_URI__, '', $imagePath);
        if (!file_exists($imagePath)) {
            return false;
        }

        $pathInfo = pathinfo($imagePath);
        $hash = substr(sha1($imagePath . filemtime($imagePath)), 0, 12); // hash court et unique par contenu
        $webpFilename = $hash . '.webp';
        $webpPath = $pathInfo['dirname'] . '/' . $webpFilename;

        if (file_exists($webpPath)) {
            return self::filePathToUrl($webpPath);
        }

        switch (strtolower($pathInfo['extension'])) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        imagepalettetotruecolor($image);

        if (imagewebp($image, $webpPath, 80)) {
            imagedestroy($image);
            return self::filePathToUrl($webpPath);
        }

        imagedestroy($image);
        return false;
    }

    public static function urlToFilePath($url)
    {
        // Parse the current domain and the image URL
        $parsedUrl = parse_url($url);
        $currentHost = parse_url(Tools::getHttpHost(true), PHP_URL_HOST);
        if (!$currentHost) {
            $currentHost = Tools::getHttpHost(false);
        }

        // Check if the image is hosted on a different domain
        if (isset($parsedUrl['host']) && $currentHost && $parsedUrl['host'] !== $currentHost) {
            // Download the image and return the local file path
            return self::downloadImage($url);
        } else {
            // Convert the URL path to a relative file path
            $relativePath = urldecode($parsedUrl['path']);
            return _PS_ROOT_DIR_ . $relativePath;
        }
    }

    private static function downloadImage($url)
    {
        try {
            $url = str_replace(' ', '%20', $url);
            // Parse the URL to get the filename
            $parsedUrl = parse_url($url);
            $fileName = basename($parsedUrl['path']);

            // Define the local path where the image will be saved
            $localPath = _PS_ROOT_DIR_ . '/img/cms/' . $fileName;

            // Download the image
            $imageContents = Tools::file_get_contents($url);
            if ($imageContents === false) {
                return false; // Return false if the download failed
            }

            // Save the image to the local path
            file_put_contents($localPath, $imageContents);

            // Return the local path
            return $localPath;
        } catch (Exception $e) {
            return false;
        }
    }

    private static function encodeUrl($url)
    {
        $parsedUrl = parse_url($url);
        if (!$parsedUrl) {
            return $url;
        }

        // Encode the path to handle special characters
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $parsedUrl['path'])));

        // Rebuild the URL with the encoded path
        $encodedUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $encodedPath;

        if (isset($parsedUrl['query'])) {
            $encodedUrl .= '?' . $parsedUrl['query'];
        }

        return $encodedUrl;
    }

    private static function filePathToUrl($filePath)
    {
        $relativePath = str_replace(_PS_ROOT_DIR_, '', $filePath);
        return Tools::getHttpHost(true) . __PS_BASE_URI__ . ltrim($relativePath, '/');
    }

    private static function relativeToAbsolutePath($relativePath)
    {
        return _PS_ROOT_DIR_ . '/' . ltrim($relativePath, '/');
    }

    private static function replaceUrlsInJsonString($jsonString, $oldUrl, $newUrl)
    {
        if (empty($jsonString)) {
            return $jsonString;
        }

        $data = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $jsonString;
        }

        $data = static::replaceUrlsRecursively($data, $oldUrl, $newUrl);

        return json_encode($data);
    }

    private static function replaceUrlsRecursively($data, $oldUrl, $newUrl)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::replaceUrlsRecursively($value, $oldUrl, $newUrl);
            } elseif (is_string($value)) {
                $data[$key] = str_replace($oldUrl, $newUrl, $value);
            }
        }

        return $data;
    }

    public static function getTemplatePath(string $relativePath, Module $module): string
    {
        // Normalise le chemin pour éviter les erreurs de slash
        $relativePath = ltrim($relativePath, '/');
        return 'module:' . $module->name . '/views/templates/' . $relativePath;
    }

    public static function getAvailableSvgIcons(): array
    {
        $iconsDir = _PS_MODULE_DIR_ . 'everblock/views/img/svg/';
        $icons = [];

        if (is_dir($iconsDir)) {
            foreach (scandir($iconsDir) as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
                    $icons[$file] = pathinfo($file, PATHINFO_FILENAME); // label = filename
                }
            }
        }

        return $icons;
    }

}

class_alias(__NAMESPACE__ . '\\EverblockTools', 'EverblockTools');
