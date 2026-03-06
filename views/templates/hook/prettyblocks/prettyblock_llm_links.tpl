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

{assign var='page_title' value=$meta_title|default:$shop.name|default:''}
{assign var='page_url' value=$urls.current_url|default:''}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
      <div class="col-12 prettyblock-llm-links__inner">
        {if isset($block.settings.heading_text) && $block.settings.heading_text}
          <span class="prettyblock-llm-links__heading">{$block.settings.heading_text|escape:'htmlall':'UTF-8'}</span>
        {/if}

        {if isset($block.states) && $block.states}
          <div class="row prettyblock-llm-links__list">
            {foreach from=$block.states item=state key=key}
              {assign var='link_label' value=$state.label|default:''}
              {assign var='base_url' value=$state.base_url|default:''}
              {assign var='prompt_template' value=$state.prompt_template|default:''}
              {assign var='prompt' value=$prompt_template|replace:'{{title}}':$page_title|replace:'{{url}}':$page_url}
              {assign var='prompt_encoded' value=$prompt|escape:'url'}
              {assign var='final_url' value=$base_url|cat:$prompt_encoded}
              {assign var='icon_url' value=''}
              {if (is_array($state.icon) || is_object($state.icon)) && isset($state.icon.url)}
                {assign var='icon_url' value=$state.icon.url}
              {/if}
              {if $link_label && $base_url}
              <div class="col-6 col-md-4 col-lg-3">
                <a href="{$final_url}" class="prettyblock-llm-links__item{if isset($block.settings.link_hover_effect) && $block.settings.link_hover_effect} everblock-link-hover--block{/if}{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" title="{$link_label|escape:'htmlall':'UTF-8'}"{if isset($state.open_in_new_tab) && $state.open_in_new_tab} target="_blank" rel="noopener noreferrer"{/if}>
                  {if $icon_url}
                    <img src="{$icon_url|escape:'htmlall':'UTF-8'}" alt="{$state.icon_alt|default:$link_label|escape:'htmlall':'UTF-8'}" class="prettyblock-llm-links__icon" loading="lazy" />
                  {/if}
                  <span>{$link_label|escape:'htmlall':'UTF-8'}</span>
                </a>
              </div>
              {/if}
            {/foreach}
          </div>
        {/if}
      </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<!-- /Module Ever Block -->
