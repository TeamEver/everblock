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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}
{assign var=everblockNow value=$smarty.now}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
{if isset($block.states) && $block.states}
  {assign var=visibleStatesCount value=0}
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

  {if $visibleStatesCount > 0}
    <section class="prettyblocks-slider container-fluid px-0 mt-3 {if $block.settings.default.container}container{/if}" style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
      <div class="prettyblocks-image-slider-wrapper position-relative px-2 px-md-0 pb-2">
        <div class="prettyblocks-image-slider"
             data-autoplay="{if isset($block.settings.slider_autoplay) && $block.settings.slider_autoplay}1{else}0{/if}"
             data-autoplay-speed="{$block.settings.slider_autoplay_delay|default:5000|intval}"
             data-transition-speed="{$block.settings.slider_transition_speed|default:500|intval}"
             data-pause-on-hover="{if isset($block.settings.slider_pause_on_hover) && $block.settings.slider_pause_on_hover}1{else}0{/if}"
             data-show-arrows="{if isset($block.settings.slider_show_arrows) && $block.settings.slider_show_arrows}1{else}0{/if}"
             data-show-dots="{if isset($block.settings.slider_show_dots) && $block.settings.slider_show_dots}1{else}0{/if}">
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
              {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_img_slider_state_spacing_style'}
              <div class="prettyblocks-slider-item">
                <div class="prettyblocks-slider-item-inner" style="{$prettyblock_img_slider_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
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
                          <img src="{$state.image.url|replace:'.webp':'.jpg'}" title="{$state.name}" alt="{$state.name}" class="img img-fluid w-100 lazyload" loading="lazy">
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
            {/if}
          {/foreach}
        </div>
      </div>
    </section>
  {/if}
{/if}

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
