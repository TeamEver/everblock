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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_latest_guides_spacing_style'}

{assign var='limit' value=$block.settings.limit|default:3}
{assign var='desktop_columns' value=$block.settings.desktop_columns|default:3}
{assign var='tablet_columns' value=$block.settings.tablet_columns|default:2}
{assign var='mobile_columns' value=$block.settings.mobile_columns|default:1}
{assign var='customer_groups' value=EverblockPage::getCustomerGroups(Context::getContext())}
{assign var='latest_guides' value=EverblockPage::getPages((int) Context::getContext()->language->id, (int) Context::getContext()->shop->id, true, $customer_groups, 1, (int) $limit)}

<div id="block-{$block.id_prettyblocks}" class="prettyblock-latest-guides{if $block.settings.default.force_full_width} container-fluid px-0 mx-0{elseif $block.settings.default.container} container{/if}{$prettyblock_visibility_class}" style="{$prettyblock_latest_guides_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
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
    {if $latest_guides}
      <div class="row row-cols-{$mobile_columns|intval} row-cols-sm-{$tablet_columns|intval} row-cols-md-{$desktop_columns|intval} row-cols-lg-{$desktop_columns|intval}">
        {foreach from=$latest_guides item=guide}
          <div class="col mb-4">
            <div class="prettyblock-guide-card h-100 position-relative">
              {assign var='cover_image_data' value=$guide->getCoverImageData(Context::getContext())}
              {if $cover_image_data.url}
                <div class="prettyblock-guide-image mb-3 position-relative overflow-hidden rounded" style="aspect-ratio: {$cover_image_data.width|intval}/{$cover_image_data.height|intval};">
                  <img src="{$cover_image_data.url|escape:'htmlall':'UTF-8'}"
                       alt="{$cover_image_data.alt|default:$guide->title|default:$guide->name|escape:'htmlall':'UTF-8'}"
                       class="w-100 h-100"
                       style="object-fit: cover;"
                       loading="lazy"
                       width="{$cover_image_data.width|intval}"
                       height="{$cover_image_data.height|intval}" />
                </div>
              {/if}
              <p class="h5 mb-2">{$guide->title|default:$guide->name|default:''|escape:'htmlall':'UTF-8'}</p>
              {if $guide->short_description || $guide->meta_description}
                <div class="mb-3">{($guide->short_description|default:$guide->meta_description)|strip_tags|truncate:180:'...':true}</div>
              {/if}
              {assign var='guide_link' value=Context::getContext()->link->getModuleLink('everblock', 'page', ['id_everblock_page' => $guide->id, 'rewrite' => $guide->link_rewrite[Context::getContext()->language->id]|default:''])}
              {if $guide_link}
                <a href="{$guide_link|escape:'htmlall':'UTF-8'}" class="stretched-link"></a>
              {/if}
              <a href="{$guide_link|escape:'htmlall':'UTF-8'}" class="btn btn-primary">
                {l s='Read guide' mod='everblock' d='Modules.Everblock.Front'}
              </a>
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
