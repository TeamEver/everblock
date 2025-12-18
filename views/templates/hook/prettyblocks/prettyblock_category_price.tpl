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
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if isset($block.states) && $block.states}
    <div class="d-none d-md-block">
      {if $block.settings.default.force_full_width}
        <div class="row row-cols-1 row-cols-md-5 gx-0 no-gutters">
      {elseif $block.settings.default.container}
        <div class="row">
      {/if}
        {assign var=imageHeight value=$block.settings.default.image_height|default:150}
        {foreach from=$block.states item=state key=key}
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_category_price_state_spacing_style'}
          {assign var=data value=$block.extra.state_data[$key]|default:null}
          {if $data}
            <div id="block-{$block.id_prettyblocks}-{$key}" class="col text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_category_price_state_spacing_style}">
              <a href="{$data.category_link|default:'#'}" class="d-flex flex-column align-items-center text-decoration-none h-100" title="{$data.title|escape:'htmlall'}">
                {if $data.image_url}
                  <div class="d-flex align-items-center justify-content-center mb-2 w-100" style="height:{$imageHeight}px;">
                    <img src="{$data.image_url|escape:'htmlall'}" alt="{$data.title|unescape:'html'|escape:'html':'UTF-8'}" class="img-fluid h-100" style="object-fit:contain;" loading="lazy"{if $data.image_width && $data.image_height} width="{$data.image_width|escape:'htmlall'}" height="{$data.image_height|escape:'htmlall'}"{/if}>
                  </div>
                {/if}
                {if $data.title}
                  <p class="h6 mb-2 text-center">{$data.title|unescape:'html'|escape:'html':'UTF-8'}</p>
                {/if}
                {if $data.min_price !== false}
                  <span class="small d-block mt-auto text-center min-price">{l s='From' mod='everblock'} {Tools::displayPrice($data.min_price)}</span>
                {/if}
              </a>
            </div>
          {/if}
        {/foreach}
      {if $block.settings.default.force_full_width || $block.settings.default.container}
        </div>
      {/if}
    </div>
    <div class="d-block d-md-none">
      <div class="overflow-auto" style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch;">
        <div class="d-flex flex-nowrap">
        {assign var=imageHeight value=$block.settings.default.image_height|default:150}
        {foreach from=$block.states item=state key=key}
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_category_price_state_spacing_style'}
          {assign var=data value=$block.extra.state_data[$key]|default:null}
          {if $data}
            <div id="block-{$block.id_prettyblocks}-{$key}-mobile" class="text-center me-3{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="flex:0 0 calc(100% / 3.5 - 1rem); scroll-snap-align:start; {$prettyblock_category_price_state_spacing_style}">
              <a href="{$data.category_link|default:'#'}" class="d-flex flex-column align-items-center text-decoration-none h-100" title="{$data.title|escape:'htmlall'}">
                {if $data.image_url}
                    <div class="d-flex align-items-center justify-content-center mb-2 w-100" style="height:{$imageHeight}px;">
                      <img src="{$data.image_url|escape:'htmlall'}" alt="{$data.title|unescape:'html'|escape:'html':'UTF-8'}" class="img-fluid h-100" style="object-fit:contain;" loading="lazy"{if $data.image_width && $data.image_height} width="{$data.image_width|escape:'htmlall'}" height="{$data.image_height|escape:'htmlall'}"{/if}>
                    </div>
                {/if}
                {if $data.title}
                    <p class="h6 mb-2 text-center">{$data.title|unescape:'html'|escape:'html':'UTF-8'}</p>
                {/if}
                {if $data.min_price !== false}
                    <span class="small d-block mt-auto text-center">{l s='From' mod='everblock'} {Tools::displayPrice($data.min_price)}</span>
                  {/if}
                </a>
              </div>
            {/if}
          {/foreach}
        </div>
      </div>
    </div>
  {/if}
</div>
