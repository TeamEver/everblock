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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}

{if isset($block.extra.products) && $block.extra.products}
  {assign var='desktopItems' value=$block.settings.items_per_slide_desktop|default:4|intval}
  {if $desktopItems < 1}
    {assign var='desktopItems' value=1}
  {/if}
  {assign var='mobileItems' value=$block.settings.items_per_slide_mobile|default:1|intval}
  {if $mobileItems < 1}
    {assign var='mobileItems' value=1}
  {/if}
  {assign var='tabletItems' value=2}

  {math equation="floor(12 / x)" x=$mobileItems assign='mobileColumnWidth'}
  {math equation="floor(12 / x)" x=$tabletItems assign='tabletColumnWidth'}
  {math equation="floor(12 / x)" x=$desktopItems assign='desktopColumnWidth'}
  {if $mobileColumnWidth < 1}
    {assign var='mobileColumnWidth' value=1}
  {/if}
  {if $tabletColumnWidth < 1}
    {assign var='tabletColumnWidth' value=1}
  {/if}
  {if $desktopColumnWidth < 1}
    {assign var='desktopColumnWidth' value=1}
  {/if}
  {assign var='productColumnClasses' value="col-"|cat:$mobileColumnWidth}
  {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-sm-"|cat:$tabletColumnWidth}
  {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-lg-"|cat:$desktopColumnWidth}
  {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-xl-"|cat:$desktopColumnWidth}

  {assign var='desktopCarouselEnabled' value=$block.settings.slider_desktop|default:0}
  {assign var='mobileCarouselEnabled' value=$block.settings.slider_mobile|default:0}
  {assign var='showBestSalesButton' value=$block.settings.show_best_sales_button|default:1}
  {assign var='bestSalesLink' value=$block.settings.button_url_override|default:''}
  {if !$bestSalesLink}
    {assign var='bestSalesLink' value=$block.extra.best_sales_url|default:''}
  {/if}

  <div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}">
    {if $block.settings.default.force_full_width}
      <div class="row gx-0 no-gutters">
    {elseif $block.settings.default.container}
      <div class="row">
    {/if}
      <div class="mt-2{if $block.settings.default.container} container{/if}" style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
        <section class="ever-featured-products featured-products clearfix mx-5 d-none d-md-block best-sales">
          {if $desktopCarouselEnabled}
            {assign var="carouselId" value="ever-best-sales-carousel-"|cat:mt_rand(1000,999999)}
            {assign var="numProductsPerSlide" value=$desktopItems}
            <div id="{$carouselId}" class="carousel slide" data-ride="false" data-bs-ride="false" data-bs-wrap="true" data-ever-infinite-carousel="1">
              <div class="carousel-inner products">
                {hook h='displayBeforeProductMiniature' products=$block.extra.products origin='best-sales' page_name=$page.page_name}
                {foreach from=$block.extra.products item=product name=products}
                  {if $product@index % $numProductsPerSlide == 0}
                    <div class="carousel-item{if $product@first} active{/if}">
                      <div class="row">
                  {/if}
                  {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
                  {if ($product@index + 1) % $numProductsPerSlide == 0 || $product@last}
                      </div>
                    </div>
                  {/if}
                {/foreach}
                {hook h='displayAfterProductMiniature' products=$block.extra.products origin='best-sales' page_name=$page.page_name}
              </div>

              <button class="carousel-control-prev" type="button" data-bs-target="#{$carouselId}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
              </button>

              <button class="carousel-control-next" type="button" data-bs-target="#{$carouselId}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
              </button>
            </div>
          {else}
            <div class="products row">
              {hook h='displayBeforeProductMiniature' products=$block.extra.products origin='best-sales' page_name=$page.page_name}
              {foreach from=$block.extra.products item=product}
                {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
              {/foreach}
              {hook h='displayAfterProductMiniature' products=$block.extra.products origin='best-sales' page_name=$page.page_name}
            </div>
          {/if}
        </section>

        <section class="ever-featured-products featured-products mx-2 d-block d-md-none">
          {if $mobileCarouselEnabled}
            {assign var="mobileCarouselId" value="ever-best-sales-carousel-mobile-"|cat:mt_rand(1000,999999)}
            {assign var="mobileNumProductsPerSlide" value=$mobileItems}
            <div id="{$mobileCarouselId}" class="carousel slide" data-ride="false" data-bs-ride="false" data-bs-wrap="true" data-ever-mobile-carousel="1" data-ever-infinite-carousel="1">
              <div class="carousel-inner products">
                {hook h='displayBeforeProductMiniature' products=$block.extra.products origin='best-sales' page_name=$page.page_name}
                {foreach from=$block.extra.products item=product name=mobileProducts}
                  {if $product@index % $mobileNumProductsPerSlide == 0}
                    <div class="carousel-item{if $product@first} active{/if}">
                      <div class="row">
                  {/if}
                  {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
                  {if ($product@index + 1) % $mobileNumProductsPerSlide == 0 || $product@last}
                      </div>
                    </div>
                  {/if}
                {/foreach}
                {hook h='displayAfterProductMiniature' products=$block.extra.products origin='best-sales' page_name=$page.page_name}
              </div>

              <button class="carousel-control-prev" type="button" data-bs-target="#{$mobileCarouselId}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
              </button>

              <button class="carousel-control-next" type="button" data-bs-target="#{$mobileCarouselId}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
              </button>
            </div>
          {else}
            <div class="products row">
              {hook h='displayBeforeProductMiniature' products=$block.extra.products origin='best-sales' page_name=$page.page_name}
              {foreach from=$block.extra.products item=product}
                {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
              {/foreach}
              {hook h='displayAfterProductMiniature' products=$block.extra.products origin='best-sales' page_name=$page.page_name}
            </div>
          {/if}
        </section>

        {if $showBestSalesButton && $bestSalesLink}
          <div class="text-center mt-4">
            <a class="btn btn-primary" href="{$bestSalesLink|escape:'htmlall':'UTF-8'}">
              {l s='See best sellers' mod='everblock'}
            </a>
          </div>
        {/if}
      </div>
    {if $block.settings.default.force_full_width || $block.settings.default.container}
      </div>
    {/if}
  </div>
{/if}
