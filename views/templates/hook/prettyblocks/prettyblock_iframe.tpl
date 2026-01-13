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
<div class="{if $block.settings.default.container}container{/if}">
    <div class="everblock"  style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
      {foreach from=$block.states item=state key=key}
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}

          <div id="block-{$block.id_prettyblocks}-{$key}" class="everblock {$state.css_class|escape:'htmlall':'UTF-8'}" style="{$prettyblock_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
            {if $state.iframe_source == 'youtube'}
            <iframe class="everblock-prettyblock-iframe" width="{if isset($state.width) && $state.width}{$state.width}{else}100{/if}" height="{if isset($state.height) && $state.height}{$state.height}{else}315{/if}" src="{$state.iframe_link}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen scrolling="no"></iframe>
            {elseif $state.iframe_source == 'vimeo'}
            <iframe class="everblock-prettyblock-iframe" src="https://player.vimeo.com/video/' . $matches[1] . '?color=ffffff&title=0&byline=0&portrait=0" width="{if isset($state.width) && $state.width}{$state.width}{else}100{/if}" height="{if isset($state.height) && $state.height}{$state.height}{else}360{/if}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen scrolling="no"></iframe>
            {elseif $state.iframe_source == 'dailymotion'}
            <iframe class="everblock-prettyblock-iframe" frameborder="0" width="{if isset($state.width) && $state.width}{$state.width}{else}100{/if}" height="{if isset($state.height) && $state.height}{$state.height}{else}270{/if}" src="{$state.iframe_link}" allowfullscreen scrolling="no"></iframe>
            {elseif $state.iframe_source == 'vidyard'}
            <iframe class="everblock-prettyblock-iframe" src="{$state.iframe_link}" width="{if isset($state.width) && $state.width}{$state.width}{else}100{/if}" height="{if isset($state.height) && $state.height}{$state.height}{else}360{/if}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen scrolling="no"></iframe>
            {/if}
          </div>
      {/foreach}
    </div>
</div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
