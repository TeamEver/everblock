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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_pages_guide_spacing_style'}

{assign var='desktop_columns' value=$block.settings.desktop_columns|default:3}
{assign var='tablet_columns' value=$block.settings.tablet_columns|default:2}
{assign var='mobile_columns' value=$block.settings.mobile_columns|default:1}

<div id="block-{$block.id_prettyblocks}" class="prettyblock-pages-guide{if $block.settings.default.force_full_width} container-fluid px-0 mx-0{elseif $block.settings.default.container} container{/if}{$prettyblock_visibility_class}" style="{$prettyblock_pages_guide_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  <div class="mt-2{if $block.settings.default.container} container{/if}">
    {if $block.settings.title}
      <p class="mb-3 h2">{$block.settings.title|escape:'htmlall':'UTF-8'}</p>
    {/if}
    {if $block.settings.description}
      <div class="mb-4">{$block.settings.description nofilter}</div>
    {/if}
    {if isset($block.states) && $block.states}
      <div class="row row-cols-{$mobile_columns|intval} row-cols-sm-{$tablet_columns|intval} row-cols-md-{$desktop_columns|intval} row-cols-lg-{$desktop_columns|intval}">
        {foreach from=$block.states item=state key=key}
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_pages_guide_state_spacing_style'}
          {assign var='page_id' value=$state.page.id|default:null}
          {assign var='page_link' value=''}
          {if $page_id}
            {assign var='page_link' value=Context::getContext()->link->getCMSLink($page_id)}
          {/if}
          {assign var='page_title' value=$state.title|default:$state.page.meta_title|default:''}
          <div class="col mb-4">
            <div id="block-{$block.id_prettyblocks}-{$key}" class="prettyblock-guide-card h-100 position-relative{if $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" style="{$prettyblock_pages_guide_state_spacing_style}">
              {if $page_title}
                <p class="h5">{$page_title|escape:'htmlall':'UTF-8'}</p>
              {/if}
              {if $state.summary}
                <div class="mb-3">{$state.summary nofilter}</div>
              {/if}
              {if $page_link}
                <a href="{$page_link|escape:'htmlall':'UTF-8'}" class="stretched-link"{if $state.target_blank} target="_blank" rel="noopener"{/if}></a>
              {/if}
              {if $page_link && $state.cta_text}
                <a href="{$page_link|escape:'htmlall':'UTF-8'}" class="btn btn-primary"{if $state.target_blank} target="_blank" rel="noopener"{/if}>
                  {$state.cta_text|escape:'htmlall':'UTF-8'}
                </a>
              {/if}
            </div>
          </div>
        {/foreach}
      </div>
    {/if}
  </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
