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
<div class="{if $block.settings.default.container}container{/if} everblock-video-products" style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
        <div class="row justify-content-center" id="video-products-{$block.id_prettyblocks}" data-fetch-url="{$link->getModuleLink('everblock', 'videoproducts')|escape:'htmlall':'UTF-8'}" data-products-label="{l s='Products featured in this video' mod='everblock'}" data-token="{$static_token}">
          {foreach from=$block.states item=state key=key}
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}

          <div class="col mb-4 col-md-4" style="{$prettyblock_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
            <div class="card {if isset($state.css_class) && $state.css_class}{$state.css_class|escape:'htmlall':'UTF-8'}{/if}">
              <img src="{$state.thumbnail.url|replace:'.webp':'.jpg'}" class="img-fluid cursor-pointer" alt="{$state.title}" title="{$state.title}" loading="lazy" data-block="{$block.id_prettyblocks}" data-key="{$key}" data-video-url="{$state.video_url|escape:'htmlall':'UTF-8'}" data-description="{$state.description|escape:'htmlall':'UTF-8'}" data-product-ids="{$state.product_ids|escape:'htmlall':'UTF-8'}">
              {if $state.title || $state.description}
              <div class="card-body">
                {if $state.title}<p class="card-title h5 text-center">{$state.title}</p>{/if}
                {if $state.description}<span class="card-text">{$state.description nofilter}</span>{/if}
              </div>
              {/if}
            </div>
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
