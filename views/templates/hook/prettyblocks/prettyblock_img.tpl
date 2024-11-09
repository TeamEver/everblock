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
<div class="{if $block.settings.default.container}container{/if}" style="
    {if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'}%;{/if}
    {if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'}%;{/if}
    {if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'}%;{/if}
    {if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'}%;{/if}
    {if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'}%;{/if}
    {if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'}%;{/if}
    {if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'}%;{/if}
    {if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'}%;{/if}
    {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    <div class="mt-4">
        {foreach from=$block.states item=state key=key}
            <div id="block-{$block.id_prettyblocks}-{$key}" style="
                {if isset($state.padding_left) && $state.padding_left}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'}%;{/if}
                {if isset($state.padding_right) && $state.padding_right}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'}%;{/if}
                {if isset($state.padding_top) && $state.padding_top}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'}%;{/if}
                {if isset($state.padding_bottom) && $state.padding_bottom}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'}%;{/if}
                {if isset($state.margin_left) && $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'}%;{/if}
                {if isset($state.margin_right) && $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'}%;{/if}
                {if isset($state.margin_top) && $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'}%;{/if}
                {if isset($state.margin_bottom) && $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'}%;{/if}
                {if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
            ">
                {if isset($state.url) && $state.url}
                    <a href="{$state.url|escape:'htmlall':'UTF-8'}">
                {/if}
                <img src="{$state.banner.url}" 
                     {if isset($state.alt) && $state.alt} alt="{$state.alt}"{else} alt="{$shop.name}"{/if}
                     {if isset($state.image_width) && $state.image_width} width="{$state.image_width|escape:'htmlall':'UTF-8'}"{/if}
                     {if isset($state.image_height) && $state.image_height} height="{$state.image_height|escape:'htmlall':'UTF-8'}"{/if}
                     class="img img-fluid lazyload" loading="lazy">
                {if isset($state.url) && $state.url}
                    </a>
                {/if}
            </div>
        {/foreach}
    </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
