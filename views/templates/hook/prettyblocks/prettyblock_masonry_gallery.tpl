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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}

<!-- Module Ever Block -->
<div class="mt-2{if $block.settings.default.container} container{/if}"  style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    <div class="everblock-masonry-gallery" data-gallery-id="{$block.id_prettyblocks}">
        {foreach from=$block.states item=state key=key}
            {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}

            <div class="masonry-item {if $state.css_class}{$state.css_class}{/if}" style="{$prettyblock_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
                <picture>
                    <source srcset="{$state.image.url}" type="image/webp">
                    <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
                    <img src="{$state.image.url|replace:'.webp':'.jpg'}" class="img img-fluid cursor-pointer lazyload" {if isset($state.alt)}alt="{$state.alt|escape:'htmlall'}"{else}alt="{$shop.name}"{/if} title="{$state.name|escape:'htmlall'}" data-src="{$state.image.url}" data-index="{$key}"{if $state.image_width} width="{$state.image_width|escape:'htmlall':'UTF-8'}"{/if}{if $state.image_height} height="{$state.image_height|escape:'htmlall':'UTF-8'}"{/if} loading="lazy">
                </picture>
            </div>
        {/foreach}
    </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
