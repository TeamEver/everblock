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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  <div class="{if $block.settings.default.container}container{/if}"  style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {foreach from=$block.states item=state key=key}
      {assign var="icon_url" value=false}
      {if (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url) && $state.icon.url}
        {assign var="icon_url" value=$state.icon.url}
      {elseif isset($state.icon) && is_string($state.icon) && $state.icon|trim != ''}
        {if $state.icon|substr:-4 != '.svg'}
          {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
        {else}
          {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
        {/if}
      {/if}
      <div id="block-{$block.id_prettyblocks}-{$key}" class="everblock-counter text-center" data-value="{$state.value|escape:'htmlall':'UTF-8'}" data-speed="{$state.animation_speed|escape:'htmlall':'UTF-8'}">
        {if $icon_url}
          <div class="mb-2">
            <img src="{$icon_url|escape:'htmlall'}" alt="{$state.label|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
          </div>
        {/if}
        <span class="everblock-counter-value">0</span>
        {if $state.label}
          <p class="everblock-counter-label mb-0">{$state.label|escape:'htmlall'}</p>
        {/if}
      </div>
    {/foreach}
    {if $block.settings.default.container}
        </div>
    {/if}
  </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
