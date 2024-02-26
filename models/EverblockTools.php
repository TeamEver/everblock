<?php
/**
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
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
    public static function renderShortcodes(string $txt, Context $context): string
    {
        $txt = static::getCustomerShortcodes($txt, $context);
        if (strpos($txt, '[everfaq') !== false) {
            $txt = static::getFaqShortcodes($txt, $context);
        }
        if (strpos($txt, '[product') !== false) {
            $txt = static::getProductShortcodes($txt, $context);
        }
        if (strpos($txt, '[category') !== false) {
            $txt = static::getCategoryShortcodes($txt, $context);
        }
        if (strpos($txt, '[manufacturer') !== false) {
            $txt = static::getManufacturerShortcodes($txt, $context);
        }
        if (strpos($txt, '[brands') !== false) {
            $txt = static::getBrandsShortcode($txt, $context);
        }
        if (strpos($txt, '[storelocator]') !== false) {
            $txt = static::generateGoogleMap($txt, $context);
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
            $txt = static::getSubcategoriesShortcode($txt, $context);
        }
        if (strpos($txt, '[everstore') !== false) {
            $txt = static::getStoreShortcode($txt, $context);
        }
        if (strpos($txt, '[video') !== false) {
            $txt = static::getVideoShortcode($txt);
        }
        if (strpos($txt, '[qcdacf') !== false) {
            $txt = static::getQcdAcfCode($txt);
        }
        if (strpos($txt, '[best-sales') !== false) {
            $txt = static::getBestSalesShortcode($txt, $context);
        }
        if (strpos($txt, '[last-products') !== false) {
            $txt = static::getLastProductsShortcode($txt, $context);
        }
        if (strpos($txt, '[evercart]') !== false) {
            $txt = static::getCartShortcode($txt, $context);
        }
        if (strpos($txt, '[nativecontact]') !== false) {
            $txt = static::getNativeContactShortcode($txt, $context);
        }
        if (strpos($txt, '[evercontactform_open]') !== false) {
            $txt = static::getFormShortcode($txt);
        }
        if (strpos($txt, '[random_product') !== false) {
            $txt = static::getRandomProductsShortcode($txt, $context);
        }
        if (strpos($txt, '[widget') !== false) {
            $txt = $txt = static::getWidgetShortcode($txt);
        }
        $txt = static::renderSmartyVars($txt, $context);
        return $txt;
    }

    public static function getFaqShortcodes(string $txt, Context $context): string
    {
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/faq.tpl';

        $pattern = '/\[everfaq tag="([^"]+)"\]/';

        $txt = preg_replace_callback($pattern, function ($matches) use ($context, $templatePath) {
            $tagName = $matches[1];

            $faqs = EverblockFaq::getFaqByTagName($context->shop->id, $context->language->id, $tagName);

            $context->smarty->assign('everFaqs', $faqs);

            return $context->smarty->fetch($templatePath);

        }, $txt);

        return $txt;
    }

    public static function getProductShortcodes(string $txt, Context $context): string
    {
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_presented_products.tpl';

        preg_match_all('/\[product\s+(\d+(?:,\s*\d+)*)\]/i', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $productIdsArray = array_map('intval', explode(',', $match[1]));
            $everPresentProducts = static::everPresentProducts($productIdsArray, $context);
            
            if (!empty($everPresentProducts)) {
                $context->smarty->assign('everPresentProducts', $everPresentProducts);
                $renderedContent = $context->smarty->fetch($templatePath);
                
                $txt = str_replace($match[0], $renderedContent, $txt);
            }
        }

        return $txt;
    }

    public static function getCategoryShortcodes(string $txt, Context $context): string
    {
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_presented_products.tpl';
        preg_match_all('/\[category\s+id="(\d+)"\s+nb="(\d+)"\]/i', $txt, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $categoryId = (int) $match[1];
            $productCount = (int) $match[2];
            $categoryProducts = static::getProductsByCategoryId($categoryId, $productCount, $context);
            if (!empty($categoryProducts)) {
                $productIds = [];
                foreach ($categoryProducts as $categoryProduct) {
                    $productIds[] = (int) $categoryProduct['id_product'];
                }
                $everPresentProducts = static::everPresentProducts($productIds, $context);
                $context->smarty->assign('everPresentProducts', $everPresentProducts);
                $renderedHtml = $context->smarty->fetch($templatePath);
                // Remplace le shortcode par son rendu HTML dans $txt
                $txt = str_replace($match[0], $renderedHtml, $txt);
            }
        }
        return $txt;
    }

    protected static function getProductsByCategoryId(int $categoryId, int $limit): array
    {
        $cacheId = 'everblock_getProductsByCategoryId_'
        . (int) $categoryId
        . '_'
        . (int) $limit;
        if (!static::isCacheStored($cacheId)) {
            $category = new Category((int) $categoryId);
            $return = [];
            if (Validate::isLoadedObject($category)) {
                $products = $category->getProducts(Context::getContext()->language->id, 1, $limit, 'id_product', 'ASC');
                $return = $products;
            }
            static::cacheStore($cacheId, $return);
            return $return;
        }
        return static::cacheRetrieve($cacheId);
    }

    public static function getManufacturerShortcodes($message, $context)
    {
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_presented_products.tpl';
        preg_match_all('/\[manufacturer\s+id="(\d+)"\s+nb="(\d+)"\]/i', $message, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $manufacturerId = (int) $match[1];
            $productCount = (int) $match[2];
            $manufacturerProducts = static::getProductsByManufacturerId($manufacturerId, $productCount);
            if (!empty($manufacturerProducts)) {
                $productIds = [];
                foreach ($manufacturerProducts as $manufacturerProduct) {
                    $productIds[] = (int) $manufacturerProduct['id_product'];
                }
                $everPresentProducts = static::everPresentProducts($productIds, $context);
                $context->smarty->assign('everPresentProducts', $everPresentProducts);
                $renderedHtml = $context->smarty->fetch($templatePath);
                $message = str_replace($match[0], $renderedHtml, $message);
            }
        }
        return $message;
    }

    protected static function getProductsByManufacturerId(int $manufacturerId, int $limit): array
    {
        $cacheId = 'everblock_getProductsByManufacturerId_'
        . (int) $manufacturerId
        . '_'
        . (int) $limit;
        if (!static::isCacheStored($cacheId)) {
            $manufacturer = new Manufacturer($manufacturerId);
            $return = [];
            if (Validate::isLoadedObject($manufacturer)) {
                $products = Manufacturer::getProducts(
                    $manufacturer->id,
                    Context::getContext()->language->id,
                    1,
                    $limit,
                    'id_product',
                    'ASC'
                );
                $return = $products;
            }
            static::cacheStore($cacheId, $return);
            return $return;
        }
        return static::cacheRetrieve($cacheId);
    }

    public static function getBrandsShortcode(string $txt, Context $context): string
    {
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_brand.tpl';
        preg_match_all('/\[brands\s+nb="(\d+)"\]/i', $txt, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $brandCount = (int) $match[1];
            $brands = static::getBrandsData($brandCount, $context);
            if (!empty($brands)) {
                $context->smarty->assign('brands', $brands);
                $renderedHtml = $context->smarty->fetch($templatePath);
                $txt = str_replace($match[0], $renderedHtml, $txt);
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
        if (!static::isCacheStored($cacheId)) {
            $brands = Manufacturer::getLiteManufacturersList(
                (int) $context->language->id
            );
            $limitedBrands = [];
            // Limite du nombre de marques en fonction du paramètre $limit
            if (!empty($brands)) {
                $brands = array_slice($brands, 0, $limit);
                foreach ($brands as $brand) {
                    $name = $brand['name'];
                    $logo = $context->link->getManufacturerImageLink(
                        (int) $brand['id']
                    );
                    $url = $brand['link'];
                    $limitedBrands[] = [
                        'id' => $brand['id'],
                        'name' => $name,
                        'logo' => $logo,
                        'url' => $url,
                    ];
                }
            }
            static::cacheStore($cacheId, $limitedBrands);
            return $limitedBrands;
        }
        return static::cacheRetrieve($cacheId);
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
        $field_type = $attributes['type'];
        $label = $attributes['label'];
        $template = '';
        $isRequired = isset($attributes['required']) && strtolower($attributes['required']) === 'true';
        switch ($field_type) {
            case 'text':
                $template = '<div class="form-group"><label for="' . $label . '">' . $label . '</label><input type="text" class="form-control" name="' . $label . '" id="' . $label . '"';
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '></div>';
                break;
            case 'number':
                $template = '<div class="form-group"><label for="' . $label . '">' . $label . '</label><input type="number" class="form-control" name="' . $label . '" id="' . $label . '"';
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '></div>';
                break;
            case 'textarea':
                $template = '<div class="form-group"><label for="' . $label . '">' . $label . '</label><textarea class="form-control" name="' . $label . '" id="' . $label . '"';
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '></textarea></div>';
                break;
            case 'select':
                $values = explode(",", $attributes['values']);
                $template = '<div class="form-group"><label for="' . $label . '">' . $label . '</label><select class="form-control" name="' . $label . '" id="' . $label . '"';
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '>';
                foreach ($values as $value) {
                    $template .= '<option value="' . trim($value) . '">' . trim($value) . '</option>';
                }
                $template .= '</select></div>';
                break;
            case 'radio':
                $values = explode(",", $attributes['values']);
                $template = '<div class="form-group"><label>' . $label . '</label><div class="form-check">';
                foreach ($values as $value) {
                    $uniqueIdentifier++; // Incrémentation du compteur
                    $radioId = 'radio_' . $uniqueIdentifier; // Identifiant unique
                    $template .= '<div class="form-check-inline"><input type="radio" class="form-check-input" name="' . $label . '" value="' . trim($value) . '" id="' . $radioId . '"';
                    if ($isRequired) {
                        $template .= ' required';
                    }
                    $template .= '><label class="form-check-label" for="' . $radioId . '">' . trim($value) . '</label></div>';
                }
                $template .= '</div></div>';
                break;
            case 'checkbox':
                $values = explode(",", $attributes['values']);
                $template = '<div class="form-group"><label>' . $label . '</label><div class="form-check">';
                foreach ($values as $value) {
                    $uniqueIdentifier++;
                    $checkboxId = 'checkbox_' . $uniqueIdentifier;
                    $template .= '<div class="form-check-inline"><input type="checkbox" class="form-check-input" name="' . $label . '[]" value="' . trim($value) . '" id="' . $checkboxId . '"';
                    if ($isRequired) {
                        $template .= ' required';
                    }
                    $template .= '><label class="form-check-label" for="' . $checkboxId . '">' . trim($value) . '</label></div>';
                }
                $template .= '</div></div>';
                break;
            case 'file':
                $template = '<div class="form-group"><label for="' . $label . '">' . $label . '</label><input type="file" class="form-control-file" name="' . $label . '" id="' . $label . '"';
                if ($isRequired) {
                    $template .= ' required';
                }
                $template .= '></div>';
                break;
            case 'submit':
                $template = '<button type="submit" class="btn btn-success evercontactsubmit">' . $label . '</button>';
                break;
            case 'hidden':
                $template = '<input type="hidden" name="' . $label . '" value="' . $label . '">';
                break;
            default:
                // Type de champ inconnu
                $template = '';
                break;
        }
        return $template;
    }

    public static function getFormShortcode(string $txt): string
    {
        $txt = str_replace('[evercontactform_open]', '<div class="container"><form method="POST" class="evercontactform" action="#">', $txt);
        $txt = str_replace('[evercontactform_close]', '</form></div>', $txt);
        $pattern = '/\[evercontact\s[^\]]+\]/';
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

    public static function getNativeContactShortcode(string $txt, Context $context): string
    {
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/contact.tpl';
        $replacement = $context->smarty->fetch($templatePath);
        $txt = str_replace('[nativecontact]', $replacement, $txt);
        return $txt;
    }

    public static function getCartShortcode(string $txt, Context $context): string    {
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/cart.tpl';
        $replacement = $context->smarty->fetch($templatePath);
        $txt = str_replace('[evercart]', $replacement, $txt);
        return $txt;
    }

    public static function getEverBlockShortcode(string $txt, Context $context): string
    {
        // Recherche des shortcodes [everblock X]
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

    public static function getRandomProductsShortcode(string $txt, Context $context): string
    {
        preg_match_all('/\[random_product\s+nb="(\d+)"\]/i', $txt, $matches);
        foreach ($matches[1] as $match) {
            $limit = (int) $match;
            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int) $context->shop->id . '
                    ORDER BY RAND()
                    LIMIT ' . (int) $limit;
            $productIds = Db::getInstance()->executeS($sql);
            if (!empty($productIds)) {
                $productIdsArray = array_map(function($row) {
                    return (int) $row['id_product'];
                }, $productIds);
                $everPresentProducts = static::everPresentProducts($productIdsArray);
                if (!empty($everPresentProducts)) {
                    $context->smarty->assign('everPresentProducts', $everPresentProducts);
                    $module = Module::getInstanceByName('everblock');
                    $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_presented_products.tpl';
                    $replacement = $context->smarty->fetch($templatePath);
                    $shortcode = '[random_product nb="' . (int) $limit . '"]';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }
        return $txt;
    }

    public static function getLastProductsShortcode(string $txt, Context $context): string
    {
        preg_match_all('/\[last-products\s+(\d+)\]/i', $txt, $matches);
        foreach ($matches[1] as $match) {
            $limit = (int) $match;
            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int) $context->shop->id . '
                    ORDER BY p.date_add DESC
                    LIMIT ' . (int) $limit;
            $productIds = Db::getInstance()->executeS($sql);
            if (!empty($productIds)) {
                $productIdsArray = array_map(function($row) {
                    return (int) $row['id_product'];
                }, $productIds);
                $everPresentProducts = static::everPresentProducts($productIdsArray);
                if (!empty($everPresentProducts)) {
                    $context->smarty->assign('everPresentProducts', $everPresentProducts);
                    $module = Module::getInstanceByName('everblock');
                    $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_presented_products.tpl';
                    $replacement = $context->smarty->fetch($templatePath);
                    $shortcode = '[last-products ' . (int) $limit . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }
        return $txt;
    }

    public static function getBestSalesShortcode(string $txt, Context $context): string
    {
        preg_match_all('/\[best-sales\s+(\d+)\]/i', $txt, $matches);
        foreach ($matches[1] as $match) {
            $limit = (int) $match;
            $sql = 'SELECT product_id, SUM(product_quantity) AS total_quantity
                    FROM ' . _DB_PREFIX_ . 'order_detail
                    GROUP BY product_id
                    ORDER BY total_quantity DESC
                    LIMIT ' . (int) $limit;
            $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            if (!empty($productIds)) {
                $productIdsArray = array_map(function($row) {
                    return (int) $row['product_id'];
                }, $productIds);
                $everPresentProducts = static::everPresentProducts($productIdsArray);
                if (!empty($everPresentProducts)) {
                    $context->smarty->assign('everPresentProducts', $everPresentProducts);
                    $module = Module::getInstanceByName('everblock');
                    $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_presented_products.tpl';
                    $replacement = $context->smarty->fetch($templatePath);
                    $shortcode = '[best-sales ' . (int) $limit . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }
        return $txt;
    }

    public static function getSubcategoriesShortcode(string $txt, Context $context): string
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
            $module = Module::getInstanceByName('everblock');
            $templatePath = $module->getLocalPath() . 'views/templates/hook/subcategories.tpl';
            $replacement = $context->smarty->fetch($templatePath);
            $txt = str_replace($match[0], $replacement, $txt);
        }
        return $txt;
    }

    public static function getStoreShortcode(string $txt, Context $context): string
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
            $module = Module::getInstanceByName('everblock');
            $templatePath = $module->getLocalPath() . 'views/templates/hook/store.tpl';
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
    public static function getQcdAcfCode(string $txt): string
    {
        if (!Module::isInstalled('qcdacf')
            || !static::moduleDirectoryExists('qcdacf')
        ) {
            return $txt;
        }
        Module::getInstanceByName('qcdacf');
        $pattern = '/\[qcdacf\s+(\w+)\s+(\w+)\s+(\w+)\]/i';
        $modifiedTxt = preg_replace_callback($pattern, function ($matches) {
            $name = $matches[1];
            $object_type = $matches[2];
            $objectId = $matches[3];
            $value = qcdacf::getVar($name, $object_type, $objectId);
            if ($value) {
                return $value;
            }
            return '';
        }, $txt);
        return $modifiedTxt;
    }

    public static function renderSmartyVars(string $txt, Context $context): string
    {
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
        if ((bool) static::getModuleConfiguration('EVERPSCSS_CACHE') === true) {
            Tools::clearAllCache();
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
        $cacheId = 'store_locator_data_' . (int) $context->shop->id;
        if (!static::isCacheStored($cacheId)) {
            $stores = Store::getStores((int) $context->language->id);
            static::cacheStore($cacheId, $stores);
        }
        return static::cacheRetrieve($cacheId);
    }

    public static function getStoreCoordinates(int $storeId): array
    {
        $cacheId = 'store_coordinates_' . (int) $storeId;
        if (!static::isCacheStored($cacheId)) {
            $store = new Store((int) $storeId);
            if (Validate::isLoadedObject($store)) {
                $coordinates = [
                    'latitude' => (float) $store->latitude,
                    'longitude' => (float) $store->longitude
                ];
                static::cacheStore($cacheId, $coordinates);
            } else {
                return [];
            }
        }
        return static::cacheRetrieve($cacheId);
    }

    public static function generateGoogleMap(string $txt, Context $context): string
    {
        $stores = static::getStoreLocatorData();
        if (!empty($stores)) {
            $smarty = $context->smarty;
            $module = Module::getInstanceByName('everblock');
            $templatePath = $module->getLocalPath() . 'views/templates/hook/storelocator.tpl';
            $smarty->assign([
                'everblock_stores' => $stores,
            ]);
            $storeLocatorContent = $smarty->fetch($templatePath);
            $txt = str_replace('[storelocator]', $storeLocatorContent, $txt);
        }
        return $txt;
    }

    private static function generateOsmScript($markers)
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
        if (!static::isCacheStored($cacheId)) {
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
            static::cacheStore($cacheId, $products);
        }
        return static::cacheRetrieve($cacheId);
    }

    public static function getAllManufacturers(int $shopId, int $langId): array
    {
        $cacheId = 'EverblockTools::getAllManufacturers_' . (int) $shopId . '_' . $langId;
        
        if (!static::isCacheStored($cacheId)) {
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
            static::cacheStore($cacheId, $manufacturers);
        }
        return static::cacheRetrieve($cacheId);
    }

    public static function getAllSuppliers(int $shopId, int $langId): array
    {
        $cacheId = 'EverblockTools::getAllSuppliers_' . (int) $shopId . '_' . $langId;
        if (!static::isCacheStored($cacheId)) {
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
            static::cacheStore($cacheId, $suppliers);
        }
        return static::cacheRetrieve($cacheId);
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
        $pattern = '/<img\s+(?:[^>]*)src="([^"]*)"([^>]*)>/i';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
        // Parcourir les correspondances et ajouter la classe 'lazyload' et l'attribut 'loading="lazy"'
        foreach ($matches as $match) {
            $imageUrl = $match[1];
            $imageAttributes = $match[2];
            // Vérifier si la balise <img> contient déjà la classe 'lazyload'
            if (stripos($imageAttributes, 'class=') === false || stripos($imageAttributes, 'lazyload') === false) {
                // Ajouter la classe 'lazyload' aux classes existantes
                $imageAttributesWithLazyLoad = 'class="lazyload ' . $imageAttributes . '"';
            } else {
                $imageAttributesWithLazyLoad = $imageAttributes;
            }
            // Construire la nouvelle balise <img> avec la classe 'lazyload' et l'attribut 'loading="lazy"'
            $newTag = '<img src="' . $imageUrl . '" ' . $imageAttributesWithLazyLoad . ' loading="lazy">';
            $text = str_replace($match[0], $newTag, $text);
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
        if (!static::isCacheStored($cacheId)) {
            $lloremParagraphNum = (int) static::getModuleConfiguration('EVERPSCSS_P_LLOREM_NUMBER');
            if ($lloremParagraphNum <= 0) {
                $lloremParagraphNum = 5;
            }
            $lloremSentencesNum = (int) static::getModuleConfiguration('EVERPSCSS_S_LLOREM_NUMBER');
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
            static::cacheStore($cacheId, $llorem);
        }
        $llorem = static::cacheRetrieve($cacheId);
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
            _DB_PREFIX_ . 'everblock_modal',
            _DB_PREFIX_ . 'everblock_modal_lang',
        ];
        $tableExists = true;
        foreach ($tableNames as $tableName) {
            $tableExists = (bool) static::ifTableExists($tableName);
        }
        if ((bool) $tableExists === false) {
            include _PS_MODULE_DIR_ . 'everblock/sql/install.php';
        }
        // Ajoute les colonnes manquantes à la table ps_everblock
        $columnsToAdd = [
            'only_home' => 'int(10) unsigned DEFAULT NULL',
            'id_hook' => 'int(10) unsigned NOT NULL',
            'only_home' => 'int(10) unsigned DEFAULT NULL',
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

    public static function everPresentProducts(array $result): array
    {
        $context = Context::getContext();
        $products = [];
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
        return $products;
    }

    public static function dropUnusedLangs():array
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
        foreach ($pstable as $table) {
            $table = bqSQL(trim($tableName));
            $sql = 'DELETE FROM ' . $table . '
            WHERE id_lang NOT IN
            (SELECT id_lang FROM ' . _DB_PREFIX_ . 'lang)';
            try {
                Db::getInstance()->Execute($sql);
                $querySuccess[] = 'Unknown lang dropped from table ' . _DB_PREFIX_ . $table;
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
        $numProducts = (int) static::getModuleConfiguration('EVERPS_DUMMY_NBR');
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
            ' * 2019-2024 Team Ever' . PHP_EOL .
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
            ' *  @copyright 2019-2024 Team Ever' . PHP_EOL .
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

    /**
     * Check if cache exists based on unique key
     * No cache on admin, no cache if PS cache is disabled
     * @return bool
    */
    public static function isCacheStored(string $cacheKey): bool
    {
        $context = Context::getContext();
        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return false;
        }
        if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_) {
            return false;
        }
        $cacheFilePath = _PS_CACHE_DIR_ . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            return true;
        }
        return false;
    }

    /**
     * Store data on file, on Prestashop cache folder
     * Do NOT store confidential informations
     * @param cacheKey, must be unique
     * @param value to store
    */
    public static function cacheStore(string $cacheKey, $cacheValue)
    {
        $context = Context::getContext();
        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return;
        }
        if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_) {
            return;
        }
        $cacheFilePath = _PS_CACHE_DIR_ . $cacheKey . '.cache';
        file_put_contents($cacheFilePath, serialize($cacheValue));
    }

    /**
     * Retrieve value from cache
     * @param cache key
     * @return cache content
    */
    public static function cacheRetrieve(string $cacheKey)
    {
        $cacheFilePath = _PS_CACHE_DIR_ . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            $cachedData = Tools::file_get_contents($cacheFilePath);
            return unserialize($cachedData);
        }
        return '';
    }

    public static function cacheDrop(string $cacheKey)
    {
        $cacheFilePath = _PS_CACHE_DIR_ . $cacheKey . '.cache';
        if (file_exists($cacheFilePath)) {
            unlink($cacheFilePath);
        }
    }

    public static function cacheDropByPattern(string $cacheKeyStart)
    {
        $cacheDir = _PS_CACHE_DIR_;
        $pattern = $cacheDir . $cacheKeyStart . '*.cache';
        $matchingFiles = glob($pattern);
        if (!empty($matchingFiles)) {
            foreach ($matchingFiles as $file) {
                unlink($file);
            }
        }
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
     * Get module configuration from cache, store it if not stored on cache
     * Do NOT store confidential informations
     * @param configuration key
     * @return configuration value
    */
    public static function getModuleConfiguration(string $key): string
    {
        $context = Context::getContext();
        if ($context->controller->controller_type == 'admin'
            || $context->controller->controller_type == 'moduleadmin'
        ) {
            return '';
        }
        if (!static::isCacheStored($key)) {
            $value = Configuration::get($key);
            static::cacheStore($key, $value);
        }
        return static::cacheRetrieve($key);
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
}
