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
      {if (not isset($state.image.url) or $state.image.url eq '') and (not isset($state.image_mobile.url) or $state.image_mobile.url eq '')}
        {assign var=isStateVisible value=false}
      {/if}
    {/if}
    {if $isStateVisible}
      {assign var=visibleStatesCount value=$visibleStatesCount+1}
    {/if}
  {/foreach}

  {if $visibleStatesCount > 0}
    {assign var=autoplayEnabled value=(isset($block.settings.slider_autoplay) && $block.settings.slider_autoplay)}
    {assign var=autoplayDelay value=$block.settings.slider_autoplay_delay|default:5000|intval}
    {if $autoplayDelay <= 0}
      {assign var=autoplayDelay value=5000}
    {/if}
    {assign var=transitionSpeed value=$block.settings.slider_transition_speed|default:500|intval}
    {if $transitionSpeed <= 0}
      {assign var=transitionSpeed value=500}
    {/if}
    {assign var=pauseOnHover value=(isset($block.settings.slider_pause_on_hover) && $block.settings.slider_pause_on_hover)}
    {assign var=showArrows value=(isset($block.settings.slider_show_arrows) && $block.settings.slider_show_arrows && $visibleStatesCount > 1)}
    {assign var=showDots value=(isset($block.settings.slider_show_dots) && $block.settings.slider_show_dots && $visibleStatesCount > 1)}
    {assign var='carouselId' value="prettyblocks-carousel-{$block.id_prettyblocks}"}

    <section class="prettyblocks-slider container-fluid px-0 mt-3 {if $block.settings.default.container}container{/if}"
             style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
      <div class="prettyblocks-image-slider-wrapper position-relative px-2 px-md-0 pb-2">
        <div id="{$carouselId}"
             class="prettyblocks-image-slider carousel slide"
             {if $autoplayEnabled}data-ride="carousel" data-bs-ride="carousel"{/if}
             data-autoplay="{if $autoplayEnabled}1{else}0{/if}"
             data-autoplay-speed="{$autoplayDelay}"
             data-transition-speed="{$transitionSpeed}"
             data-pause-on-hover="{if $pauseOnHover}1{else}0{/if}"
             data-show-arrows="{if $showArrows}1{else}0{/if}"
             data-show-dots="{if $showDots}1{else}0{/if}"
             data-interval="{if $autoplayEnabled}{$autoplayDelay}{else}false{/if}"
             data-bs-interval="{if $autoplayEnabled}{$autoplayDelay}{else}false{/if}"
             data-pause="{if $pauseOnHover}hover{else}false{/if}"
             data-bs-pause="{if $pauseOnHover}hover{else}false{/if}"
             style="--prettyblocks-transition-speed: {$transitionSpeed}ms;">
          {if $showDots}
            <ol class="carousel-indicators">
              {assign var='indicatorIndex' value=0}
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
                  {if (not isset($state.image.url) or $state.image.url eq '') and (not isset($state.image_mobile.url) or $state.image_mobile.url eq '')}
                    {assign var=isStateVisible value=false}
                  {/if}
                {/if}
                {if $isStateVisible}
                  <li data-target="#{$carouselId}"
                      data-slide-to="{$indicatorIndex}"
                      data-bs-target="#{$carouselId}"
                      data-bs-slide-to="{$indicatorIndex}"
                      class="{if $indicatorIndex == 0}active{/if}"
                      aria-label="{$state.name|escape:'htmlall'}"
                      aria-current="{if $indicatorIndex == 0}true{else}false{/if}"></li>
                  {assign var='indicatorIndex' value=$indicatorIndex+1}
                {/if}
              {/foreach}
            </ol>
          {/if}
          <div class="carousel-inner">
            {assign var='slideIndex' value=0}
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
                {if (not isset($state.image.url) or $state.image.url eq '') and (not isset($state.image_mobile.url) or $state.image_mobile.url eq '')}
                  {assign var=isStateVisible value=false}
                {/if}
              {/if}
              {if $isStateVisible}
                {assign var='desktopImageUrl' value=$state.image.url|default:''}
                {assign var='desktopImageJpgUrl' value=$desktopImageUrl|replace:'.webp':'.jpg'}
                {assign var='mobileImageUrl' value=$state.image_mobile.url|default:''}
                {assign var='mobileImageJpgUrl' value=$mobileImageUrl|replace:'.webp':'.jpg'}
                {assign var='fallbackImageUrl' value=$desktopImageUrl}
                {assign var='fallbackImageJpgUrl' value=$desktopImageJpgUrl}
                {if $fallbackImageUrl eq ''}
                  {assign var='fallbackImageUrl' value=$mobileImageUrl}
                  {assign var='fallbackImageJpgUrl' value=$mobileImageJpgUrl}
                {/if}
                {assign var='fallbackWidth' value=''}
                {assign var='fallbackHeight' value=''}
                {if isset($state.image.width) && $state.image.width > 0}
                  {assign var='fallbackWidth' value=$state.image.width|intval}
                {elseif isset($state.image_mobile.width) && $state.image_mobile.width > 0}
                  {assign var='fallbackWidth' value=$state.image_mobile.width|intval}
                {/if}
                {if isset($state.image.height) && $state.image.height > 0}
                  {assign var='fallbackHeight' value=$state.image.height|intval}
                {elseif isset($state.image_mobile.height) && $state.image_mobile.height > 0}
                  {assign var='fallbackHeight' value=$state.image_mobile.height|intval}
                {/if}
                {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_img_slider_state_spacing_style'}
                <div class="carousel-item prettyblocks-slider-item{if $slideIndex == 0} active{/if}">
                  <div class="prettyblocks-slider-item-inner" style="{$prettyblock_img_slider_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
                    {if $state.link}
                      {if $state.obfuscate}
                        {assign var="obflink" value=$state.link|base64_encode}
                        <span class="obflink d-block" data-obflink="{$obflink}">
                      {else}
                        <a href="{$state.link}" title="{$state.name}" {if $state.target_blank}target="_blank"{/if} class="d-block">
                      {/if}
                    {/if}
                      <picture class="prettyblocks-slider-picture d-block w-100">
                        {if $mobileImageUrl ne ''}
                          <source media="(max-width: 767.98px)" srcset="{$mobileImageUrl}" type="image/webp">
                          <source media="(max-width: 767.98px)" srcset="{$mobileImageJpgUrl}" type="image/jpeg">
                        {/if}
                        {if $desktopImageUrl ne ''}
                          <source media="(min-width: 768px)" srcset="{$desktopImageUrl}" type="image/webp">
                          <source media="(min-width: 768px)" srcset="{$desktopImageJpgUrl}" type="image/jpeg">
                        {/if}
                        <img src="{$fallbackImageJpgUrl}" title="{$state.name}" alt="{$state.name}" class="img img-fluid w-100 prettyblocks-slider-image lazyload" loading="lazy"{if $fallbackWidth ne ''} width="{$fallbackWidth}"{/if}{if $fallbackHeight ne ''} height="{$fallbackHeight}"{/if}>
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
                {assign var='slideIndex' value=$slideIndex+1}
              {/if}
            {/foreach}
          </div>
          {if $showArrows}
            <a class="carousel-control-prev" href="#{$carouselId}" role="button" data-slide="prev" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only visually-hidden">Previous</span>
            </a>
            <a class="carousel-control-next" href="#{$carouselId}" role="button" data-slide="next" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only visually-hidden">Next</span>
            </a>
          {/if}
        </div>
      </div>
    </section>
  {/if}
{/if}

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
