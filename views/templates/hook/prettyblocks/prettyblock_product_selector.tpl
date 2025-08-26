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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<div class="mt-2{if $block.settings.default.container} container{/if}"  style="{if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
  {if $block.settings.slider}
    {if $block.extra.products}
    <section class="prettyblocks-slider container-fluid px-0 {if $block.settings.default.container}container{/if}">
      <div class="ever-wrapper overflow-auto px-2 px-md-0 pb-2">
        <div class="d-flex flex-nowrap gap-3 pe-1">
          {foreach from=$block.extra.products item=product}
            <div class="flex-shrink-0 prettyblocks-slider-item" style="width: 90%; max-width: 90%;">
              {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses="w-100"}
            </div>
          {/foreach}
        </div>
      </div>
    </section>
    <style>
      @media (min-width: 768px) {
        .prettyblocks-slider-item {
          width: 25% !important;
          max-width: 25% !important;
        }
      }
    </style>
    {/if}
  {else}
    {if $block.extra.products}
    <section class="ever-featured-products featured-products clearfix mt-3 product_selector">
      <div class="products row">
        {hook h='displayBeforeProductMiniature' products=$block.extra.products origin='product_selector' page_name=$page.page_name}
        {foreach from=$block.extra.products item=product}
          {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses="col-xs-12 col-sm-6 col-lg-4 col-xl-3"}
        {/foreach}
        {hook h='displayAfterProductMiniature' products=$block.extra.products origin='product_selector' page_name=$page.page_name}
      </div>
    </section>
    {/if}
  {/if}
</div>
{if $block.settings.default.force_full_width || $block.settings.default.container}
  </div>
{/if}
</div>
