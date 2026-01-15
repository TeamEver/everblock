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
    {if $block.settings.default.container}
      <div class="row">
    {/if}
      <div class="everblock {$block.settings.css_class|escape:'htmlall':'UTF-8'}" style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
        {if isset($block.settings.iframe_src) && $block.settings.iframe_src}
          {assign var='iframe_src' value=$block.settings.iframe_src|trim}
          {if $iframe_src && ($iframe_src|regex_replace:"/^(https?:)?\\/\\//":'' == $iframe_src)}
            {assign var='iframe_src' value="https://`$iframe_src`"}
          {/if}
          <iframe class="everblock-prettyblock-iframe" src="{$iframe_src|escape:'htmlall':'UTF-8'}"
                  width="{if isset($block.settings.iframe_width) && $block.settings.iframe_width}{$block.settings.iframe_width|escape:'htmlall':'UTF-8'}{else}100%{/if}"
                  height="{if isset($block.settings.iframe_height) && $block.settings.iframe_height}{$block.settings.iframe_height|escape:'htmlall':'UTF-8'}{else}400{/if}"
                  style="border:0;"
                  {if isset($block.settings.loading_behavior) && $block.settings.loading_behavior}loading="{$block.settings.loading_behavior|escape:'htmlall':'UTF-8'}"{/if}
                  {if $block.settings.allow_fullscreen}allowfullscreen{/if} scrolling="no"></iframe>
        {/if}
      </div>
    {if $block.settings.default.container}
      </div>
    {/if}
  </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
