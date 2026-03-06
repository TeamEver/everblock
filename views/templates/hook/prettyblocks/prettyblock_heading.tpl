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

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if}" style="{$prettyblock_spacing_style}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
        {assign var='heading_styles' value=''}
        {if isset($block.settings.text_color) && $block.settings.text_color}
          {assign var='heading_styles' value="{$heading_styles}color:{$block.settings.text_color|escape:'htmlall':'UTF-8'};"}
        {/if}
        {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}
          {assign var='heading_styles' value="{$heading_styles}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"}
        {/if}
        <div class="container">
          <div class="row justify-content-center text-center">
            <div class="col-auto">
              <{$block.settings.level|default:'h2'} id="{$block.id_prettyblocks}" class="everblock everblock-heading px-4 py-2 {$block.settings.css_class|escape:'htmlall':'UTF-8'}"{if $heading_styles|trim} style="{$heading_styles}"{/if}>{$block.settings.title|escape:'htmlall':'UTF-8'}</{$block.settings.level|default:'h2'}>
            </div>
          </div>
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
