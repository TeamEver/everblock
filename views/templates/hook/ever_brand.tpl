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
{if isset($brands) && $brands}
  {assign var="numBrandsPerSlide" value=$brandsPerSlide|default:4}
  {assign var="colWidth" value=12/$numBrandsPerSlide}
  {if isset($carousel) && $carousel}
    {assign var="carouselId" value="ever-brand-carousel-"|cat:mt_rand(1000,999999)}
    <section class="featured-brands clearfix mt-3 d-none d-md-block">
      <div id="{$carouselId}" class="carousel slide" data-ride="false" data-bs-ride="false" data-bs-wrap="true">
        <div class="carousel-inner brands">
          {foreach from=$brands item=brand name=brands}
            {if $brand@index % $numBrandsPerSlide == 0}
              <div class="carousel-item{if $brand@first} active{/if}">
                <div class="row">
            {/if}
            <div class="col-6 col-md-{$colWidth|intval} mb-3 d-flex justify-content-center align-items-center text-center">
              <a href="{$brand.url|escape:'htmlall':'UTF-8'}" title="{$brand.name|escape:'htmlall':'UTF-8'}" class="brand-link">
                <picture>
                  <source srcset="{$brand.logo|escape:'htmlall':'UTF-8'}" type="image/webp">
                  <source srcset="{$brand.logo|replace:'.webp':'.jpg'|escape:'htmlall':'UTF-8'}" type="image/jpeg">
                  <img src="{$brand.logo|replace:'.webp':'.jpg'|escape:'htmlall':'UTF-8'}" alt="{$brand.name|escape:'htmlall':'UTF-8'}"
                       width="{$brand.width|escape:'htmlall':'UTF-8'}"
                       height="{$brand.height|escape:'htmlall':'UTF-8'}"
                       class="brand-image lazyload" loading="lazy">
                </picture>
              </a>
            </div>
            {if ($brand@index + 1) % $numBrandsPerSlide == 0 || $brand@last}
                </div>
              </div>
            {/if}
          {/foreach}
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
    </section>
    {assign var="mobileBrandsPerSlide" value=3.5}
    <section class="featured-brands mt-3 d-block d-md-none">
      <div id="everBrandsCarouselMobile" class="overflow-auto" style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch;">
        <div class="d-flex flex-nowrap">
          {foreach from=$brands item=brand}
            <div class="me-3" style="flex: 0 0 calc(100% / {$mobileBrandsPerSlide} - 1rem); scroll-snap-align: start;">
              <div class="d-flex justify-content-center align-items-center text-center">
                <a href="{$brand.url|escape:'htmlall':'UTF-8'}" title="{$brand.name|escape:'htmlall':'UTF-8'}" class="brand-link">
                  <picture>
                    <source srcset="{$brand.logo|escape:'htmlall':'UTF-8'}" type="image/webp">
                    <source srcset="{$brand.logo|replace:'.webp':'.jpg'|escape:'htmlall':'UTF-8'}" type="image/jpeg">
                    <img src="{$brand.logo|replace:'.webp':'.jpg'|escape:'htmlall':'UTF-8'}" alt="{$brand.name|escape:'htmlall':'UTF-8'}"
                         width="{$brand.width|escape:'htmlall':'UTF-8'}"
                         height="{$brand.height|escape:'htmlall':'UTF-8'}"
                         class="brand-image lazyload" loading="lazy">
                  </picture>
                </a>
              </div>
            </div>
          {/foreach}
        </div>
      </div>
    </section>
  {else}
    <section class="featured-brands clearfix mt-3">
      <div class="brands row">
        {foreach from=$brands item=brand}
          <div class="col-6 col-md-{$colWidth|intval} mb-3 d-flex justify-content-center align-items-center text-center">
            <a href="{$brand.url|escape:'htmlall':'UTF-8'}" title="{$brand.name|escape:'htmlall':'UTF-8'}" class="brand-link">
              <picture>
                <source srcset="{$brand.logo|escape:'htmlall':'UTF-8'}" type="image/webp">
                <source srcset="{$brand.logo|replace:'.webp':'.jpg'|escape:'htmlall':'UTF-8'}" type="image/jpeg">
                <img src="{$brand.logo|replace:'.webp':'.jpg'|escape:'htmlall':'UTF-8'}" alt="{$brand.name|escape:'htmlall':'UTF-8'}"
                     width="{$brand.width|escape:'htmlall':'UTF-8'}"
                     height="{$brand.height|escape:'htmlall':'UTF-8'}"
                     class="brand-image lazyload" loading="lazy">
              </picture>
            </a>
          </div>
        {/foreach}
      </div>
    </section>
  {/if}
{/if}

