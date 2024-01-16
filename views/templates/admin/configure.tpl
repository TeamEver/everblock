{*
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
*}

<div class="alert alert-info">
    <h4>{l s='What is a hook ?' mod='everblock'}</h4>
    <p>{l s='A hook is a location where you can "plug" a module. Very numerous on Prestashop, they are saved in your database, and displayed by your theme and your modules.' mod='everblock'}</p>
    <p>{l s='These hooks are available both on your site, but also in the administration of your store.' mod='everblock'}</p>
    <p>{l s='You can personalize the messages using the secure shortcodes offered by Ever Block' mod='everblock'}</p>
    <p>{l s='You can modify, add and delete display hooks by going to the "Ever Block" tab then "Hook management"' mod='everblock'}</p>
    <br>
    <h4>{l s='Where do I manage my blocks ?' mod='everblock'}</h3>
    <p>{l s='A new tab has appeared in the administration of your store, named "Ever Block"' mod='everblock'}</p>
    <p>{l s='You will be able to manage your blocks according to many criteria, such as the client group, the device used (mobile, tablet, computer)...' mod='everblock'}</p>
    <p>{l s='You can create your own shortcodes to automate content on your store.' mod='everblock'}</p>
    <p>{l s='And you will also be able to manage your hooks, in order to create them with a view to inserting new hooks into your theme.' mod='everblock'}</p>
    <br>
    <h4>{l s='What about overrides ?' mod='everblock'}</h3>
    <p>{l s='This module does not perform any overrides, it does not need any.' mod='everblock'}</p>
    <br>
    <h4>{l s='How can I change blocks design ?' mod='everblock'}</h3>
    <p>{l s='Each block has specific class and identifiers. You can design the blocks with custom CSS code.' mod='everblock'}</p>
    <p>{l s='CSS code should be added on :' mod='everblock'}<code>/themes/YOUR_THEME/asssets/css/custom.css</code></p>
    <p>{l s='However, you can add your personalized HTML code below, which will be cached and returned to your store while respecting Prestashop standards.' mod='everblock'}</p>
    <br>
    <h4>{l s='Can the shortcodes be used anywhere on my store?' mod='everblock'}</h3>
    <p>{l s='If you are using Prestashop 1.7 or 8, the shortcodes will indeed be accessible on any page of your store' mod='everblock'}</p>
    <br>
    <h4>{l s='Is it really free ?' mod='everblock'}</h3>
    <p>{l s='This module has always been free and will always be.' mod='everblock'}</p>
    <p>{l s='You can support our free modules developpment by making a donation. This will help us make more free modules and help e-merchants. Thanks for your support !' mod='everblock'}</p>
    <br>
    <h4>{l s='Smarty vars list' mod='everblock'}</h3>
    <ul>
        <li><p>{literal}$currency.name{/literal} => {l s='the name of the currency (euro, dollar, pound sterling, etc.)' mod='everblock'}</p></li>
        <li><p>{literal}$currency.iso_code{/literal} => {l s='the ISO code of the currency (like EUR for the euro)' mod='everblock'}</p></li>
        <li><p>{literal}$currency.sign{/literal} => {l s='the acronym of the currency displayed (so € or $)' mod='everblock'}</p></li>
        <li><p>{literal}$currency.iso_code_num{/literal} => {l s='the ISO code number of this currency (like 978 for the euro)' mod='everblock'}</p></li>
        <li><p>{literal}$shop.name{/literal} => {l s='shop name' mod='everblock'}</p></li>
        <li><p>{literal}$shop.email{/literal} => {l s='email associated with the store' mod='everblock'}</p></li>
        <li><p>{literal}$shop.logo{/literal} => {l s='logo of the store (which can be found in “Appearance” then “Theme and logo”)' mod='everblock'}</p></li>
        <li><p>{literal}$shop.favicon{/literal} => {l s='the favicon of your store (also in the same place as the logos and the theme)' mod='everblock'}</p></li>
        <li><p>{literal}$shop.phone{/literal} => {l s='phone of your store' mod='everblock'}</p></li>
        <li><p>{literal}$shop.fax{/literal} => {l s='and finally the fax from your store' mod='everblock'}</p></li>
        <li><p>{literal}$customer.lastname{/literal} => {l s='the last name of the connected customer' mod='everblock'}</p></li>
        <li><p>{literal}$customer.firstname{/literal} => {l s='the first name of the connected customer' mod='everblock'}</p></li>
        <li><p>{literal}$customer.email{/literal} => {l s='the customer\'s email address' mod='everblock'}</p></li>
        <li><p>{literal}$customer.birthday{/literal} => {l s='date of birth of the customer (but this is no longer mandatory from now on)' mod='everblock'}</p></li>
        <li><p>{literal}$customer.newsletter{/literal} => {l s='if he is subscribed to the newsletter (boolean therefore)' mod='everblock'}</p></li>
        <li><p>{literal}$customer.ip_registration_newsletter{/literal} => {l s='newsletter registration IP address' mod='everblock'}</p></li>
        <li><p>{literal}$customer.optin{/literal} => {l s='yes or no, has the customer agreed to receive offers from your partners?' mod='everblock'}</p></li>
        <li><p>{literal}$customer.date_add{/literal} => {l s='customer creation date' mod='everblock'}</p></li>
        <li><p>{literal}$customer.date_upd{/literal} => {l s='customer last modified date' mod='everblock'}</p></li>
        <li><p>{literal}$customer.id{/literal} => {l s='customer identifier (its database ID)' mod='everblock'}</p></li>
        <li><p>{literal}$customer.id_default_group{/literal} => {l s='identifier of the default customer group of this customer' mod='everblock'}</p></li>
        <li><p>{literal}$customer.is_logged{/literal} => {l s='is the customer logged in?' mod='everblock'}</p></li>
        <li><p>{literal}$page.meta.title{/literal} => {l s='title tag of your page (65 characters maximum!)' mod='everblock'}</p></li>
        <li><p>{literal}$page.meta.description{/literal} => {l s='meta description tag of your page (no more than 165 characters, don\'t go below 90!)' mod='everblock'}</p></li>
        <li><p>{literal}$page.page_name{/literal} => {l s='name of the page you are on (like index, product, category…)' mod='everblock'}</p></li>
        <li><p>{literal}$urls.base_url{/literal} => {l s='this is the URL of the home page of your Prestashop' mod='everblock'}</p></li>
        <li><p>{literal}$urls.current_url{/literal} => {l s='the page you are on!' mod='everblock'}</p></li>
        <li><p>{literal}$urls.shop_domain_url{/literal} => {l s='the domain name of the store' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_ps_url{/literal} => {l s='the URL of the /img directory of your Prestashop' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_cat_url{/literal} => {l s='the URL of the category images, therefore in /img/c' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_lang_url{/literal} => {l s='the URL of the site’s language images' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_prod_url{/literal} => {l s='the URL of the product images, therefore /img/p' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_manu_url{/literal} => {l s='the URL of the manufacturers images, therefore /img/m' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_sup_url{/literal} => {l s='the URL of the images linked to the suppliers' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_ship_url{/literal} => {l s='the URL of images linked to carriers' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_store_url{/literal} => {l s='the URL of your store images' mod='everblock'}</p></li>
        <li><p>{literal}$urls.img_url{/literal} => {l s='the URL of the images in your theme, so /themes/yourtheme/assets/img' mod='everblock'}</p></li>
        <li><p>{literal}$urls.css_url{/literal} => {l s='the URL of your theme\'s CSS files, so /themes/yourtheme/assets/css' mod='everblock'}</p></li>
        <li><p>{literal}$urls.js_url{/literal} => {l s='the URL of your theme\'s javascript files, so /themes/yourtheme/assets/js' mod='everblock'}</p></li>
        <li><p>{literal}$urls.pic_url{/literal} => {l s='the URL of the /upload directory' mod='everblock'}</p></li>
        <li><p>{literal}{hook h='displayFooter'}{/literal} => {l s='show displayFooter hook (you can try with every display hook' mod='everblock'}</p></li>
    </ul>
    <br>
    <h4>{l s='Shortcodes list' mod='everblock'}</h3>
    <ul>
        <li><p>[product 1] {l s='to show product ID 1' mod='everblock'}</p></li>
        <li><p>[product 1,2,3] {l s='to show products ID 1, 2 and 3' mod='everblock'}</p></li>
        <li><p>[random_product nb="4"] {l s='to show 4 random products' mod='everblock'}</p></li>        
        <li><p>[entity_lastname] {l s='for customer lastname' mod='everblock'}</p></li>
        <li><p>[entity_firstname] {l s='for customer firstname' mod='everblock'}</p></li>
        <li><p>[entity_gender] {l s='for customer gender' mod='everblock'}</p></li>
        <li><p>[start_cart_link] {l s='for starting link to cart page' mod='everblock'}</p></li>
        <li><p>[end_cart_link] {l s='for ending link to cart page' mod='everblock'}</p></li>
        <li><p>[start_shop_link] {l s='for starting link to shop' mod='everblock'}</p></li>
        <li><p>[end_shop_link] {l s='for ending link to shop' mod='everblock'}</p></li>
        <li><p>[start_contact_link] {l s='for starting link to native contact page' mod='everblock'}</p></li>
        <li><p>[end_contact_link] {l s='for ending link to native contact page' mod='everblock'}</p></li>
        <li><p>[llorem] {l s='will generate fake text' mod='everblock'}</p></li>
        <li><p>[shop_url] {l s='for shop url' mod='everblock'}</p></li>
        <li><p>[shop_name] {l s='for shop name' mod='everblock'}</p></li>
        <li><p>[theme_uri] {l s='for current theme url' mod='everblock'}</p></li>
        <li><p>[category id="8" nb="8"] {l s='to show 8 products from category ID 8' mod='everblock'}</p></li>
        <li><p>[manufacturer id="2" nb="8"] {l s='to show 8 products from manufacturer ID 2' mod='everblock'}</p></li>
        <li><p>[brands nb="8"] {l s='to show 8 brands name with their associated logos' mod='everblock'}</p></li>
        <li><p>[storelocator] {l s='to show a store locator on any CMS page' mod='everblock'}</p></li>
        <li><p>[subcategories id="2" nb="8"] {l s='to display 8 subcategories (name, image and link) of category 2' mod='everblock'}</p></li>
        <li><p>[last-products 4] {l s='to display the last 4 products listed in the store' mod='everblock'}</p></li>
        <li><p>[best-sales 4] {l s='to display the 4 best-selling products in your store' mod='everblock'}</p></li>
        <li><p>[evercart] {l s='to display dropdown cart' mod='everblock'}</p></li>
        <li><p>[nativecontact] {l s='to display Prestashop native contact form' mod='everblock'}</p></li>
        <li><p>[everstore 4] {l s='to display store information id 1' mod='everblock'}</p></li>
        <li><p>[video https://www.youtube.com/embed/35kwlY_RR08?si=QfwsUt9sEukni0Gj] {l s='to display a YouTube iframe of the video whose sharing URL is in parameter (also works with Vimeo, Dailymotion, and Vidyard)' mod='everblock'}</p></li>
    </ul>
    <br>
    <h4>{l s='Create a custom contact form using shortcodes' mod='everblock'}</h3>
    <p>{l s='A contact form must start with the shortcode [evercontactform_open] and end with the shortcode [evercontactform_close]' mod='everblock'}</p>
    <p>{l s='You can add the following fields between these two shortcodes:' mod='everblock'}</p>
    <ul>
        <li><p>[evercontact type="text" label="Your name"] {l s='to display a text input field with the label "Your name"' mod='everblock'}</p></li>
        <li><p>[evercontact type="number" label="Your age"] {l s='to display a number input field with the label "Your age"' mod='everblock'}</p></li>
        <li><p>[evercontact type="textarea" label="Message"] {l s='to display a textarea input field with the label "Message"' mod='everblock'}</p></li>
        <li><p>[evercontact type="select" label="You are" values="Man,Woman,Other"] {l s='to display a select field with the label "Your are" and options "Man,Woman,Other"' mod='everblock'}</p></li>
        <li><p>[evercontact type="radio" label="You are" values="Man,Woman,Other"] {l s='is same than select, but using radio buttons instead of select' mod='everblock'}</p></li>
        <li><p>[evercontact type="checkbox" label="You are" values="Man,Woman,Other"] {l s='is same than select, but using checkbox buttons instead of select' mod='everblock'}</p></li>
        <li><p>[evercontact type="file" label="Pièce jointe"] {l s='to display a file upload field' mod='everblock'}</p></li>
        <li><p>[evercontact type="hidden" label="Champ caché"] {l s='to display a hidden field with value & label "Champ caché"' mod='everblock'}</p></li>
        <li><p>[evercontact type="submit" label="Submit"] {l s='to show a submit button for your custom contact form' mod='everblock'}</p></li>
    </ul>
    <br>
    <h4>{l s='Management of product tabs' mod='everblock'}</h3>
    <p>{l s='You have an additional tab for each product.' mod='everblock'}</p>
    <p>{l s='In your store administration, select the “Module” tab in the product sheet then select Everblock.' mod='everblock'}</p>
    <p>{l s='You will be able to enter a title for the personalized tab as well as its content.' mod='everblock'}</p>
    <br>
    <h4>{l s='Using PrettyBlocks (from PrestaSafe)' mod='everblock'}</h3>
    <p>{l s='You can also use the shortcodes with the Pretty Blocks page builder' mod='everblock'}</p>
    <p>
    {l s='Pretty Blocks is freely accessible here:' mod='everblock'}
    <a href="https://prettyblocks.io/" target="_blank">https://prettyblocks.io/</a>
    </p>
    <br>
    <h4>{l s='Using QCD ACF (from 410 Gone)' mod='everblock'}</h3>
    <p>{l s='The 410 Gone QCD ACF module allows you to add text, textarea, color, etc. fields to almost all Prestashop elements (order, product, category, characteristic, etc.)' mod='everblock'}</p>
    <p>{l s='If the module is installed on your store, you will be able, for example, to enter the following shortcode:' mod='everblock'}</p>
    <ul>
        <li>[qcdacf text_bas_page category 8]</li>
    </ul>
    <p>{l s='This will display for category ID 8 the QCD ACF content named text_bas_page' mod='everblock'}</p>
    <p>{l s='If no content is found, the shortcode will be hidden.' mod='everblock'}</p>
    <p>
    {l s='410 Gone agency can be contacted here:' mod='everblock'}
    <a href="https://www.410-gone.fr/e-commerce/prestashop.html" target="_blank">https://www.410-gone.fr/e-commerce/prestashop.html</a>
    </p>
    <br>
</div>
<div class="alert alert-warning">
    <h4>{l s='Troubleshooting' mod='everblock'}</h3>
    <p>{l s='If an HTML block is not displayed, first check that the hook is present in your theme files.' mod='everblock'}</p>
    <p>{l s='If a shortcode is not displayed correctly, check the logs in your store\'s advanced settings.' mod='everblock'}</p>
    <p>{l s='If you don\'t see your content or your content hasn\'t changed, make sure to clear your store\'s cache.' mod='everblock'}</p>
    <br>
</div>