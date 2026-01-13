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

<div id="block-{$block.id_prettyblocks}" class="prettyblock-category-tabs{if $block.settings.default.force_full_width} container-fluid px-0 mx-0{elseif $block.settings.default.container} container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<div class="mt-2{if $block.settings.default.container} container{/if}"  style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    <div class="mt-2 col-12 d-flex justify-content-center text-center">
        <div class="tab-container">
            <ul class="nav nav-tabs justify-content-center prettyblock-category-tabs__nav" role="tablist">
                {foreach from=$block.states item=state key=key name=categorytabs}
                    {assign var='tabId' value="tab-"|cat:$block.id_prettyblocks|cat:"-"|cat:$key}
                    <li class="nav-item">
                        <a class="nav-link prettyblock-category-tabs__tab-link {if $smarty.foreach.categorytabs.first}active{/if}" id="{$tabId}-tab" data-toggle="tab" data-bs-toggle="tab" data-bs-target="#{$tabId}" href="#{$tabId}" role="tab" aria-controls="{$tabId}" aria-selected="{if $smarty.foreach.categorytabs.first}true{else}false{/if}" title="{$state.name|escape:'htmlall'}">
                            {$state.name}
                        </a>
                    </li>
                {/foreach}
            </ul>
            <div class="tab-content">
                {foreach from=$block.states item=state key=key name=categorytabs}
                    {assign var='tabId' value="tab-"|cat:$block.id_prettyblocks|cat:"-"|cat:$key}
                    {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
                    {assign var='useSlider' value=(isset($state.slider) && $state.slider && isset($block.extra.products[$key]) && $block.extra.products[$key])}
                    {assign var='sliderDevices' value=$state.slider_devices|default:'both'}
                    {assign var='useDesktopSlider' value=($useSlider && ($sliderDevices == 'both' || $sliderDevices == 'desktop'))}
                    {assign var='useMobileSlider' value=($useSlider && ($sliderDevices == 'both' || $sliderDevices == 'mobile'))}
                    {assign var='productCount' value=0}
                    {if isset($block.extra.products[$key]) && $block.extra.products[$key]}
                        {assign var='productCount' value=$block.extra.products[$key]|@count}
                    {/if}
                    {assign var='desktopItems' value=$state.products_per_slide_desktop|default:4|intval}
                    {if $desktopItems < 1}
                        {assign var='desktopItems' value=1}
                    {/if}
                    {if $productCount > 0 && $productCount < $desktopItems}
                        {assign var='desktopItems' value=$productCount}
                    {/if}
                    {assign var='tabletItems' value=$state.products_per_slide_tablet|default:2|intval}
                    {if $tabletItems < 1}
                        {assign var='tabletItems' value=1}
                    {/if}
                    {if $productCount > 0 && $productCount < $tabletItems}
                        {assign var='tabletItems' value=$productCount}
                    {/if}
                    {assign var='mobileItems' value=$state.products_per_slide_mobile|default:2|intval}
                    {if $mobileItems < 1}
                        {assign var='mobileItems' value=1}
                    {/if}
                    {if $productCount > 0 && $productCount < $mobileItems}
                        {assign var='mobileItems' value=$productCount}
                    {/if}
                    {math equation="ceil(12 / x)" x=$mobileItems assign='mobileColumnWidth'}
                    {math equation="ceil(12 / x)" x=$tabletItems assign='tabletColumnWidth'}
                    {math equation="ceil(12 / x)" x=$desktopItems assign='desktopColumnWidth'}
                    {assign var='productColumnClasses' value="col-"|cat:$mobileColumnWidth}
                    {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-sm-"|cat:$tabletColumnWidth}
                    {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-lg-"|cat:$desktopColumnWidth}
                    {assign var='productColumnClasses' value=$productColumnClasses|cat:" col-xl-"|cat:$desktopColumnWidth}
                    <div class="tab-pane {if $smarty.foreach.categorytabs.first}show active{/if}{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" id="{$tabId}" role="tabpanel" aria-labelledby="{$tabId}-tab"style="{$prettyblock_state_spacing_style}{if isset($state.background_color) && $state.background_color}background-color:{$state.background_color|escape:'htmlall':'UTF-8'};{/if}{if isset($state.text_color) && $state.text_color}color:{$state.text_color|escape:'htmlall':'UTF-8'};{/if}">
                        {if isset($state.html_before_products) && $state.html_before_products}
                            <div class="prettyblock-category-tabs__intro">
                                {$state.html_before_products nofilter}
                            </div>
                        {/if}
                        {assign var='sliderImages' value=[]}
                        {if isset($state.slider_image_1.url) && $state.slider_image_1.url}
                            {append var='sliderImages' value=$state.slider_image_1}
                        {/if}
                        {if isset($state.slider_image_2.url) && $state.slider_image_2.url}
                            {append var='sliderImages' value=$state.slider_image_2}
                        {/if}
                        {if isset($state.slider_image_3.url) && $state.slider_image_3.url}
                            {append var='sliderImages' value=$state.slider_image_3}
                        {/if}
                        {if isset($state.slider_image_4.url) && $state.slider_image_4.url}
                            {append var='sliderImages' value=$state.slider_image_4}
                        {/if}
                        {assign var='sliderImagesCount' value=$sliderImages|@count}
                        {if $sliderImagesCount > 0}
                            {assign var='carouselId' value="category-tabs-slider-"|cat:$block.id_prettyblocks|cat:"-"|cat:$key|cat:"-"|cat:mt_rand(1000,999999)}
                            <div class="prettyblock-category-tabs__slider mt-3">
                                <div id="{$carouselId}" class="prettyblocks-image-slider carousel slide" data-ride="carousel" data-bs-ride="carousel" data-bs-wrap="true" data-autoplay="0" data-transition-speed="600">
                                    {if $sliderImagesCount > 1}
                                        <ol class="carousel-indicators">
                                            {foreach from=$sliderImages item=sliderImage name=categorytabsimages}
                                                <li data-target="#{$carouselId}" data-slide-to="{$smarty.foreach.categorytabsimages.index}" data-bs-target="#{$carouselId}" data-bs-slide-to="{$smarty.foreach.categorytabsimages.index}" class="{if $smarty.foreach.categorytabsimages.first}active{/if}" aria-label="{$state.name|escape:'htmlall'}" aria-current="{if $smarty.foreach.categorytabsimages.first}true{else}false{/if}"></li>
                                            {/foreach}
                                        </ol>
                                    {/if}
                                    <div class="carousel-inner">
                                        {foreach from=$sliderImages item=sliderImage name=categorytabsimages}
                                            <div class="carousel-item{if $smarty.foreach.categorytabsimages.first} active{/if}">
                                                <div class="prettyblocks-slider-item-inner">
                                                    <img src="{$sliderImage.url}" alt="{$state.name|escape:'htmlall'}" class="img img-fluid w-100 prettyblocks-slider-image lazyload" loading="lazy"{if isset($sliderImage.width) && $sliderImage.width} width="{$sliderImage.width|intval}"{/if}{if isset($sliderImage.height) && $sliderImage.height} height="{$sliderImage.height|intval}"{/if}>
                                                </div>
                                            </div>
                                        {/foreach}
                                    </div>
                                    {if $sliderImagesCount > 1}
                                        <button class="carousel-control-prev" type="button" data-target="#{$carouselId}" data-slide="prev" data-bs-target="#{$carouselId}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-target="#{$carouselId}" data-slide="next" data-bs-target="#{$carouselId}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
                                        </button>
                                    {/if}
                                </div>
                            </div>
                        {/if}
                        {if isset($block.extra.products[$key]) && $block.extra.products[$key]}
                        {if $useDesktopSlider || $useMobileSlider}
                            {if $useDesktopSlider}
                                {assign var='carouselIdDesktop' value="category-tabs-carousel-desktop-"|cat:$block.id_prettyblocks|cat:"-"|cat:$key|cat:"-"|cat:mt_rand(1000,999999)}
                                <section class="ever-featured-products featured-products clearfix mt-3 category_tabs d-none d-md-block">
                                    <div id="{$carouselIdDesktop}" class="carousel slide prettyblocks-image-slider" data-ride="carousel" data-bs-ride="carousel" data-bs-wrap="true">
                                        <div class="carousel-inner products">
                                            {hook h='displayBeforeProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                            {foreach from=$block.extra.products[$key] item=product name=desktopProducts}
                                                {if $product@index % $desktopItems == 0}
                                                    <div class="carousel-item{if $product@first} active{/if}">
                                                        <div class="row">
                                                {/if}
                                                {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
                                                {if ($product@index + 1) % $desktopItems == 0 || $product@last}
                                                        </div>
                                                    </div>
                                                {/if}
                                            {/foreach}
                                            {hook h='displayAfterProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-target="#{$carouselIdDesktop}" data-slide="prev" data-bs-target="#{$carouselIdDesktop}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-target="#{$carouselIdDesktop}" data-slide="next" data-bs-target="#{$carouselIdDesktop}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
                                        </button>
                                    </div>
                                </section>
                            {else}
                                <section class="ever-featured-products featured-products clearfix mt-3 category_tabs d-none d-md-block">
                                    <div class="products row">
                                        {hook h='displayBeforeProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                        {foreach from=$block.extra.products[$key] item=product}
                                            {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
                                        {/foreach}
                                        {hook h='displayAfterProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                    </div>
                                </section>
                            {/if}
                            {if $useMobileSlider}
                                {assign var='carouselIdMobile' value="category-tabs-carousel-mobile-"|cat:$block.id_prettyblocks|cat:"-"|cat:$key|cat:"-"|cat:mt_rand(1000,999999)}
                                <section class="ever-featured-products featured-products clearfix mt-3 category_tabs d-block d-md-none">
                                    <div id="{$carouselIdMobile}" class="carousel slide prettyblocks-image-slider" data-ride="carousel" data-bs-ride="carousel" data-bs-wrap="true">
                                        <div class="carousel-inner products">
                                            {hook h='displayBeforeProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                            {foreach from=$block.extra.products[$key] item=product name=mobileProducts}
                                                {if $product@index % $mobileItems == 0}
                                                    <div class="carousel-item{if $product@first} active{/if}">
                                                        <div class="row">
                                                {/if}
                                                {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
                                                {if ($product@index + 1) % $mobileItems == 0 || $product@last}
                                                        </div>
                                                    </div>
                                                {/if}
                                            {/foreach}
                                            {hook h='displayAfterProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                        </div>
                                        <button class="carousel-control-prev" type="button" data-target="#{$carouselIdMobile}" data-slide="prev" data-bs-target="#{$carouselIdMobile}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-target="#{$carouselIdMobile}" data-slide="next" data-bs-target="#{$carouselIdMobile}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
                                        </button>
                                    </div>
                                </section>
                            {else}
                                <section class="ever-featured-products featured-products clearfix mt-3 category_tabs d-block d-md-none">
                                    <div class="products row">
                                        {hook h='displayBeforeProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                        {foreach from=$block.extra.products[$key] item=product}
                                            {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
                                        {/foreach}
                                        {hook h='displayAfterProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                    </div>
                                </section>
                            {/if}
                        {else}
                            <section class="ever-featured-products featured-products clearfix mt-3 category_tabs">
                                <div class="products row">
                                    {hook h='displayBeforeProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                    {foreach from=$block.extra.products[$key] item=product}
                                        {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses=$productColumnClasses}
                                    {/foreach}
                                    {hook h='displayAfterProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                </div>
                            </section>
                        {/if}
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
