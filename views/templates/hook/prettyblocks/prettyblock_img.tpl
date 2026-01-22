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

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
{assign var=everblockNow value=$smarty.now}
{assign var=visibleStatesCount value=0}
{assign var=displayMode value=$block.settings.display_mode|default:''}
{if $displayMode == '' && isset($block.settings.slider) && $block.settings.slider}
  {assign var=displayMode value='Slider'}
{elseif $displayMode == ''}
  {assign var=displayMode value='Grid'}
{/if}
{assign var=columnsDesktop value=$block.settings.columns_desktop|default:1}
{assign var=columnsTablet value=$block.settings.columns_tablet|default:1}
{assign var=columnsMobile value=$block.settings.columns_mobile|default:1}
{assign var=colDesktopClass value='col-lg-12'}
{if $columnsDesktop == 2}
  {assign var=colDesktopClass value='col-lg-6'}
{elseif $columnsDesktop == 3}
  {assign var=colDesktopClass value='col-lg-4'}
{elseif $columnsDesktop == 4}
  {assign var=colDesktopClass value='col-lg-3'}
{elseif $columnsDesktop == 6}
  {assign var=colDesktopClass value='col-lg-2'}
{/if}
{assign var=colTabletClass value='col-md-12'}
{if $columnsTablet == 2}
  {assign var=colTabletClass value='col-md-6'}
{elseif $columnsTablet == 3}
  {assign var=colTabletClass value='col-md-4'}
{/if}
{assign var=colMobileClass value='col-12'}
{if $columnsMobile == 2}
  {assign var=colMobileClass value='col-6'}
{/if}
{assign var=gapSetting value=$block.settings.gap|default:'medium'}
{assign var=gapClass value='g-3'}
{if $gapSetting == 'none'}
  {assign var=gapClass value='g-0'}
{elseif $gapSetting == 'small'}
  {assign var=gapClass value='g-2'}
{elseif $gapSetting == 'large'}
  {assign var=gapClass value='g-4'}
{/if}
{assign var=sliderItemsDesktop value=$block.settings.slider_items|default:3|intval}
{assign var=sliderItemsTablet value=$block.settings.columns_tablet|default:1|intval}
{assign var=sliderItemsMobile value=$block.settings.columns_mobile|default:1|intval}
{assign var=maxSliderItems value=$sliderItemsDesktop}
{if $sliderItemsTablet > $maxSliderItems}
  {assign var=maxSliderItems value=$sliderItemsTablet}
{/if}
{if $sliderItemsMobile > $maxSliderItems}
  {assign var=maxSliderItems value=$sliderItemsMobile}
{/if}
{assign var=baseItemClass value='position-relative overflow-hidden'}
{assign var=layoutItemClass value="{$baseItemClass} {$colMobileClass} {$colTabletClass} {$colDesktopClass}"}
{if isset($block.states) && $block.states}
  {foreach from=$block.states item=state}
    {assign var=isStateVisible value=true}
    {assign var=startDateStr value=$state.start_date|default:''}
    {if $startDateStr ne ''}
      {assign var=startTimestamp value=$startDateStr|@strtotime}
      {if $startTimestamp && $everblockNow < $startTimestamp}
        {assign var=isStateVisible value=false}
      {/if}
    {/if}
    {if $isStateVisible}
      {assign var=endDateStr value=$state.end_date|default:''}
      {if $endDateStr ne ''}
        {assign var=endTimestamp value=$endDateStr|@strtotime}
        {if $endTimestamp && $everblockNow > $endTimestamp}
          {assign var=isStateVisible value=false}
        {/if}
      {/if}
    {/if}
    {if $isStateVisible}
      {assign var=visibleStatesCount value=$visibleStatesCount+1}
    {/if}
  {/foreach}
  {assign var='use_slider' value=($displayMode == 'Slider' && $visibleStatesCount > 1 && $maxSliderItems < $visibleStatesCount)}
  {if $use_slider}
    <div class="ever-slider overflow-hidden position-relative"
         data-items="{$sliderItemsDesktop|escape:'htmlall':'UTF-8'}"
         data-items-mobile="{$sliderItemsMobile|escape:'htmlall':'UTF-8'}"
         data-autoplay="{if isset($block.settings.slider_autoplay) && $block.settings.slider_autoplay}1{else}0{/if}"
         data-autoplay-delay="{$block.settings.slider_autoplay_delay|default:5000|escape:'htmlall':'UTF-8'}"
         data-infinite="1">
      <div class="ever-slider-track d-flex">
      {foreach from=$block.states item=state key=key}
        {assign var=isStateVisible value=true}
        {assign var=startDateStr value=$state.start_date|default:''}
        {if $startDateStr ne ''}
          {assign var=startTimestamp value=$startDateStr|@strtotime}
          {if $startTimestamp && $everblockNow < $startTimestamp}
            {assign var=isStateVisible value=false}
          {/if}
        {/if}
        {if $isStateVisible}
          {assign var=endDateStr value=$state.end_date|default:''}
          {if $endDateStr ne ''}
            {assign var=endTimestamp value=$endDateStr|@strtotime}
            {if $endTimestamp && $everblockNow > $endTimestamp}
              {assign var=isStateVisible value=false}
            {/if}
          {/if}
        {/if}
        {if $isStateVisible}
          {assign var=itemClass value="{$baseItemClass} ever-slider-item flex-shrink-0"}
          {if $state.css_class}
            {assign var=itemClass value="{$baseItemClass} ever-slider-item flex-shrink-0 {$state.css_class}"}
          {/if}
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
          <div id="block-{$block.id_prettyblocks}-{$key}" class="{$itemClass|escape:'htmlall'}" style="
            {$prettyblock_state_spacing_style}
            {if isset($state.default.bg_color)}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
          ">
            {if isset($state.url) && $state.url}
              <a href="{$state.url|escape:'htmlall':'UTF-8'}" class="d-block position-relative" title="{if isset($state.alt)}{$state.alt|escape:'htmlall':'UTF-8'}{else}{$shop.name|escape:'htmlall':'UTF-8'}{/if}">
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
          {if (isset($state.margin_left_mobile) && $state.margin_left_mobile) ||
              (isset($state.margin_right_mobile) && $state.margin_right_mobile) ||
              (isset($state.margin_top_mobile) && $state.margin_top_mobile) ||
              (isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile)}
            <style>
              @media (max-width: 767px) {
                #block-{$block.id_prettyblocks}-{$key} {
                  {if isset($state.margin_left_mobile) && $state.margin_left_mobile}margin-left:{$state.margin_left_mobile|escape:'htmlall':'UTF-8'};{/if}
                  {if isset($state.margin_right_mobile) && $state.margin_right_mobile}margin-right:{$state.margin_right_mobile|escape:'htmlall':'UTF-8'};{/if}
                  {if isset($state.margin_top_mobile) && $state.margin_top_mobile}margin-top:{$state.margin_top_mobile|escape:'htmlall':'UTF-8'};{/if}
                  {if isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile}margin-bottom:{$state.margin_bottom_mobile|escape:'htmlall':'UTF-8'};{/if}
                }
              }
            </style>
          {/if}
        {/if}
      {/foreach}
      </div>
      <button class="ever-slider-prev" type="button" aria-label="Previous"></button>
      <button class="ever-slider-next" type="button" aria-label="Next"></button>
    </div>
  {else}
    {if $block.settings.default.force_full_width}
      <div class="row gx-0 no-gutters">
    {elseif $block.settings.default.container}
      <div class="row">
    {/if}
    <div class="row mt-4 {$gapClass} justify-content-center">
      {foreach from=$block.states item=state key=key}
        {assign var=isStateVisible value=true}
        {assign var=startDateStr value=$state.start_date|default:''}
        {if $startDateStr ne ''}
          {assign var=startTimestamp value=$startDateStr|@strtotime}
          {if $startTimestamp && $everblockNow < $startTimestamp}
            {assign var=isStateVisible value=false}
          {/if}
        {/if}
        {if $isStateVisible}
          {assign var=endDateStr value=$state.end_date|default:''}
          {if $endDateStr ne ''}
            {assign var=endTimestamp value=$endDateStr|@strtotime}
            {if $endTimestamp && $everblockNow > $endTimestamp}
              {assign var=isStateVisible value=false}
            {/if}
          {/if}
        {/if}
        {if $isStateVisible}
          {assign var=itemClass value=$layoutItemClass}
          {if $state.css_class}
            {assign var=itemClass value="{$baseItemClass} {$state.css_class}"}
          {/if}
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
          <div id="block-{$block.id_prettyblocks}-{$key}" class="{$itemClass|escape:'htmlall'}" style="
            {$prettyblock_state_spacing_style}
            {if isset($state.default.bg_color)}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
          ">
            {if isset($state.url) && $state.url}
              <a href="{$state.url|escape:'htmlall':'UTF-8'}" class="d-block position-relative" title="{if isset($state.alt)}{$state.alt|escape:'htmlall':'UTF-8'}{else}{$shop.name|escape:'htmlall':'UTF-8'}{/if}">
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
          {if (isset($state.margin_left_mobile) && $state.margin_left_mobile) ||
              (isset($state.margin_right_mobile) && $state.margin_right_mobile) ||
              (isset($state.margin_top_mobile) && $state.margin_top_mobile) ||
              (isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile)}
            <style>
              @media (max-width: 767px) {
                #block-{$block.id_prettyblocks}-{$key} {
                  {if isset($state.margin_left_mobile) && $state.margin_left_mobile}margin-left:{$state.margin_left_mobile|escape:'htmlall':'UTF-8'};{/if}
                  {if isset($state.margin_right_mobile) && $state.margin_right_mobile}margin-right:{$state.margin_right_mobile|escape:'htmlall':'UTF-8'};{/if}
                  {if isset($state.margin_top_mobile) && $state.margin_top_mobile}margin-top:{$state.margin_top_mobile|escape:'htmlall':'UTF-8'};{/if}
                  {if isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile}margin-bottom:{$state.margin_bottom_mobile|escape:'htmlall':'UTF-8'};{/if}
                }
              }
            </style>
          {/if}
        {/if}
      {/foreach}
    </div>
    {if $block.settings.default.force_full_width || $block.settings.default.container}
      </div>
    {/if}
  {/if}
{/if}
</div>
