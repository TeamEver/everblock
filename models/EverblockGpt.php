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

class EverblockGpt extends ObjectModel
{
    private $API_KEY = '';
    private $textURL = 'https://api.openai.com/v1/completions';
    private $imageURL =  'https://api.openai.com/v1/images/generations';

    public $curl;       // create cURL object
    public $data = [];  // data request array

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function initialize($requestType = 'text' || 'image')
    {
        $this->curl = curl_init();
        $this->API_KEY = Configuration::get('EVERGPT_API_KEY');
        if ($requestType === 'image')
            curl_setopt($this->curl, CURLOPT_URL, $this->imageURL);
        if ($requestType === 'text')
            curl_setopt($this->curl, CURLOPT_URL, $this->textURL);

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_POST, true);

        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer $this->API_KEY"
        );

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
    }

    // returns text
    public function createTextRequest($prompt, $model = 'gpt-3.5-turbo-instruct', $temperature = 0.5, $maxTokens = 1000)
    {
        curl_reset($this->curl);
        $this->initialize('text');

        $this->data['model'] = $model;
        $this->data['prompt'] = $prompt;
        $this->data['temperature'] = $temperature;
        $this->data['max_tokens'] = $maxTokens;

        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false); // Désactive la vérification du certificat SSL

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($this->data));

        $response = curl_exec($this->curl);

        if ($response === false) {
            $error_message = curl_error($this->curl);
            curl_close($this->curl);
            throw new Exception("cURL Error: $error_message");
        }

        $response_data = json_decode($response, true);

        if (isset($response_data['error'])) {
            // You exceeded your current quota, please check your plan and billing details.
            throw new Exception("API Error: {$response_data['error']['message']}");
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
            $prompt .= 'il est disponible dans la catégorie "' . $product_category->name . '". ';
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
            $prompt .= 'Fais cela dans la langue ' . $language->name . ' . ';
            return $prompt;
        }
    }
}
