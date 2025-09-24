{*
 * 2019-2025 Team Ever
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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<div class="{if $block.settings.default.container}container{/if}" style="{$prettyblock_spacing_style}">

  <div class="row align-items-center">
    <div class="col-md-6 mb-4 mb-md-0">
      {if $block.settings.title}
        <h2>{$block.settings.title|escape:'htmlall'}</h2>
      {/if}
      {if $block.settings.content}
        <div>{$block.settings.content nofilter}</div>
      {/if}
      {if $block.settings.button_link && $block.settings.button_text}
        <a href="{$block.settings.button_link|escape:'htmlall'}" class="btn btn-primary mt-3">
          {$block.settings.button_text|escape:'htmlall'}
        </a>
      {/if}
    </div>
    <div class="col-md-6 text-center">
      {if $block.settings.button_link}<a href="{$block.settings.button_link|escape:'htmlall'}" title="{$block.settings.title|escape:'htmlall'}">{/if}
      <div class="position-relative d-inline-block">
        {if isset($block.settings.map_image.url) && $block.settings.map_image.url}
          <picture>
            <source srcset="{$block.settings.map_image.url}" type="image/webp">
            <source srcset="{$block.settings.map_image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
            <img src="{$block.settings.map_image.url|replace:'.webp':'.jpg'}" alt="{$block.settings.title|escape:'htmlall'}" class="img-fluid" loading="lazy">
          </picture>
        {/if}
        {if isset($block.states) && $block.states}
          {foreach from=$block.states item=state}
            <div class="position-absolute translate-middle text-nowrap" style="top:{$state.top|escape:'htmlall'};left:{$state.left|escape:'htmlall'};">
              <span class="d-inline-block bg-primary rounded-circle" style="width:10px;height:10px;"></span>
              {if $state.label}
                <span class="badge bg-primary ms-1">{$state.label|escape:'htmlall'}</span>
              {/if}
            </div>
          {/foreach}
        {/if}
      </div>
      {if $block.settings.button_link}</a>{/if}
    </div>
  </div>
</div>


  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
