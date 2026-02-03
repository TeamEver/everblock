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

{if isset($everPresentProducts) && $everPresentProducts}
  {if !isset($carouselCounter)}
    {assign var='carouselCounter' value=0}
  {/if}
  {assign var='carouselCounter' value=$carouselCounter+1}

  {assign var='mobileItems' value=$carouselMobileItems|default:1|intval}
  {if $mobileItems < 1}
    {assign var='mobileItems' value=1}
  {/if}
  {if isset($carousel) && $carousel && $mobileItems < 2}
    {assign var='mobileItems' value=2}
  {/if}
  {assign var='tabletItems' value=$carouselTabletItems|default:2|intval}
  {if $tabletItems < 1}
    {assign var='tabletItems' value=1}
  {/if}
  {assign var='desktopItems' value=$carouselDesktopItems|default:4|intval}
  {if $desktopItems < 1}
    {assign var='desktopItems' value=1}
  {/if}

  {math equation="ceil(12 / x)" x=$mobileItems assign='mobileColumnWidth'}
  {math equation="ceil(12 / x)" x=$tabletItems assign='tabletColumnWidth'}
  {math equation="ceil(12 / x)" x=$desktopItems assign='desktopColumnWidth'}
  {assign var='productColumnClasses' value="col-"|cat:$mobileColumnWidth}
  {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-sm-"|cat:$tabletColumnWidth}
  {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-lg-"|cat:$desktopColumnWidth}
  {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-xl-"|cat:$desktopColumnWidth}

  <section class="ever-featured-products featured-products clearfix mx-5 d-none d-md-block{if isset($shortcodeClass)} {$shortcodeClass|escape:'htmlall':'UTF-8'}{/if}">
    {if isset($carousel) && $carousel}
      {assign var="carouselId" value="ever-presented-carousel-"|cat:mt_rand(1000,999999)}
      {assign var="numProductsPerSlide" value=$desktopItems}
      <div id="{$carouselId}" class="carousel slide" data-ride="false" data-bs-ride="false" data-bs-wrap="true" data-ever-infinite-carousel="1">
        <div class="carousel-inner products">
          {hook h='displayBeforeProductMiniature' products=$everPresentProducts origin=$shortcodeClass|default:'' page_name=$page.page_name}
          {foreach from=$everPresentProducts item=product name=products}
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
          {hook h='displayAfterProductMiniature' products=$everPresentProducts origin=$shortcodeClass|default:'' page_name=$page.page_name}
        </div>

        <!-- Controls -->
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
        {hook h='displayBeforeProductMiniature' products=$everPresentProducts origin=$shortcodeClass|default:'' page_name=$page.page_name}
        {foreach $everPresentProducts item=product}
          {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
        {/foreach}
        {hook h='displayAfterProductMiniature' products=$everPresentProducts origin=$shortcodeClass|default:'' page_name=$page.page_name}
      </div>
    {/if}
  </section>
  {if isset($carousel) && $carousel}
  <section class="ever-featured-products featured-products mx-2 d-block d-md-none{if isset($shortcodeClass)} {$shortcodeClass|escape:'htmlall':'UTF-8'}{/if}">
    {assign var="mobileCarouselId" value="ever-presented-carousel-mobile-"|cat:mt_rand(1000,999999)}
    {assign var="mobileNumProductsPerSlide" value=$mobileItems}
    <div id="{$mobileCarouselId}" class="carousel slide" data-ride="false" data-bs-ride="false" data-bs-wrap="true" data-ever-mobile-carousel="1" data-ever-infinite-carousel="1">
      <div class="carousel-inner products">
        {hook h='displayBeforeProductMiniature' products=$everPresentProducts origin=$shortcodeClass|default:'' page_name=$page.page_name}
        {foreach from=$everPresentProducts item=product name=mobileProducts}
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
        {hook h='displayAfterProductMiniature' products=$everPresentProducts origin=$shortcodeClass|default:'' page_name=$page.page_name}
      </div>

      <!-- Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#{$mobileCarouselId}" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
      </button>

      <button class="carousel-control-next" type="button" data-bs-target="#{$mobileCarouselId}" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
      </button>
    </div>
  </section>
  {/if}
{/if}
