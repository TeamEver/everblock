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

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
    {if $block.settings.default.force_full_width}
      <div class="row row-cols-1 row-cols-md-{$block.settings.desktop_columns|default:2} gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row row-cols-1 row-cols-md-{$block.settings.desktop_columns|default:2}">
  {/if}
  {foreach from=$block.states item=state key=key}
    {if isset($state.category.id) && $state.category.id}
    {assign var='category_link' value=Context::getContext()->link->getCategoryLink($state.category.id)}
    {else}
    {assign var='category_link' value='#'}
    {/if}
    {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_category_highlight_state_spacing_style'}
    <div id="block-{$block.id_prettyblocks}-{$key}" class="col {$state.css_class|escape:'htmlall'}">
      <div class="position-relative overflow-hidden h-100 w-100" style="{$prettyblock_category_highlight_state_spacing_style}">
        {if $state.obfuscate}
          {assign var="obflink" value=$category_link|base64_encode}
          <span class="obflink obfme d-block h-100 w-100" data-obflink obfme="{$obflink}">
        {else}
          <a href="{$category_link}" title="{$state.name}" class="d-block h-100 w-100 text-decoration-none text-white"{if $state.target_blank} target="_blank"{/if}>
        {/if}
          {if $state.image.url}
            <picture>
              <source srcset="{$state.image.url}" type="image/webp">
              <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
              <img src="{$state.image.url|replace:'.webp':'.jpg'}"
                   alt="{$state.name|escape:'htmlall'}"
                   title="{$state.name|escape:'htmlall'}"
                   class="img-fluid w-100 h-100 object-fit-cover"
                   width="{$state.image_width}"
                   height="{$state.image_height}"
                   loading="lazy">
            </picture>
          {/if}

        {if $state.name}
          {assign var='title_desktop_style' value=''}
          {if isset($state.title_font_size_desktop) && $state.title_font_size_desktop}
            {assign var='title_desktop_style' value="font-size:{$state.title_font_size_desktop|escape:'htmlall'};"}
          {/if}
          <div class="prettyblock-cover-overlay category_state_name position-desktop-{$state.title_position_desktop|default:'center'|lower|replace:' ':'-'|escape:'htmlall'} position-mobile-{$state.title_position_mobile|default:'center'|lower|replace:' ':'-'|escape:'htmlall'}">
            <h2 class="m-0 text-white"{if $title_desktop_style} style="{$title_desktop_style}"{/if}>{$state.name nofilter}</h2>
          </div>
        {/if}

        {if $state.obfuscate}
          </span>
        {else}
          </a>
        {/if}
      </div>
    </div>
    {capture assign='mobile_position_styles'}
      {if isset($state.margin_left_mobile) && $state.margin_left_mobile}margin-left:{$state.margin_left_mobile|escape:'htmlall'};{/if}
      {if isset($state.margin_right_mobile) && $state.margin_right_mobile}margin-right:{$state.margin_right_mobile|escape:'htmlall'};{/if}
      {if isset($state.margin_top_mobile) && $state.margin_top_mobile}margin-top:{$state.margin_top_mobile|escape:'htmlall'};{/if}
      {if isset($state.margin_bottom_mobile) && $state.margin_bottom_mobile}margin-bottom:{$state.margin_bottom_mobile|escape:'htmlall'};{/if}
    {/capture}
    {capture assign='mobile_title_styles'}
      {if isset($state.title_font_size_mobile) && $state.title_font_size_mobile}font-size:{$state.title_font_size_mobile|escape:'htmlall'};{/if}
    {/capture}
    {if $mobile_position_styles|trim || $mobile_title_styles|trim}
      <style>
        @media (max-width: 767px) {
          {if $mobile_position_styles|trim}
          #block-{$block.id_prettyblocks}-{$key} .position-relative {
            {$mobile_position_styles|trim}
          }
          {/if}
          {if $mobile_title_styles|trim}
          #block-{$block.id_prettyblocks}-{$key} .category_state_name h2 {
            {$mobile_title_styles|trim}
          }
          {/if}
        }
      </style>
    {/if}
  {/foreach}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
