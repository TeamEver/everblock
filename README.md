# Ever Block - Free HTML Block Module for PrestaShop
![Ever Block logo](logo.png)

Ever Block lets PrestaShop 1.7, 8 and 9 users add unlimited custom HTML blocks anywhere using hooks and shortcodes.

Works seamlessly with PrestaShop hooks and is fully compatible with PrestaShop 1.7, 8 and 9.

## Key Features
- Unlimited HTML blocks in any PrestaShop hook
- Shortcodes for products, forms, categories and more
- Compatible with PrestaShop 1.7, 8 & 9
- Works with Pretty Blocks page builder
- Supports QCD ACF custom fields module
- Built-in cache and obfuscation tools for SEO
- Easily create modals and extra order steps


## PrestaShop free HTML block module
This free module allows you to create unlimited HTML blocks on your shop

[You can make a donation to support the development of free modules by clicking on this link](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE)

## PrestaShop 1.7, 8 & 9 hooks 
Dev documentation show every native PrestaShop hook :
[PrestaShop 1.7 hook list](https://devdocs.prestashop.com/1.7/modules/concepts/hooks/)
Please check ps_hook table on your database to see every available hook on your shop. Only display hooks are used with this module

## Pretty Blocks compatibility
This module is compatible with the Pretty Blocks page builder. [Find this free module here.](https://prettyblocks.io/)


## QCD ACF compatibility
This module is compatible with the QCD ACF module developed by the 410 Gone agency. The QCD ACF module allows you to add custom fields to products, categories, brands, suppliers, characteristics, etc. [You can contact the 410 Gone agency from their website to obtain the QCD ACF module.](https://www.410-gone.fr/e-commerce/prestashop.html)

## Smarty Variables

- `$currency.name`: The name of the currency (euro, dollar, pound sterling, etc.).
- `$currency.iso_code`: The ISO code of the currency (like EUR for the euro).
- `$currency.sign`: The acronym of the currency displayed (e.g., € or $).
- `$currency.iso_code_num`: The ISO code number of this currency (like 978 for the euro).
- `$shop.name`: Shop name.
- `$shop.email`: Email associated with the store.
- `$shop.logo`: Logo of the store (can be found in “Appearance” then “Theme and logo”).
- `$shop.favicon`: The favicon of your store (also in the same place as the logos and the theme).
- `$shop.phone`: Phone number of your store.
- `$shop.fax`: Fax number of your store.
- `$customer.lastname`: The last name of the connected customer.
- `$customer.firstname`: The first name of the connected customer.
- `$customer.email`: The customer's email address.
- `$customer.birthday`: Date of birth of the customer (no longer mandatory).
- `$customer.newsletter`: Whether the customer is subscribed to the newsletter (boolean).
- `$customer.ip_registration_newsletter`: Newsletter registration IP address.
- `$customer.optin`: Whether the customer has agreed to receive offers from partners (yes or no).
- `$customer.date_add`: Customer creation date.
- `$customer.date_upd`: Customer last modified date.
- `$customer.id`: Customer identifier (database ID).
- `$customer.id_default_group`: Identifier of the default customer group of this customer.
- `$customer.is_logged`: Is the customer logged in?
- `$urls.base_url`: URL of the home page of your PrestaShop.
- `$urls.current_url`: The current page's URL.
- `$urls.shop_domain_url`: The domain name of the store.
- `$urls.img_ps_url`: URL of the /img directory of your PrestaShop.
- `$urls.img_cat_url`: URL of the category images (e.g., /img/c).
- `$urls.img_lang_url`: URL of the site’s language images.
- `$urls.img_prod_url`: URL of the product images (e.g., /img/p).
- `$urls.img_manu_url`: URL of the manufacturers' images (e.g., /img/m).
- `$urls.img_sup_url`: URL of the images linked to the suppliers.
- `$urls.img_ship_url`: URL of images linked to carriers.
- `$urls.img_store_url`: URL of your store's images.
- `$urls.img_url`: URL of the images in your theme (e.g., /themes/yourtheme/assets/img).
- `$urls.css_url`: URL of your theme's CSS files (e.g., /themes/yourtheme/assets/css).
- `$urls.js_url`: URL of your theme's JavaScript files (e.g., /themes/yourtheme/assets/js).
- `$urls.pic_url`: URL of the /upload directory.

## Shortcodes
The module allows you to use many shortcodes anywhere in your store. However, restrictions may be in place, such as not allowing a hook shortcode or store locator to be used in a modal.

You can create your own shortcodes from the "Shortcodes" tab accessible in the "Ever block" submenu.

-### Basic shortcodes
- `[product 1]`: Display product with ID 1. Supports `carousel=true`.
- `[product 1,2,3]`: Display products with IDs 1, 2, and 3. Supports `carousel=true`.
- `[entity_lastname]`: Display customer's last name.
- `[entity_firstname]`: Display customer's first name.
- `[entity_gender]`: Display customer's gender.
- `[category id="8" nb="8"]`: Display 8 products from category with ID 8.
- `[manufacturer id="2" nb="8"]`: Display 8 products from manufacturer with ID 2.
- `[brands nb="8"]`: Display 8 brand names with their associated logos. Optional `carousel=true`.
- `[storelocator]`: Show a store locator on any CMS page.
- `[evermap]`: Display a Google Map centered on the shop address when a Google Maps API key is configured.
- `[subcategories id="2" nb="8"]`: Display 8 subcategories (name, image and link) of category 2.
- `[last-products 4]`: Display the last 4 products listed in the store. Supports `carousel=true`.
- `[best-sales 4]`: Display the 4 best-selling products in your store. Supports `carousel=true`.
- `[evercart]`: Display dropdown cart.
- `[cart_total]`: Display the total value of the current cart.
- `[cart_quantity]`: Display the number of products currently in the cart.
- `[newsletter_form]`: Display the PrestaShop newsletter subscription form.
- `[nativecontact]`: Embed the native PrestaShop contact form (this replaces the obsolete `[evercontact]` shortcode).
- `[everstore 4]`: Display store information for store ID 4 (several IDs can be separated with commas).
- `[video https://www.youtube.com/embed/35kwlY_RR08?si=QfwsUt9sEukni0Gj]`: Display a YouTube iframe of the video whose sharing URL is in the parameter (may also works with Vimeo, Dailymotion, and Vidyard).
- `[everaddtocart ref="1234" text="Add me to cart"]`: Creates an add to cart button for product reference 1234 with the text "Add me to cart". By clicking on the link, the product will be automatically added to the cart and the user will be redirected directly to the cart page. Also works in emails.
- `[everfaq tag="faq1"]`: Shows FAQs related to the faq tag
- `[productfeature id="2" nb="12" carousel="true"]`: Displays 12 products with the ID 2 feature, in the form of a carousel (the carousel is optional, you must have slick slider by activating it in the module configuration)
- `[productfeaturevalue id="2" nb="12" carousel="true"]`: Same as before, but this time concerns products that have the characteristic value id 2
- `[promo-products 10 carousel=true]`: Displays ten products on sale in a carousel format.
- `[best-sales 10 carousel=true]`: Displays the top ten best-selling products. Optional parameters: `days`, `orderby`, `orderway`.
- `[categorybestsales id="8" nb="10"]`: Displays the best-selling products from category ID 8. Optional parameters: `orderby`, `orderway`.
- `[brandbestsales id="3" nb="10"]`: Displays the best-selling products from brand ID 3. Optional parameters: `orderby`, `orderway`.
- `[featurebestsales id="2" nb="10"]`: Displays the best-selling products with feature ID 2. Optional parameters: `orderby`, `orderway`.
- `[featurevaluebestsales id="5" nb="10"]`: Displays the best-selling products with feature value ID 5. Optional parameters: `orderby`, `orderway`.
- `[random_product nb="10" carousel=true]`: Displays ten random products in a carousel.
- `[linkedproducts nb="8" orderby="date_add" orderway="DESC"]`: Displays products linked to the current product in a Bootstrap carousel.
- `[accessories nb="8" orderby="date_add" orderway="DESC"]`: Displays accessories of the current product in a Bootstrap carousel.
- `[crosselling nb=4 orderby="id_product" orderway="asc"]`: If the cart is empty, shows best-selling products. Otherwise displays accessories of cart products. If there are none or not enough, it adds best sellers from the same categories and finally completes with overall best sellers.
- `{hook h='displayHome'}`: Displays the `displayHome` hook (hooks are not allowed on modals)
- `[everinstagram]`: Display your latest Instagram photos. Images are stored in `/img/cms/instagram`. Images are cached for 24h and refreshed when the cache expires or when you run `everblock:tools:execute refreshtokens`.
- `[nativecontact]`: Embed the native PrestaShop contact form.
- `[everimg name="image.jpg" class="img-fluid" carousel=true]`: Display one or more CMS images. When `carousel=true` and multiple images are provided, a Bootstrap slideshow is rendered.
- `[displayQcdSvg name="icon" class="myclass" inline=true]`: Display a QCD SVG icon. Module available at [410 Gone](https://www.410-gone.fr/).
- `[qcdacf field objectType objectId]`: Display a value from QCD ACF fields. Module available at [410 Gone](https://www.410-gone.fr/).
- `[widget moduleName="mymodule" hookName="displayHome"]`: Render another module's widget.

- `[prettyblocks name="myzone"]`: Render a PrettyBlocks zone if the module is installed.
- `[everblock 3]`: Insert the content of block ID 3.
- `[cms id="1"]` or `[evercms id="1"]`: Display the content of CMS page ID 1.
### Contact form shortcodes
A contact form must start with the shortcode `[evercontactform_open]` and end with the shortcode `[evercontactform_close]`

- `[evercontact type="text" label="Your name"]` to display a text input field with the label "Your name"
- `[evercontact type="number" label="Your age"]` to display a numeric input field with the label "Your age"
- `[evercontact type="textarea" label="Message"]` to display a textarea input field with the label "Message"
- `[evercontact type="select" label="You are" values="Man,Woman,Other"]` to display a select field with the label "You are" and the options "Man, Woman, Other"
- `[evercontact type="radio" label="You are" values="Man,Woman,Other"]` is the same as select, but using radio buttons instead of select
- `[evercontact type="checkbox" label="You are" values="Man,Woman,Other"]` is the same as select, but using checkboxes instead of select
- `[evercontact type="multiselect" label="You are" values="Man,Woman,Other"]` to display a multiple select field with the label "You are" and the options "Man, Woman, Other"
- `[evercontact type="file" label="Attachment"]` to display a file upload field
- `[evercontact type="hidden" label="Hidden field"]` to display a hidden field that will have the label and value "Hidden field"
- `[evercontact type="sento" label="me@email.fr"]` to display the recipient's email in a coded way. The recipient's email will not be clearly displayed on the pages. Not using this means sending the email to the email address defined in your store by default. You can specify multiple emails by separating them with commas. Be sure to use the EI Captcha module to secure email sending.
- `[evercontact type="submit" label="Submit"]` to display a submit button for your custom contact form

The HTML for each `[evercontact]` field is rendered through the `contact_field.tpl` template located in `views/templates/hook`.  
Copy this file into your theme (`/themes/your_theme/modules/everblock/views/templates/hook/`) to customize the markup.

No emails are saved on your store.
A contact form can be added in a block used as a modal.

### Order funnel form shortcodes
To use the form in the order tunnel, you must first create the new step in the module configuration.

A form for the new order funnel step must be put on the `displayEverblockExtraOrderStep` hook. Therefore, you can create a new block, set it on the `displayEverblockExtraOrderStep` hook and add these shortcodes below.

Please make sure that the title of the new order step is set in the module configuration.

A form for the new order funnel step must start with the shortcode `[everorderform_open]` and end with the shortcode `[everorderform_close]`

You can add the following fields between these two shortcodes:
`[everorderform type="text" label="Your name"]` to display a text input field with the label "Your name"
`[everorderform type="number" label="Your age"]` to display a numeric input field with the label "Your age"
`[everorderform type="textarea" label="Message"]` to display a textarea input field with the label "Message"
`[everorderform type="select" label="You are" values="Man,Woman,Other"]` to display a select field with the label "You are" and the options "Man, Woman, Other"
`[everorderform type="radio" label="You are" values="Man,Woman,Other"]` is the same as select, but using radio buttons instead of select
`[everorderform type="checkbox" label="You are" values="Man,Woman,Other"]` is the same as select, but using checkboxes instead of select
`[everorderform type="multiselect" label="You are" values="Man,Woman,Other"]` to display a multiple select field with the label "You are" and the options "Man, Woman, Other"
`[everorderform type="hidden" label="Hidden field"]` to display a hidden field which will have the label and value "Hidden field"

The choices made in the form of the additional step of the order tunnel will be displayed in invoices, delivery notes, in the order confirmation page and in the order administration page.

## FAQ Management
FAQs are grouped using tags. All FAQs with exactly the same tags will be grouped together when you enter the shortcode.

For example, the shortcode `[everfaq tag="faq1"]` will display all FAQs with the tag "faq1".

You can determine the order of FAQs within a tag by specifying a position for them.

## Blocks Management
An HTML block is grafted onto a hook. You can determine the customer group(s) concerned by the block, as well as the type of device (smartphone, tablet, computer).

Settings allow you to add conditions on the display of these blocks, such as:
- display the block only on the home page
- display the block only on category pages, with a selection of the categories concerned
- display the block only on product sheets, with a selection of product categories concerned
- display the block only on brand pages, with a selection of the brands concerned
- display the block only on supplier pages, with a selection of the suppliers concerned

Obfuscation settings will help you improve your SEO, the obfuscation script can be disabled in the module configuration.

Make sure that the hook used in the block matches the criteria of the settings of this block, so as to guarantee its display.

Each block can be converted to a modal and can have shortcodes in its content (except hook and store locator shortcodes). You can therefore create contact forms in a modal.

## Triggering modals from a button
You can trigger an Everblock modal manually from any hook. Add a button with the
class `everblock-modal-button` and provide the block ID in a `data-everclickmodal`
attribute:

```html
<button class="everblock-modal-button" data-everclickmodal="12">Open modal</button>
```

To display the content of a CMS page in a modal, use the same class with a
`data-evercms` attribute holding the CMS page ID:

```html
<button class="everblock-modal-button" data-evercms="5">Open CMS</button>
```

When clicked, the module will load the corresponding modal content via AJAX and
display it using Bootstrap.

## Cache & logs

The module uses its own cache system in addition to the PrestaShop one.

The cache directory is located in /var/cache/dev?prod/everblock/

The logs directory is located in /var/logs/
Log files are created only when there is content to log.

Clearing the native PrestaShop cache will also clear the module cache, but the module will clear its own cache on a block expiry automatically.

---

## README en français

# Ever Block - Module gratuit de bloc HTML pour PrestaShop
![Ever Block logo](logo.png)

Ever Block permet aux utilisateurs de PrestaShop 1.7, 8 et 9 d'ajouter un nombre illimité de blocs HTML personnalisés n'importe où grâce aux hooks et aux shortcodes.

Il fonctionne parfaitement avec les hooks PrestaShop et est totalement compatible avec PrestaShop 1.7, 8 et 9.


## Fonctionnalités clés
- Blocs HTML illimités sur n'importe quel hook PrestaShop
- Shortcodes pour produits, formulaires, catégories et plus encore
- Compatible avec PrestaShop 1.7, 8 & 9
- Fonctionne avec le constructeur de pages Pretty Blocks
- Prend en charge le module de champs personnalisés QCD ACF
- Outils intégrés de cache et d'obfuscation pour le SEO
- Création facile de modales et d'étapes de commande supplémentaires

## Module de bloc HTML gratuit pour PrestaShop
Ce module gratuit vous permet de créer un nombre illimité de blocs HTML sur votre boutique

[Vous pouvez faire un don pour soutenir le développement de modules gratuits en cliquant sur ce lien](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE)

## Hooks PrestaShop 1.7, 8 & 9
La documentation développeur présente tous les hooks PrestaShop natifs :
[Liste des hooks PrestaShop 1.7](https://devdocs.prestashop.com/1.7/modules/concepts/hooks/)
Veuillez consulter la table ps_hook de votre base de données pour voir tous les hooks disponibles sur votre boutique. Seuls les hooks d'affichage sont utilisés avec ce module

## Compatibilité Pretty Blocks
Ce module est compatible avec le constructeur de pages Pretty Blocks. [Retrouvez ce module gratuit ici.](https://prettyblocks.io/)

## Compatibilité QCD ACF
Ce module est compatible avec le module QCD ACF développé par l'agence 410 Gone. Il permet d'ajouter des champs personnalisés aux produits, catégories, marques, fournisseurs, caractéristiques, etc. [Contactez l'agence 410 Gone depuis leur site pour obtenir le module QCD ACF.](https://www.410-gone.fr/e-commerce/prestashop.html)

## Variables Smarty
- `$currency.name` : Nom de la devise (euro, dollar, livre sterling, etc.)
- `$currency.iso_code` : Code ISO de la devise (comme EUR pour l'euro)
- `$currency.sign` : Signe de la devise (€, $ ...)
- `$currency.iso_code_num` : Code ISO numérique de la devise (ex : 978 pour l'euro)
- `$shop.name` : Nom de la boutique
- `$shop.email` : Adresse email de la boutique
- `$shop.logo` : Logo de la boutique
- `$shop.favicon` : Favicon de la boutique
- `$shop.phone` : Numéro de téléphone de la boutique
- `$shop.fax` : Fax de la boutique
- `$customer.lastname` : Nom du client connecté
- `$customer.firstname` : Prénom du client connecté
- `$customer.email` : Email du client
- `$customer.birthday` : Date de naissance du client
- `$customer.newsletter` : Inscription à la newsletter (booléen)
- `$customer.ip_registration_newsletter` : IP d'inscription à la newsletter
- `$customer.optin` : Consentement aux offres partenaires
- `$customer.date_add` : Date de création du client
- `$customer.date_upd` : Date de dernière modification du client
- `$customer.id` : Identifiant du client
- `$customer.id_default_group` : Groupe client par défaut
- `$customer.is_logged` : Le client est-il connecté ?
- `$urls.base_url` : URL de la page d'accueil
- `$urls.current_url` : URL de la page actuelle
- `$urls.shop_domain_url` : Domaine de la boutique
- `$urls.img_ps_url` : URL du dossier /img de PrestaShop
- `$urls.img_cat_url` : URL des images catégories
- `$urls.img_lang_url` : URL des images de langues
- `$urls.img_prod_url` : URL des images produits
- `$urls.img_manu_url` : URL des images fabricants
- `$urls.img_sup_url` : URL des images fournisseurs
- `$urls.img_ship_url` : URL des images transporteurs
- `$urls.img_store_url` : URL des images de la boutique
- `$urls.img_url` : URL des images du thème
- `$urls.css_url` : URL des fichiers CSS du thème
- `$urls.js_url` : URL des fichiers JavaScript du thème
- `$urls.pic_url` : URL du dossier /upload

## Shortcodes
Le module vous permet d'utiliser de nombreux shortcodes partout dans votre boutique. Certaines restrictions peuvent s'appliquer, par exemple un hook ou un store locator ne peuvent pas être utilisés dans une modale.

Vous pouvez créer vos propres shortcodes depuis l'onglet "Shortcodes" accessible dans le sous-menu "Ever block".

### Shortcodes basiques
- `[product 1]` : Affiche le produit ayant l'ID 1. Supporte `carousel=true`.
- `[product 1,2,3]` : Affiche les produits 1, 2 et 3. Supporte `carousel=true`.
- `[entity_lastname]` : Affiche le nom du client connecté.
- `[promo-products 10 carousel=true]` : Affiche dix produits en promotion en carousel.
- `[best-sales 10 carousel=true]` : Affiche les dix meilleures ventes. Paramètres optionnels : `days`, `orderby`, `orderway`.
- `[categorybestsales id="8" nb="10"]` : Affiche les meilleures ventes de la catégorie 8. Paramètres optionnels : `orderby`, `orderway`.
- `[brandbestsales id="3" nb="10"]` : Affiche les meilleures ventes de la marque 3. Paramètres optionnels : `orderby`, `orderway`.
- `[featurebestsales id="2" nb="10"]` : Affiche les meilleures ventes associées à la caractéristique 2. Paramètres optionnels : `orderby`, `orderway`.
- `[featurevaluebestsales id="5" nb="10"]` : Affiche les meilleures ventes pour la valeur de caractéristique 5. Paramètres optionnels : `orderby`, `orderway`.
- `[random_product nb="10" carousel=true]` : Affiche dix produits aléatoires en carousel.
- `[linkedproducts nb="8" orderby="date_add" orderway="DESC"]` : Affiche les produits liés au produit courant en carousel Bootstrap.
- `[accessories nb="8" orderby="date_add" orderway="DESC"]` : Affiche les accessoires du produit courant en carousel Bootstrap.
- `[crosselling nb=4 orderby="id_product" orderway="asc"]` : Si le panier est vide, affiche les meilleures ventes. Sinon, affiche les accessoires des produits du panier. S'il n'y en a pas ou si le nombre est insuffisant, complète avec les meilleures ventes des mêmes catégories puis avec les meilleures ventes globales.
- `{hook h='displayHome'}` : Affiche le hook `displayHome` (les hooks ne sont pas autorisés dans les modales)
- `[everinstagram]` : Affiche vos dernières photos Instagram. Les images sont enregistrées dans `/img/cms/instagram`. Les images sont mises en cache pendant 24h et régénérées automatiquement ou via la commande `everblock:tools:execute refreshtokens`.
- `[nativecontact]` : Intègre le formulaire de contact natif PrestaShop.
- `[everimg name="image.jpg" class="img-fluid"]` : Affiche une ou plusieurs images CMS.
- `[displayQcdSvg name="icon" class="myclass" inline=true]` : Affiche une icône SVG QCD. Module disponible chez [410 Gone](https://www.410-gone.fr/).
- `[evermap]` : Affiche une carte Google centrée sur l'adresse de la boutique si la clé Google Maps est renseignée.
- `[qcdacf field objectType objectId]` : Affiche une valeur provenant des champs QCD ACF. Module disponible chez [410 Gone](https://www.410-gone.fr/).
- `[widget moduleName="mymodule" hookName="displayHome"]` : Affiche le widget d'un autre module.
- `[prettyblocks name="myzone"]` : Affiche une zone PrettyBlocks si le module est installé.
- `[everblock 3]` : Insère le contenu du bloc ayant l'ID 3.
- `[cms id="1"]` or `[evercms id="1"]` : Affiche le contenu de la page CMS ayant l'ID 1.

### Shortcodes de formulaire de contact
Un formulaire de contact doit commencer par `[evercontactform_open]` et se terminer par `[evercontactform_close]`
- `[evercontact type="text" label="Votre nom"]` : champ texte "Votre nom"
- `[evercontact type="number" label="Votre âge"]` : champ numérique "Votre âge"
- `[evercontact type="textarea" label="Message"]` : champ zone de texte "Message"
- `[evercontact type="select" label="Vous êtes" values="Homme,Femme,Autre"]` : champ select
- `[evercontact type="radio" label="Vous êtes" values="Homme,Femme,Autre"]` : boutons radio
- `[evercontact type="checkbox" label="Vous êtes" values="Homme,Femme,Autre"]` : cases à cocher
- `[evercontact type="multiselect" label="Vous êtes" values="Homme,Femme,Autre"]` : champ multisélection
- `[evercontact type="file" label="Pièce jointe"]` : upload de fichier
- `[evercontact type="hidden" label="Champ caché"]` : champ caché avec valeur "Champ caché"
- `[evercontact type="sento" label="me@email.fr"]` : email destinataire chiffré
- `[evercontact type="submit" label="Envoyer"]` : bouton d'envoi du formulaire

Aucun email n'est enregistré sur votre boutique. Un formulaire peut être ajouté dans un bloc utilisé en modal.

### Shortcodes du tunnel de commande
Pour utiliser le formulaire dans le tunnel de commande, créez d'abord la nouvelle étape dans la configuration du module.

Le formulaire de la nouvelle étape doit être placé sur le hook `displayEverblockExtraOrderStep`. Créez donc un nouveau bloc, positionnez-le sur ce hook et ajoutez les shortcodes ci-dessous.

Assurez-vous que le titre de la nouvelle étape soit renseigné dans la configuration du module.

Un formulaire d'étape supplémentaire commence par `[everorderform_open]` et se termine par `[everorderform_close]`
`[everorderform type="text" label="Votre nom"]` : champ texte "Votre nom"
`[everorderform type="number" label="Votre âge"]` : champ numérique
`[everorderform type="textarea" label="Message"]` : zone de texte
`[everorderform type="select" label="Vous êtes" values="Homme,Femme,Autre"]` : champ select
`[everorderform type="radio" label="Vous êtes" values="Homme,Femme,Autre"]` : boutons radio
`[everorderform type="checkbox" label="Vous êtes" values="Homme,Femme,Autre"]` : cases à cocher
`[everorderform type="multiselect" label="Vous êtes" values="Homme,Femme,Autre"]` : champ multisélection
`[everorderform type="hidden" label="Champ caché"]` : champ caché "Champ caché"

Les choix faits dans cette étape supplémentaire apparaîtront sur les factures, les bons de livraison, la page de confirmation de commande et dans l'administration des commandes.

## Gestion de la FAQ
Les FAQ sont regroupées grâce à des tags. Toutes les FAQ portant exactement les mêmes tags seront regroupées lors de l'utilisation du shortcode correspondant.

Par exemple, le shortcode `[everfaq tag="faq1"]` affichera toutes les FAQ portant le tag "faq1".

Vous pouvez définir l'ordre des FAQ au sein d'un tag en leur attribuant une position.

## Gestion des blocs
Un bloc HTML se greffe sur un hook. Vous pouvez définir les groupes de clients concernés ainsi que le type d'appareil (smartphone, tablette, ordinateur).

Les réglages permettent d'ajouter des conditions d'affichage comme :
- afficher le bloc uniquement sur la page d'accueil
- afficher le bloc uniquement sur les pages catégorie, avec sélection des catégories concernées
- afficher le bloc uniquement sur les fiches produits, avec sélection des catégories concernées
- afficher le bloc uniquement sur les pages marque, avec sélection des marques
- afficher le bloc uniquement sur les pages fournisseur, avec sélection des fournisseurs

Les réglages d'obfuscation vous aideront à améliorer votre SEO ; le script d'obfuscation peut être désactivé dans la configuration du module.

Assurez-vous que le hook utilisé dans le bloc corresponde aux critères du bloc afin de garantir son affichage.

Chaque bloc peut être converti en modal et peut contenir des shortcodes (à l'exception des hooks et du store locator). Vous pouvez donc créer des formulaires de contact dans une modal.

## Déclenchement des modales depuis un bouton
Vous pouvez déclencher manuellement une modal Everblock depuis n'importe quel hook. Ajoutez un bouton avec la classe `everblock-modal-button` et indiquez l'ID du bloc dans l'attribut `data-everclickmodal` :
```html
<button class="everblock-modal-button" data-everclickmodal="12">Ouvrir la modal</button>
```
Vous pouvez également afficher le contenu d'une page CMS en utilisant l'attribut `data-evercms` avec l'identifiant de la page :

```html
<button class="everblock-modal-button" data-evercms="5">Ouvrir la page CMS</button>
```
Lors du clic, le module chargera le contenu de la modal via AJAX et l'affichera avec Bootstrap.

## Cache et logs
Le module utilise son propre système de cache en plus de celui de PrestaShop.

Le dossier du cache se situe dans /var/cache/dev?prod/everblock/

Le dossier des logs se situe dans /var/logs/
Les fichiers de log ne sont créés que s'il y a un message à enregistrer.

Vider le cache natif de PrestaShop videra également le cache du module, mais ce dernier vide automatiquement son cache lorsqu'un bloc expire.

---

## README en español

# Ever Block - Módulo de bloque HTML gratuito para PrestaShop
![Ever Block logo](logo.png)

Ever Block permite a los usuarios de PrestaShop 1.7, 8 y 9 añadir bloques HTML personalizados ilimitados en cualquier parte mediante hooks y shortcodes.

Funciona sin problemas con los hooks de PrestaShop y es totalmente compatible con PrestaShop 1.7, 8 y 9.


## Funcionalidades clave
- Bloques HTML ilimitados en cualquier hook de PrestaShop
- Shortcodes para productos, formularios, categorías y más
- Compatible con PrestaShop 1.7, 8 y 9
- Funciona con el constructor de páginas Pretty Blocks
- Soporta el módulo de campos personalizados QCD ACF
- Herramientas integradas de caché y ofuscación para SEO
- Creación sencilla de modales y pasos adicionales en el pedido

## Módulo gratuito de bloque HTML para PrestaShop
Este módulo gratuito te permite crear un número ilimitado de bloques HTML en tu tienda

[Puedes hacer una donación para apoyar el desarrollo de módulos gratuitos haciendo clic en este enlace](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE)

## Hooks de PrestaShop 1.7, 8 y 9
La documentación de desarrolladores muestra todos los hooks nativos de PrestaShop:
[Lista de hooks PrestaShop 1.7](https://devdocs.prestashop.com/1.7/modules/concepts/hooks/)
Consulta la tabla ps_hook de tu base de datos para ver todos los hooks disponibles en tu tienda. Solo se utilizan hooks de display con este módulo

## Compatibilidad con Pretty Blocks
Este módulo es compatible con el constructor de páginas Pretty Blocks. [Encuentra este módulo gratuito aquí.](https://prettyblocks.io/)

## Compatibilidad con QCD ACF
Este módulo es compatible con el módulo QCD ACF desarrollado por la agencia 410 Gone. Permite añadir campos personalizados a productos, categorías, marcas, proveedores, características, etc. [Contacta con la agencia 410 Gone desde su web para obtener el módulo QCD ACF.](https://www.410-gone.fr/e-commerce/prestashop.html)

## Variables Smarty
- `$currency.name`: Nombre de la divisa (euro, dólar, libra, etc.)
- `$currency.iso_code`: Código ISO de la divisa (como EUR para el euro)
- `$currency.sign`: Símbolo de la divisa (€, $ ...)
- `$currency.iso_code_num`: Código ISO numérico de la divisa (ej: 978 para el euro)
- `$shop.name`: Nombre de la tienda
- `$shop.email`: Correo de la tienda
- `$shop.logo`: Logo de la tienda
- `$shop.favicon`: Favicon de la tienda
- `$shop.phone`: Teléfono de la tienda
- `$shop.fax`: Fax de la tienda
- `$customer.lastname`: Apellido del cliente conectado
- `$customer.firstname`: Nombre del cliente conectado
- `$customer.email`: Correo del cliente
- `$customer.birthday`: Fecha de nacimiento del cliente
- `$customer.newsletter`: Suscripción al boletín (booleano)
- `$customer.ip_registration_newsletter`: IP de registro al boletín
- `$customer.optin`: Aceptación de ofertas de socios
- `$customer.date_add`: Fecha de creación del cliente
- `$customer.date_upd`: Fecha de modificación del cliente
- `$customer.id`: Identificador del cliente
- `$customer.id_default_group`: Grupo por defecto del cliente
- `$customer.is_logged`: ¿Está conectado el cliente?
- `$urls.base_url`: URL de la página principal
- `$urls.current_url`: URL de la página actual
- `$urls.shop_domain_url`: Dominio de la tienda
- `$urls.img_ps_url`: URL del directorio /img de PrestaShop
- `$urls.img_cat_url`: URL de las imágenes de categorías
- `$urls.img_lang_url`: URL de las imágenes de idiomas
- `$urls.img_prod_url`: URL de las imágenes de productos
- `$urls.img_manu_url`: URL de las imágenes de fabricantes
- `$urls.img_sup_url`: URL de las imágenes de proveedores
- `$urls.img_ship_url`: URL de las imágenes de transportistas
- `$urls.img_store_url`: URL de las imágenes de la tienda
- `$urls.img_url`: URL de las imágenes del tema
- `$urls.css_url`: URL de los archivos CSS del tema
- `$urls.js_url`: URL de los archivos JavaScript del tema
- `$urls.pic_url`: URL del directorio /upload

## Shortcodes
El módulo permite usar muchos shortcodes en cualquier lugar de la tienda. Pueden existir restricciones, por ejemplo un hook o un store locator no pueden usarse en una modal.

Puedes crear tus propios shortcodes desde la pestaña "Shortcodes" disponible en el submenú "Ever block".

### Shortcodes básicos
- `[product 1]`: Muestra el producto con ID 1. Soporta `carousel=true`.
- `[product 1,2,3]`: Muestra los productos 1, 2 y 3. Soporta `carousel=true`.
- `[entity_lastname]`: Muestra el apellido del cliente conectado.
- `[promo-products 10 carousel=true]`: Muestra diez productos en promoción en un carrusel.
- `[best-sales 10 carousel=true]`: Muestra los diez productos más vendidos. Parámetros opcionales: `days`, `orderby`, `orderway`.
- `[categorybestsales id="8" nb="10"]`: Muestra los productos más vendidos de la categoría 8. Parámetros opcionales: `orderby`, `orderway`.
- `[brandbestsales id="3" nb="10"]`: Muestra los productos más vendidos de la marca 3. Parámetros opcionales: `orderby`, `orderway`.
- `[featurebestsales id="2" nb="10"]`: Muestra los productos más vendidos con la característica 2. Parámetros opcionales: `orderby`, `orderway`.
- `[featurevaluebestsales id="5" nb="10"]`: Muestra los productos más vendidos con el valor de característica 5. Parámetros opcionales: `orderby`, `orderway`.
- `[random_product nb="10" carousel=true]`: Muestra diez productos aleatorios en carrusel.
- `[linkedproducts nb="8" orderby="date_add" orderway="DESC"]`: Muestra productos relacionados con el producto actual en un carrusel Bootstrap.
- `[accessories nb="8" orderby="date_add" orderway="DESC"]`: Muestra los accesorios del producto actual en un carrusel Bootstrap.
- `[crosselling nb=4 orderby="id_product" orderway="asc"]`: Si el carrito está vacío, se muestran los productos más vendidos. De lo contrario, muestra los accesorios de los productos del carrito. Si no hay suficientes, se añaden los más vendidos de las mismas categorías y, en último lugar, los más vendidos globales.
- `{hook h='displayHome'}`: Muestra el hook `displayHome` (los hooks no están permitidos en modales)
- `[everinstagram]`: Muestra tus últimas fotos de Instagram. Las imágenes se guardan en `/img/cms/instagram`. Las imágenes se almacenan en caché durante 24h y se regeneran de forma automática o ejecutando `everblock:tools:execute refreshtokens`.
- `[nativecontact]`: Inserta el formulario de contacto nativo de PrestaShop.
- `[everimg name="image.jpg" class="img-fluid"]`: Muestra una o más imágenes CMS.
- `[displayQcdSvg name="icon" class="myclass" inline=true]`: Muestra un icono SVG de QCD. Módulo disponible en [410 Gone](https://www.410-gone.fr/).
- `[qcdacf field objectType objectId]`: Muestra un valor de los campos QCD ACF. Módulo disponible en [410 Gone](https://www.410-gone.fr/).
- `[widget moduleName="mymodule" hookName="displayHome"]`: Muestra el widget de otro módulo.
- `[prettyblocks name="myzone"]`: Muestra una zona PrettyBlocks si el módulo está instalado.
- `[everblock 3]`: Inserta el contenido del bloque con ID 3.
- `[cms id="1"]` or `[evercms id="1"]`: Muestra el contenido de la página CMS con ID 1.

### Shortcodes para formularios de contacto
Un formulario de contacto debe comenzar con `[evercontactform_open]` y finalizar con `[evercontactform_close]`
- `[evercontact type="text" label="Tu nombre"]`: campo de texto "Tu nombre"
- `[evercontact type="number" label="Tu edad"]`: campo numérico "Tu edad"
- `[evercontact type="textarea" label="Mensaje"]`: área de texto "Mensaje"
- `[evercontact type="select" label="Eres" values="Hombre,Mujer,Otro"]`: campo select
- `[evercontact type="radio" label="Eres" values="Hombre,Mujer,Otro"]`: botones radio
- `[evercontact type="checkbox" label="Eres" values="Hombre,Mujer,Otro"]`: casillas de verificación
- `[evercontact type="multiselect" label="Eres" values="Hombre,Mujer,Otro"]`: lista de selección múltiple
- `[evercontact type="file" label="Adjunto"]`: subida de archivo
- `[evercontact type="hidden" label="Campo oculto"]`: campo oculto con valor "Campo oculto"
- `[evercontact type="sento" label="me@email.fr"]`: correo destinatario cifrado
- `[evercontact type="submit" label="Enviar"]`: botón de envío del formulario

No se guardan correos en tu tienda. Un formulario puede añadirse en un bloque usado como modal.

### Shortcodes para el túnel de pedido
Para usar el formulario en el túnel de pedido, primero crea el nuevo paso en la configuración del módulo.

El formulario del nuevo paso debe colocarse en el hook `displayEverblockExtraOrderStep`. Crea un nuevo bloque, colócalo en ese hook y añade los siguientes shortcodes.

Asegúrate de que el título del nuevo paso esté configurado en el módulo.

Un formulario de paso adicional empieza con `[everorderform_open]` y termina con `[everorderform_close]`
`[everorderform type="text" label="Tu nombre"]`: campo de texto "Tu nombre"
`[everorderform type="number" label="Tu edad"]`: campo numérico
`[everorderform type="textarea" label="Mensaje"]`: área de texto
`[everorderform type="select" label="Eres" values="Hombre,Mujer,Otro"]`: campo select
`[everorderform type="radio" label="Eres" values="Hombre,Mujer,Otro"]`: botones radio
`[everorderform type="checkbox" label="Eres" values="Hombre,Mujer,Otro"]`: casillas de verificación
`[everorderform type="multiselect" label="Eres" values="Hombre,Mujer,Otro"]`: lista de selección múltiple
`[everorderform type="hidden" label="Campo oculto"]`: campo oculto "Campo oculto"

Las elecciones realizadas en este paso adicional se mostrarán en facturas, albaranes, en la página de confirmación y en la administración de pedidos.

## Gestión de FAQ
Las FAQs se agrupan mediante etiquetas. Todas las FAQs con las mismas etiquetas se agruparán al usar el shortcode correspondiente.

Por ejemplo, el shortcode `[everfaq tag="faq1"]` mostrará todas las FAQs con la etiqueta "faq1".

Puedes determinar el orden de las FAQs dentro de una etiqueta asignándoles una posición.

## Gestión de bloques
Un bloque HTML se engancha a un hook. Puedes determinar el grupo de clientes al que se dirige y el tipo de dispositivo (móvil, tableta, ordenador).

Las opciones permiten añadir condiciones de visualización como:
- mostrar el bloque solo en la página de inicio
- mostrar el bloque solo en páginas de categoría, con selección de categorías
- mostrar el bloque solo en fichas de producto, con selección de categorías de producto
- mostrar el bloque solo en páginas de marca, con selección de marcas
- mostrar el bloque solo en páginas de proveedor, con selección de proveedores

Las opciones de ofuscación te ayudarán a mejorar tu SEO; el script puede desactivarse en la configuración del módulo.

Asegúrate de que el hook del bloque coincida con los criterios de configuración para garantizar su visualización.

Cada bloque puede convertirse en modal y puede contener shortcodes (excepto hooks y store locator). Puedes crear formularios de contacto en una modal.

## Disparar modales desde un botón
Puedes lanzar una modal de Everblock manualmente desde cualquier hook. Añade un botón con la clase `everblock-modal-button` e indica el ID del bloque en el atributo `data-everclickmodal`:
```html
<button class="everblock-modal-button" data-everclickmodal="12">Abrir modal</button>
```
Tambien puedes mostrar el contenido de una página CMS usando el atributo `data-evercms` con el ID de la página:

```html
<button class="everblock-modal-button" data-evercms="5">Abrir CMS</button>
```
Al hacer clic, el módulo cargará el contenido de la modal vía AJAX y lo mostrará con Bootstrap.

## Caché y logs
El módulo utiliza su propio sistema de caché además del de PrestaShop.

El directorio de caché está en /var/cache/dev?prod/everblock/

El directorio de logs está en /var/logs/
Los archivos de registro solo se crean si contienen información.

Borrar la caché nativa de PrestaShop también limpiará la del módulo, pero este limpia automáticamente su caché cuando expira un bloque.

---

## README in italiano

# Ever Block - Modulo gratuito di blocchi HTML per PrestaShop
![Ever Block logo](logo.png)

Ever Block consente agli utenti di PrestaShop 1.7, 8 e 9 di aggiungere blocchi HTML personalizzati illimitati ovunque mediante hook e shortcode.

Funziona perfettamente con gli hook di PrestaShop ed è completamente compatibile con PrestaShop 1.7, 8 e 9.


## Funzionalità principali
- Blocchi HTML illimitati in qualsiasi hook di PrestaShop
- Shortcode per prodotti, moduli, categorie e altro
- Compatibile con PrestaShop 1.7, 8 e 9
- Funziona con il page builder Pretty Blocks
- Supporta il modulo di campi personalizzati QCD ACF
- Strumenti integrati di cache e offuscamento per la SEO
- Creazione semplice di modali e passaggi extra nell'ordine

## Modulo gratuito di blocchi HTML per PrestaShop
Questo modulo gratuito permette di creare un numero illimitato di blocchi HTML nel tuo shop

[Puoi fare una donazione per sostenere lo sviluppo di moduli gratuiti cliccando su questo link](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE)

## Hook PrestaShop 1.7, 8 e 9
La documentazione per sviluppatori mostra tutti gli hook nativi di PrestaShop:
[Elenco hook PrestaShop 1.7](https://devdocs.prestashop.com/1.7/modules/concepts/hooks/)
Controlla la tabella ps_hook del tuo database per vedere tutti gli hook disponibili sul tuo shop. Con questo modulo vengono utilizzati solo gli hook di display

## Compatibilità con Pretty Blocks
Questo modulo è compatibile con il page builder Pretty Blocks. [Trovi il modulo gratuito qui.](https://prettyblocks.io/)

## Compatibilità con QCD ACF
Questo modulo è compatibile con il modulo QCD ACF sviluppato dall'agenzia 410 Gone. Permette di aggiungere campi personalizzati a prodotti, categorie, marchi, fornitori, caratteristiche, ecc. [Contatta l'agenzia 410 Gone dal loro sito per ottenere il modulo QCD ACF.](https://www.410-gone.fr/e-commerce/prestashop.html)

## Variabili Smarty
- `$currency.name`: Nome della valuta (euro, dollaro, sterlina, ecc.)
- `$currency.iso_code`: Codice ISO della valuta (es: EUR per l'euro)
- `$currency.sign`: Simbolo della valuta (€, $ ...)
- `$currency.iso_code_num`: Codice ISO numerico della valuta (es: 978 per l'euro)
- `$shop.name`: Nome del negozio
- `$shop.email`: Email del negozio
- `$shop.logo`: Logo del negozio
- `$shop.favicon`: Favicon del negozio
- `$shop.phone`: Numero di telefono del negozio
- `$shop.fax`: Fax del negozio
- `$customer.lastname`: Cognome del cliente loggato
- `$customer.firstname`: Nome del cliente loggato
- `$customer.email`: Email del cliente
- `$customer.birthday`: Data di nascita del cliente
- `$customer.newsletter`: Iscrizione alla newsletter (booleano)
- `$customer.ip_registration_newsletter`: IP di registrazione alla newsletter
- `$customer.optin`: Consenso alle offerte dei partner
- `$customer.date_add`: Data di creazione del cliente
- `$customer.date_upd`: Data di modifica del cliente
- `$customer.id`: ID del cliente
- `$customer.id_default_group`: Gruppo predefinito del cliente
- `$customer.is_logged`: Il cliente è loggato?
- `$urls.base_url`: URL della home page
- `$urls.current_url`: URL della pagina attuale
- `$urls.shop_domain_url`: Dominio del negozio
- `$urls.img_ps_url`: URL della cartella /img di PrestaShop
- `$urls.img_cat_url`: URL delle immagini categorie
- `$urls.img_lang_url`: URL delle immagini delle lingue
- `$urls.img_prod_url`: URL delle immagini prodotto
- `$urls.img_manu_url`: URL delle immagini produttori
- `$urls.img_sup_url`: URL delle immagini fornitori
- `$urls.img_ship_url`: URL delle immagini dei corrieri
- `$urls.img_store_url`: URL delle immagini del negozio
- `$urls.img_url`: URL delle immagini del tema
- `$urls.css_url`: URL dei file CSS del tema
- `$urls.js_url`: URL dei file JavaScript del tema
- `$urls.pic_url`: URL della cartella /upload

## Shortcode
Il modulo consente di utilizzare molti shortcode in qualsiasi parte del negozio. Possono esserci restrizioni, ad esempio un hook o uno store locator non possono essere usati in una modale.

Puoi creare i tuoi shortcode dalla scheda "Shortcodes" nel sottomenu "Ever block".

### Shortcode di base
- `[product 1]`: Mostra il prodotto con ID 1. Supporta `carousel=true`.
- `[product 1,2,3]`: Mostra i prodotti 1, 2 e 3. Supporta `carousel=true`.
- `[entity_lastname]`: Mostra il cognome del cliente loggato.
- `[promo-products 10 carousel=true]`: Mostra dieci prodotti in promozione in un carosello.
- `[best-sales 10 carousel=true]`: Mostra i dieci prodotti più venduti. Parametri opzionali: `days`, `orderby`, `orderway`.
- `[categorybestsales id="8" nb="10"]`: Mostra i prodotti più venduti della categoria 8. Parametri opzionali: `orderby`, `orderway`.
- `[brandbestsales id="3" nb="10"]`: Mostra i prodotti più venduti del marchio 3. Parametri opzionali: `orderby`, `orderway`.
- `[featurebestsales id="2" nb="10"]`: Mostra i prodotti più venduti con la caratteristica 2. Parametri opzionali: `orderby`, `orderway`.
- `[featurevaluebestsales id="5" nb="10"]`: Mostra i prodotti più venduti con il valore caratteristica 5. Parametri opzionali: `orderby`, `orderway`.
- `[random_product nb="10" carousel=true]`: Mostra dieci prodotti casuali in carosello.
- `[linkedproducts nb="8" orderby="date_add" orderway="DESC"]`: Mostra i prodotti collegati a quello attuale in un carosello Bootstrap.
- `[accessories nb="8" orderby="date_add" orderway="DESC"]`: Mostra gli accessori del prodotto corrente in un carosello Bootstrap.
- `[crosselling nb=4 orderby="id_product" orderway="asc"]`: Se il carrello è vuoto vengono mostrati i prodotti più venduti. Altrimenti mostra gli accessori dei prodotti in carrello; se mancano articoli o non si raggiunge il limite, aggiunge i più venduti delle stesse categorie e infine i best seller globali.
- `{hook h='displayHome'}`: Mostra l'hook `displayHome` (gli hook non sono consentiti nelle modali)
- `[everinstagram]`: Mostra le ultime foto di Instagram. Le immagini vengono salvate in `/img/cms/instagram`. Le immagini sono mantenute in cache per 24h e vengono rigenerate automaticamente o eseguendo `everblock:tools:execute refreshtokens`.
- `[nativecontact]`: Inserisce il modulo di contatto nativo di PrestaShop.
- `[everimg name="image.jpg" class="img-fluid"]`: Mostra una o più immagini CMS.
- `[displayQcdSvg name="icon" class="myclass" inline=true]`: Mostra un'icona SVG QCD. Modulo disponibile presso [410 Gone](https://www.410-gone.fr/).
- `[qcdacf field objectType objectId]`: Mostra un valore dai campi QCD ACF. Modulo disponibile presso [410 Gone](https://www.410-gone.fr/).
- `[widget moduleName="mymodule" hookName="displayHome"]`: Mostra il widget di un altro modulo.
- `[prettyblocks name="myzone"]`: Mostra una zona PrettyBlocks se il modulo è installato.
- `[everblock 3]`: Inserisce il contenuto del blocco con ID 3.
- `[cms id="1"]` or `[evercms id="1"]`: Mostra il contenuto della pagina CMS con ID 1.

### Shortcode per moduli di contatto
Un modulo di contatto deve iniziare con `[evercontactform_open]` e terminare con `[evercontactform_close]`
- `[evercontact type="text" label="Il tuo nome"]`: campo di testo "Il tuo nome"
- `[evercontact type="number" label="La tua età"]`: campo numerico "La tua età"
- `[evercontact type="textarea" label="Messaggio"]`: area di testo "Messaggio"
- `[evercontact type="select" label="Sei" values="Uomo,Donna,Altro"]`: campo select
- `[evercontact type="radio" label="Sei" values="Uomo,Donna,Altro"]`: pulsanti radio
- `[evercontact type="checkbox" label="Sei" values="Uomo,Donna,Altro"]`: caselle di controllo
- `[evercontact type="multiselect" label="Sei" values="Uomo,Donna,Altro"]`: elenco multiselezione
- `[evercontact type="file" label="Allegato"]`: caricamento file
- `[evercontact type="hidden" label="Campo nascosto"]`: campo nascosto con valore "Campo nascosto"
- `[evercontact type="sento" label="me@email.fr"]`: indirizzo email cifrato del destinatario
- `[evercontact type="submit" label="Invia"]`: pulsante di invio del modulo

Nessuna email viene salvata sul tuo shop. Un modulo può essere aggiunto in un blocco usato come modale.

### Shortcode per il percorso d'ordine
Per utilizzare il modulo nel percorso d'ordine, crea prima il nuovo passaggio nella configurazione del modulo.

Il modulo del nuovo passaggio deve essere inserito sull'hook `displayEverblockExtraOrderStep`. Crea quindi un nuovo blocco, posizionalo su questo hook e aggiungi gli shortcode di seguito.

Assicurati che il titolo del nuovo passaggio sia impostato nella configurazione del modulo.

Un modulo di passaggio aggiuntivo inizia con `[everorderform_open]` e termina con `[everorderform_close]`
`[everorderform type="text" label="Il tuo nome"]`: campo di testo "Il tuo nome"
`[everorderform type="number" label="La tua età"]`: campo numerico
`[everorderform type="textarea" label="Messaggio"]`: area di testo
`[everorderform type="select" label="Sei" values="Uomo,Donna,Altro"]`: campo select
`[everorderform type="radio" label="Sei" values="Uomo,Donna,Altro"]`: pulsanti radio
`[everorderform type="checkbox" label="Sei" values="Uomo,Donna,Altro"]`: caselle di controllo
`[everorderform type="multiselect" label="Sei" values="Uomo,Donna,Altro"]`: elenco multiselezione
`[everorderform type="hidden" label="Campo nascosto"]`: campo nascosto "Campo nascosto"

Le scelte effettuate in questo passaggio aggiuntivo saranno mostrate nelle fatture, nei documenti di consegna, nella pagina di conferma dell'ordine e nell'amministrazione degli ordini.

## Gestione delle FAQ
Le FAQ sono raggruppate tramite tag. Tutte le FAQ con gli stessi tag saranno raggruppate quando utilizzerai lo shortcode corrispondente.

Ad esempio, lo shortcode `[everfaq tag="faq1"]` mostrerà tutte le FAQ con il tag "faq1".

Puoi determinare l'ordine delle FAQ all'interno di un tag assegnando loro una posizione.

## Gestione dei blocchi
Un blocco HTML è agganciato a un hook. Puoi definire i gruppi di clienti interessati e il tipo di dispositivo (smartphone, tablet, computer).

Le impostazioni permettono di aggiungere condizioni di visualizzazione come:
- mostrare il blocco solo nella home page
- mostrare il blocco solo nelle pagine categoria, selezionando le categorie interessate
- mostrare il blocco solo nelle schede prodotto, selezionando le categorie interessate
- mostrare il blocco solo nelle pagine marca, selezionando le marche
- mostrare il blocco solo nelle pagine fornitore, selezionando i fornitori

Le impostazioni di offuscamento ti aiuteranno a migliorare la SEO; lo script di offuscamento può essere disattivato nella configurazione del modulo.

Assicurati che l'hook utilizzato nel blocco corrisponda ai criteri di configurazione per garantirne la visualizzazione.

Ogni blocco può essere convertito in modale e può contenere shortcode (tranne hook e store locator). Puoi quindi creare moduli di contatto in una modale.

## Attivare modali da un pulsante
Puoi attivare manualmente una modale Everblock da qualsiasi hook. Aggiungi un pulsante con la classe `everblock-modal-button` e indica l'ID del blocco nell'attributo `data-everclickmodal`:
```html
<button class="everblock-modal-button" data-everclickmodal="12">Apri modale</button>
```
Puoi anche mostrare il contenuto di una pagina CMS usando l'attributo `data-evercms` con l'ID della pagina:

```html
<button class="everblock-modal-button" data-evercms="5">Apri CMS</button>
```
Al clic, il modulo caricherà il contenuto della modale tramite AJAX e lo mostrerà con Bootstrap.

## Cache e log
Il modulo utilizza un proprio sistema di cache oltre a quello di PrestaShop.

La cartella cache si trova in /var/cache/dev?prod/everblock/

La cartella log si trova in /var/logs/
I file di log vengono creati solo se contengono dei messaggi.

Cancellare la cache nativa di PrestaShop cancellerà anche quella del modulo, ma quest'ultimo la pulisce automaticamente alla scadenza di un blocco.
## Continuous Integration
A GitHub Actions workflow checks PHP and Smarty template syntax on every push or pull request. The `vendor` directory is skipped during these checks to avoid issues with third-party code.
