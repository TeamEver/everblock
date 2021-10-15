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
        <p class="alert alert-warning">
            {l s='This module is free and will always be ! You can support our free modules by making a donation by clicking the button below' mod='everblock'}
        </p>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="display: flex;justify-content: center;">
        <input type="hidden" name="cmd" value="_s-xclick" />
        <input type="hidden" name="hosted_button_id" value="3LE8ABFYJKP98" />
        <input type="image" src="https://www.team-ever.com/wp-content/uploads/2019/06/appel_a_dons-1.jpg" border="0" name="submit" title="Soutenez le développement des modules gratuits de Team Ever !" alt="Soutenez le développement des modules gratuits de Team Ever !" style="width: 150px;" />
        <img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
        </form>
    </div>
</div>