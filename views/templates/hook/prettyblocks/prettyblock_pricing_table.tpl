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

{if isset($block.states) && $block.states}
<div id="block-{$block.id_prettyblocks}" class="everblock-pricing-table{if $block.settings.default.force_full_width} container-fluid px-0 mx-0{elseif $block.settings.default.container} container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.slider}
    <div class="ever-bootstrap-carousel"
         data-items="{$block.settings.plans_per_slide|escape:'htmlall'}"
         data-row-class="row g-4"
         data-controls="true"
         data-indicators="true"
         data-infinite="1"
         style="{$prettyblock_spacing_style}">
  {else}
    <div class="row g-4" style="{$prettyblock_spacing_style}">
  {/if}
    {foreach from=$block.states item=state}
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="pricing-plan{if $state.highlight} highlight{/if} h-100 p-4">
          {if $state.title}<h3 class="pricing-title h4">{$state.title|escape:'htmlall'}</h3>{/if}
          {if $state.price}<div class="pricing-price">{$state.price|escape:'htmlall'}</div>{/if}
          {if $state.features}
            <ul class="pricing-features">
              {if $state.features|is_array}
                {foreach from=$state.features item=feature}
                  {if $feature|is_array}
                    {assign var='feature_text' value=$feature.text|default:$feature.value|default:''}
                    {if $feature_text}<li>{$feature_text|escape:'htmlall'}</li>{/if}
                  {elseif $feature}<li>{$feature|escape:'htmlall'}</li>{/if}
                {/foreach}
              {else}
                {foreach from=$state.features|preg_split:"/\\r\\n|\\r|\\n/" item=feature}
                  {if $feature}<li>{$feature|escape:'htmlall'}</li>{/if}
                {/foreach}
              {/if}
            </ul>
          {/if}
          {if $state.cta_url && $state.cta_label}
            <a href="{$state.cta_url|escape:'htmlall'}" class="btn btn-primary" title="{$state.cta_label|escape:'htmlall'}">{$state.cta_label|escape:'htmlall'}</a>
          {/if}
        </div>
      </div>
    {/foreach}
  </div>
</div>
{/if}
