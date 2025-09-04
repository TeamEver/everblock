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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if isset($block.states) && $block.states}
    <div class="overflow-auto px-2 px-md-0 pb-2">
      <div class="d-flex flex-nowrap gap-3 pe-1">
        {foreach from=$block.states item=state}
          <div class="flex-shrink-0 prettyblocks-card-item{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="width:90%;max-width:90%;">
            <div class="card border border-light-subtle rounded-4 shadow-sm">
              <div class="card-body d-flex flex-column h-100 p-2">
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
                  <div class="text-end">
                    <a href="{$state.button_link|escape:'htmlall'}" class="btn btn-light rounded-circle p-2 d-inline-flex align-items-center justify-content-center" title="{$state.title|escape:'htmlall'}">
                      <span class="visually-hidden">En savoir plus</span>
                      <span aria-hidden="true">&rarr;</span>
                    </a>
                  </div>
                {/if}
              </div>
            </div>
          </div>
        {/foreach}
      </div>
    </div>
    <style>
      @media (min-width: 992px) {
        #block-{$block.id_prettyblocks} .prettyblocks-card-item {
          width: 20% !important;
          max-width: 20% !important;
        }
      }
    </style>
  {/if}
</div>
