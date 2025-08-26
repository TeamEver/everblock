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
<!-- Module Ever Block -->
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
    <div class="everblock-social-links d-flex flex-row flex-wrap">
        {foreach from=$block.states item=state}
          {if isset($state.url) && $state.url}
            {assign var="icon_url" value=false}
            {if (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url) && $state.icon.url}
              {assign var="icon_url" value=$state.icon.url}
            {elseif isset($state.icon) && is_string($state.icon)}
              {if $state.icon|substr:-4 == '.svg'}
                {assign var="icon_url" value=$smarty.const._PS_MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon}
              {else}
                {assign var="icon_url" value=$smarty.const._PS_MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
              {/if}
            {/if}
            {if $icon_url}
              {assign var="svg_content" value=$icon_url|@file_get_contents}
              {if isset($block.settings.icon_color) && $block.settings.icon_color}
                {assign var="svg_content" value=$svg_content|regex_replace:'/fill="[^"]+"/':'fill="currentColor"'}
              {/if}

              {* ✅ Chaque icône dans un wrapper séparé *}
              <span class="everblock-social-link">
                <a href="{$state.url|escape:'htmlall'}"
                   title="{$state.url|escape:'htmlall'}"
                   target="_blank"
                   style="{if isset($block.settings.icon_color) && $block.settings.icon_color}color:{$block.settings.icon_color|escape:'htmlall'};{/if}">
                  {$svg_content nofilter}
                </a>
              </span>

            {/if}
          {/if}
        {/foreach}
      </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<!-- /Module Ever Block -->

