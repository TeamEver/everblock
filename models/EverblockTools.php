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
if (!defined('_PS_VERSION_')) {
    exit;
}
use \PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;

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
        if (strpos($txt, '[everfaq') !== false) {
            $txt = static::getFaqShortcodes($txt, $context, $module);
        }
        if (strpos($txt, '[everinstagram]') !== false) {
            $txt = static::getInstagramShortcodes($txt, $context, $module);
        }
        if (strpos($txt, '[product') !== false) {
            $txt = static::getProductShortcodes($txt, $context, $module);
        }
        if (strpos($txt, '[productfeature') !== false) {
            $txt = static::getFeatureProductShortcodes($txt, $context, $module);
        }
        if (strpos($txt, '[productfeaturevalue') !== false) {
            $txt = static::getFeatureValueProductShortcodes($txt, $context, $module);
        }
        if (strpos($txt, '[category') !== false) {
            $txt = static::getCategoryShortcodes($txt, $context, $module);
        }
        if (strpos($txt, '[manufacturer') !== false) {
            $txt = static::getManufacturerShortcodes($txt, $context, $module);
        }
        if (strpos($txt, '[brands') !== false) {
            $txt = static::getBrandsShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[storelocator]') !== false) {
            $txt = static::generateGoogleMap($txt, $context, $module);
        }
        if (strpos($txt, '{hook h=') !== false) {
            $txt = static::replaceHook($txt);
        }
        if (strpos($txt, '[llorem]') !== false) {
            $txt = static::generateLoremIpsum($txt, $context);
        }
        if (strpos($txt, '[everblock') !== false) {
            $txt = static::getEverBlockShortcode($txt, $context);
        }
        if (strpos($txt, '[subcategories') !== false) {
            $txt = static::getSubcategoriesShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[everstore') !== false) {
            $txt = static::getStoreShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[video') !== false) {
            $txt = static::getVideoShortcode($txt);
        }
        if (strpos($txt, '[qcdacf') !== false) {
            $txt = static::getQcdAcfCode($txt, $context);
        }
        if (strpos($txt, '[displayQcdSvg') !== false) {
            $txt = static::getQcdSvgCode($txt, $context);
        }
        if (strpos($txt, '[everimg') !== false) {
            $txt = static::getEverImgShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[wordpress-posts]') !== false) {
            $txt = static::getWordpressPostsShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[best-sales') !== false) {
            $txt = static::getBestSalesShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[categorybestsales') !== false) {
            $txt = static::getCategoryBestSalesShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[brandbestsales') !== false) {
            $txt = static::getBrandBestSalesShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[featurebestsales') !== false) {
            $txt = static::getFeatureBestSalesShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[featurevaluebestsales') !== false) {
            $txt = static::getFeatureValueBestSalesShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[last-products') !== false) {
            $txt = static::getLastProductsShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[promo-products') !== false) {
            $txt = static::getPromoProductsShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[evercart]') !== false) {
            $txt = static::getCartShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[cart_total]') !== false) {
            $txt = static::getCartTotalShortcode($txt, $context);
        }
        if (strpos($txt, '[cart_quantity]') !== false) {
            $txt = static::getCartQuantityShortcode($txt, $context);
        }
        if (strpos($txt, '[newsletter_form]') !== false) {
            $txt = static::getNewsletterFormShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[nativecontact]') !== false) {
            $txt = static::getNativeContactShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[evercontactform_open]') !== false) {
            $txt = static::getFormShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[everorderform_open]') !== false) {
            $txt = static::getOrderFormShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[random_product') !== false) {
            $txt = static::getRandomProductsShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[accessories') !== false) {
            $txt = static::getAccessoriesShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[linkedproducts') !== false) {
            $txt = static::getLinkedProductsShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[crosselling') !== false) {
            $txt = static::getCrossSellingShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[widget') !== false) {
            $txt = $txt = static::getWidgetShortcode($txt);
        }
        if (strpos($txt, '[prettyblocks') !== false) {
            $txt = static::getPrettyblocksShortcodes($txt, $context, $module);
        }
        if (strpos($txt, '[everaddtocart') !== false) {
            $txt = static::getAddToCartShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[cms') !== false) {
            $txt = static::getCmsShortcode($txt, $context);
        }
        if (in_array($context->controller->controller_type, $controllerTypes)) {
            $txt = static::getCustomerShortcodes($txt, $context);
            $txt = static::obfuscateTextByClass($txt);
        }
        $txt = static::renderSmartyVars($txt, $context);
        Hook::exec('displayAfterRenderingShortcodes', ['html' => &$txt]);
        return $txt;
    }

    public static function getCrossSellingShortcode(string $txt, Context $context, Everblock $module): string
    {

        preg_match_all(
            '/\[crosselling(?:\s+nb=(\d+))?(?:\s+limit=(\d+))?(?:\s+orderby=(\w+))?(?:\s+orderway=(ASC|DESC))?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $limit = isset($match[1]) && $match[1] !== '' ? (int) $match[1] : (isset($match[2]) ? (int) $match[2] : 4);
            $orderBy = isset($match[3]) ? strtolower($match[3]) : 'id_product';
            $orderWay = isset($match[4]) ? strtoupper($match[4]) : 'ASC';

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
                $cartIds = array_map(fn($p) => (int) $p['id_product'], $context->cart->getProducts());
            }

            if (empty($cartIds)) {
                $bestIds = static::getBestSellingProductIds($limit, $orderBy, $orderWay);
                $everPresentProducts = static::everPresentProducts($bestIds, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => false,
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
                if (!in_array($id, $cartIds) && !in_array($id, $ids)) {
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
                        if (!in_array($pid, $cartIds) && !in_array($pid, $ids)) {
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
                    if (!in_array($bid, $cartIds) && !in_array($bid, $ids)) {
                        $ids[] = $bid;
                    }
                }
            }

            if (empty($ids)) {
                $bestIds = static::getBestSellingProductIds($limit, $orderBy, $orderWay);
                $everPresentProducts = static::everPresentProducts($bestIds, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => false,
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

            $everPresentProducts = static::everPresentProducts($ids, $context);

            if (!empty($everPresentProducts)) {
                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => false,
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

        foreach ($matches as $match) {
            $renderedContent = $context->smarty->fetch($templatePath);
            $txt = str_replace($match[0], $renderedContent, $txt);
        }

        return $txt;
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

    protected static function getProductsByCategoryId(int $categoryId, int $limit, string $orderBy = 'id_product', string $orderWay = 'ASC'): array
    {
        $cacheId = 'everblock_getProductsByCategoryId_' . $categoryId . '_' . $limit . '_' . $orderBy . '_' . $orderWay;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $category = new Category($categoryId);
            $return = [];

            if (Validate::isLoadedObject($category)) {
                $products = $category->getProducts(
                    Context::getContext()->language->id,
                    1,
                    $limit,
                    $orderBy,
                    $orderWay
                );
                $return = $products;
            }

            EverblockCache::cacheStore($cacheId, $return);
            return $return;
        }

        return EverblockCache::cacheRetrieve($cacheId);
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
            $ids = array_map(fn($row) => (int) $row['product_id'], $rows);
            EverblockCache::cacheStore($cacheId, $ids);
            return $ids;
        }

        return EverblockCache::cacheRetrieve($cacheId);
    }

    protected static function getBestSellingProductIdsByCategory(int $categoryId, int $limit, string $orderBy = 'total_quantity', string $orderWay = 'DESC', ?int $days = null): array
    {
        $context = Context::getContext();
        $cacheId = 'everblock_bestSellingProductIds_category_'
            . (int) $context->shop->id . '_'
            . $categoryId . '_'
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
                . ' JOIN ' . _DB_PREFIX_ . 'category_product cp ON od.product_id = cp.id_product'
                . ' WHERE ps.active = 1'
                . ' AND ps.id_shop = ' . $shopId
                . ' AND o.id_shop = ' . $shopId
                . ' AND cp.id_category = ' . (int) $categoryId;

            if ($days !== null) {
                $dateFrom = date('Y-m-d H:i:s', strtotime("-$days days"));
                $sql .= ' AND o.date_add >= "' . pSQL($dateFrom) . '"';
            }

            $sql .= ' GROUP BY od.product_id'
                . ' ORDER BY ' . pSQL($orderBy) . ' ' . pSQL($orderWay)
                . ' LIMIT ' . (int) $limit;

            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $ids = array_map(fn($row) => (int) $row['product_id'], $rows);
            EverblockCache::cacheStore($cacheId, $ids);
            return $ids;
        }

        return EverblockCache::cacheRetrieve($cacheId);
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
            $ids = array_map(fn($row) => (int) $row['product_id'], $rows);
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
            $ids = array_map(fn($row) => (int) $row['product_id'], $rows);
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
            $ids = array_map(fn($row) => (int) $row['product_id'], $rows);
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
            // Définir le chemin vers le template
            $templatePath = static::getTemplatePath('hook/prettyblocks.tpl', $module);
            // Regex pour trouver les shortcodes de type [prettyblocks name="mon_nom"]
            $pattern = '/\[prettyblocks name="([^"]+)"\]/';
            
            // Fonction de remplacement pour traiter chaque shortcode trouvé
            $replacementFunction = function($matches) use ($context, $templatePath) {
                // Extraire le nom de la zone depuis le shortcode
                $zoneName = $matches[1];
                // Assigner le nom de la zone à Smarty
                $context->smarty->assign('zone_name', $zoneName);
                
                // Récupérer le rendu du template avec Smarty
                return $context->smarty->fetch($templatePath);
            };
            
            // Remplacer tous les shortcodes trouvés par le rendu Smarty correspondant
            $txt = preg_replace_callback($pattern, $replacementFunction, $txt);
        }
        
        return $txt;
    }

    public static function generateFormFromShortcode(
        string $shortcode,
        Context $context,
        Everblock $module
    )
    {
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
        $field = [
            'type' => htmlspecialchars($attributes['type'], ENT_QUOTES),
            'label' => htmlspecialchars($attributes['label'], ENT_QUOTES),
            'value' => $attributes['value'] ?? null,
            'values' => isset($attributes['values']) ? explode(',', $attributes['values']) : [],
            'required' => isset($attributes['required']) && strtolower($attributes['required']) === 'true',
            'unique' => $uid,
            'id' => 'everfield_' . $uid,
        ];

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

    public static function getNewsletterFormShortcode(string $txt, Context $context, Everblock $module): string
    {
        if (Module::isInstalled('ps_emailsubscription') && Module::isEnabled('ps_emailsubscription')) {
            $newsletter = Module::getInstanceByName('ps_emailsubscription');
            if (method_exists($newsletter, 'renderWidget')) {
                $replacement = $newsletter->renderWidget('displayFooter', []);
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
                $productIdsArray = array_map(function($row) {
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
                $productIdsArray = array_map(function($row) {
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
                $productIdsArray = array_map(function($row) {
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

            $cacheId = 'getBestSalesShortcode_' . (int)$context->shop->id . "_$limit" . "_" . ($days ?? 'all') . "_$orderBy_$orderWay";

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
                $productIdsArray = array_map(fn($row) => (int)$row['product_id'], $productIds);
                $everPresentProducts = static::everPresentProducts($productIdsArray, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel' => $carousel,
                        'shortcodeClass' => 'best-sales'
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    // Recompose shortcode (avec tous les paramètres capturés)
                    $shortcodeParts = ['[best-sales'];
                    if (isset($match[1]) && $match[1] !== '') { $shortcodeParts[] = 'nb=' . $match[1]; }
                    elseif (isset($match[2])) { $shortcodeParts[] = 'limit=' . $match[2]; }
                    if (isset($match[3])) $shortcodeParts[] = 'days=' . $match[3];
                    if (isset($match[4])) $shortcodeParts[] = 'carousel=' . $match[4];
                    if (isset($match[5])) $shortcodeParts[] = 'orderby=' . $match[5];
                    if (isset($match[6])) $shortcodeParts[] = 'orderway=' . $match[6];
                    $shortcode = implode(' ', $shortcodeParts) . ']';

                    $txt = str_replace($shortcode, $replacement, $txt);
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
                $productIdsArray = array_map(fn($row) => (int) $row['id_product'], $productIds);
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
                $productIdsArray = array_map(fn($row) => (int) $row['id_product'], $productIds);
                $everPresentProducts = static::everPresentProducts($productIdsArray, $context);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign([
                        'everPresentProducts' => $everPresentProducts,
                        'carousel_id' => 'accessoriesCarousel-' . uniqid(),
                    ]);

                    $templatePath = static::getTemplatePath('hook/linkedproducts_carousel.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcodeParts = ['[accessories'];
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
        preg_match_all('/\[everimg\s+name="([^"]+)"(?:\s+class="([^"]*)")?(?:\s+carousel=(true|false))?\]/', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $filenames = array_map('trim', explode(',', $match[1]));
            $class = isset($match[2]) ? trim($match[2]) : 'img-fluid';
            $carousel = isset($match[3]) && $match[3] === 'true';

            $images = [];
            foreach ($filenames as $filename) {
                $safeFilename = basename($filename);
                $filepath = _PS_IMG_DIR_ . 'cms/' . $safeFilename;
                $webPath = _PS_BASE_URL_ . __PS_BASE_URI__ . 'img/cms/' . $safeFilename;

                if (!file_exists($filepath)) {
                    continue;
                }

                [$width, $height] = getimagesize($filepath);
                $alt = htmlspecialchars(pathinfo($safeFilename, PATHINFO_FILENAME), ENT_QUOTES);
                $classAttr = htmlspecialchars($class, ENT_QUOTES);

                $images[] = [
                    'src' => htmlspecialchars($webPath, ENT_QUOTES),
                    'width' => $width,
                    'height' => $height,
                    'alt' => $alt,
                    'class' => $classAttr,
                ];
            }

            $replacement = '';
            if (!empty($images)) {
                if ($carousel && count($images) > 1) {
                    $context->smarty->assign([
                        'images' => $images,
                        'carousel_id' => 'everImgCarousel-' . uniqid(),
                    ]);
                    $templatePath = static::getTemplatePath('hook/ever_img_carousel.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);
                } else {
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
                    $replacement = count($images) > 1 ? '<div class="row">' . implode('', $html) . '</div>' : $html[0];
                }
            }

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
                $txt = str_replace($elementSearch, $value, $txt);
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
        $cacheId = 'store_locator_data_' . $id_shop;

        if (!EverblockCache::isCacheStored($cacheId)) {
            $stores = Store::getStores($id_lang);
            $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

            $now = new \DateTime('now', new \DateTimeZone(Configuration::get('PS_TIMEZONE')));
            $todayIndex = (int) $now->format('w'); // 0 = dimanche
            $currentTime = $now->format('H:i');
            $todayDate = $now->format('Y-m-d');
            $frenchHolidays = self::getFrenchHolidays((int) $now->format('Y'));
            $holidayHours = self::getHolidayHoursConfig();
            $todayHolidaySlot = $holidayHours[$todayDate] ?? null;
            $isHoliday = in_array($todayDate, $frenchHolidays);

            foreach ($stores as &$store) {
                $id_store = (int) $store['id_store'];
                $cms_id = (int) Configuration::get('QCD_ASSOCIATED_CMS_PAGE_ID_STORE_' . $id_store, null, null, $id_shop);
                $cms_link = null;
                $storeHolidayHours = self::getStoreHolidayHoursConfig($id_store);
                $todayStoreHolidaySlot = $storeHolidayHours[$todayDate] ?? $todayHolidaySlot;

                if ($cms_id > 0) {
                    $cms = new CMS($cms_id, $id_lang, $id_shop);
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
                        $subSlots = explode(' / ', $slot);
                        foreach ($subSlots as $subSlot) {
                            $hoursFormatted[] = trim($subSlot);

                            if (($i === $todayIndex) && (!$isHoliday || $todayStoreHolidaySlot) && strpos($subSlot, '-') !== false) {
                                [$startRaw, $endRaw] = explode(' - ', $subSlot);
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

            EverblockCache::cacheStore($cacheId, $stores);
        }

        return EverblockCache::cacheRetrieve($cacheId);
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


    protected static function getFrenchHolidays($year)
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

    protected static function getHolidayHoursConfig(): array
    {
        $config = Configuration::get('EVERBLOCK_HOLIDAY_HOURS');
        $result = [];
        if ($config) {
            $lines = preg_split('/[\r\n]+/', $config);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!$line) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    [$date, $hours] = array_map('trim', explode('=', $line, 2));
                    if (Validate::isDate($date) && $hours !== '') {
                        $result[$date] = $hours;
                    }
                }
            }
        }
        return $result;
    }

    protected static function getStoreHolidayHoursConfig(int $storeId): array
    {
        $result = [];
        $holidays = self::getFrenchHolidays((int) date('Y'));
        foreach ($holidays as $date) {
            $openKey = 'EVERBLOCK_OPEN_' . (int) $storeId . '_' . $date;
            $closeKey = 'EVERBLOCK_CLOSE_' . (int) $storeId . '_' . $date;
            $open = Configuration::get($openKey);
            $close = Configuration::get($closeKey);
            if ($open && $close) {
                $result[$date] = trim($open) . ' - ' . trim($close);
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
        $stores = static::getStoreLocatorData();
        if (!empty($stores)) {
            $smarty = $context->smarty;
            $templatePath = static::getTemplatePath('hook/storelocator.tpl', $module);
            $smarty->assign([
                'everblock_stores' => $stores,
            ]);
            $storeLocatorContent = $smarty->fetch($templatePath);
            $txt = str_replace('[storelocator]', $storeLocatorContent, $txt);
        }
        return $txt;
    }

    public static function generateOsmScript($markers)
    {
        if (!$markers || !is_array($markers)) {
            return;
        }
        $mapCode = '
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script>
            var mapContainer = document.getElementById("everblock-storelocator");

            // Extraire les coordonnées du premier marqueur
            var firstMarker = ' . json_encode($markers[0]) . ';
            var initialLat = firstMarker.lat;
            var initialLng = firstMarker.lng;

            var map = L.map(mapContainer).setView([initialLat, initialLng], 13);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
            
            var markers = ' . json_encode($markers) . ';
            
            markers.forEach(function(marker) {
                L.marker([marker.lat, marker.lng]).addTo(map)
                    .bindPopup(marker.title);
            });
            
            // Ajustez la hauteur du conteneur de la carte ici
            mapContainer.style.height = "500px"; // Par exemple, réglez la hauteur à 500 pixels
        </script>';
        return $mapCode;
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
            (function() {
                var map;
                var markers = ' . json_encode($markers) . '; // Initialisez la variable markers avec vos données JSON

                // Fonction pour trouver le marqueur le plus proche
                function findClosestMarker(userLocation) {
                    var closestMarker = null;
                    var closestDistance = Number.MAX_VALUE;

                    markers.forEach(function(marker) {
                        var markerLocation = new google.maps.LatLng(marker.lat, marker.lng);
                        var distance = google.maps.geometry.spherical.computeDistanceBetween(userLocation, markerLocation);

                        if (distance < closestDistance) {
                            closestDistance = distance;
                            closestMarker = marker;
                        }
                    });

                    return closestMarker;
                }

                function initMap() {
                    map = new google.maps.Map(document.getElementById("everblock-storelocator"), {
                        center: { lat: ' . $markers[0]['lat'] . ', lng: ' . $markers[0]['lng'] . ' },
                        zoom: 13
                    });

                    markers.forEach(function(marker) {
                        new google.maps.Marker({
                            position: { lat: marker.lat, lng: marker.lng },
                            map: map,
                            title: marker.title
                        });
                    });

                    document.getElementById("everblock-storelocator").style.height = "500px";
                }

                function initAutocomplete() {
                    var autocomplete = new google.maps.places.Autocomplete(document.getElementById("store_search"));

                    autocomplete.addListener("place_changed", function() {
                        var place = autocomplete.getPlace();
                        // Vous pouvez accéder aux informations sur le lieu sélectionné ici
                        console.log(place);

                        if (place.geometry && place.geometry.location) {
                            var userLocation = place.geometry.location;

                            // Maintenant, recherchez le marqueur le plus proche
                            var closestMarker = findClosestMarker(userLocation);

                            if (closestMarker) {
                                // Définir la vue de la carte pour zoomer sur le marqueur le plus proche
                                map.panTo({ lat: closestMarker.lat, lng: closestMarker.lng });
                                map.setZoom(15); // Réglez le niveau de zoom souhaité
                            }
                        }
                    });

                }

                google.maps.event.addDomListener(window, "load", initAutocomplete);
                google.maps.event.addDomListener(window, "load", initMap);
            })();
        ';
        return $googleMapCode;
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
        $pattern = '/<a\s+(.*?)>/i';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $wholeTag = $match[0];
            $attributesPart = $match[1];
            // Vérifie si la classe 'obfme' est présente
            if (preg_match('/\bclass="[^"]*\bobfme\b[^"]*"/', $wholeTag) || preg_match("/\bclass='[^']*\\bobfme\\b[^']*'/", $wholeTag)) {
                // Extraire l'URL
                preg_match('/href="([^"]*)"/i', $wholeTag, $urlMatch);
                $linkUrl = $urlMatch[1];
                $encodedLink = base64_encode($linkUrl);

                $newClassAttribute = preg_replace_callback(
                    '/\bclass=("|\')([^"\']*)("|\')/i',
                    function ($classMatch) {
                        return 'class=' . $classMatch[1] . $classMatch[2] . ' obflink' . $classMatch[3];
                    },
                    $attributesPart
                );
                $newAttributesPart = preg_replace('/href="([^"]*)"/i', 'data-obflink="' . $encodedLink . '"', $newClassAttribute);
                $newTag = '<span ' . $newAttributesPart . '>';
                $text = str_replace($wholeTag, $newTag, $text);
            }
        }
        return $text;
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
            $txt = str_replace($key, $value, $txt);
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
            $txt = str_replace($sc->shortcode, $sc->content, $txt);
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
        $tableNames = [
            _DB_PREFIX_ . 'everblock',
            _DB_PREFIX_ . 'everblock_lang',
            _DB_PREFIX_ . 'everblock_shortcode',
            _DB_PREFIX_ . 'everblock_shortcode_lang',
            _DB_PREFIX_ . 'everblock_faq',
            _DB_PREFIX_ . 'everblock_faq_lang',
            _DB_PREFIX_ . 'everblock_tabs',
            _DB_PREFIX_ . 'everblock_flags',
        ];
        $tableExists = false;
        foreach ($tableNames as $tableName) {
            if (!static::ifTableExists($tableName)) {
                $tableExists = true;
                break; // Pas besoin de vérifier les autres tables
            }
        }

        if ($tableExists) {
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
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }
        // Ajoute les colonnes manquantes à la table ps_everblock_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'content' => 'text DEFAULT NULL',
            'custom_code' => 'text DEFAULT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }
        // Ajoute les colonnes manquantes à la table everblock_shortcode
        $columnsToAdd = [
            'shortcode' => 'text DEFAULT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL DEFAULT 1',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_shortcode` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_shortcode` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }
        // Ajoute les colonnes manquantes à la table everblock_shortcode_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'title' => 'text DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_shortcode_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_shortcode_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }
        // Ajoute les colonnes manquantes à la table everblock_faq
        $columnsToAdd = [
            'tag_name' => 'text DEFAULT NULL',
            'position' => 'int(10) unsigned NOT NULL DEFAULT 0',
            'active' => 'int(10) unsigned NOT NULL',
            'id_shop' => 'int(10) unsigned NOT NULL DEFAULT 1',
            'date_add' => 'DATETIME DEFAULT NULL',
            'date_upd' => 'DATETIME DEFAULT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_faq` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_faq` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }
        // Ajoute les colonnes manquantes à la table everblock_faq_lang
        $columnsToAdd = [
            'id_lang' => 'int(10) unsigned NOT NULL',
            'title' => 'text DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_faq_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_faq_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block database');
                }
            }
        }
        // Ajoute les colonnes manquantes à la table everblock_tabs
        $columnsToAdd = [
            'id_product' => 'int(10) unsigned NOT NULL',
            'id_tab' => 'int(10) unsigned DEFAULT 0',
            'id_shop' => 'int(10) unsigned DEFAULT 1',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_tabs` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_tabs` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block tabs database');
                }
            }
        }
        // Ajoute les colonnes manquantes à la table everblock_tabs_lang
        $columnsToAdd = [
            'id_everblock_tabs' => 'int(10) unsigned NOT NULL',
            'id_lang' => 'int(10) unsigned NOT NULL',
            'title' => 'varchar(255) DEFAULT NULL',
            'content' => 'text DEFAULT NULL',
        ];
        foreach ($columnsToAdd as $columnName => $columnDefinition) {
            $columnExists = $db->ExecuteS('DESCRIBE `' . _DB_PREFIX_ . 'everblock_tabs_lang` `' . pSQL($columnName) . '`');
            if (!$columnExists) {
                try {
                    $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'everblock_tabs_lang` ADD `' . pSQL($columnName) . '` ' . $columnDefinition;
                    $db->execute($query);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Unable to update Ever Block tabs lang database');
                }
            }
        }
        static::cleanObsoleteFiles();
    }

    public static function everPresentProducts(array $result, Context $context): array
    {
        $resultHash = md5(json_encode($result));
        $cacheId = 'everblock_everPresentProducts_'
        . (int) $context->shop->id
        . '_'
        . (int) $context->language->id
        . '_'
        . $resultHash;
        $products = [];
        if (!EverblockCache::isCacheStored($cacheId)) {
            if (!empty($result)) {
                $assembler = new ProductAssembler($context);
                $presenterFactory = new ProductPresenterFactory($context);
                $presentationSettings = $presenterFactory->getPresentationSettings();
                $presenter = new ProductListingPresenter(
                    new ImageRetriever(
                        $context->link
                    ),
                    $context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $context->getTranslator()
                );
                $presentationSettings->showPrices = true;
                foreach ($result as $productId) {
                    $psProduct = new Product(
                        (int) $productId
                    );
                    if (!Validate::isLoadedObject($psProduct)) {
                        continue;
                    }
                    if ((bool) $psProduct->active === false) {
                        continue;
                    }
                    $rawProduct = [
                        'id_product' => $productId,
                        'id_lang' => $context->language->id,
                        'id_shop' => $context->shop->id,
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
            EverblockCache::cacheStore($cacheId, $products);
            return $products;
        }
        return EverblockCache::cacheRetrieve($cacheId);
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
                    $escapedKeys = array_map(function($key) {
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
        return Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'log');;
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
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            $relativePath = str_replace('\\', '/', substr($fileInfo->getPathname(), strlen($moduleDir)));
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

                    $imgs[] = [
                        'id' => isset($post['id']) ? $post['id'] : $post['id'],
                        'permalink' => isset($post['permalink']) ? $post['permalink'] : '',
                        'low_resolution' => $webPath,
                        'thumbnail' => $webPath,
                        'standard_resolution' => $webPath,
                        'caption' => isset($post['caption']) ? $post['caption'] : '',
                        'is_video' => strpos($mediaUrl, '.mp4') !== false,
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
        $requestUrl = rtrim($apiUrl, '/') . '?per_page=' . $limit . '&_embed';
        $user = Configuration::get('EVERWP_API_USER');
        $pwd = Configuration::get('EVERWP_API_PWD');
        $contextOptions = [];
        if ($user && $pwd) {
            $contextOptions['http'] = [
                'header' => 'Authorization: Basic ' . base64_encode($user . ':' . $pwd),
            ];
        }
        $response = Tools::file_get_contents($requestUrl, false, empty($contextOptions) ? null : stream_context_create($contextOptions));
        $posts = json_decode($response, true);
        if (!$posts || !is_array($posts)) {
            return false;
        }
        $filePath = _PS_MODULE_DIR_ . 'everblock/views/templates/hook/generated_wp_posts.tpl';
        $html = '<div class="row row-cols-1 row-cols-md-3 g-4">';
        foreach ($posts as $post) {
            $title = strip_tags($post['title']['rendered'] ?? '');
            $link = $post['link'] ?? '#';
            $excerpt = strip_tags($post['excerpt']['rendered'] ?? '');
            $imgUrl = '';
            if (isset($post['_embedded']['wp:featuredmedia'][0]['source_url'])) {
                $imgUrl = $post['_embedded']['wp:featuredmedia'][0]['source_url'];
            }
            $imgTag = '';
            if ($imgUrl) {
                $localPath = self::downloadImage($imgUrl);
                if ($localPath) {
                    $webpUrl = self::convertToWebP($localPath, 800, 450);
                    $originalUrl = self::filePathToUrl($localPath);
                    $size = file_exists($localPath) ? getimagesize($localPath) : [0,0];
                    $width = $size[0] ?? '';
                    $height = $size[1] ?? '';
                    if ($webpUrl) {
                        $imgTag = '<a href="' . htmlspecialchars($link, ENT_QUOTES) . '" class="obfme" target="_blank" title="' . htmlspecialchars($title, ENT_QUOTES) . '"><picture><source srcset="' . htmlspecialchars($webpUrl, ENT_QUOTES) . '" type="image/webp"><source srcset="' . htmlspecialchars($originalUrl, ENT_QUOTES) . '" type="image/jpeg"><img src="' . htmlspecialchars($originalUrl, ENT_QUOTES) . '" width="' . $width . '" height="' . $height . '" loading="lazy" alt="' . htmlspecialchars($title, ENT_QUOTES) . '" class="card-img-top img-fluid"></picture></a>';
                    }
                }
            }
            $html .= '<div class="col"><div class="card h-100">' . $imgTag . '<div class="card-body"><h5 class="card-title">' . htmlspecialchars($title, ENT_QUOTES) . '</h5><p class="card-text">' . $excerpt . '</p></div></div></div>';
        }
        $html .= '</div>';
        file_put_contents($filePath, $html);
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

        $htmlContent = preg_replace_callback($pattern, function($matches) use ($shopName) {
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

    public static function convertToWebP($imagePath, int $maxWidth = 1920, int $maxHeight = 600)
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

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int) round($originalWidth * $ratio);
            $newHeight = (int) round($originalHeight * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

            imagedestroy($image);
            $image = $resized;
        }

        imagepalettetotruecolor($image);

        if (imagewebp($image, $webpPath, 80)) {
            imagedestroy($image);
            return self::filePathToUrl($webpPath);
        }

        imagedestroy($image);
        return false;
    }

    private static function urlToFilePath($url)
    {
        // Parse the current domain and the image URL
        $parsedUrl = parse_url($url);
        $currentDomain = Tools::getHttpHost(true) . __PS_BASE_URI__;

        // Check if the image is hosted on a different domain
        if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $currentDomain) {
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
            $imageContents = file_get_contents($url);
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

    /**
     * Warmup a given URL in all active languages of the current shop.
     *
     * @param string $baseUrl The base URL without language code (e.g., https://example.com/)
     * @param array $extraQuery Optional query parameters to add (e.g., ['force_warmup' => 1])
     * @return void
     */
    public static function warmup(string $baseUrl, array $extraQuery = []): void
    {
        try {
            $idShop = (int)Context::getContext()->shop->id;
            $languages = Language::getLanguages(true, $idShop);

            foreach ($languages as $lang) {
                $url = rtrim($baseUrl, '/') . '/' . $lang['iso_code'] . '/';

                // Ajouter les paramètres GET additionnels s'il y en a
                if (!empty($extraQuery)) {
                    $url .= '?' . http_build_query($extraQuery);
                }

                $ch = curl_init();

                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_USERAGENT => 'Prestashop-WarmupBot/1.0',
                    CURLOPT_HEADER => false,
                ]);

                curl_exec($ch);

                if (curl_errno($ch)) {
                    PrestaShopLogger::addLog('[Warmup] Curl error for ' . $url . ': ' . curl_error($ch), 2);
                } else {
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($httpCode >= 400) {
                        PrestaShopLogger::addLog('[Warmup] HTTP ' . $httpCode . ' for ' . $url, 2);
                    }
                }

                curl_close($ch);
            }
        } catch (\Exception $e) {
            PrestaShopLogger::addLog('[Warmup] Exception for ' . $baseUrl . ': ' . $e->getMessage(), 3);
        }
    }
}
