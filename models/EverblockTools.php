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
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use \PrestaShop\PrestaShop\Core\Product\ProductPresenter;

class EverblockTools extends ObjectModel
{
    public static function renderShortcodes($txt)
    {
            $txt = self::replaceHook($txt);
            $txt = self::getEverBlockShortcode($txt);
            $txt = self::getSubcategoriesShortcode($txt);
            $txt = self::getStoreShortcode($txt);
            $txt = self::getVideoShortcode($txt);
            $txt = self::getQcdAcfCode($txt);
            $txt = self::getBestSalesShortcode($txt);
            $txt = self::getLastProductsShortcode($txt);
            $txt = self::getCartShortcode($txt);
            $txt = self::getContactShortcode($txt);
            $txt = self::renderSmartyVars($txt);
        try {
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Everblock : ' . $e->getMessage());
        }
        return $txt;
    }

    public static function replaceHook($content)
    {
        // Recherche du hook dans le contenu
        preg_match_all('/\{hook h=\'(.*?)\'\}/', $content, $matches);

        // Si des hooks sont trouvés
        if (!empty($matches[1])) {
            foreach ($matches[1] as $hookName) {
                // Récupération du résultat du hook
                $hookContent = Hook::exec($hookName, array(), null, true);
                $hookContentString = '';
                // Vérification si le résultat est un tableau
                if (is_array($hookContent)) {
                    foreach ($hookContent as $hcontent) {
                        $hookContentString .= $hcontent;
                    }
                } else {
                    // Utiliser le résultat directement comme une chaîne de caractères
                    $hookContentString = (string)$hookContent;
                }

                // Remplacement du hook par le résultat dans le contenu
                $content = str_replace("{hook h='$hookName'}", $hookContentString, $content);
            }
        }

        return $content;
    }

    public static function getContactShortcode($txt)
    {
        $context = Context::getContext();
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/contact.tpl';
        $replacement = $context->smarty->fetch($templatePath);
        $txt = str_replace('[evercontact]', $replacement, $txt);
        return $txt;
    }

    public static function getCartShortcode($txt)
    {
        $context = Context::getContext();
        $module = Module::getInstanceByName('everblock');
        $templatePath = $module->getLocalPath() . 'views/templates/hook/cart.tpl';
        $replacement = $context->smarty->fetch($templatePath);
        $txt = str_replace('[evercart]', $replacement, $txt);
        return $txt;
    }

    public static function getEverBlockShortcode($txt)
    {
        $context = Context::getContext();

        // Recherche des shortcodes [everblock X]
        preg_match_all('/\[everblock\s+(\d+)\]/i', $txt, $matches);

        foreach ($matches[1] as $match) {
            $everblockId = (int) $match;

            // Initialise la classe EverblockClass avec l'ID spécifié
            $everblock = new EverblockClass(
                (int) $everblockId,
                (int) $context->language->id,
                (int) $context->shop->id
            );

            $shortcode = '[everblock ' . $everblockId . ']';
            if (Validate::isLoadedObject($everblock)) {
                // Remplace le shortcode par le contenu de l'objet EverblockClass
                $replacement = $everblock->content;
                $txt = str_replace($shortcode, $replacement, $txt);
            } else {
                $txt = str_replace($shortcode, '', $txt);
            }
        }

        return $txt;
    }

    public static function getLastProductsShortcode($txt)
    {
        $context = Context::getContext();

        // Recherche des shortcodes [last-products X]
        preg_match_all('/\[last-products\s+(\d+)\]/i', $txt, $matches);

        foreach ($matches[1] as $match) {
            $limit = (int) $match;

            // Requête SQL pour obtenir les X derniers produits basés sur date_add
            $sql = 'SELECT p.id_product
                    FROM ' . _DB_PREFIX_ . 'product_shop p
                    WHERE p.id_shop = ' . (int)$context->shop->id . '
                    ORDER BY p.date_add DESC
                    LIMIT ' . $limit;

            $productIds = Db::getInstance()->executeS($sql);

            if (!empty($productIds)) {
                // Convertir les ID de produit en un tableau d'entiers
                $productIdsArray = array_map(function($row) {
                    return (int) $row['id_product'];
                }, $productIds);

                $everPresentProducts = self::everPresentProducts($productIdsArray);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign('everPresentProducts', $everPresentProducts);
                    $module = Module::getInstanceByName('everblock');
                    $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_presented_products.tpl';
                    $replacement = $context->smarty->fetch($templatePath);
                    $shortcode = '[last-products ' . $limit . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getBestSalesShortcode($txt)
    {
        $context = Context::getContext();

        // Recherche des shortcodes [best-sales X]
        preg_match_all('/\[best-sales\s+(\d+)\]/i', $txt, $matches);
        foreach ($matches[1] as $match) {
            $limit = (int) $match;

            // Requête SQL pour obtenir les ID des produits les mieux vendus
            $sql = 'SELECT product_id, SUM(product_quantity) AS total_quantity
                    FROM ' . _DB_PREFIX_ . 'order_detail
                    GROUP BY product_id
                    ORDER BY total_quantity DESC
                    LIMIT ' . (int) $limit;

            $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (!empty($productIds)) {
                // Convertir les ID de produit en un tableau d'entiers
                $productIdsArray = array_map(function($row) {
                    return (int) $row['product_id'];
                }, $productIds);

                $everPresentProducts = self::everPresentProducts($productIdsArray);

                if (!empty($everPresentProducts)) {
                    $context->smarty->assign('everPresentProducts', $everPresentProducts);
                    $module = Module::getInstanceByName('everblock');
                    $templatePath = $module->getLocalPath() . 'views/templates/hook/ever_presented_products.tpl';
                    $replacement = $context->smarty->fetch($templatePath);
                    $shortcode = '[best-sales ' . $limit . ']';
                    $txt = str_replace($shortcode, $replacement, $txt);
                }
            }
        }

        return $txt;
    }

    public static function getSubcategoriesShortcode($txt)
    {
        $categoryShortcodes = [];
        $context = Context::getContext();
        preg_match_all('/\[subcategories\s+id="(\d+)"\s+nb="(\d+)"\]/i', $txt, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $categoryId = (int) $match[1];
            $categoryCount = (int) $match[2];
            $category = new Category(
                (int) $categoryId,
                (int) $context->language->id,
                (int) $context->shop->id
            );
            if (!Validate::isLoadedObject($category) || (bool) $category->active === false) {
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
                    'category_default',
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

    public static function getStoreShortcode($txt)
    {
        $context = Context::getContext();
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
                $storeInfo[] = array(
                    'id_store' => $store->id,
                    'image_link' => $context->link->getStoreImageLink('medium_default', $store->id),
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
                );
            }
            $context->smarty->assign('storeInfos', $storeInfo);
            $module = Module::getInstanceByName('everblock');
            $templatePath = $module->getLocalPath() . 'views/templates/hook/store.tpl';
            $replacement = $context->smarty->fetch($templatePath);

            // Utilisez preg_replace_callback pour gérer le remplacement de manière plus précise
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
    public static function getQcdAcfCode($txt)
    {
        if (!Module::isInstalled('qcdacf')) {
            return $txt;
        }
        Module::getInstanceByName('qcdacf');

        // Utilisez une expression régulière pour rechercher les shortcodes QCD ACF
        $pattern = '/\[qcdacf\s+(\w+)\s+(\w+)\s+(\w+)\]/i';

        // Remplacez les shortcodes par le résultat de qcdacf::getVar
        $modifiedTxt = preg_replace_callback($pattern, function ($matches) {
            $name = $matches[1];
            $object_type = $matches[2];
            $objectId = $matches[3];
            $value = qcdacf::getVar($name, $object_type, $objectId);
            // Si la valeur est vide, remplacez le shortcode par une chaîne vide
            if ($value) {
                return $value;
            }
            return '';
        }, $txt);

        return $modifiedTxt;
    }

    public static function renderSmartyVars($txt)
    {
        $context = Context::getContext();
        $templateVars = [
            'customer' => $context->controller->getTemplateVarCustomer(),
            'page' => $context->controller->getTemplateVarPage(),
            'currency' => $context->controller->getTemplateVarCurrency(),
            'shop' => $context->controller->getTemplateVarShop(),
            'urls' => $context->controller->getTemplateVarUrls(),
            'configuration' => $context->controller->getTemplateVarConfiguration(),
            'breadcrumb' => $context->controller->getBreadcrumb(),
        ];
        // Parcourir le tableau templateVars
        foreach ($templateVars as $key => $value) {
            // Construire la chaîne de recherche
            $search = '$' . $key;

            // Vérifier si la valeur est un tableau
            if (is_array($value)) {
                // Appeler récursivement la méthode pour traiter les tableaux imbriqués
                $txt = self::renderSmartyVarsInArray($txt, $search, $value);
            } elseif (is_string($value)) {
                // Remplacer les occurrences dans le texte
                $txt = str_replace($search, $value, $txt);
            }
        }

        return $txt;
    }

    private static function renderSmartyVarsInArray($txt, $search, $array)
    {
        // Parcourir le tableau
        foreach ($array as $key => $value) {
            // Construire la chaîne de recherche spécifique à cet élément
            $elementSearch = $search . '.' . $key;
            // Vérifier si la valeur est un tableau
            if (is_array($value)) {
                // Appeler récursivement la méthode pour traiter les tableaux imbriqués
                $txt = self::renderSmartyVarsInArray($txt, $elementSearch, $value);
            } else {
                // Remplacer les occurrences dans le texte
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
    public static function migrateUrls($oldUrl, $newUrl, $id_shop)
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
        if ((bool) Configuration::get('EVERPSCSS_CACHE') === true) {
            Tools::clearAllCache();
        }
        return [
            'postErrors' => $postErrors,
            'querySuccess' => $querySuccess,
        ];
    }
    
    public static function getVideoShortcode($txt)
    {
        preg_match_all('/\[video\s+(.*?)\]/i', $txt, $videoMatches);
        foreach ($videoMatches[0] as $shortcode) {
            $videoUrl = preg_replace('/\[video\s+|\]/i', '', $shortcode); // Récupérer l'URL à partir du shortcode
            $iframe = self::detectVideoSite($videoUrl);
            if ($iframe) {
                $txt = str_replace($shortcode, $iframe, $txt);
            }
        }

        return $txt;
    }

    public static function detectVideoSite($url)
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
        return false;
    }

    public static function getStoreLocatorData()
    {
        $cacheId = 'store_locator_data_' . (int) Context::getContext()->shop->id;

        if (!Cache::isStored($cacheId)) {
            $stores = Store::getStores((int) Context::getContext()->language->id);
            Cache::store($cacheId, $stores);
        }

        return Cache::retrieve($cacheId);
    }

    public static function getStoreCoordinates($storeId)
    {
        $cacheId = 'store_coordinates_' . (int) $storeId;

        if (!Cache::isStored($cacheId)) {
            $store = new Store((int) $storeId);
            if (Validate::isLoadedObject($store)) {
                $coordinates = [
                    'latitude' => (float) $store->latitude,
                    'longitude' => (float) $store->longitude
                ];

                Cache::store($cacheId, $coordinates);
            } else {
                return null;
            }
        }

        return Cache::retrieve($cacheId);
    }

    public static function generateGoogleMap()
    {
        $stores = self::getStoreLocatorData();

        if (!empty($stores)) {
            $context = Context::getContext();
            $smarty = $context->smarty;
            $module = Module::getInstanceByName('everblock');
            $templatePath = $module->getLocalPath() . 'views/templates/hook/storelocator.tpl';
            $smarty->assign([
                'everblock_stores' => $stores,
            ]);
            $storeLocatorContent = $smarty->fetch($templatePath);
            return $storeLocatorContent;
        }
    }

    private static function generateOsmScript($markers)
    {
        if (!$markers) {
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
            $marker['lat'] = (float)$marker['lat'];
            $marker['lng'] = (float)$marker['lng'];
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


    public static function stringExistsInFileContent($needle, $fileContent)
    {
        return strpos($fileContent, $needle) !== false;
    }

    protected static function registerPrettyBlockHook($hookName)
    {
        if (Module::isInstalled('prettyblocks')) {
            $prettyblocks = Module::getInstanceByName('prettyblocks');
            return $prettyblocks->registerHook($hookName);
        }
        return false;
    }

    public static function addHooksToTheme()
    {
        self::addHookCmsContent();
        self::addHookCmsCategoryContent();
        self::addHookProductContent();
        self::addHookToManufacturerTpl();
        self::addHooksToLayouts();
    }

    public static function addHookCmsContent()
    {
        $theme = Context::getContext()->shop->theme;
        $themePath = _PS_ALL_THEMES_DIR_ . $theme->getName() . '/';

        $pageTplPath = $themePath . 'templates/cms/page.tpl';

        if (file_exists($pageTplPath)) {
            $pageTplContent = file_get_contents($pageTplPath);

            $newContent = "{prettyblocks_zone zone_name='cmsContent\$cms.id'}\n{block name='cms_content'}";
            if ((bool) self::stringExistsInFileContent($newContent, $pageTplContent) === false) {
                $modifiedContent = str_replace("{block name='cms_content'}", $newContent, $pageTplContent);

                file_put_contents($pageTplPath, $modifiedContent);

                Tools::clearSmartyCache();

                return true;
            }
            return false;
        }

        return false;
    }

    public static function addHookCmsCategoryContent()
    {
        $theme = Context::getContext()->shop->theme;
        $themePath = _PS_ALL_THEMES_DIR_ . $theme->getName() . '/';

        $categoryTplPath = $themePath . 'templates/cms/category.tpl';

        if (file_exists($categoryTplPath)) {
            $categoryTplContent = file_get_contents($categoryTplPath);
            
            $newContent = "{prettyblocks_zone zone_name='cmsCategory\$cms_category.id'}\n{block name='page_content'}";
            if ((bool) self::stringExistsInFileContent($newContent, $categoryTplContent) === false) {
                $modifiedContent = str_replace("{block name='page_content'}", $newContent, $categoryTplContent);

                file_put_contents($categoryTplPath, $modifiedContent);

                Tools::clearSmartyCache();
                return true;
            }
            return false;
        }

        return false;
    }

    public static function addHookProductContent()
    {
        $theme = Context::getContext()->shop->theme;
        $themePath = _PS_ALL_THEMES_DIR_ . $theme->getName() . '/';

        $productTplPath = $themePath . 'templates/catalog/product.tpl';
        
        if (file_exists($productTplPath)) {
            $productTplContent = file_get_contents($productTplPath);
            
            $newContent = "{prettyblocks_zone zone_name='shortDescription\$product.id'}\n{block name='product_description_short'}";
            if ((bool) self::stringExistsInFileContent($newContent, $productTplContent) === false) {
                $modifiedContent = str_replace("{block name='product_description_short'}", $newContent, $productTplContent);
                file_put_contents($productTplPath, $modifiedContent);
            }

            // Ajouter le widget pour le hook displayDescriptionProductId
            $productTplContent = file_get_contents($productTplPath);
            $newContent = "{prettyblocks_zone zone_name='description\$product.id'}\n{block name='product_description'}";
            if ((bool) self::stringExistsInFileContent($newContent, $productTplContent) === false) {
                $modifiedContent = str_replace("{block name='product_description'}", $newContent, $productTplContent);
                file_put_contents($productTplPath, $modifiedContent);
            }

            // Ajouter le widget pour le hook displayReassuranceProductId
            $productTplContent = file_get_contents($productTplPath);
            $newContent = "{prettyblocks_zone zone_name='reassurance\$product.id'}\n{block name='hook_display_reassurance'}";
            if ((bool) self::stringExistsInFileContent($newContent, $productTplContent) === false) {
                $modifiedContent = str_replace("{block name='hook_display_reassurance'}", $newContent, $productTplContent);
                file_put_contents($productTplPath, $modifiedContent);
            }

            // Réinitialisez le cache de PrestaShop
            Tools::clearSmartyCache();
        }
    }

    public static function addHooksToLayouts()
    {
        // Liste des fichiers de mise en page où vous souhaitez ajouter les hooks
        $layoutFiles = array(
            'layout-both-columns.tpl',
            'layout-content-only.tpl',
            'layout-full-width.tpl',
            'layout-left-column.tpl'
        );

        foreach ($layoutFiles as $layoutFile) {
            $layoutPath = _PS_ALL_THEMES_DIR_ . Context::getContext()->shop->theme->getName() . '/templates/layouts/' . $layoutFile;
            if (file_exists($layoutPath)) {
                $layoutContent = file_get_contents($layoutPath);

                $newContent = "{if isset(\$manufacturer) && is_array(\$manufacturer)}{prettyblocks_zone zone_name='manufacturer\$manufacturer.id'}{/if}{if isset(\$supplier) && is_array(\$supplier)}{prettyblocks_zone zone_name='supplier\$supplier.id'}{/if}{if isset(\$category) && is_array(\$category)}{prettyblocks_zone zone_name='category\$category.id'}{/if}{hook h=\"displayContentWrapperBottom\"}";
                if ((bool) self::stringExistsInFileContent($newContent, $layoutContent) === false) {
                    $modifiedContent = preg_replace('/\{hook h="displayContentWrapperBottom"\}/', $newContent, $layoutContent);

                    file_put_contents($layoutPath, $modifiedContent);
                }

                $newContent = "{if isset(\$manufacturer) && is_array(\$manufacturer)}{prettyblocks_zone zone_name='manufacturer\$manufacturer.id'}{/if}{if isset(\$supplier) && is_array(\$supplier)}{prettyblocks_zone zone_name='supplier\$supplier.id'}{/if}{if isset(\$category) && is_array(\$category)}{prettyblocks_zone zone_name='category\$category.id'}{/if}{hook h=\"displayWrapperBottom\"}";
                if ((bool) self::stringExistsInFileContent($newContent, $layoutContent) === false) {
                    $modifiedContent = preg_replace('/\{hook h="displayWrapperBottom"\}/', $newContent, $layoutContent);

                    file_put_contents($layoutPath, $modifiedContent);
                }

                $newContent = "{if isset(\$manufacturer) && is_array(\$manufacturer)}{prettyblocks_zone zone_name='manufacturer\$manufacturer.id'}{/if}{if isset(\$supplier) && is_array(\$supplier)}{prettyblocks_zone zone_name='supplier\$supplier.id'}{/if}{if isset(\$category) && is_array(\$category)}{prettyblocks_zone zone_name='category\$category.id'}{/if}{hook h=\"displayWrapperTop\"}";
                if ((bool) self::stringExistsInFileContent($newContent, $layoutContent) === false) {
                    $modifiedContent = preg_replace('/\{hook h="displayWrapperTop"\}/', $newContent, $layoutContent);

                    $put = file_put_contents($layoutPath, $modifiedContent);
                }
            }
        }
        Tools::clearSmartyCache();
    }

    public static function addHookToManufacturerTpl()
    {
        // Chemin vers le fichier manufacturer.tpl
        $manufacturerTplPath = _PS_ALL_THEMES_DIR_ . Context::getContext()->shop->theme->getName() . '/templates/catalog/listing/manufacturer.tpl';

        if (file_exists($manufacturerTplPath)) {
            $manufacturerTplContent = file_get_contents($manufacturerTplPath);

            $newContent = "{prettyblocks_zone zone_name='supplier\$supplier.id'}\n{block name='product_list'}";
            if ((bool) self::stringExistsInFileContent($newContent, $manufacturerTplContent) === false) {
                $modifiedContent = preg_replace('/\{block name="product_list"\}/', $newContent, $manufacturerTplContent);
                file_put_contents($manufacturerTplPath, $modifiedContent);

                Tools::clearSmartyCache();
            }
        }
    }

    /**
    * Get IP address for current visitor
    * @return string IP address
    */
    public static function getIpAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
    * Get all maintenance IP address
    * @return array of IP
    */
    public static function getMaintenanceIpAddress()
    {
        if (!Configuration::get('PS_MAINTENANCE_IP')) {
            return ['::1'];
        }
        $maintenance_ip = explode(
            ',',
            Configuration::get('PS_MAINTENANCE_IP')
        );
        return $maintenance_ip;
    }

    /**
    * If IP address is on maintenance
    * @return bool
    */
    public static function isMaintenanceIpAddress()
    {
        if (in_array(self::getIpAddress(), self::getMaintenanceIpAddress())) {
            return true;
        }
        return false;
    }

    public static function isEmployee(): bool
    {
        return !empty((new Cookie('psAdmin'))->id_employee);
    }

    public static function getAllProducts($shopId, $langId, $start = null, $limit = null, $orderBy = null, $orderWay = null)
    {
        $cacheId = 'EverblockTools::getAllProducts_' . (int) $shopId . '_' . $langId;
        if (!Cache::isStored($cacheId)) {
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
            Cache::store($cacheId, $products);
        }

        return Cache::retrieve($cacheId);
    }

    public static function getAllManufacturers($shopId, $langId)
    {
        $cacheId = 'EverblockTools::getAllManufacturers_' . (int) $shopId . '_' . $langId;
        
        if (!Cache::isStored($cacheId)) {
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
                    $manufacturers[$row['id_manufacturer']] = (int)$row['id_manufacturer'] . ' - ' . $row['name'];
                }
            }

            Cache::store($cacheId, $manufacturers);
        }

        return Cache::retrieve($cacheId);
    }

    public static function getAllSuppliers($shopId, $langId)
    {
        $cacheId = 'EverblockTools::getAllSuppliers_' . (int) $shopId . '_' . $langId;
        
        if (!Cache::isStored($cacheId)) {
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
                    $suppliers[$row['id_supplier']] = (int)$row['id_supplier'] . ' - ' . $row['name'];
                }
            }

            Cache::store($cacheId, $suppliers);
        }

        return Cache::retrieve($cacheId);
    }

    public static function getProductIdsBySupplier($supplierId, $start = null, $limit = null, $orderBy = null, $orderWay = null)
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

    public static function getProductIdsByManufacturer($manufacturerId, $start = null, $limit = null, $orderBy = null, $orderWay = null)
    {
        $sql = new DbQuery();
        $sql->select('id_product');
        $sql->from('product');
        $sql->where('id_manufacturer = ' . (int) $manufacturerId);
        if ($limit) {
            $sql->limit($limit);
        }

        $productIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return array_column($productIds, 'id_product');
    }

    public static function addLazyLoadToImages($text)
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

    public static function obfuscateText($text)
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

    public static function generateLoremIpsum()
    {
        $lloremParagraphNum = (int) Configuration::get('EVERPSCSS_P_LLOREM_NUMBER');
        if ($lloremParagraphNum <= 0) {
            $lloremParagraphNum = 5;
        }
        $lloremSentencesNum = (int) Configuration::get('EVERPSCSS_S_LLOREM_NUMBER');
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
        return implode("\n\n", $paragraphs);
    }

    public static function checkAndFixDatabase()
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
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
            'date_start' => 'DATETIME DEFAULT NULL',
            'date_end' => 'DATETIME DEFAULT NULL',
            'active' => 'int(10) unsigned NOT NULL',
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
            'id_shop' => 'int(10) unsigned NOT NULL',
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
    }

    public static function everPresentProducts($result)
    {
        $products = [];
        if (!empty($result)) {
            $assembler = new ProductAssembler(Context::getContext());
            $presenterFactory = new ProductPresenterFactory(Context::getContext());
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    Context::getContext()->link
                ),
                Context::getContext()->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                Context::getContext()->getTranslator()
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
                    'id_lang' => Context::getContext()->language->id,
                    'id_shop' => Context::getContext()->shop->id,
                ];
                $pproduct = $assembler->assembleProduct($rawProduct);
                if (Product::checkAccessStatic((int) $productId, false)) {
                    $products[] = $presenter->present(
                        $presentationSettings,
                        $pproduct,
                        Context::getContext()->language
                    );
                }
            }
        }
        return $products;
    }

    public static function dropUnusedLangs()
    {
        $postErrors = [];
        $querySuccess = [];
        $pstable = [
            'category_lang',
            'product_lang',
            'image_lang',
            'cms_lang',
            'meta_lang',
            'manufacturer_lang',
            'supplier_lang',
            'group_lang',
            'gender_lang',
            'feature_lang',
            'feature_value_lang',
            'customization_field_lang',
            'country_lang',
            'cart_rule_lang',
            'carrier_lang',
            'attachment_lang',
            'attribute_lang',
            'attribute_group_lang',
        ];
        foreach ($pstable as $table) {
            $sql = 'DELETE FROM ' . _DB_PREFIX_ . pSQL($table) . '
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
    public static function exportModuleTablesSQL()
    {
        // Liste des tables de module sans préfixe
        $tables = [
            _DB_PREFIX_ . 'everblock',
            _DB_PREFIX_ . 'everblock_lang',
            _DB_PREFIX_ . 'everblock_shortcode',
            _DB_PREFIX_ . 'everblock_shortcode_lang'
        ];

        // Valider et nettoyer les noms de table (vous pouvez ajouter d'autres vérifications ici)
        $validTables = array();
        foreach ($tables as $table) {
            $table = trim($table);
            if (!empty($table)) {
                if (self::ifTableExists($table)) {
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
        $sqlData = "";
        foreach ($validTables as $tableName) {
            // Obtenir la structure de la table (inclut les contraintes et les index)
            $createTableSql = self::getTableStructure($tableName);

            // Ajoutez DROP TABLE
            $sqlData .= "DROP TABLE IF EXISTS `$tableName`;\n";

            // Ajoutez CREATE TABLE avec la structure
            $sqlData .= "$createTableSql;\n";

            // Exécutez la requête SQL pour extraire les données de la table
            $sql = "SELECT * FROM `$tableName`";
            $result = $db->executeS($sql);

            if ($result) {
                // Ajoutez INSERT INTO
                foreach ($result as $row) {
                    $sqlData .= "INSERT INTO `$tableName` (";
                    $escapedKeys = array_map(array(Db::getInstance(), 'escape'), array_keys($row));
                    $escapedKeys = array_map(function($key) {
                        return "`$key`";
                    }, $escapedKeys); // Ajout des backticks aux noms de colonnes
                    $sqlData .= implode(',', $escapedKeys);
                    $sqlData .= ") VALUES (";

                    // Échappez et formatez correctement les valeurs
                    $escapedValues = array();
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

        // Chemin du fichier de sauvegarde
        $moduleDir = _PS_MODULE_DIR_ . 'everblock';
        $filePath = "$moduleDir/dump.sql";

        // Enregistrez les données dans un fichier
        if (file_put_contents($filePath, $sqlData)) {
            return true;
        }

        return false;
    }

    /**
     * Récupère la structure d'une table dans la base de données.
     *
     * @param string $tableName Nom de la table.
     * @return string|null Structure de la table en SQL, ou null en cas d'erreur.
     */
    protected static function getTableStructure($tableName)
    {
        $db = Db::getInstance();
        $sql = "SHOW CREATE TABLE $tableName";
        $result = $db->executeS($sql);

        if ($result && isset($result[0]['Create Table'])) {
            return $result[0]['Create Table'];
        }

        return null;
    }

    /**
     * Vérifie si une table existe dans la base de données.
     *
     * @param string $tableName Nom de la table à vérifier.
     * @return bool True si la table existe, sinon False.
     */
    protected static function ifTableExists($tableName)
    {
        $db = Db::getInstance();

        $result = $db->executeS("SHOW TABLES LIKE '" . pSQL($tableName) . "'");

        return !empty($result);
    }

    /**
     * Teste si le fichier SQL de sauvegarde existe et restaure les tables et données si possible.
     *
     * @return bool True si la restauration réussie, sinon False.
     */
    public static function restoreModuleTablesFromBackup()
    {
        // Chemin du fichier de sauvegarde
        $moduleDir = _PS_MODULE_DIR_ . 'everblock';
        $filePath = "$moduleDir/dump.sql";

        if (file_exists($filePath)) {
            try {
                // Exécute les requêtes SQL du fichier de sauvegarde
                $sqlContent = file_get_contents($filePath);
                $db = Db::getInstance();
                $queries = preg_split("/;\n/", $sqlContent);
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        $db->execute($query);
                    }
                }

                // Log de la réussite de la restauration dans PrestaShop Logger
                PrestaShopLogger::addLog("Tables and data of Ever Block module have been successfully restored from backup.", 1);

                // Retourne True en cas de succès
                return true;
            } catch (Exception $e) {
                // En cas d'erreur, log l'erreur dans PrestaShop Logger
                PrestaShopLogger::addLog("Error during Ever Block module tables restoration: " . $e->getMessage(), 3);

                // Retourne False en cas d'échec
                return false;
            }
        }

        // Retourne False si le fichier de sauvegarde n'existe pas
        return false;
    }
}
