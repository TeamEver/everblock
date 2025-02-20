{*
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
*}
<div class="panel row">
    <h3><i class="icon icon-smile"></i> {l s='Ever Block' mod='everblock'} {$everblock_version|escape:'htmlall':'UTF-8'}</h3>
    <div class="col-md-12">
        <a href="#everlogobottom">
          <img id="everlogotop" src="{$everblock_dir|escape:'htmlall':'UTF-8'}logo.png" style="max-width: 120px;">
        </a>
        <p>{l s='Thanks for using Team Ever\'s modules' mod='everblock'}<br /></p>
        {if isset($block_admin_link) && $block_admin_link}
        <a href="{$block_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-success">{l s='Manage blocks' mod='everblock'}</a>
        {/if}
        {if isset($faq_admin_link) && $faq_admin_link}
        <a href="{$faq_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-success">{l s='Manage FAQ' mod='everblock'}</a>
        {/if}
        {if isset($hook_admin_link) && $hook_admin_link}
        <a href="{$hook_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-success">{l s='Manage all hooks' mod='everblock'}</a>
        {/if}
        {if isset($shortcode_admin_link) && $shortcode_admin_link}
        <a href="{$shortcode_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-success">{l s='Manage shortcodes' mod='everblock'}</a>
        {/if}
    </div>
</div>