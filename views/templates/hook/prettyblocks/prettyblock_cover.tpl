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
    <div class="ever-cover-carousel">
      {foreach from=$block.states item=state key=key}
        <div id="block-{$block.id_prettyblocks}-{$key}"
             class="prettyblock-cover-item{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}"
             {if $state.margin_left || $state.margin_right || $state.margin_top || $state.margin_bottom}style="{if $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall'};{/if}{if $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall'};{/if}{if $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall'};{/if}{if $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall'};{/if}"{/if}>
            {if isset($state.background_image.url) && $state.background_image.url}
              <picture>
                {if isset($state.background_image_mobile.url) && $state.background_image_mobile.url}
                  <source media="(max-width: 767px)" srcset="{$state.background_image_mobile.url|escape:'htmlall'}">
                {/if}
                <img src="{$state.background_image.url|escape:'htmlall'}"
                     alt="{$state.title|escape:'htmlall'}"
                     {if isset($state.background_image.width)} width="{$state.background_image.width|escape:'htmlall'}"{/if}
                     {if isset($state.background_image.height)} height="{$state.background_image.height|escape:'htmlall'}"{/if}
                     class="prettyblock-cover-image">
              </picture>
            {/if}
          <div class="prettyblock-cover-overlay position-desktop-{$state.content_position_desktop|default:'center'|lower|escape:'htmlall'} position-mobile-{$state.content_position_mobile|default:'center'|lower|escape:'htmlall'}">
            {if $state.title}
              <{$state.title_tag|default:'h2'}{if isset($state.title_color) && $state.title_color} style="color: {$state.title_color|escape:'htmlall'}"{/if}>{$state.title|escape:'htmlall'}</{$state.title_tag|default:'h2'}>
            {/if}
            {if $state.content}
              <div class="prettyblock-cover-content">
                {$state.content nofilter}
              </div>
            {/if}
            {if ($state.btn1_text && $state.btn1_link) || ($state.btn2_text && $state.btn2_link)}
              <div class="d-flex justify-content-center gap-2">
                {if $state.btn1_text && $state.btn1_link}
                  <a href="{$state.btn1_link|escape:'htmlall'}" class="btn btn-{$state.btn1_type|escape:'htmlall'}">{$state.btn1_text|escape:'htmlall'}</a>
                {/if}
                {if $state.btn2_text && $state.btn2_link}
                  <a href="{$state.btn2_link|escape:'htmlall'}" class="btn btn-{$state.btn2_type|escape:'htmlall'}">{$state.btn2_text|escape:'htmlall'}</a>
                {/if}
              </div>
            {/if}
          </div>
        </div>
        {if (isset($state.margin_left_mobile) && $state.margin_left_mobile) ||
            (isset($state.margin_right_mobile) && $state.margin_right_mobile) ||
            (isset($state.margin_top_mobile) && $state.margin_top_mobile) ||
            (isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile)}
          <style>
            @media (max-width: 767px) {
              #block-{$block.id_prettyblocks}-{$key} {
                {if isset($state.margin_left_mobile) && $state.margin_left_mobile}margin-left:{$state.margin_left_mobile|escape:'htmlall'};{/if}
                {if isset($state.margin_right_mobile) && $state.margin_right_mobile}margin-right:{$state.margin_right_mobile|escape:'htmlall'};{/if}
                {if isset($state.margin_top_mobile) && $state.margin_top_mobile}margin-top:{$state.margin_top_mobile|escape:'htmlall'};{/if}
                {if isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile}margin-bottom:{$state.margin_bottom_mobile|escape:'htmlall'};{/if}
              }
            }
          </style>
        {/if}
      {/foreach}
  {else}
    {foreach from=$block.states item=state key=key}
      <div id="block-{$block.id_prettyblocks}-{$key}"
           class="prettyblock-cover-item{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}"
           {if $state.margin_left || $state.margin_right || $state.margin_top || $state.margin_bottom}style="{if $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall'};{/if}{if $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall'};{/if}{if $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall'};{/if}{if $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall'};{/if}"{/if}>
        {if isset($state.background_image.url) && $state.background_image.url}
          <picture>
            {if isset($state.background_image_mobile.url) && $state.background_image_mobile.url}
              <source media="(max-width: 767px)" srcset="{$state.background_image_mobile.url|escape:'htmlall'}">
            {/if}
            <img src="{$state.background_image.url|escape:'htmlall'}"
                 alt="{$state.title|escape:'htmlall'}"
                 {if isset($state.background_image.width)} width="{$state.background_image.width|escape:'htmlall'}"{/if}
                 {if isset($state.background_image.height)} height="{$state.background_image.height|escape:'htmlall'}"{/if}
                 class="prettyblock-cover-image">
          </picture>
        {/if}
        <div class="prettyblock-cover-overlay position-desktop-{$state.content_position_desktop|default:'center'|lower|escape:'htmlall'} position-mobile-{$state.content_position_mobile|default:'center'|lower|escape:'htmlall'}">
          {if $state.title}
            <{$state.title_tag|default:'h2'}{if isset($state.title_color) && $state.title_color} style="color: {$state.title_color|escape:'htmlall'}"{/if}>{$state.title|escape:'htmlall'}</{$state.title_tag|default:'h2'}>
          {/if}
          {if $state.content}
            <div class="prettyblock-cover-content">
              {$state.content nofilter}
            </div>
          {/if}
          {if ($state.btn1_text && $state.btn1_link) || ($state.btn2_text && $state.btn2_link)}
            <div class="d-flex justify-content-center gap-2">
              {if $state.btn1_text && $state.btn1_link}
                <a href="{$state.btn1_link|escape:'htmlall'}" class="btn btn-{$state.btn1_type|escape:'htmlall'}">{$state.btn1_text|escape:'htmlall'}</a>
              {/if}
              {if $state.btn2_text && $state.btn2_link}
                <a href="{$state.btn2_link|escape:'htmlall'}" class="btn btn-{$state.btn2_type|escape:'htmlall'}">{$state.btn2_text|escape:'htmlall'}</a>
              {/if}
            </div>
          {/if}
        </div>
      </div>
      {if (isset($state.margin_left_mobile) && $state.margin_left_mobile) ||
          (isset($state.margin_right_mobile) && $state.margin_right_mobile) ||
          (isset($state.margin_top_mobile) && $state.margin_top_mobile) ||
          (isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile)}
        <style>
          @media (max-width: 767px) {
            #block-{$block.id_prettyblocks}-{$key} {
              {if isset($state.margin_left_mobile) && $state.margin_left_mobile}margin-left:{$state.margin_left_mobile|escape:'htmlall'};{/if}
              {if isset($state.margin_right_mobile) && $state.margin_right_mobile}margin-right:{$state.margin_right_mobile|escape:'htmlall'};{/if}
              {if isset($state.margin_top_mobile) && $state.margin_top_mobile}margin-top:{$state.margin_top_mobile|escape:'htmlall'};{/if}
              {if isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile}margin-bottom:{$state.margin_bottom_mobile|escape:'htmlall'};{/if}
            }
          }
        </style>
      {/if}
    {/foreach}
  {/if}
{/if}
</div>

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
