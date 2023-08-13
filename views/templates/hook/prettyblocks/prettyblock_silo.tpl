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
    <div class="everblock everblock-silos {$block.settings.css_class|escape:'htmlall':'UTF-8'} {$block.settings.bootstrap_class|escape:'htmlall':'UTF-8'}" {if isset($block.settings.bg_color) && $block.settings.bg_color} style="background-color:{$block.settings.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
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