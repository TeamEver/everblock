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

class EverblockGpt extends ObjectModel
{
    private $API_KEY = '';
    private $textURL = 'https://api.openai.com/v1/completions';
    private $imageURL =  'https://api.openai.com/v1/images/generations';

    public $curl;
    public $data = [];

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function initialize($requestType = 'text' || 'image')
    {
        $this->curl = curl_init();
        $this->API_KEY = Configuration::get('EVERGPT_API_KEY');
        if ($requestType === 'image') {
            curl_setopt($this->curl, CURLOPT_URL, $this->imageURL);
        }
        if ($requestType === 'text') {
            curl_setopt($this->curl, CURLOPT_URL, $this->textURL);
        }
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_POST, true);
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->API_KEY,
        ];
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
    }

    // returns text
    public function createTextRequest($prompt, $model = 'gpt-4', $temperature = 0.5, $maxTokens = 1000)
    {
        curl_reset($this->curl);
        $this->initialize('text');
        $this->data['model'] = $model;
        $this->data['prompt'] = $prompt;
        $this->data['temperature'] = $temperature;
        $this->data['max_tokens'] = $maxTokens;
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($this->data));
        $response = curl_exec($this->curl);
        if ($response === false) {
            $error_message = curl_error($this->curl);
            curl_close($this->curl);
            throw new Exception('cURL Error: $error_message');
        }
        $response_data = json_decode($response, true);
        if (isset($response_data['error'])) {
            // You exceeded your current quota, please check your plan and billing details.
            throw new Exception('API Error: ' . $response_data['error']['message']);
        }
        return $response_data['choices'][0]['text'] ?? null; // return text or -1 if error
    }

    // returns URL with the image
    public function generateImage($prompt, $imageSize = '512x512', $numberOfImages = 1)
    {
        curl_reset($this->curl);
        $this->initialize('image');
        $this->data['prompt'] = $prompt;
        $this->data['n'] = $numberOfImages;
        $this->data['size'] = $imageSize;
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($this->data));
        $response = curl_exec($this->curl);
        $response = json_decode($response, true);
        return $response['data'][0]['url'] ?? -1; //return the first url or -1 if error
    }

    public static function getObjectPrompt($object, $objectId, $langId, $shopId)
    {
        if (!$object instanceof ObjectModel) {
            return;
        }
        $className = get_class($object);
        switch ($className) {
            case 'Product':
                $prompt = static::getProductPrompt(
                    (int) $objectId,
                    (int) $langId,
                    (int) $shopId
                );
                break;

            case 'Category':
                $prompt = static::getCategoryPrompt(
                    (int) $objectId,
                    (int) $langId,
                    (int) $shopId
                );
                break;

            case 'Manufacturer':
                $prompt = static::getManufacturerPrompt(
                    (int) $objectId,
                    (int) $langId,
                    (int) $shopId
                );
                break;

            case 'Supplier':
                $prompt = static::getSupplierPrompt(
                    (int) $objectId,
                    (int) $langId,
                    (int) $shopId
                );
                break;

            case 'EverBlockClass':
                $prompt = static::getEverblockClassPrompt(
                    (int) $objectId,
                    (int) $langId,
                    (int) $shopId
                );
                break;

            case 'EverblockTabsClass':
                // As tab is on product page, let's use product prompt
                $prompt = static::getProductPrompt(
                    (int) $objectId,
                    (int) $langId,
                    (int) $shopId
                );
                break;

            default:
                // code pour le cas par défaut...
                break;
        }
        if (isset($prompt) && $prompt) {
            return $prompt;
        }
    }

    public static function getProductPrompt($productId, $langId, $shopId)
    {
        $product = new Product(
            (int) $productId,
            false,
            (int) $langId,
            (int) $shopId
        );
        if (Validate::isLoadedObject($product)) {
            // Récupérer les données du produit
            $link = new Link();
            $language = new Language(
                (int) $langId
            );
            $product_name = $product->name;
            $product_description = strip_tags($product->description);
            $product_price = Tools::displayPrice($product->getPrice(), new Currency(Configuration::get('PS_CURRENCY_DEFAULT')));
            $product_category = new Category(
                $product->id_category_default,
                (int) $langId,
                (int) $shopId
            );
            $product_manufacturer = new Manufacturer($product->id_manufacturer);
            $product_sku = $product->reference;
            $product_url = $link->getProductLink($product);
            // Construire une requête pour ChatGPT
            $prompt = 'Génère un contenu HTML orienté SEO pour le produit ' . $product_name . ' pour la boutique ' . Configuration::get('PS_SHOP_NAME. ');
            if (!empty($product_manufacturer->name)) {
                $prompt .= 'Le produit est de la marque ' . $product_manufacturer->name . ', ';
            }
            $prompt .= 'il est disponible dans la catégorie ' . $product_category->name . ' .';
            $prompt .= 'Son prix est de ' . $product_price . '. ';
            if (!empty($product_description)) {
                $prompt .= 'Description : ' . $product_description . '. ';
            }
            if (!empty($product_sku)) {
                $prompt .= 'Le SKU du produit est ' . $product_sku . '. ';
            }
            $prompt .= 'Lien vers le produit : ' . $product_url . '. ';
            $prompt .= 'Assure-toi que le contenu soit optimisé pour les moteurs de recherche, utilise du code HTML pour cela. ';
            $prompt .= 'N\'utilise pas de balise HTML de niveau de titre, utilise en revanche une stratégie de mots-clés pertinente afin de rendre le produit plus visible sur les moteurs de recherche. ';
            $prompt .= 'Ne crée pas de balise div. Fais des liens vers la catégorie du produit sans oublier de mettre un attribut title. ';
            $prompt .= 'Utilise uniquement des termes bienveillant, ne parle pas de la référence. ';
            $prompt .= 'Précise les avantages à faire l\'acquisition de ce produit. ';
            $prompt .= 'Ne fais pas de lien vers la fiche produit, on est déjà dessus. ';
            $prompt .= self::getPromptRecommendations();
            $prompt .= 'Fais cela dans la langue ' . $language->name . ' . ';
            return $prompt;
        }
    }

    public static function getCategoryPrompt($categoryId, $langId, $shopId)
    {
        $category = new Category(
            (int) $categoryId,
            (int) $langId,
            (int) $shopId
        );
        if (!Validate::isLoadedObject($category)) {
            return;
        }
        $categoryName = $category->name;
        $categoryDescription = strip_tags($category->description);
        // Construire la directive pour ChatGPT
        $prompt = 'ChatGPT, rédige une description attrayante et SEO-friendly pour la catégorie ' . $categoryName . ' de notre boutique en ligne. ';
        $prompt .= 'Inclus les éléments suivants dans la description: ';
        if (!empty($categoryDescription)) {
            $prompt .= 'une brève présentation basée sur la description actuelle - ' . $categoryDescription . ', ';
        }
        $prompt .= 'les avantages d’explorer cette catégorie, ';
        $prompt .= 'et des suggestions sur comment les produits de cette catégorie peuvent répondre aux besoins des clients. ';
        $prompt .= 'Utilise un langage accueillant et engageant, adapté au SEO. ';
        $prompt .= 'Assure-toi que le contenu soit unique et captivant, sans copier les descriptions existantes. ';
        $prompt .= 'Le but est de maximiser l’intérêt et l’engagement des visiteurs de notre boutique en ligne. ';
        $prompt .= self::getPromptRecommendations();
        $prompt .= 'La langue de rédaction est le ' . (new Language((int) $langId))->name . '. ';
        return $prompt;
    }

    public static function getManufacturerPrompt($manufacturerId, $langId, $shopId)
    {
        $manufacturer = new Manufacturer(
            (int) $manufacturerId,
            (int) $langId,
            (int) $shopId
        );
        if (!Validate::isLoadedObject($manufacturer)) {
            return;
        }
        $manufacturerName = $manufacturer->name;
        $manufacturerDescription = strip_tags($manufacturer->description);
        $prompt = 'ChatGPT, je souhaite que tu rédiges une description captivante et optimisée pour le SEO pour le fabricant ' . $manufacturerName . '. ';
        $prompt .= 'La description devrait inclure: ';
        if (!empty($manufacturerDescription)) {
            $prompt .= 'un résumé basé sur leur description actuelle - ' . $manufacturerDescription . ', ';
        }
        $prompt .= 'les points forts et l’histoire du fabricant, ';
        $prompt .= 'comment leurs produits se distinguent sur le marché, ';
        $prompt .= 'et pourquoi les clients devraient choisir leurs produits. ';
        $prompt .= 'La description doit être informative, engageante et fidèle à la marque du fabricant. ';
        $prompt .= 'Assure-toi que le contenu soit unique et qu\'il mette en valeur le fabricant de manière positive. ';
        $prompt .= self::getPromptRecommendations();
        $prompt .= 'La langue de rédaction est le ' . (new Language((int) $langId))->name . '. ';

        return $prompt;
    }

    public static function getSupplierPrompt($supplierId, $langId, $shopId)
    {
        $supplier = new Supplier(
            (int) $supplierId,
            (int) $langId,
            (int) $shopId
        );
        if (!Validate::isLoadedObject($supplier)) {
            return;
        }
        $supplierName = $supplier->name;
        $supplierDescription = strip_tags($supplier->description);
        // Construire la directive pour ChatGPT
        $prompt = 'ChatGPT, rédige une description attrayante et SEO-friendly pour le fournisseur ' . $supplierName . ' pour notre boutique en ligne. ';
        $prompt .= 'La description devrait inclure: ';
        if (!empty($supplierDescription)) {
            $prompt .= 'une introduction basée sur leur description actuelle - ' . $supplierDescription . ', ';
        }
        $prompt .= 'les atouts et les spécificités du fournisseur, ';
        $prompt .= 'comment leurs produits ou services se différencient dans le marché, ';
        $prompt .= 'et l’importance de choisir ce fournisseur pour nos clients. ';
        $prompt .= 'Veille à ce que le contenu soit informatif, engageant et reflète fidèlement l’image du fournisseur. ';
        $prompt .= 'Il est important que la description soit unique et valorise le fournisseur de manière positive. ';
        $prompt .= self::getPromptRecommendations();
        $prompt .= 'La rédaction doit se faire en ' . (new Language((int) $langId))->name . '. ';
        return $prompt;
    }

    public static function getEverblockClassPrompt($objId, $langId, $shopId)
    {
        $everblock = new EverblockClass(
            (int) $objId,
            (int) $langId,
            (int) $shopId
        );
        if (!Validate::isLoadedObject($everblock)) {
            return;
        }
        $hook = new Hook(
            (int) $everblock->id_hook
        );
        $infos = [
            'name' => $everblock->name,
            'only_home' => $everblock->only_home,
            'only_category' => $everblock->only_category,
            'only_category_product' => $everblock->only_category_product,
            'only_manufacturer' => $everblock->only_manufacturer,
            'only_supplier' => $everblock->only_supplier,
            'only_cms_category' => $everblock->only_cms_category,
            'obfuscate_link' => $everblock->obfuscate_link,
            'add_container' => $everblock->add_container,
            'lazyload' => $everblock->lazyload,
            'hook' => $hook->name,
            'device' => $everblock->device,
            'categories' => self::loadDetails(json_decode($everblock->categories), 'Category', $langId, $shopId),
            'manufacturers' => self::loadDetails(json_decode($everblock->manufacturers), 'Manufacturer', $langId, $shopId),
            'suppliers' => self::loadDetails(json_decode($everblock->suppliers), 'Supplier', $langId, $shopId),
            'cms_categories' => self::loadDetails(json_decode($everblock->cms_categories), 'CmsCategory', $langId, $shopId),
        ];
        // Construire la directive pour ChatGPT
        $prompt = 'Crée un contenu HTML pour mon bloc HTML sur Prestashop ' . $infos['name'] . '. ';
        if ($infos['hook']) {
            $prompt .= 'Ce bloc sera affiché sur la position Prestashop intitulée ' . $infos['hook'] . '. ';
        }
        if (!empty($infos['categories'])) {
            $prompt .= 'Il est associé aux catégories suivantes : ' . implode(', ', $infos['categories']) . '. ';
        }
        if (!empty($infos['manufacturers'])) {
            $prompt .= 'Il est associé aux marques suivantes : ' . implode(', ', $infos['manufacturers']) . '. ';
        }
        if (!empty($infos['suppliers'])) {
            $prompt .= 'Il est associé aux fournisseurs suivants : ' . implode(', ', $infos['suppliers']) . '. ';
        }
        if ($infos['only_home']) {
            $prompt .= 'Ce bloc sera affiché uniquement sur la page d\'accueil. ';
        }
        $prompt .= self::getPromptRecommendations();
        $prompt .= 'La langue de rédaction est le ' . (new Language((int) $langId))->name . '. ';
        return $prompt;
    }

    private function loadDetails($ids, $type, $langId, $shopId) {
        $details = [];
        if (!$ids) {
            return [];
        }
        foreach ($ids as $id) {
            switch ($type) {
                case 'Category':
                    $obj = new Category($id, $langId, $shopId);
                    break;
                case 'Manufacturer':
                    $obj = new Manufacturer($id, $langId, $shopId);
                    break;
                case 'Supplier':
                    $obj = new Supplier($id, $langId, $shopId);
                    break;
                case 'CmsCategory':
                    $obj = new CmsCategory($id, $langId, $shopId);
                    break;
                // autres types si nécessaire
            }
            if (Validate::isLoadedObject($obj)) {
                if (isset($obj->name) && !empty($obj->name)) {
                    $details[] = $obj->name;
                }
            }
        }
        return $details;
    }

    public function saveObjContent($object, $objectId, $shopId, $content)
    {
        if (!$object instanceof ObjectModel) {
            return;
        }
        // Content must be multilingual field
        if (!is_array($content)) {
            $content = [
                Configuration::get('PS_LANG_DEFAULT') => $content,
            ];
        }
        $className = get_class($object);
        switch ($className) {
            case 'Product':
            case 'Category':
            case 'Manufacturer':
            case 'Supplier':
                $obj = new $className($objectId, false, false, $shopId);
                $obj->description = $content ?? '';
                break;

            case 'EverBlockClass'::
                $obj = new $className($objectId, false, $shopId);
                $obj->content = $content ?? '';
                break;

            case 'EverblockTabsClass':
                $obj->$className::getByIdProduct($objectId, $shopId);
                $obj->content = $content ?? '';
                break;

            default:
                // code pour le cas par défaut...
                return;
        }
        try {
            if (Validate::isLoadedObject($obj)) {
                $obj->save();
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'Everblock GPT : cannot save object ' . $className . ' : ' . $e->getMessage()
            );
        }
    }

    private function getPromptRecommendations()
    {
        $recommendations = 'Le contenu HTML doit être optimisé, propre et conforme aux standards actuels du web. ';
        $recommendations .= 'Le contenu doit être attrayant et inciter à l\'acte d\'achat ou à visiter des fiches produits sur la boutique. ';
        $recommendations .= 'Il doit aussi être adapté aux différents appareils et réactif. ';
        $recommendations .= 'Il ne doit pas contenir de balise h1, toute autre balise de titre ne doit pas être orpheline. Donc si tu places une balise h2, il doit forcément en avoir une autre plus loin. ';
        $recommendations .= 'Inclus des éléments de design spécifiques si nécessaire. ';
        $recommendations .= 'Ne parle jamais de chatGPT. ';
        $recommendations .= 'Ne génère pas d’image. ';
        $recommendations .= 'Tout lien doit avoir un attribut HTML title. ';
        $recommendations .= 'Le nom de la boutique est ' . Configuration::get('PS_SHOP_NAME') . ' ';
        $recommendations .= 'L\'URL de la boutique est ' . Tools::getHttpHost(true) . __PS_BASE_URI__ . ' ';
        $recommendations .= 'Pense à visiter le site afin de récupérer des informations pertinentes pour la generation du contenu. ';
        $recommendations .= 'Veille à générer les liens afin qu\'ils correspondent à ceux présents sur la boutique, pas de 404. ';
        $recommendations .= 'N\'ajoute que des liens vers les pages produits, catégories, marques ou fournisseurs. ';
        return $recommendations;
    }
}
