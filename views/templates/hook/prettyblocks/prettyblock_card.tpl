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

{function name=renderPrettyblockCard state=null}
  <div class="card h-100 mb-3 border border-light-subtle rounded-4 shadow-sm">
    <div class="card-body d-flex flex-column h-100 p-4">
      {if isset($state.image.url) && $state.image.url}
        <div class="mb-4">
          {assign var="imgExt" value=$state.image.url|lower|substr:-4}
          {if $imgExt == '.svg'}
            <img src="{$state.image.url}" class="img img-fluid" loading="lazy"{if $state.image_width} width="{$state.image_width|escape:'htmlall'}"{/if}{if $state.image_height} height="{$state.image_height|escape:'htmlall'}"{/if} alt="{if $state.alt}{$state.alt|escape:'htmlall'}{else}{$state.title|escape:'htmlall'}{/if}" title="{$state.title|escape:'htmlall'}">
          {else}
            <picture>
              <source srcset="{$state.image.url}" type="image/webp">
              <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
              <img src="{$state.image.url|replace:'.webp':'.jpg'}" class="img img-fluid lazyload" loading="lazy"{if $state.image_width} width="{$state.image_width|escape:'htmlall'}"{/if}{if $state.image_height} height="{$state.image_height|escape:'htmlall'}"{/if} alt="{if $state.alt}{$state.alt|escape:'htmlall'}{else}{$state.title|escape:'htmlall'}{/if}" title="{$state.title|escape:'htmlall'}">
            </picture>
          {/if}
        </div>
      {/if}
      <div class="flex-grow-1">
        {if $state.title}<span class="card-title h5 d-block mb-2">{$state.title|escape:'htmlall'}</span>{/if}
        {if $state.content}<div class="card-text text-body-secondary">{$state.content nofilter}</div>{/if}
      </div>
      {if $state.button_link}
        <div class="mt-4 text-end">
          <a href="{$state.button_link|escape:'htmlall'}" class="btn btn-light rounded-circle p-2 d-inline-flex align-items-center justify-content-center" title="{$state.title|escape:'htmlall'}">
            <span class="visually-hidden">En savoir plus</span>
            <span aria-hidden="true">&rarr;</span>
          </a>
        </div>
      {/if}
    </div>
  </div>
{/function}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if isset($block.states) && $block.states}
    {assign var="carouselId" value="prettyblock-card-carousel-"|cat:$block.id_prettyblocks}
    <div class="d-none d-md-block mx-5">
      <div id="{$carouselId}" class="carousel slide" data-ride="false" data-bs-ride="false" data-bs-wrap="true">
        <div class="carousel-inner">
          {assign var="numCardsPerSlide" value=4}
          {foreach from=$block.states item=state name=cardLoop}
            {if $state@index % $numCardsPerSlide == 0}
              <div class="carousel-item{if $state@first} active{/if}">
                <div class="row">
            {/if}
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
              {renderPrettyblockCard state=$state}
            </div>
            {if ($state@index + 1) % $numCardsPerSlide == 0 || $state@last}
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
    </div>
    <div class="d-block d-md-none mx-2">
      <div id="prettyblockCardCarouselMobile-{$block.id_prettyblocks}" class="overflow-auto" style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch;">
        <div class="d-flex flex-nowrap">
          {foreach from=$block.states item=state}
            <div class="me-3" style="flex: 0 0 85%; scroll-snap-align: start;">
              {renderPrettyblockCard state=$state}
            </div>
          {/foreach}
        </div>
      </div>
    </div>
  {/if}
</div>
