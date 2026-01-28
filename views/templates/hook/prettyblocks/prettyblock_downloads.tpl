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

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  {if isset($block.settings.title) && $block.settings.title}
    <span class="h2 pb-downloads-title">{$block.settings.title|escape:'htmlall'}</span>
  {/if}
  {if isset($block.states) && $block.states}
    <ul class="everblock-downloads list-unstyled">
      {foreach from=$block.states item=state key=key}
        {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
        {assign var="icon_url" value=false}
        {if isset($state.icon) && $state.icon}
          {if $state.icon|substr:-4 != '.svg'}
            {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
          {else}
            {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
          {/if}
        {/if}
        <li class="d-flex{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_state_spacing_style}">
          {if $icon_url}
            <img src="{$icon_url|escape:'htmlall'}" alt="" loading="lazy" class="download-icon" width="24" height="24" />
          {/if}
          <div>
            {if isset($state.file.url) && $state.file.url}
              <a href="{$state.file.url|escape:'htmlall'}" class="download-title{if isset($block.settings.link_hover_effect) && $block.settings.link_hover_effect} everblock-link-hover--text{/if}" download title="{$state.title|escape:'htmlall'}">{$state.title|escape:'htmlall'}</a>
            {else}
              <span class="download-title">{$state.title|escape:'htmlall'}</span>
            {/if}
            {if $state.description}
              <p class="download-description">{$state.description nofilter}</p>
            {/if}
          </div>
        </li>
      {/foreach}
    </ul>
  {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<!-- /Module Ever Block -->
