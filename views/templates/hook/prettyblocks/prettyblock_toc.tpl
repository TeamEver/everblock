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
{capture name='prettyblock_toc_wrapper_style'}
  {$prettyblock_spacing_style}
  {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}
    background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};
  {/if}
{/capture}
{assign var='prettyblock_toc_wrapper_style' value=$smarty.capture.prettyblock_toc_wrapper_style|trim}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if $prettyblock_toc_wrapper_style} style="{$prettyblock_toc_wrapper_style}"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  {if isset($block.states) && $block.states}
    <div class="col-12 col-lg-4">
      <aside class="pb-toc-summary">
        {if isset($block.settings.title) && $block.settings.title}
          <h2 class="pb-toc-title h4 mb-4">{$block.settings.title|escape:'htmlall'}</h2>
        {/if}
        <ul class="list-unstyled pb-toc-menu">
          {assign var='currentCategory' value=''}
          {assign var='currentSub' value=''}
          {assign var='catIndex' value=0}
          {assign var='subIndex' value=0}
          {foreach from=$block.states item=state}
            {if $state.category != $currentCategory}
              {if $currentSub != ''}</ul></li>{/if}
              {if $currentCategory != ''}</ul></li>{/if}
              {assign var='catIndex' value=$catIndex+1}
              <li class="pb-toc-category">
                {assign var='categoryDefaultOpen' value=($catIndex eq 1)}
                <button class="pb-toc-toggle pb-toc-toggle-category{if $categoryDefaultOpen} is-open{/if}" type="button" data-toggle="collapse" data-target="#pb-toc-cat-{$block.id_prettyblocks}-{$catIndex}" data-bs-toggle="collapse" data-bs-target="#pb-toc-cat-{$block.id_prettyblocks}-{$catIndex}" aria-expanded="{if $categoryDefaultOpen}true{else}false{/if}" aria-controls="pb-toc-cat-{$block.id_prettyblocks}-{$catIndex}">
                  <span class="pb-toc-toggle-label">{$state.category|escape:'htmlall'}</span>
                  <span class="pb-toc-toggle-icon" aria-hidden="true"></span>
                </button>
                <ul id="pb-toc-cat-{$block.id_prettyblocks}-{$catIndex}" class="list-unstyled collapse{if $categoryDefaultOpen} show{/if}">
              {assign var='currentCategory' value=$state.category}
              {assign var='currentSub' value=''}
            {/if}
            {if $state.subcategory != $currentSub}
              {if $currentSub != ''}</ul></li>{/if}
              {if $state.subcategory != ''}
                {assign var='subIndex' value=$subIndex+1}
                <li class="pb-toc-subcategory">
                  {assign var='subDefaultOpen' value=($categoryDefaultOpen && $subIndex eq 1)}
                  <button class="pb-toc-toggle pb-toc-toggle-sub{if $subDefaultOpen} is-open{/if}" type="button" data-toggle="collapse" data-target="#pb-toc-sub-{$block.id_prettyblocks}-{$catIndex}-{$subIndex}" data-bs-toggle="collapse" data-bs-target="#pb-toc-sub-{$block.id_prettyblocks}-{$catIndex}-{$subIndex}" aria-expanded="{if $subDefaultOpen}true{else}false{/if}" aria-controls="pb-toc-sub-{$block.id_prettyblocks}-{$catIndex}-{$subIndex}">
                    <span class="pb-toc-toggle-label">{$state.subcategory|escape:'htmlall'}</span>
                    <span class="pb-toc-toggle-icon" aria-hidden="true"></span>
                  </button>
                  <ul id="pb-toc-sub-{$block.id_prettyblocks}-{$catIndex}-{$subIndex}" class="list-unstyled collapse{if $subDefaultOpen} show{/if}">
              {assign var='currentSub' value=$state.subcategory}
            {else}
              {assign var='currentSub' value=''}
            {/if}
          {/if}
          <li><a class="pb-toc-link" href="#{$state.anchor|escape:'htmlall'}">{$state.title|escape:'htmlall'}</a></li>
        {/foreach}
        {if $currentSub != ''}</ul></li>{/if}
        {if $currentCategory != ''}</ul></li>{/if}
        </ul>
      </aside>
    </div>
    <div class="col-12 col-lg-8 pb-toc-content">
      {foreach from=$block.states item=state}
        {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_toc_state_style'}
        {capture name='prettyblock_toc_state_style_attr'}
          {$prettyblock_toc_state_style}
        {/capture}
        {assign var='prettyblock_toc_state_style_attr' value=$smarty.capture.prettyblock_toc_state_style_attr|trim}
        <div id="{$state.anchor|escape:'htmlall'}" class="pb-toc-section"{if $prettyblock_toc_state_style_attr} style="{$prettyblock_toc_state_style_attr}"{/if}>
          {$state.content nofilter}
        </div>
      {/foreach}
    </div>
  {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<!-- /Module Ever Block -->

