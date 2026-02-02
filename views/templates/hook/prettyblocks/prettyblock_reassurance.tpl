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
{assign var='reassuranceColumns' value=$block.settings.items_per_row|default:$block.settings.default.items_per_row|default:$block.settings.columns|default:$block.settings.default.columns|default:3|intval}
{assign var='statesCount' value=$block.states|@count}
{assign var='useSlider' value=(isset($block.settings.slider) && $block.settings.slider && $statesCount > 1)}
{assign var='sliderDevices' value=$block.settings.slider_devices|default:'Desktop and mobile'}
{assign var='useDesktopSlider' value=($useSlider && ($sliderDevices == 'Desktop and mobile' || $sliderDevices == 'Desktop'))}
{assign var='useMobileSlider' value=($useSlider && ($sliderDevices == 'Desktop and mobile' || $sliderDevices == 'Mobile only'))}
{assign var='sliderItemsDesktop' value=$block.settings.slider_items_desktop|default:3|intval}
{assign var='sliderItemsMobile' value=$block.settings.slider_items_mobile|default:1|intval}
{assign var='carouselIdBase' value='everblock-reassurance-carousel-'|cat:$block.id_prettyblocks}
{assign var='reassuranceColumnClass' value=''}
{if $reassuranceColumns > 0 && (!$useSlider || ($useSlider && (!$useDesktopSlider || $statesCount <= $sliderItemsDesktop)))}
  {math assign="reassuranceColumnWidth" equation="12 / x" x=$reassuranceColumns format="%.0f"}
  {assign var='reassuranceColumnClass' value="col-12 col-md-"|cat:$reassuranceColumnWidth|cat:' '}
{elseif $block.settings.default.display_inline && (!$useSlider || ($useSlider && (!$useDesktopSlider || $statesCount <= $sliderItemsDesktop)))}
  {assign var='reassuranceColumnClass' value='col '}
{elseif $useSlider}
  {assign var='reassuranceColumnClass' value='col-12 '}
{/if}

{assign var='containerClass' value=''}
{if $block.settings.default.force_full_width}
  {assign var='containerClass' value='container-fluid px-0 mx-0 mt-20px'}
{elseif $block.settings.default.container}
  {assign var='containerClass' value='container'}
{/if}
{assign var='wrapperClasses' value=$containerClass|cat:' '|cat:$prettyblock_visibility_class|trim}

{assign var='shouldRenderRow' value=$block.settings.default.force_full_width || $block.settings.default.container || $block.settings.default.display_inline || $reassuranceColumns > 0 || $useSlider}
{assign var='rowClass' value=''}
{if $shouldRenderRow}
  {if $block.settings.default.force_full_width}
    {assign var='rowClass' value='row g-10px justify-content-center'}
  {else}
    {assign var='rowClass' value='row justify-content-center'}
  {/if}
{/if}

<div id="block-{$block.id_prettyblocks}" class="{$wrapperClasses}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if isset($block.states) && $block.states}
    {if $useSlider}
      {assign var='sliderRowClass' value='ever-bootstrap-carousel'}

      {if $useDesktopSlider && !$useMobileSlider}
        <div id="{$carouselIdBase}-desktop" class="{$sliderRowClass} d-none d-md-block"
             data-items-desktop="{$sliderItemsDesktop}"
             data-items-mobile="{$sliderItemsMobile}"
             data-row-class="{$rowClass}"
             data-controls="true"
             data-indicators="true"
             data-infinite="1">
          {foreach from=$block.states item=state key=key}
            {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
            {assign var="icon_url" value=false}
            {if (is_array($state.image) || is_object($state.image)) && isset($state.image.url) && $state.image.url}
              {assign var="icon_url" value=$state.image.url}
            {elseif (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url) && $state.icon.url}
              {assign var="icon_url" value=$state.icon.url}
            {elseif isset($state.icon) && is_string($state.icon) && $state.icon|trim != ''}
              {if $state.icon|substr:-4 != '.svg'}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
              {else}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
              {/if}
            {/if}
            <div id="block-{$block.id_prettyblocks}-{$key}" class="{$reassuranceColumnClass}text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_state_spacing_style}{if $state.background_color}background-color:{$state.background_color};{/if}{if $state.text_color}color:{$state.text_color};{/if}">
              {if $icon_url}
                <div class="mb-2">
                  {if $icon_url|substr:-4 == '.svg'}
                    <img src="{$icon_url|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                  {else}
                    <picture>
                      <source srcset="{$icon_url|escape:'htmlall'}" type="image/webp">
                      <source srcset="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" type="image/jpeg">
                      <img src="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                    </picture>
                  {/if}
                </div>
              {/if}
              {if $state.title}
                <p class="h6 fw-bold mb-1">{$state.title|escape:'htmlall'}</p>
              {/if}
              {if $state.text}
                <p class="small m-0">{$state.text nofilter}</p>
              {/if}
            </div>
          {/foreach}
        </div>
        <div class="{$rowClass} d-block d-md-none">
          {foreach from=$block.states item=state key=key}
            {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
            {assign var="icon_url" value=false}
            {if (is_array($state.image) || is_object($state.image)) && isset($state.image.url) && $state.image.url}
              {assign var="icon_url" value=$state.image.url}
            {elseif (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url) && $state.icon.url}
              {assign var="icon_url" value=$state.icon.url}
            {elseif isset($state.icon) && is_string($state.icon) && $state.icon|trim != ''}
              {if $state.icon|substr:-4 != '.svg'}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
              {else}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
              {/if}
            {/if}
            <div id="block-{$block.id_prettyblocks}-{$key}-mobile" class="{$reassuranceColumnClass}text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_state_spacing_style}{if $state.background_color}background-color:{$state.background_color};{/if}{if $state.text_color}color:{$state.text_color};{/if}">
              {if $icon_url}
                <div class="mb-2">
                  {if $icon_url|substr:-4 == '.svg'}
                    <img src="{$icon_url|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                  {else}
                    <picture>
                      <source srcset="{$icon_url|escape:'htmlall'}" type="image/webp">
                      <source srcset="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" type="image/jpeg">
                      <img src="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                    </picture>
                  {/if}
                </div>
              {/if}
              {if $state.title}
                <p class="h6 fw-bold mb-1">{$state.title|escape:'htmlall'}</p>
              {/if}
              {if $state.text}
                <p class="small m-0">{$state.text nofilter}</p>
              {/if}
            </div>
          {/foreach}
        </div>
      {elseif $useMobileSlider && !$useDesktopSlider}
        <div class="{$rowClass} d-none d-md-block">
          {foreach from=$block.states item=state key=key}
            {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
            {assign var="icon_url" value=false}
            {if (is_array($state.image) || is_object($state.image)) && isset($state.image.url) && $state.image.url}
              {assign var="icon_url" value=$state.image.url}
            {elseif (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url) && $state.icon.url}
              {assign var="icon_url" value=$state.icon.url}
            {elseif isset($state.icon) && is_string($state.icon) && $state.icon|trim != ''}
              {if $state.icon|substr:-4 != '.svg'}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
              {else}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
              {/if}
            {/if}
            <div id="block-{$block.id_prettyblocks}-{$key}-desktop" class="{$reassuranceColumnClass}text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_state_spacing_style}{if $state.background_color}background-color:{$state.background_color};{/if}{if $state.text_color}color:{$state.text_color};{/if}">
              {if $icon_url}
                <div class="mb-2">
                  {if $icon_url|substr:-4 == '.svg'}
                    <img src="{$icon_url|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                  {else}
                    <picture>
                      <source srcset="{$icon_url|escape:'htmlall'}" type="image/webp">
                      <source srcset="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" type="image/jpeg">
                      <img src="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                    </picture>
                  {/if}
                </div>
              {/if}
              {if $state.title}
                <p class="h6 fw-bold mb-1">{$state.title|escape:'htmlall'}</p>
              {/if}
              {if $state.text}
                <p class="small m-0">{$state.text nofilter}</p>
              {/if}
            </div>
          {/foreach}
        </div>
        <div id="{$carouselIdBase}-mobile" class="{$sliderRowClass} d-block d-md-none"
             data-items-desktop="{$sliderItemsDesktop}"
             data-items-mobile="{$sliderItemsMobile}"
             data-row-class="{$rowClass}"
             data-controls="true"
             data-indicators="true"
             data-infinite="1">
          {foreach from=$block.states item=state key=key}
            {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
            {assign var="icon_url" value=false}
            {if (is_array($state.image) || is_object($state.image)) && isset($state.image.url) && $state.image.url}
              {assign var="icon_url" value=$state.image.url}
            {elseif (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url) && $state.icon.url}
              {assign var="icon_url" value=$state.icon.url}
            {elseif isset($state.icon) && is_string($state.icon) && $state.icon|trim != ''}
              {if $state.icon|substr:-4 != '.svg'}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
              {else}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
              {/if}
            {/if}
            <div id="block-{$block.id_prettyblocks}-{$key}-slider" class="{$reassuranceColumnClass}text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_state_spacing_style}{if $state.background_color}background-color:{$state.background_color};{/if}{if $state.text_color}color:{$state.text_color};{/if}">
              {if $icon_url}
                <div class="mb-2">
                  {if $icon_url|substr:-4 == '.svg'}
                    <img src="{$icon_url|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                  {else}
                    <picture>
                      <source srcset="{$icon_url|escape:'htmlall'}" type="image/webp">
                      <source srcset="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" type="image/jpeg">
                      <img src="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                    </picture>
                  {/if}
                </div>
              {/if}
              {if $state.title}
                <p class="h6 fw-bold mb-1">{$state.title|escape:'htmlall'}</p>
              {/if}
              {if $state.text}
                <p class="small m-0">{$state.text nofilter}</p>
              {/if}
            </div>
          {/foreach}
        </div>
      {else}
        <div id="{$carouselIdBase}-all" class="{$sliderRowClass}"
             data-items-desktop="{$sliderItemsDesktop}"
             data-items-mobile="{$sliderItemsMobile}"
             data-row-class="{$rowClass}"
             data-controls="true"
             data-indicators="true"
             data-infinite="1">
          {foreach from=$block.states item=state key=key}
            {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
            {assign var="icon_url" value=false}
            {if (is_array($state.image) || is_object($state.image)) && isset($state.image.url) && $state.image.url}
              {assign var="icon_url" value=$state.image.url}
            {elseif (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url) && $state.icon.url}
              {assign var="icon_url" value=$state.icon.url}
            {elseif isset($state.icon) && is_string($state.icon) && $state.icon|trim != ''}
              {if $state.icon|substr:-4 != '.svg'}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
              {else}
                {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
              {/if}
            {/if}
            <div id="block-{$block.id_prettyblocks}-{$key}-all" class="{$reassuranceColumnClass}text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_state_spacing_style}{if $state.background_color}background-color:{$state.background_color};{/if}{if $state.text_color}color:{$state.text_color};{/if}">
              {if $icon_url}
                <div class="mb-2">
                  {if $icon_url|substr:-4 == '.svg'}
                    <img src="{$icon_url|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                  {else}
                    <picture>
                      <source srcset="{$icon_url|escape:'htmlall'}" type="image/webp">
                      <source srcset="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" type="image/jpeg">
                      <img src="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                    </picture>
                  {/if}
                </div>
              {/if}
              {if $state.title}
                <p class="h6 fw-bold mb-1">{$state.title|escape:'htmlall'}</p>
              {/if}
              {if $state.text}
                <p class="small m-0">{$state.text nofilter}</p>
              {/if}
            </div>
          {/foreach}
        </div>
      {/if}
    {else}
      {if $shouldRenderRow}
        <div class="{$rowClass}">
      {/if}
      {foreach from=$block.states item=state key=key}
        {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
        {assign var="icon_url" value=false}
        {if (is_array($state.image) || is_object($state.image)) && isset($state.image.url) && $state.image.url}
          {assign var="icon_url" value=$state.image.url}
        {elseif (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url) && $state.icon.url}
          {assign var="icon_url" value=$state.icon.url}
        {elseif isset($state.icon) && is_string($state.icon) && $state.icon|trim != ''}
          {if $state.icon|substr:-4 != '.svg'}
            {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
          {else}
            {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
          {/if}
        {/if}
        <div id="block-{$block.id_prettyblocks}-{$key}" class="{$reassuranceColumnClass}text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_state_spacing_style}{if $state.background_color}background-color:{$state.background_color};{/if}{if $state.text_color}color:{$state.text_color};{/if}">
          {if $icon_url}
            <div class="mb-2">
              {if $icon_url|substr:-4 == '.svg'}
                <img src="{$icon_url|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
              {else}
                <picture>
                  <source srcset="{$icon_url|escape:'htmlall'}" type="image/webp">
                  <source srcset="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" type="image/jpeg">
                  <img src="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
                </picture>
              {/if}
            </div>
          {/if}
          {if $state.title}
            <p class="h6 fw-bold mb-1">{$state.title|escape:'htmlall'}</p>
          {/if}
          {if $state.text}
            <p class="small m-0">{$state.text nofilter}</p>
          {/if}
        </div>
      {/foreach}
      {if $shouldRenderRow}
        </div>
      {/if}
    {/if}
  {/if}
</div>
