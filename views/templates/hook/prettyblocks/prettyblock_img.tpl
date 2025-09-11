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
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
{if isset($block.states) && $block.states}
  {assign var='use_slider' value=(isset($block.settings.slider) && $block.settings.slider && $block.states|@count > 1)}
  {if $use_slider}
    <div class="mt-4 ever-cover-carousel" data-items="{$block.settings.slider_items|default:3|escape:'htmlall':'UTF-8'}">
      {foreach from=$block.states item=state key=key}
        <div id="block-{$block.id_prettyblocks}-{$key}" class="position-relative overflow-hidden{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="
          {if isset($state.padding_left)}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.padding_right)}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.padding_top)}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.padding_bottom)}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.margin_left)}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.margin_right)}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.margin_top)}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.margin_bottom)}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.default.bg_color)}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
        ">
          {if isset($state.url) && $state.url}
            <a href="{$state.url|escape:'htmlall':'UTF-8'}" class="d-block position-relative">
          {/if}
            <picture>
              {if isset($state.banner_mobile.url) && $state.banner_mobile.url}
                <source media="(max-width: 767px)" srcset="{$state.banner_mobile.url|replace:'.webp':'.jpg'}">
              {/if}
              <source srcset="{$state.banner.url}" type="image/webp">
              <source srcset="{$state.banner.url|replace:'.webp':'.jpg'}" type="image/jpeg">
              <img src="{$state.banner.url|replace:'.webp':'.jpg'}"
                   {if isset($state.alt)}alt="{$state.alt}"{else}alt="{$shop.name}"{/if}
                   {if $state.image_width} width="{$state.image_width|escape:'htmlall':'UTF-8'}"{/if}
                   {if $state.image_height} height="{$state.image_height|escape:'htmlall':'UTF-8'}"{/if}
                   class="img img-fluid lazyload" loading="lazy">
            </picture>

            <div class="position-absolute bottom-0 start-0 end-0 p-3 text-center text-white">
              {if $state.text_highlight_1}
                <div class="fw-bold small">{$state.text_highlight_1 nofilter}</div>
              {/if}
              {if $state.text_highlight_2}
                <div class="fw-bold small mb-2">{$state.text_highlight_2 nofilter}</div>
              {/if}
            </div>
          {if isset($state.url) && $state.url}
            </a>
          {/if}
        </div>
        {if $state.margin_left_mobile || $state.margin_right_mobile || $state.margin_top_mobile || $state.margin_bottom_mobile}
          <style>
            @media (max-width: 767px) {
              #block-{$block.id_prettyblocks}-{$key} {
                {if $state.margin_left_mobile}margin-left:{$state.margin_left_mobile|escape:'htmlall':'UTF-8'};{/if}
                {if $state.margin_right_mobile}margin-right:{$state.margin_right_mobile|escape:'htmlall':'UTF-8'};{/if}
                {if $state.margin_top_mobile}margin-top:{$state.margin_top_mobile|escape:'htmlall':'UTF-8'};{/if}
                {if $state.margin_bottom_mobile}margin-bottom:{$state.margin_bottom_mobile|escape:'htmlall':'UTF-8'};{/if}
              }
            }
          </style>
        {/if}
      {/foreach}
    </div>
  {else}
    <div class="mt-4 d-flex flex-wrap gap-3 justify-content-center">
      {foreach from=$block.states item=state key=key}
        <div id="block-{$block.id_prettyblocks}-{$key}" class="position-relative overflow-hidden{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="
          {if isset($state.padding_left)}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.padding_right)}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.padding_top)}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.padding_bottom)}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.margin_left)}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.margin_right)}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.margin_top)}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.margin_bottom)}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
          {if isset($state.default.bg_color)}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
        ">
          {if isset($state.url) && $state.url}
            <a href="{$state.url|escape:'htmlall':'UTF-8'}" class="d-block position-relative">
          {/if}
            <picture>
              {if isset($state.banner_mobile.url) && $state.banner_mobile.url}
                <source media="(max-width: 767px)" srcset="{$state.banner_mobile.url|replace:'.webp':'.jpg'}">
              {/if}
              <source srcset="{$state.banner.url}" type="image/webp">
              <source srcset="{$state.banner.url|replace:'.webp':'.jpg'}" type="image/jpeg">
              <img src="{$state.banner.url|replace:'.webp':'.jpg'}"
                   {if isset($state.alt)}alt="{$state.alt}"{else}alt="{$shop.name}"{/if}
                   {if $state.image_width} width="{$state.image_width|escape:'htmlall':'UTF-8'}"{/if}
                   {if $state.image_height} height="{$state.image_height|escape:'htmlall':'UTF-8'}"{/if}
                   class="img img-fluid lazyload" loading="lazy">
            </picture>

            <div class="position-absolute bottom-0 start-0 end-0 p-3 text-center text-white">
              {if $state.text_highlight_1}
                <div class="fw-bold small">{$state.text_highlight_1 nofilter}</div>
              {/if}
              {if $state.text_highlight_2}
                <div class="fw-bold small mb-2">{$state.text_highlight_2 nofilter}</div>
              {/if}
            </div>
          {if isset($state.url) && $state.url}
            </a>
          {/if}
        </div>
        {if $state.margin_left_mobile || $state.margin_right_mobile || $state.margin_top_mobile || $state.margin_bottom_mobile}
          <style>
            @media (max-width: 767px) {
              #block-{$block.id_prettyblocks}-{$key} {
                {if $state.margin_left_mobile}margin-left:{$state.margin_left_mobile|escape:'htmlall':'UTF-8'};{/if}
                {if $state.margin_right_mobile}margin-right:{$state.margin_right_mobile|escape:'htmlall':'UTF-8'};{/if}
                {if $state.margin_top_mobile}margin-top:{$state.margin_top_mobile|escape:'htmlall':'UTF-8'};{/if}
                {if $state.margin_bottom_mobile}margin-bottom:{$state.margin_bottom_mobile|escape:'htmlall':'UTF-8'};{/if}
              }
            }
          </style>
        {/if}
      {/foreach}
    </div>
  {/if}
{/if}

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
