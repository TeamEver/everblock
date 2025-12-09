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
{assign var='reassuranceColumnClass' value=''}
{if $reassuranceColumns > 0}
  {math assign="reassuranceColumnWidth" equation="12 / x" x=$reassuranceColumns format="%.0f"}
  {assign var='reassuranceColumnClass' value="col-12 col-md-"|cat:$reassuranceColumnWidth|cat:' '}
{elseif $block.settings.default.display_inline}
  {assign var='reassuranceColumnClass' value='col '}
{/if}

{assign var='containerClass' value=''}
{if $block.settings.default.force_full_width}
  {assign var='containerClass' value='container-fluid px-0 mx-0 mt-20px'}
{elseif $block.settings.default.container}
  {assign var='containerClass' value='container'}
{/if}
{assign var='wrapperClasses' value=$containerClass|cat:' '|cat:$prettyblock_visibility_class|trim}

{assign var='shouldRenderRow' value=$block.settings.default.force_full_width || $block.settings.default.container || $block.settings.default.display_inline || $reassuranceColumns > 0}
{assign var='rowClass' value=''}
{if $shouldRenderRow}
  {if $block.settings.default.force_full_width}
    {assign var='rowClass' value='row g-10px'}
  {else}
    {assign var='rowClass' value='row'}
  {/if}
{/if}

<div id="block-{$block.id_prettyblocks}" class="{$wrapperClasses}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $shouldRenderRow}
    <div class="{$rowClass}">
  {/if}

  {if isset($block.states) && $block.states}
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
  {/if}

  {if $shouldRenderRow}
    </div>
  {/if}
</div>
