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
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    <div class="everblock everblock-silos {$block.settings.css_class|escape:'htmlall':'UTF-8'}"  style="{if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'}%;{/if}{if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'}%;{/if}{if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'}%;{/if}{if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'}%;{/if}{if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'}%;{/if}{if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'}%;{/if}{if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'}%;{/if}{if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'}%;{/if}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
        {if isset($block.extra.silos) && $block.extra.silos}
        {if $block.extra.is_product && isset($smarty.get.id_product) && $smarty.get.id_product}
        {l s='Find this product in our universes' mod='everblock'} 
        {else}
        {l s='Find this category in our universes' mod='everblock'} 
        {/if}
        {foreach from=$block.extra.silos item=silo key=key}
        {assign var="isLastKey" value=$key === ($block.extra.silos|@count - 1)}
        <a href="{$silo.link}" title="{$silo.name}">
            {$silo.name}{if !$isLastKey}, {/if}
        </a>
        {/foreach}
        {/if}
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->