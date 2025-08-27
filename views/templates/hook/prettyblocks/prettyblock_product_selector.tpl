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
      {assign var='carouselId' value="productSelectorCarousel-`$block.id_prettyblocks`"}
      {assign var='chunks' value=$block.extra.products|@array_chunk:4}
      <section class="ever-featured-products featured-products clearfix mt-3 product_selector">
        <div id="{$carouselId}" class="carousel slide" data-bs-ride="carousel" data-ride="carousel">
          <div class="carousel-indicators">
            {foreach from=$chunks item=chunk name=indicators}
              <button type="button" data-bs-target="#{$carouselId}" data-target="#{$carouselId}" data-bs-slide-to="{$smarty.foreach.indicators.index}" data-slide-to="{$smarty.foreach.indicators.index}" class="{if $smarty.foreach.indicators.first}active{/if}" {if $smarty.foreach.indicators.first}aria-current="true"{/if} aria-label="Slide {$smarty.foreach.indicators.iteration}"></button>
            {/foreach}
          </div>
          <div class="carousel-inner">
            {foreach from=$chunks item=chunk name=slides}
              <div class="carousel-item{if $smarty.foreach.slides.first} active{/if}">
                <div class="row">
                  {foreach from=$chunk item=product}
                    <div class="col-6 col-lg-3">
                      {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses="w-100"}
                    </div>
                  {/foreach}
                </div>
              </div>
            {/foreach}
          </div>
          <a class="carousel-control-prev" href="#{$carouselId}" role="button" data-slide="prev" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only visually-hidden">{l s='Previous' d='Shop.Theme.Actions'}</span>
          </a>
          <a class="carousel-control-next" href="#{$carouselId}" role="button" data-slide="next" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only visually-hidden">{l s='Next' d='Shop.Theme.Actions'}</span>
          </a>
        </div>
        <style>
          #{$carouselId} .carousel-indicators button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #000;
          }
        </style>
      </section>
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
