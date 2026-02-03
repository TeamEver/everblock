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

{assign var='page_url' value=$urls.current_url|default:''}
{assign var='page_title' value=$page.meta.title|default:$page.meta.page_title|default:$page.page_title|default:$page_title|default:''}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if} prettyblock-llm-links" style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
      <div class="prettyblock-llm-links__inner {$block.settings.css_class|escape:'htmlall':'UTF-8'}" data-page-title="{$page_title|escape:'htmlall':'UTF-8'}" data-page-url="{$page_url|escape:'htmlall':'UTF-8'}">
        {if $block.settings.heading_text}
          <div class="prettyblock-llm-links__heading">
            <strong>{$block.settings.heading_text|escape:'htmlall':'UTF-8'}</strong>
          </div>
        {/if}
        {if isset($block.states) && $block.states}
          <div class="prettyblock-llm-links__list row">
            {foreach from=$block.states item=state key=key}
              {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
              {assign var='prompt_text' value=$state.prompt_template|default:''}
              {assign var='prompt_text' value=$prompt_text|replace:'{{title}}':$page_title}
              {assign var='prompt_text' value=$prompt_text|replace:'{{url}}':$page_url}
              {assign var='base_url' value=$state.base_url|default:''}
              {assign var='link_target' value='_self'}
              {if isset($state.open_in_new_tab) && $state.open_in_new_tab}
                {assign var='link_target' value='_blank'}
              {/if}
              <div class="col-6 col-md-4 col-lg-3" style="{$prettyblock_state_spacing_style}">
                <a class="prettyblock-llm-links__item{if isset($block.settings.link_hover_effect) && $block.settings.link_hover_effect} everblock-link-hover--block{/if}{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" href="{$base_url}{$prompt_text|escape:'url'}" data-base-url="{$base_url|escape:'htmlall':'UTF-8'}" data-prompt-template="{$state.prompt_template|escape:'htmlall':'UTF-8'}" target="{$link_target}"{if $link_target === '_blank'} rel="noopener noreferrer"{/if}>
                  {if isset($state.icon.url) && $state.icon.url}
                    <img class="prettyblock-llm-links__icon" src="{$state.icon.url|escape:'htmlall':'UTF-8'}" alt="{$state.icon_alt|default:$state.label|default:''|escape:'htmlall':'UTF-8'}" loading="lazy" width="92" height="92">
                  {/if}
                  {if $state.label}
                    <span class="prettyblock-llm-links__label">{$state.label|escape:'htmlall':'UTF-8'}</span>
                  {/if}
                </a>
              </div>
            {/foreach}
          </div>
        {/if}
      </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
