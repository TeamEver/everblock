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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if isset($block.states) && $block.states}
    <div id="cardCarousel-{$block.id_prettyblocks}" class="carousel slide" data-ride="carousel" data-bs-ride="carousel">
      <div class="carousel-inner">
        {foreach from=$block.states item=state}
          <div class="carousel-item{if $state@first} active{/if}{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}">
            <div class="card h-100 mb-3">
              {if isset($state.image.url) && $state.image.url}
                {assign var="imgExt" value=$state.image.url|lower|substr:-4}
                {if $imgExt == '.svg'}
                  <img src="{$state.image.url}" class="card-img-top img img-fluid" loading="lazy"{if $state.image_width} width="{$state.image_width|escape:'htmlall'}"{/if}{if $state.image_height} height="{$state.image_height|escape:'htmlall'}"{/if} alt="{if $state.alt}{$state.alt|escape:'htmlall'}{else}{$state.title|escape:'htmlall'}{/if}" title="{$state.title|escape:'htmlall'}">
                {else}
                  <picture>
                    <source srcset="{$state.image.url}" type="image/webp">
                    <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
                    <img src="{$state.image.url|replace:'.webp':'.jpg'}" class="card-img-top img img-fluid lazyload" loading="lazy"{if $state.image_width} width="{$state.image_width|escape:'htmlall'}"{/if}{if $state.image_height} height="{$state.image_height|escape:'htmlall'}"{/if} alt="{if $state.alt}{$state.alt|escape:'htmlall'}{else}{$state.title|escape:'htmlall'}{/if}" title="{$state.title|escape:'htmlall'}">
                  </picture>
                {/if}
              {/if}
              <div class="card-body">
                {if $state.title}<span class="card-title h5">{$state.title|escape:'htmlall'}</span>{/if}
                {if $state.content}<div class="card-text">{$state.content nofilter}</div>{/if}
              </div>
              {if $state.button_link}
                <div class="card-footer bg-transparent border-0">
                  <a href="{$state.button_link|escape:'htmlall'}" class="card-link d-inline-flex align-items-center" title="{$state.title|escape:'htmlall'}">
                    En savoir plus <span class="ms-1">&rarr;</span>
                  </a>
                </div>
              {/if}
            </div>
          </div>
        {/foreach}
      </div>
      <a class="carousel-control-prev" href="#cardCarousel-{$block.id_prettyblocks}" role="button" data-slide="prev" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only visually-hidden">Previous</span>
      </a>
      <a class="carousel-control-next" href="#cardCarousel-{$block.id_prettyblocks}" role="button" data-slide="next" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only visually-hidden">Next</span>
      </a>
    </div>
  {/if}
</div>
