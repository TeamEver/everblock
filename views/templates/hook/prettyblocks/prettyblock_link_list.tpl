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

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
    <div class="prettyblock-link-list">
      <details class="prettyblock-link-list__details" open>
        <summary class="prettyblock-link-list__summary">
          {if isset($block.settings.title) && $block.settings.title}
            <span class="h2 pb-link-list-title">{$block.settings.title|escape:'htmlall'}</span>
          {/if}
          <span class="prettyblock-link-list__icon" aria-hidden="true"></span>
        </summary>
        {if isset($block.states) && $block.states}
          <ul class="list-unstyled prettyblock-link-list__list">
            {foreach from=$block.states item=state key=key}
              {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
              <li class="{if $state.css_class}{$state.css_class|escape:'htmlall'}{/if}" style="{$prettyblock_state_spacing_style}">
                <a href="{$state.url|escape:'htmlall'}" class="text-decoration-none link-secondary{if !isset($page.page_name) || $page.page_name != 'index'} obfme{/if}{if isset($block.settings.link_hover_effect) && $block.settings.link_hover_effect} everblock-link-hover--text{/if}" title="{$state.name|escape:'htmlall'}"{if $state.target_blank} target="_blank"{/if}>{$state.name|escape:'htmlall'}</a>
              </li>
            {/foreach}
          </ul>
        {/if}
      </details>
    </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<!-- /Module Ever Block -->
