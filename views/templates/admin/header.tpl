{*
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
*}
<div class="panel row">
    <h3><i class="icon icon-smile"></i> {l s='Ever Block' mod='everblock'}</h3>
    <div class="col-md-12">
        <a href="#everlogobottom">
          <img id="everlogotop" src="{$everblock_dir|escape:'htmlall':'UTF-8'}logo.png" style="max-width: 120px;">
        </a>
        <p>{l s='Thanks for using Team Ever\'s modules' mod='everblock'}<br /></p>
        {if isset($block_admin_link) && $block_admin_link}
        <a href="{$block_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-success">{l s='Manage blocks' mod='everblock'}</a>
        {/if}
        {if isset($module_link) && $module_link}
        <a href="{$module_link|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-success">{l s='Module configuration' mod='everblock'}</a>
        {/if}
        <p>{l s='You can use shortcodes for your content' mod='everblock'}</p>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {l s='Smarty & shortcodes list' mod='everblock'}
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" onclick="event.stopPropagation();">
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
                <li><p>[product 1] {l s='to show product ID 1' mod='everblock'}</p></li>
                <li><p>[product 1,2,3] {l s='to show products ID 1, 2 and 3' mod='everblock'}</p></li>
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
                <li><p>[everstore 4] {l s='to display store information id 1' mod='everblock'}</p></li>
                <li><p>[video [video https://www.youtube.com/embed/35kwlY_RR08?si=QfwsUt9sEukni0Gj]] {l s='to display a YouTube iframe of the video whose sharing URL is in parameter (also works with Vimeo, Dailymotion, and Vidyard)' mod='everblock'}</p></li>
                <li><p>[evercart] {l s='to display dropdown cart' mod='everblock'}</p></li>
                <li><p>[evercontact] {l s='to display Prestashop native contact form' mod='everblock'}</p></li>
                <li><p>[everstore 4] {l s='to display store information id 1' mod='everblock'}</p></li>
            </ul>
        </div>
    </div>
</div>