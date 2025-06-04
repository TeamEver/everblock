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
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use \PrestaShop\PrestaShop\Core\Product\ProductPresenter;

class EverblockTools extends ObjectModel
{
    public static function renderShortcodes(string $txt, Context $context, Everblock $module): string
    {
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
            $txt = static::getEverImgShortcode($txt);
        }
        if (strpos($txt, '[best-sales') !== false) {
            $txt = static::getBestSalesShortcode($txt, $context, $module);
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
        if (strpos($txt, '[nativecontact]') !== false) {
            $txt = static::getNativeContactShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[evercontactform_open]') !== false) {
            $txt = static::getFormShortcode($txt);
        }
        if (strpos($txt, '[everorderform_open]') !== false) {
            $txt = static::getOrderFormShortcode($txt);
        }
        if (strpos($txt, '[random_product') !== false) {
            $txt = static::getRandomProductsShortcode($txt, $context, $module);
        }
        if (strpos($txt, '[linkedproducts') !== false) {
            $txt = static::getLinkedProductsShortcode($txt, $context, $module);
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
        // Regex pour [cms id="X"]
        preg_match_all('/\[cms\s+id="?(\d+)"?\]/i', $txt, $matches, PREG_SET_ORDER);

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
                    'carousel' => $carousel
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
            '/\[productfeature\s+id=(\d+)\s+nb=(\d+)\s+carousel=(true|false)(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $featureId = (int) $match[1];
            $productLimit = (int) $match[2];
            $carousel = strtolower($match[3]) === 'true';

            $orderBy = isset($match[4]) ? strtolower($match[4]) : 'id_product';
            $orderWay = isset($match[5]) ? strtoupper($match[5]) : 'DESC';

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
                    'carousel' => $carousel
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
        // Mise à jour de la regex pour capturer les paramètres id, nb et carousel
        preg_match_all('/\[productfeaturevalue\s+id=(\d+)\s+nb=(\d+)\s+carousel=(true|false)\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $featureId = intval($match[1]);
            $productLimit = intval($match[2]);
            $carousel = $match[3] === 'true';

            // Rechercher les produits par caractéristique
            $featureProducts = static::getProductsByFeatureValue($featureId, $productLimit, $context);
            $productIds = array_column($featureProducts, 'id_product');
            $everPresentProducts = static::everPresentProducts($productIds, $context);
            if (!empty($featureProducts)) {
                // Assigner les produits et le flag carousel au template
                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => $carousel
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
    protected static function getProductsByFeatureValue(int $featureValueId, int $limit, Context $context)
    {
        $cacheId = 'everblock_getProductsByFeatureValue_'
        . (int) $featureValueId
        . '_'
        . (int) $limit
        . '_'
        . (int) $context->language->id;
        if (!EverblockCache::isCacheStored($cacheId)) {
            $sql = new DbQuery();
            $sql->select('p.id_product');
            $sql->from('product', 'p');
            $sql->innerJoin('feature_product', 'fp', 'p.id_product = fp.id_product');
            $sql->where('fp.id_feature_value = ' . (int) $featureValueId);
            $sql->where('p.active = 1');
            $sql->orderBy('p.date_add DESC');
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
            '/\[category\s+id="(\d+)"\s+nb="(\d+)"(?:\s+carousel=(?:"?(true|false)"?))?(?:\s+orderby="?(id_product|price|name|date_add|position)"?)?(?:\s+orderway="?(ASC|DESC)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $categoryId = (int) $match[1];
            $productCount = (int) $match[2];
            $carousel = isset($match[3]) && strtolower($match[3]) === 'true';
            $orderBy = isset($match[4]) ? $match[4] : 'id_product';
            $orderWay = isset($match[5]) ? strtoupper($match[5]) : 'ASC';

            $categoryProducts = static::getProductsByCategoryId($categoryId, $productCount, $orderBy, $orderWay);
            if (!empty($categoryProducts)) {
                $productIds = array_column($categoryProducts, 'id_product');
                $everPresentProducts = static::everPresentProducts($productIds, $context);
                $context->smarty->assign([
                    'everPresentProducts' => $everPresentProducts,
                    'carousel' => $carousel,
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
            '/\[manufacturer\s+id="(\d+)"\s+nb="(\d+)"(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $message,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $manufacturerId = (int) $match[1];
            $productCount = (int) $match[2];
            $carousel = isset($match[3]) && $match[3] === 'true';
            $orderBy = isset($match[4]) ? strtolower($match[4]) : 'id_product';
            $orderWay = isset($match[5]) ? strtoupper($match[5]) : 'DESC';

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
                    'carousel' => $carousel
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

    public static function generateFormFromShortcode(string $shortcode)
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

        $field_type = htmlspecialchars($attributes['type'], ENT_QUOTES);
        $label = htmlspecialchars($attributes['label'], ENT_QUOTES);
        $valueAttribute = isset($attributes['value']) ? ' value="' . htmlspecialchars($attributes['value'], ENT_QUOTES) . '"' : '';
        $template = '';
        $isRequired = isset($attributes['required']) && strtolower($attributes['required']) === 'true';

        switch ($field_type) {
            case 'sento':
                $template = '<input type="hidden" name="everHide" value="' . base64_encode($label) . '">';
                break;

            case 'password':
            case 'tel':
            case 'email':
            case 'datetime-local':
            case 'date':
            case 'text':
            case 'number':
                $template = '<div class="form-group mb-4"><label for="' . $label . '" class="d-none">' . $label . '</label>';
                $template .= '<input type="' . $field_type . '" class="form-control" name="' . $label . '" id="' . $label . '" placeholder="' . $label . '"' . $valueAttribute;
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '></div>';
                break;

            case 'textarea':
                $textareaValue = htmlspecialchars($attributes['value'] ?? '', ENT_QUOTES);
                $template = '<div class="form-group mb-4"><label for="' . $label . '" class="d-none">' . $label . '</label>';
                $template .= '<textarea class="form-control" name="' . $label . '" id="' . $label . '" placeholder="' . $label . '"';
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '>' . $textareaValue . '</textarea></div>';
                break;

            case 'select':
                $values = explode(",", $attributes['values']);
                $selectedValue = $attributes['value'] ?? null;
                $template = '<div class="form-group mb-4"><label for="' . $label . '" class="d-none">' . $label . '</label>';
                $template .= '<select class="form-control" name="' . $label . '" id="' . $label . '"';
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '>';
                $template .= '<option value="" disabled selected>' . $label . '</option>';
                foreach ($values as $value) {
                    $trimmedValue = trim($value);
                    $selected = ($trimmedValue === $selectedValue) ? ' selected' : '';
                    $template .= '<option value="' . $trimmedValue . '"' . $selected . '>' . $trimmedValue . '</option>';
                }
                $template .= '</select></div>';
                break;

            case 'radio':
                $values = explode(",", $attributes['values']);
                $selectedValue = $attributes['value'] ?? null;
                $template = '<div class="form-group mb-4"><label>' . $label . '</label><div class="form-check">';
                foreach ($values as $value) {
                    $uniqueIdentifier++;
                    $radioId = 'radio_' . $uniqueIdentifier;
                    $trimmedValue = trim($value);
                    $checked = ($trimmedValue === $selectedValue) ? ' checked' : '';
                    $template .= '<div class="form-check-inline">';
                    $template .= '<input type="radio" class="form-check-input" name="' . $label . '" value="' . $trimmedValue . '" id="' . $radioId . '"' . $checked;
                    if ($isRequired) {
                        $template .= ' required';
                    }
                    $template .= '>';
                    $template .= '<label class="form-check-label" for="' . $radioId . '">' . $trimmedValue . '</label></div>';
                }
                $template .= '</div></div>';
                break;

            case 'checkbox':
                $values = explode(",", $attributes['values']);
                $checkedValues = isset($attributes['value']) ? explode(",", $attributes['value']) : [];
                $template = '<div class="form-group mb-4"><label class="d-none">' . $label . '</label><div class="form-check">';
                foreach ($values as $value) {
                    $uniqueIdentifier++;
                    $checkboxId = 'checkbox_' . $uniqueIdentifier;
                    $trimmedValue = trim($value);
                    $checked = in_array($trimmedValue, $checkedValues) ? ' checked' : '';
                    $template .= '<div class="form-check-inline">';
                    $template .= '<input type="checkbox" class="form-check-input" name="' . $label . '[]" value="' . $trimmedValue . '" id="' . $checkboxId . '"' . $checked;
                    if ($isRequired) {
                        $template .= ' required';
                    }
                    $template .= '>';
                    $template .= '<label class="form-check-label" for="' . $checkboxId . '">' . $trimmedValue . '</label></div>';
                }
                $template .= '</div></div>';
                break;

            case 'file':
                $template = '<div class="form-group mb-4"><label for="' . $label . '" class="d-none">' . $label . '</label>';
                $template .= '<input type="file" class="form-control-file" name="' . $label . '" id="' . $label . '"';
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '></div>';
                break;

            case 'submit':
                $template = '<button type="submit" class="btn btn-primary evercontactsubmit">' . $label . '</button>';
                break;

            case 'hidden':
                $template = '<input type="hidden" name="hidden" value="' . $label . '">';
                break;

            default:
                $template = '';
                break;
        }

        return $template;
    }

    public static function getFormShortcode(string $txt): string
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
        $result = preg_replace_callback($pattern, function ($matches) {
            return static::generateFormFromShortcode($matches[0]);
        }, $txt);

        return $result;
    }

    public static function getOrderFormShortcode(string $txt): string
    {
        $txt = str_replace('[everorderform_open]', '<div class="container">', $txt);
        $txt = str_replace('[everorderform_close]', '</div>', $txt);
        $pattern = '/\[everorderform\s[^\]]+\]/';
        $result = preg_replace_callback($pattern, function ($matches) {
            // $matches[0] contient le shortcode trouvé
            return static::generateFormFromShortcode($matches[0]);
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
        // Update regex to capture optional carousel parameter
        preg_match_all('/\[random_product\s+nb="(\d+)"(?:\s+carousel=(true|false))?\]/i', $txt, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $limit = (int) $match[1];
            $carousel = isset($match[2]) && $match[2] === 'true';

            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int) $context->shop->id . '
                    ORDER BY RAND()
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
                        'carousel' => $carousel
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcode = '[random_product nb="' . (int) $limit . '"' . ($carousel ? ' carousel=true' : '') . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getLastProductsShortcode(string $txt, Context $context, Everblock $module): string
    {
        // Update regex to capture optional carousel parameter
        preg_match_all('/\[last-products\s+(\d+)(?:\s+carousel=(true|false))?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $limit = (int) $match[1];
            $carousel = isset($match[2]) && $match[2] === 'true';

            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int) $context->shop->id . '
                    AND p.active = 1
                    ORDER BY p.date_add DESC
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
                        'carousel' => $carousel
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcode = '[last-products ' . (int) $limit . ($carousel ? ' carousel=true' : '') . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getPromoProductsShortcode(string $txt, Context $context, Everblock $module): string
    {
        // Update regex to capture optional carousel parameter
        preg_match_all('/\[promo-products\s+(\d+)(?:\s+carousel=(true|false))?\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $limit = (int) $match[1];
            $carousel = isset($match[2]) && $match[2] === 'true';

            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int) $context->shop->id . '
                    AND p.active = 1
                    AND p.on_sale = 1
                    ORDER BY p.date_add DESC
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
                        'carousel' => $carousel
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    $shortcode = '[promo-products ' . (int) $limit . ($carousel ? ' carousel=true' : '') . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getBestSalesShortcode(string $txt, Context $context, Everblock $module): string
    {
        preg_match_all(
            '/\[best-sales(?:\s+nb=(\d+))?(?:\s+days=(\d+))?(?:\s+carousel=(true|false))?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(\w+)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $limit = isset($match[1]) ? (int)$match[1] : 10;
            $days = isset($match[2]) ? (int)$match[2] : null;
            $carousel = isset($match[3]) && $match[3] === 'true';
            $orderBy = isset($match[4]) ? strtolower($match[4]) : 'total_quantity';
            $orderWay = isset($match[5]) ? strtoupper($match[5]) : 'DESC';

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
                        'carousel' => $carousel
                    ]);

                    $templatePath = static::getTemplatePath('hook/ever_presented_products.tpl', $module);
                    $replacement = $context->smarty->fetch($templatePath);

                    // Recompose shortcode (avec tous les paramètres capturés)
                    $shortcodeParts = ['[best-sales'];
                    if (isset($match[1])) $shortcodeParts[] = 'nb=' . $match[1];
                    if (isset($match[2])) $shortcodeParts[] = 'days=' . $match[2];
                    if (isset($match[3])) $shortcodeParts[] = 'carousel=' . $match[3];
                    if (isset($match[4])) $shortcodeParts[] = 'orderby=' . $match[4];
                    if (isset($match[5])) $shortcodeParts[] = 'orderway=' . $match[5];
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
            '/\[linkedproducts(?:\s+nb="?(\d+)"?)?(?:\s+orderby="?(\w+)"?)?(?:\s+orderway="?(ASC|DESC)"?)?\]/i',
            $txt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $limit = isset($match[1]) ? (int) $match[1] : 8;
            $orderBy = isset($match[2]) ? strtolower($match[2]) : 'position';
            $orderWay = isset($match[3]) ? strtoupper($match[3]) : 'ASC';

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
                    if (isset($match[2])) { $shortcodeParts[] = 'orderby="' . $match[2] . '"'; }
                    if (isset($match[3])) { $shortcodeParts[] = 'orderway="' . $match[3] . '"'; }
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
        $modifiedTxt = preg_replace_callback($pattern, function ($matches) {
            $name = $matches[1];
            $value = qcdacf::getVar($name, $objectType, $objectId, $context->language->id);
            if ($value) {
                return $value;
            }
            return '';
        }, $txt);
        return $modifiedTxt;
    }

    public static function getEverImgShortcode(string $txt): string
    {
        preg_match_all('/\[everimg\s+name="([^"]+)"(?:\s+class="([^"]*)")?\]/', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $filenames = array_map('trim', explode(',', $match[1]));
            $class = isset($match[2]) ? trim($match[2]) : 'img-fluid';

            $html = [];
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

                $imgTag = sprintf(
                    '<img src="%s" width="%d" height="%d" alt="%s" loading="lazy" class="%s" />',
                    htmlspecialchars($webPath, ENT_QUOTES),
                    $width,
                    $height,
                    $alt,
                    $classAttr
                );

                $html[] = count($filenames) > 1
                    ? '<div class="col">' . $imgTag . '</div>'
                    : $imgTag;
            }

            $replacement = '';
            if (!empty($html)) {
                $replacement = count($filenames) > 1
                    ? '<div class="row">' . implode('', $html) . '</div>'
                    : $html[0];
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
        $jsonEncodedOldUrl = json_encode($oldUrl);
        $jsonEncodedNewUrl = json_encode($newUrl);
        // Configuration
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'configuration
            SET value =
            REPLACE(
                value,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                value,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Configuration table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // CMS content
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'cms_lang
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '")
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_cms IN (
                SELECT id_cms FROM ' . _DB_PREFIX_ . 'cms_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Content of CMS rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // CMS meta title
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'cms_lang
            SET meta_title =
            REPLACE(
                meta_title,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '")
            WHERE INSTR(
                meta_title,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_cms IN (
                SELECT id_cms FROM ' . _DB_PREFIX_ . 'cms_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'meta_title of CMS rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // CMS meta description
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'cms_lang
            SET meta_title =
            REPLACE(
                meta_description,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '")
            WHERE INSTR(
                meta_description,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_cms IN (
                SELECT id_cms FROM ' . _DB_PREFIX_ . 'cms_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'meta_description of CMS rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Product description
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'product_lang
            SET description =
            REPLACE(
                description,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                description,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_product IN (
                SELECT id_product FROM ' . _DB_PREFIX_ . 'product_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'description of product rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Product name
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'product_lang
            SET name =
            REPLACE(
                name,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                name,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_product IN (
                SELECT id_product FROM ' . _DB_PREFIX_ . 'product_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'name of product rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Product description short
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'product_lang
            SET description_short =
            REPLACE(
                description_short,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                description_short,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_product IN (
                SELECT id_product FROM ' . _DB_PREFIX_ . 'product_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'description_short of product rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Product meta title
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'product_lang
            SET meta_title =
            REPLACE(
                meta_title,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(meta_title, "' . pSQL($oldUrl, true) . '") > 0
            AND id_product IN (
                SELECT id_product FROM ' . _DB_PREFIX_ . 'product_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'meta_title of product rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Product meta description
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'product_lang
            SET meta_description =
            REPLACE(
                meta_description,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                meta_description,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_product IN (
                SELECT id_product FROM ' . _DB_PREFIX_ . 'product_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'meta_description of product rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Category description
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'category_lang
            SET description =
            REPLACE(
                description,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                description,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_category IN (
                SELECT id_category FROM ' . _DB_PREFIX_ . 'category_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'description of category rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Category meta title
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'category_lang
            SET meta_title =
            REPLACE(
                meta_title,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                meta_title,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_category IN (
                SELECT id_category FROM ' . _DB_PREFIX_ . 'category_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'meta_title of category rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Category meta description
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'category_lang
            SET meta_description =
            REPLACE(
                meta_description,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                meta_description,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_category IN (
                SELECT id_category FROM ' . _DB_PREFIX_ . 'category_shop
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'meta_description of category rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // EverBlock
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'everblock_lang
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_everblock IN (
                SELECT id_everblock FROM ' . _DB_PREFIX_ . 'everblock
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'EverBlock table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'everblock_shortcode_lang
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_everblock_shortcode IN (
                SELECT id_everblock_shortcode FROM ' . _DB_PREFIX_ . 'everblock_shortcode
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'EverBlock table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Everblock tabs title
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'everblock_tabs_lang
            SET title =
            REPLACE(
                title,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                title,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_everblock_tabs IN (
                SELECT id_everblock_tabs FROM ' . _DB_PREFIX_ . 'everblock_tabs
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'EverBlock tabs title rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Everblock tabs title
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'everblock_tabs_lang
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_everblock_tabs IN (
                SELECT id_everblock_tabs FROM ' . _DB_PREFIX_ . 'everblock_tabs
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'EverBlock tabs content rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Everblog
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'ever_blog_post_lang
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_ever_post IN (
                SELECT id_ever_post FROM ' . _DB_PREFIX_ . 'ever_blog_post
                WHERE id_shop = ' . (int) $id_shop . '
            )';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'EverBlock table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Prestacrea slider
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pte_slider
            SET description =
            REPLACE(
                description,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                description,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Prestacrea slider table rewrited (description)';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Prestacrea slider
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pte_slider
            SET link =
            REPLACE(
                link,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                link,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Prestacrea slider table rewrited (link)';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Prestacrea blocks
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pte_blocks
            SET description =
            REPLACE(
                description,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                description,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Prestacrea blocks table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pte_blocks
            SET link =
            REPLACE(
                link,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                link,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Prestacrea blocks table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Prestacrea footer text
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pte_footer_text
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Prestacrea footer text table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Prestacrea footer links
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pte_footer_links
            SET url =
            REPLACE(
                url,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                url,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Prestacrea footer links table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Advanced top menu
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pm_advancedtopmenu_columns_lang
            SET link =
            REPLACE(
                link,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                link,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Advanced top menu columns table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pm_advancedtopmenu_elements_lang
            SET link =
            REPLACE(
                link,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                link,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Advanced top menu elements table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // QCD Redirect
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'qcdredirect
            SET not_found =
            REPLACE(
                not_found,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                not_found,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'QCD Redirect not_found column rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'qcdredirect
            SET redirection =
            REPLACE(
                redirection,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                redirection,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'QCD Redirect redirection column rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // QCD Obfuscation
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'qcdobfuscation
            SET link =
            REPLACE(
                link,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                link,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'QCD Obfuscation table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // QCD ACF
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'qcdacf_value
            SET value =
            REPLACE(
                value,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                value,
                "' . pSQL($oldUrl, true) . '"
            ) > 0
            AND id_shop = ' . (int) $id_shop . '';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'QCD Obfuscation table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // LG Canonical URL
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'lgcanonicalurls_lang
            SET canonical_url =
            REPLACE(
                canonical_url,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                canonical_url,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'LG Canonical URL table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Pretty Blocks
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'prettyblocks
            SET config =
            REPLACE(
                config,
                "' . pSQL($jsonEncodedOldUrl) . '",
                "' . pSQL($jsonEncodedNewUrl) . '"
            )
            WHERE INSTR(
                config,
                "' . pSQL($jsonEncodedOldUrl) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Prettyblocks table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        if ((bool) EverblockCache::getModuleConfiguration('EVERPSCSS_CACHE') === true) {
            Tools::clearAllCache();
        }
        // GDPR
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'psgdpr_consent_lang
            SET message =
            REPLACE(
                message,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                message,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'GDPR URL table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // Elementor
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'ce_meta
            SET value =
            REPLACE(
                value,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                value,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Elementor CE Meta table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'ce_revision
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Elementor CE Revision table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'ce_template
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Elementor CE Revision table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // QCD CRON
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'qcd_cron
            SET link =
            REPLACE(
                link,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                link,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'QCD Cron table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // qcdsearchproduct_options
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'qcdsearchproduct_options
            SET url =
            REPLACE(
                url,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                url,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'QCD Search Product table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // qcdbanner_lang
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'qcdbanner_lang
            SET content =
            REPLACE(
                content,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                content,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'QCD Banner lang table rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        // pm_advancedtopmenu_lang
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pm_advancedtopmenu_lang
            SET value_under =
            REPLACE(
                value_under,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                value_under,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Advanced Top Menu value_under column rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
        }
        $sql =
            'UPDATE ' . _DB_PREFIX_ . 'pm_advancedtopmenu_lang
            SET value_over =
            REPLACE(
                value_over,
                "' . pSQL($oldUrl, true) . '",
                "' . pSQL($newUrl, true) . '"
            )
            WHERE INSTR(
                value_over,
                "' . pSQL($oldUrl, true) . '"
            ) > 0';
        try {
            Db::getInstance()->execute($sql);
            $querySuccess[] = 'Advanced Top Menu value_over column rewrited';
        } catch (Exception $e) {
            $postErrors[] = $e->getMessage();
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
            $isHoliday = in_array($todayDate, $frenchHolidays);

            foreach ($stores as &$store) {
                $id_store = (int) $store['id_store'];
                $cms_id = (int) Configuration::get('QCD_ASSOCIATED_CMS_PAGE_ID_STORE_' . $id_store, null, null, $id_shop);
                $cms_link = null;

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
                    $hoursFormatted = [];

                    foreach ($slots as $slot) {
                        if (empty($slot)) {
                            continue;
                        }

                        // Plusieurs créneaux ? → on découpe
                        $subSlots = explode(' / ', $slot);
                        foreach ($subSlots as $subSlot) {
                            $hoursFormatted[] = trim($subSlot);

                            if (!$isHoliday && $i === $todayIndex && strpos($subSlot, '-') !== false) {
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
                        $label = 'Fermé (jour férié)';
                    }

                    $store['hours_display'][] = [
                        'day' => $day,
                        'hours' => $label,
                    ];
                }

                if ($isHoliday) {
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
                    if (Product::checkAccessStatic((int) $productId, false)) {
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
     * Create fake products
     * @param shop id
     * @return bool
    */
    public static function generateProducts(int $idShop): bool
    {
        $numProducts = (int) EverblockCache::getModuleConfiguration('EVERPS_DUMMY_NBR');
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

    public static function fetchInstagramImages()
    {
        $cacheId = 'fetchInstagramImages';
        if (!EverblockCache::isCacheStored($cacheId)) {
            $request = static::getInstagramRequest();
            // $request = Tools::file_get_contents('https://graph.instagram.com/me/media?access_token=IGQWRNTDdaUnFyaFNway14eTJ0NFpiSDlSZAlNNemV0U3hwNmlma3laMC01WUVxdVlucnJOM2JReF9Oblg2SmdHRlVwLXdPWXRPNVNLb1RZASjMtN0JHMW4zemNnYzZA6MVpYSGEwcHEtOG5MQQZDZD&fields=id,caption,media_type,media_url,permalink,thumbnail_url,username,timestamp');
            $result = json_decode($request, true);
            $imgs = [];
            if ($result && isset($result['data']) && $result['data']) {
                foreach ($result['data'] as $post) {
                    $imgs[] = [
                        'id' => isset($post['id']) ? $post['id'] : $post['id'],
                        'permalink' => isset($post['permalink']) ? $post['permalink'] : $post['permalink'],
                        'low_resolution' => isset($post['thumbnail_url']) ? $post['thumbnail_url'] : $post['media_url'],
                        'thumbnail' => isset($post['thumbnail_url']) ? $post['thumbnail_url'] : $post['media_url'],
                        'standard_resolution' => isset($post['media_url']) ? $post['media_url'] : '',
                        'caption' => isset($post['caption']) ? $post['caption'] : '',
                        'is_video' => strpos($post['media_url'], '.mp4?') !== false ? true : false,
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

    public static function isBot()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
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

    public static function convertToWebP($imagePath, int $maxWidth = 1920, int $maxHeight = 1920)
    {
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
        $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';

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

        // Récupération des dimensions d’origine
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        // Redimensionner si plus grand que max
        $resize = false;
        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $resize = true;

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
