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
This module is now compatible with the Pretty Blocks page builder. [Find this free module here.](https://prettyblocks.io/)

## Documentation (French only)
Available at https://www.team-ever.com/prestashop-utilisation-du-module-ever-block-html/

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

- `[product 1]`: Display product with ID 1.
- `[product 1,2,3]`: Display products with IDs 1, 2, and 3.
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
- `[brands nb="8"]`: Display 8 brand names with their associated logos.
- `[storelocator]`: Show a store locator on any CMS page.
- `[subcategories id="2" nb="8"]`: Display 8 subcategories (name, image, and link) of category 2.
- `[last-products 4]`: Display the last 4 products listed in the store.
- `[best-sales 4]`: Display the 4 best-selling products in your store.
- `[evercart]`: Display dropdown cart.
- `[evercontact]`: Display PrestaShop native contact form.
- `[everstore 4]`: Display store information id 1.
- `[video https://www.youtube.com/embed/35kwlY_RR08?si=QfwsUt9sEukni0Gj]`: Display a YouTube iframe of the video whose sharing URL is in the parameter (may also works with Vimeo, Dailymotion, and Vidyard).

