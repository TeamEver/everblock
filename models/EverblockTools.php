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

class EverblockTools extends ObjectModel
{
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
            // die(var_dump($elementSearch));
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
     * @return array of success/error txts
    */
    public static function migrateUrls($oldUrl, $newUrl, $id_shop)
    {
        $postErrors = [];
        $querySuccess = [];
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
        if ((bool) Configuration::get('EVERPSCSS_CACHE') === true) {
            Tools::clearAllCache();
        }
        return [
            'postErrors' => $postErrors,
            'querySuccess' => $querySuccess,
        ];
    }

    public static function detectVideoSite($url)
    {
        $patterns = [
            'youtube' => '/^(?:https?:\/\/)?(?:www\.)?youtu(?:be\.com\/watch\?v=|\.be\/)([\w\-\_]+)(?:\S+)?$/',
            'vimeo' => '/^(?:https?:\/\/)?(?:www\.)?vimeo\.com\/([0-9]+)$/i',
            'dailymotion' => '/^(?:https?:\/\/)?(?:www\.)?dailymotion\.com\/video\/([a-z0-9]+)$/i',
            'vidyard' => '/^(?:https?:\/\/)?(?:embed\.)?vidyard.com\/(?:watch\/)?([a-zA-Z0-9\-\_]+)$/'
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                switch ($key) {
                    case 'youtube':
                        return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $matches[1] . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                    case 'vimeo':
                        return '<iframe src="https://player.vimeo.com/video/' . $matches[1] . '?color=ffffff&title=0&byline=0&portrait=0" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                    case 'dailymotion':
                        return '<iframe frameborder="0" width="480" height="270" src="//www.dailymotion.com/embed/video/' . $matches[1] . '" allowfullscreen></iframe>';
                    case 'vidyard':
                        return '<iframe src="https://play.vidyard.com/' . $matches[1] . '.html?v=3.1.1&type=lightbox" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
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

            $products = array();

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

            $manufacturers = array();

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

            $suppliers = array();

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
        $db = Db::getInstance();
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
}
