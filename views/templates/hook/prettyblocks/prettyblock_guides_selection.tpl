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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_guides_selection_spacing_style'}

{assign var='desktop_columns' value=$block.settings.desktop_columns|default:3}
{assign var='tablet_columns' value=$block.settings.tablet_columns|default:2}
{assign var='mobile_columns' value=$block.settings.mobile_columns|default:1}

<div id="block-{$block.id_prettyblocks}" class="prettyblock-guides-selection{if $block.settings.default.force_full_width} container-fluid px-0 mx-0{elseif $block.settings.default.container} container{/if}{$prettyblock_visibility_class}" style="{$prettyblock_guides_selection_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  <div class="{if $block.settings.default.container} container{/if}">
    {if $block.settings.title}
      <p class="mb-3 font-weight-bold">{$block.settings.title|escape:'htmlall':'UTF-8'}</p>
    {/if}
    {if $block.settings.description}
      <div class="mb-4">{$block.settings.description nofilter}</div>
    {/if}
    {if isset($block.states) && $block.states}
      <div class="row row-cols-{$mobile_columns|intval} row-cols-sm-{$tablet_columns|intval} row-cols-md-{$desktop_columns|intval} row-cols-lg-{$desktop_columns|intval}">
        {foreach from=$block.states item=state key=key}
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_guides_selection_state_spacing_style'}
          {assign var='guide_id' value=$state.guide.id|default:$state.guide|default:null}
          {assign var='guide_object' value=null}
          {assign var='cover_image_data' value=[]}
          {if $guide_id}
            {assign var='guide_object' value=EverblockPage::getById((int) $guide_id, (int) Context::getContext()->language->id, (int) Context::getContext()->shop->id)}
          {/if}
          {assign var='guide_link' value=''}
          {if $guide_object instanceof EverblockPage}
            {assign var='guide_link' value=Context::getContext()->link->getModuleLink('everblock', 'page', ['id_everblock_page' => $guide_object->id, 'rewrite' => $guide_object->link_rewrite[Context::getContext()->language->id] ?? ''])}
            {assign var='cover_image_data' value=$guide_object->getCoverImageData(Context::getContext())}
          {/if}
          {assign var='guide_title' value=$state.title|default:''}
          {if !$guide_title && $guide_object instanceof EverblockPage}
            {assign var='guide_title' value=$guide_object->title|default:$guide_object->name|default:''}
          {/if}
          {assign var='guide_summary' value=$state.summary|default:''}
          {if !$guide_summary && $guide_object instanceof EverblockPage}
            {assign var='guide_summary' value=$guide_object->short_description|default:$guide_object->meta_description|default:''}
          {/if}
          <div class="col mb-4">
            <div id="block-{$block.id_prettyblocks}-{$key}" class="prettyblock-guide-card h-100{if $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" style="{$prettyblock_guides_selection_state_spacing_style}">
              {if isset($cover_image_data.url) && $cover_image_data.url}
                <div class="prettyblock-guide-image mb-3 position-relative overflow-hidden rounded" style="aspect-ratio: {$cover_image_data.width|intval}/{$cover_image_data.height|intval};">
                  <img src="{$cover_image_data.url|escape:'htmlall':'UTF-8'}"
                       alt="{$cover_image_data.alt|default:$guide_title|escape:'htmlall':'UTF-8'}"
                       class="w-100 h-100"
                       style="object-fit: cover;"
                       loading="lazy"
                       width="{$cover_image_data.width|intval}"
                       height="{$cover_image_data.height|intval}" />
                </div>
              {/if}
              {if $guide_title}
                <p class="h5 mb-2">{$guide_title|escape:'htmlall':'UTF-8'}</p>
              {/if}
              {if $guide_summary}
                <div class="mb-3">{$guide_summary nofilter}</div>
              {/if}
              {if $guide_link && $state.cta_text}
                <a href="{$guide_link|escape:'htmlall':'UTF-8'}" class="btn btn-primary"{if $state.target_blank} target="_blank" rel="noopener"{/if}>
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
