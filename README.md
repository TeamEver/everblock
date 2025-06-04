# Ever PS Block for Prestashop

HTML module for Prestashop, hooks everywhere on your shop

Prestashop administrators can create HTML blocks hooked to any available display hook on shop. Works on Prestashop 1.7 and Prestashop 8 (recommanded)

https://www.team-ever.com/prestashop-module-bloc-editeur-html-illimite-shortcode/

## Prestashop free HTML block module
This free module allows you to create illimited HTML blocks on your shop

[You can make a donation to support the development of free modules by clicking on this link](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE)

## Prestashop 1.7 & 8 hooks 
Dev documentation show every native Prestashop hook :
[Prestashop 1.7 hook list](https://devdocs.prestashop.com/1.7/modules/concepts/hooks/)
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
- `[start_cart_link]`: Generate a link to start the cart page.
- `[end_cart_link]`: Generate a link to end the cart page.
- `[start_shop_link]`: Generate a link to start the shop.
- `[end_shop_link]`: Generate a link to end the shop.
- `[start_contact_link]`: Generate a link to start the native contact page.
- `[end_contact_link]`: Generate a link to end the native contact page.
- `[llorem]`: Generate fake text.
- `[shop_url]`: Display the shop's URL.
- `[shop_name]`: Display the shop's name.
- `[theme_uri]`: Display the current theme's URL.
- `[category id="8" nb="8"]`: Display 8 products from category with ID 8.
- `[manufacturer id="2" nb="8"]`: Display 8 products from manufacturer with ID 2.
- `[brands nb="8"]`: Display 8 brand names with their associated logos. Optional `carousel=true`.
- `[storelocator]`: Show a store locator on any CMS page.
- `[subcategories id="2" nb="8"]`: Display 8 subcategories (name, image and link) of category 2.
- `[last-products 4]`: Display the last 4 products listed in the store. Supports `carousel=true`.
- `[best-sales 4]`: Display the 4 best-selling products in your store. Supports `carousel=true`.
- `[evercart]`: Display dropdown cart.
- `[evercontact]`: Display PrestaShop native contact form.
- `[everstore 4]`: Display store information for store ID 4 (several IDs can be separated with commas).
- `[video https://www.youtube.com/embed/35kwlY_RR08?si=QfwsUt9sEukni0Gj]`: Display a YouTube iframe of the video whose sharing URL is in the parameter (may also works with Vimeo, Dailymotion, and Vidyard).
- `[everaddtocart ref="1234" text="Add me to cart"]`: Creates an add to cart button for product reference 1234 with the text "Add me to cart". By clicking on the link, the product will be automatically added to the cart and the user will be redirected directly to the cart page. Also works in emails.
- `[everfaq tag="faq1"]`: Shows FAQs related to the faq tag
- `[productfeature id="2" nb="12" carousel="true"]`: Displays 12 products with the ID 2 feature, in the form of a carousel (the carousel is optional, you must have slick slider by activating it in the module configuration)
- `[productfeaturevalue id="2" nb="12" carousel="true"]`: Same as before, but this time concerns products that have the characteristic value id 2
- `[promo-products 10 carousel=true]`: Displays ten products on sale in a carousel format.
- `[best-sales 10 carousel=true]`: Displays the top ten best-selling products. Optional parameters: `days`, `orderby`, `orderway`.
- `[random_product nb="10" carousel=true]`: Displays ten random products in a carousel.
- `[linkedproducts nb="8" orderby="date_add" orderway="DESC"]`: Displays products linked to the current product in a Bootstrap carousel.
- `{hook h='displayHome'}`: Displays the `displayHome` hook (hooks are not allowed on modals)
- `[everinstagram]`: Display your latest Instagram photos.
- `[nativecontact]`: Embed the native PrestaShop contact form.
- `[everimg name="image.jpg" class="img-fluid" carousel=true]`: Display one or more CMS images. When `carousel=true` and multiple images are provided, a Bootstrap slideshow is rendered.
- `[displayQcdSvg name="icon" class="myclass" inline=true]`: Display a QCD SVG icon. Module available at [410 Gone](https://www.410-gone.fr/).
- `[qcdacf field objectType objectId]`: Display a value from QCD ACF fields. Module available at [410 Gone](https://www.410-gone.fr/).
- `[widget moduleName="mymodule" hookName="displayHome"]`: Render another module's widget.

- `[prettyblocks name="myzone"]`: Render a PrettyBlocks zone if the module is installed.
- `[everblock 3]`: Insert the content of block ID 3.
- `[cms id="1"]`: Display the content of CMS page ID 1.
### Contact form shortcodes
A contact form must start with the shortcode `[evercontactform_open]` and end with the shortcode `[evercontactform_close]`

- `[evercontact type="text" label="Your name"]` to display a text input field with the label "Your name"
- `[evercontact type="number" label="Your age"]` to display a numeric input field with the label "Your age"
- `[evercontact type="textarea" label="Message"]` to display a textarea input field with the label "Message"
- `[evercontact type="select" label="You are" values="Man,Woman,Other"]` to display a select field with the label "You are" and the options "Man, Woman, Other"
- `[evercontact type="radio" label="You are" values="Man,Woman,Other"]` is the same as select, but using radio buttons instead of select
- `[evercontact type="checkbox" label="You are" values="Man,Woman,Other"]` is the same as select, but using checkboxes instead of select
- `[evercontact type="file" label="Attachment"]` to display a file upload field
- `[evercontact type="hidden" label="Hidden field"]` to display a hidden field that will have the label and value "Hidden field"
- `[evercontact type="sento" label="me@email.fr"]` to display the recipient's email in a coded way. The recipient's email will not be clearly displayed on the pages. Not using this means sending the email to the email address defined in your store by default. You can specify multiple emails by separating them with commas. Be sure to use the EI Captcha module to secure email sending.
- `[evercontact type="submit" label="Submit"]` to display a submit button for your custom contact form

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
class `everblock-modal-button` and provide the block ID in a `data-evermodal`
attribute:

```html
<button class="everblock-modal-button" data-evermodal="12">Open modal</button>
```

When clicked, the module will load the corresponding modal content via AJAX and
display it using Bootstrap.

## Cache & logs

The module uses its own cache system in addition to the Prestashop one.

The cache directory is located in /var/cache/dev?prod/everblock/

The logs directory is located in /var/logs/

Clearing the native Prestashop cache will also clear the module cache, but the module will clear its own cache on a block expiry automatically.