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
        <img id="everlogo" src="{$everblock_dir|escape:'htmlall':'UTF-8'}logo.png" style="max-width: 120px;">
        <p>{l s='Thanks for using Team Ever\'s modules' mod='everblock'}.<br /></p>
        {if isset($block_admin_link) && $block_admin_link}
        <a href="{$block_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-success">{l s='Manage blocks' mod='everblock'}</a>
        {/if}
        {if isset($module_link) && $module_link}
        <a href="{$module_link|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-success">{l s='Module configuration' mod='everblock'}</a>
        {/if}
        <p>{l s='You can use shortcodes for your content' mod='everblock'}</p>
        <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {l s='See all shortcodes' mod='everblock'}
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <p class="dropdown-item px-2">[product 1] {l s='to show product ID 1' mod='everblock'}</p>
            <p class="dropdown-item px-2">[category id="8" nb="8"] {l s='to show 8 products from category ID 8' mod='everblock'}</p>
            <p class="dropdown-item px-2">[manufacturer id="2" nb="8"] {l s='to show 8 products from manufacturer ID 2' mod='everblock'}</p>
            <p class="dropdown-item px-2">[brands nb="8"] {l s='to show 8 brands name with their associated logos' mod='everblock'}</p>
            <p class="dropdown-item px-2">[entity_lastname] {l s='for customer or employee lastname' mod='everblock'}</p>
            <p class="dropdown-item px-2">[entity_firstname] {l s='for customer or employee firstname' mod='everblock'}</p>
            <p class="dropdown-item px-2">[entity_gender] {l s='for customer or employee gender' mod='everblock'}</p>
            <p class="dropdown-item px-2">[start_cart_link] {l s='for starting link to cart page' mod='everblock'}</p>
            <p class="dropdown-item px-2">[end_cart_link] {l s='for ending link to cart page' mod='everblock'}</p>
            <p class="dropdown-item px-2">[start_shop_link] {l s='for starting link to shop' mod='everblock'}</p>
            <p class="dropdown-item px-2">[end_shop_link] {l s='for ending link to shop' mod='everblock'}</p>
            <p class="dropdown-item px-2">[start_contact_link] {l s='for starting link to contact page' mod='everblock'}</p>
            <p class="dropdown-item px-2">[end_contact_link] {l s='for ending link to contact page' mod='everblock'}</p>
            <p class="dropdown-item px-2">[llorem] {l s='will generate fake text' mod='everblock'}</p>
            <p class="dropdown-item px-2">[shop_url] {l s='for shop url' mod='everblock'}</p>
            <p class="dropdown-item px-2">[shop_name] {l s='for shop name' mod='everblock'}</p>
            <p class="dropdown-item px-2">[theme_uri] {l s='for current theme url' mod='everblock'}</p>
          </div>
        </div>
    </div>
</div>