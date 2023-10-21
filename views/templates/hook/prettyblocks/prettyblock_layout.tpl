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
<div class="{if $block.settings.default.container}container{/if}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
    {foreach from=$block.states item=state key=key}{if $state.order == '100%'}
        {assign var="bootstrapClass" value="col-12"}
    {elseif $state.order == '50%'}
        {assign var="bootstrapClass" value="col-12 col-md-6"}
    {elseif $state.order == '33,33%'}
        {assign var="bootstrapClass" value="col-12 col-md-4"}
    {elseif $state.order == '25%'}
        {assign var="bootstrapClass" value="col-12 col-md-3"}
    {elseif $state.order == '16,67%'}
        {assign var="bootstrapClass" value="col-12 col-md-2"}
    {else}
        {assign var="bootstrapClass" value="col-12"}
    {/if}
    {if $state.link}
    {if $state.obfuscate}
    {assign var="obflink" value=$state.link|base64_encode}
    <span class="obflink" data-obflink="{$obflink}">
    {else}
    <a href="{$state.link}" title="{$state.name}"{if $state.target_blank} target="_blank"{/if}>
    {/if}
    {/if}
    <div class="{$bootstrapClass}">
      {$state.content nofilter}
    </div>
    {if $state.link}
    {if $state.obfuscate}
    </span>
    {else}
    </a>
    {/if}
    {/if}
    {/foreach}
</div>