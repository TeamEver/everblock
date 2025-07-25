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
{if isset($block.states) && $block.states}
  <section class="prettyblocks-slider container-fluid px-0 mt-3 {if $block.settings.default.container}container{/if}" style="
    {if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
  ">
    <div class="ever-wrapper overflow-auto px-2 px-md-0 pb-2">
      <div class="d-flex flex-nowrap gap-3 pe-1">
        {foreach from=$block.states item=state}
          <div class="flex-shrink-0 prettyblocks-slider-item" style="
            width: 90%; /* Mobile fallback */
            max-width: 90%;
          ">
            <div class="w-100" style="
              {if isset($state.padding_left) && $state.padding_left}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'};{/if}
              {if isset($state.padding_right) && $state.padding_right}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'};{/if}
              {if isset($state.padding_top) && $state.padding_top}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'};{/if}
              {if isset($state.padding_bottom) && $state.padding_bottom}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
              {if isset($state.margin_left) && $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'};{/if}
              {if isset($state.margin_right) && $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'};{/if}
              {if isset($state.margin_top) && $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'};{/if}
              {if isset($state.margin_bottom) && $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
              {if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
            ">
              {if $state.link}
                {if $state.obfuscate}
                  {assign var="obflink" value=$state.link|base64_encode}
                  <span class="obflink d-block" data-obflink="{$obflink}">
                {else}
                  <a href="{$state.link}" title="{$state.name}" {if $state.target_blank}target="_blank"{/if} class="d-block">
                {/if}
              {/if}
                    <picture>
                      <source srcset="{$state.image.url}" type="image/webp">
                      <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
                      <img src="{$state.image.url|replace:'.webp':'.jpg'}" title="{$state.name}" alt="{$state.name}" class="img img-fluid lazyload" loading="lazy">
                    </picture>
              {if $state.link}
                {if $state.obfuscate}
                  </span>
                {else}
                  </a>
                {/if}
              {/if}
            </div>
          </div>
        {/foreach}
      </div>
    </div>
  </section>

  {* Ajout d’un style responsive pour forcer 4 par ligne en desktop *}
  <style>
    @media (min-width: 768px) {
      .prettyblocks-slider-item {
        width: 25% !important;
        max-width: 25% !important;
      }
    }
  </style>
{/if}
