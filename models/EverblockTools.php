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

    public static function getProductIdsBySupplier($supplierId, $limit = false)
    {
        $sql = new DbQuery();
        $sql->select('id_product');
        $sql->from('product');
        $sql->where('id_supplier = ' . (int) $supplierId);
        if ($limit) {
            $sql->limit($limit);
        }

        $productIds = Db::getInstance()->executeS($sql);

        return array_column($productIds, 'id_product');
    }

    public static function getProductIdsByManufacturer($manufacturerId, $limit = false)
    {
        $sql = new DbQuery();
        $sql->select('id_product');
        $sql->from('product');
        $sql->where('id_manufacturer = ' . (int) $manufacturerId);
        if ($limit) {
            $sql->limit($limit);
        }

        $productIds = Db::getInstance()->executeS($sql);

        return array_column($productIds, 'id_product');
    }

    public static function getStoreLocatorData()
    {
        $cacheId = 'store_locator_data_' . (int) Context::getContext()->shop->id;

        if (!Cache::isStored($cacheId)) {
            $stores = Store::getStores((int) Context::getContext()->language->id, true, false, (int) Context::getContext()->shop->id);
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
            $markers = [];

            foreach ($stores as $store) {
                $coordinates = self::getStoreCoordinates($store['id_store']);

                if ($coordinates !== null) {
                    $marker = [
                        'lat' => $coordinates['latitude'],
                        'lng' => $coordinates['longitude'],
                        'title' => $store['name'], // Nom du magasin
                    ];

                    $markers[] = $marker;
                }
            }
            if ((bool) Configuration::get('EVERBLOCK_USE_GMAP') === true) {
                $mapCode = self::generateGoogleMapScript($markers);
            } else {
                $mapCode = self::generateOsmScript($markers);
            }
            $context = Context::getContext();
            $smarty = $context->smarty;
            $module = Module::getInstanceByName('everblock');
            $templatePath = $module->getLocalPath() . 'views/templates/hook/storelocator.tpl';
            $smarty->assign([
                'mapCode' => $mapCode,
                'everblock_stores' => $stores,
            ]);
            $storeLocatorContent = $smarty->fetch($templatePath);
            return $storeLocatorContent;
        }
    }

    private static function generateOsmScript($markers)
    {
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


    private static function generateGoogleMapScript($markers)
    {
        // Code pour générer une carte Google Maps ici
        // Utilisez la documentation de Google Maps JavaScript API pour cela
        // Assurez-vous d'inclure le code nécessaire pour initialiser la carte Google Maps
        // et ajouter des marqueurs avec les données de $markers

        // Par exemple, voici comment vous pouvez initialiser une carte Google Maps
        // et ajouter des marqueurs (veuillez remplacer avec vos propres clés API Google Maps) :
        $apiKey = Configuration::get('EVERBLOCK_GMAP_KEY');
        $googleMapCode = '
        <script src="https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '&callback=initMap" async defer></script>
        <script>
            var map;

            function initMap() {
                map = new google.maps.Map(document.getElementById("everblock-storelocator"), {
                    center: { lat: ' . $markers[0]['lat'] . ', lng: ' . $markers[0]['lng'] . ' },
                    zoom: 13
                });

                var markers = ' . json_encode($markers) . ';

                markers.forEach(function(marker) {
                    new google.maps.Marker({
                        position: { lat: marker.lat, lng: marker.lng },
                        map: map,
                        title: marker.title
                    });
                });

                // Ajustez la hauteur du conteneur de la carte ici
                document.getElementById("everblock-storelocator").style.height = "500px";
            }
        </script>';
        
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

            $newContent = "{widget name='custom_cms_widget' cms_id=\$cms.id}\n{block name='cms_content'}";
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
            
            $newContent = "{widget name='custom_cms_category_widget' cms_category_id=\$cms_category.id}\n{block name='page_content'}";
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
            
            // Ajouter le widget pour le hook displayDescriptionShortProductId
            $newContent = "{widget name='custom_product_description_short_widget' product_id=\$product.id}\n{block name='product_description_short'}";
            if ((bool) self::stringExistsInFileContent($newContent, $productTplContent) === false) {
                $modifiedContent = str_replace("{block name='product_description_short'}", $newContent, $productTplContent);
                file_put_contents($productTplPath, $modifiedContent);
            }

            // Ajouter le widget pour le hook displayDescriptionProductId
            $productTplContent = file_get_contents($productTplPath);
            $newContent = "{widget name='custom_product_description_widget' product_id=\$product.id}\n{block name='product_description'}";
            if ((bool) self::stringExistsInFileContent($newContent, $productTplContent) === false) {
                $modifiedContent = str_replace("{block name='product_description'}", $newContent, $productTplContent);
                file_put_contents($productTplPath, $modifiedContent);
            }

            // Ajouter le widget pour le hook displayReassuranceProductId
            $productTplContent = file_get_contents($productTplPath);
            $newContent = "{widget name='custom_product_reassurance_widget' product_id=\$product.id}\n{block name='hook_display_reassurance'}";
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

                // Ajouter le widget pour le hook displayWrapperBottomCategoryId
                $newContent = "{widget name='custom_wrapper_bottom_category_widget' category_id=\$category.id}\n{if isset(\$manufacturer) && is_array(\$manufacturer)}{block name='hook_wrapper_bottom_manufacturer_id'}{hook h=\"displayWrapperBottomManufacturerId`\$manufacturer.id`\"}{/block}{/if}{if isset(\$supplier) && is_array(\$supplier)}{block name='hook_wrapper_bottom_supplier_id'}{hook h=\"displayWrapperBottomSupplierId`\$supplier.id`\"}{/block}{/if}{widget name='custom_content_wrapper_bottom_widget'}";
                if ((bool) self::stringExistsInFileContent($newContent, $layoutContent) === false) {
                    $modifiedContent = preg_replace('/\{hook h="displayContentWrapperBottom"\}/', $newContent, $layoutContent);

                    file_put_contents($layoutPath, $modifiedContent);
                }
            }
        }
    }

    public static function addHookToManufacturerTpl()
    {
        // Chemin vers le fichier manufacturer.tpl
        $manufacturerTplPath = _PS_ALL_THEMES_DIR_ . Context::getContext()->shop->theme->getName() . '/templates/catalog/listing/manufacturer.tpl';

        if (file_exists($manufacturerTplPath)) {
            $manufacturerTplContent = file_get_contents($manufacturerTplPath);

            // Ajouter le widget pour le hook displaySupplierId
            $newContent = "{widget name='custom_supplier_id_widget' supplier_id=\$supplier.id}\n{block name='product_list'}";

            if ((bool) self::stringExistsInFileContent($newContent, $manufacturerTplContent) === false) {
                $modifiedContent = preg_replace('/\{block name="product_list"\}/', $newContent, $manufacturerTplContent);
                file_put_contents($manufacturerTplPath, $modifiedContent);
            }
        }
    }
}
