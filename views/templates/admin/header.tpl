{*
 * 2019-2021 Team Ever
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
    <div class="col-md-6">
        <img id="everlogo" src="{$everblock_dir|escape:'htmlall':'UTF-8'}logo.png" style="max-width: 120px;">
        <p>{l s='Thanks for using Team Ever\'s modules' mod='everblock'}.<br /></p>
        <p>{l s='You can use shortcodes for your content' mod='everblock'}</p>
        <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {l s='See all shortcodes' mod='everblock'}
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <p class="dropdown-item px-2">[entity_lastname] {l s='for customer or employee lastname' mod='everblock'}</p>
            <p class="dropdown-item px-2">[entity_firstname] {l s='for customer or employee firstname' mod='everblock'}</p>
            <p class="dropdown-item px-2">[entity_gender] {l s='for customer or employee gender' mod='everblock'}</p>
            <p class="dropdown-item px-2">[start_cart_link] {l s='for starting link to cart page' mod='everblock'}</p>
            <p class="dropdown-item px-2">[end_cart_link] {l s='for ending link to cart page' mod='everblock'}</p>
            <p class="dropdown-item px-2">[start_shop_link] {l s='for starting link to shop' mod='everblock'}</p>
            <p class="dropdown-item px-2">[end_shop_link] {l s='for ending link to shop' mod='everblock'}</p>
            <p class="dropdown-item px-2">[start_contact_link] {l s='for starting link to contact page' mod='everblock'}</p>
            <p class="dropdown-item px-2">[end_contact_link] {l s='for ending link to contact page' mod='everblock'}</p>
            <p class="dropdown-item px-2">[shop_url] {l s='for shop url' mod='everblock'}</p>
            <p class="dropdown-item px-2">[shop_name] {l s='for shop name' mod='everblock'}</p>
          </div>
        </div>
    </div>
    <div class="col-md-6">
        <h4>{l s='How to be first on Google pages ?' mod='everblock'}</h4>
        <p>{l s='We have created the best SEO module, by working with huge websites and SEO societies' mod='everblock'}</p>
        <p>
            <a href="https://addons.prestashop.com/fr/seo-referencement-naturel/39489-ever-ultimate-seo.html" target="_blank">{l s='See the best SEO module on Prestashop Addons' mod='everblock'}</a>
        </p>
    </div>
</div>