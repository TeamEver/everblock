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

  {if isset($block.states) && $block.states}
    {foreach from=$block.states item=state key=key}
      <div id="block-{$block.id_prettyblocks}-{$key}" class="block-cta col-12{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if} {if isset($state.background_image.url) && $state.background_image.url} cta_background{/if}" style="
        {if $state.padding_left}padding-left:{$state.padding_left};{/if}
        {if $state.padding_right}padding-right:{$state.padding_right};{/if}
        {if $state.padding_top}padding-top:{$state.padding_top};{/if}
        {if $state.padding_bottom}padding-bottom:{$state.padding_bottom};{/if}
        {if $state.margin_left}margin-left:{$state.margin_left};{/if}
        {if $state.margin_right}margin-right:{$state.margin_right};{/if}
        {if $state.margin_top}margin-top:{$state.margin_top};{/if}
        {if $state.margin_bottom}margin-bottom:{$state.margin_bottom};{/if}
        {if $state.background_color}background-color:{$state.background_color};{/if}
        {if $state.text_color}color:{$state.text_color};{/if}
        {if isset($state.background_image.url) && $state.background_image.url}
          background-image: url('{$state.background_image.url|escape:'htmlall'}');
          background-size: cover;
          background-position: center;
          {if $state.parallax}background-attachment: fixed;{/if}
        {/if}
      ">
        <div class="d-flex flex-column align-items-center justify-content-center h-100 w-100 px-3" style="z-index: 1;">
          {if $state.name}
            <p class="mb-3 h2" style="{if $state.text_color}color:{$state.text_color}!important;{/if}">{$state.name nofilter}</p>
          {/if}
          {if $state.content}
            {$state.content nofilter}
          {/if}
          {if $state.cta_link && $state.cta_text}
            <a href="{$state.cta_link|escape:'htmlall'}" class="btn btn-primary btn-cta mt-3">
              {$state.cta_text nofilter}
            </a>
          {/if}
        </div>
      </div>
    {/foreach}
  {/if}

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
