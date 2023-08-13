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
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    <div class="featured-products clearfix mt-3 everblock {$block.settings.css_class|escape:'htmlall':'UTF-8'} {$block.settings.bootstrap_class|escape:'htmlall':'UTF-8'}" {if isset($block.settings.bg_color) && $block.settings.bg_color} style="background-color:{$block.settings.bg_color|escape:'htmlall':'UTF-8'};"{/if}>

        <div class="products row">
            {if isset($block.extra.presenteds) && $block.extra.presenteds}
            {include file="catalog/_partials/productlist.tpl" products=$block.extra.presenteds cssClass="row" productClass="col-xs-12 col-sm-6 col-lg-4 col-xl-3"}
            {/if}
        </div>
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->