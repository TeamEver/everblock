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
    {if isset($block.extra.states) && $block.extra.states}
    {foreach $block.extra.states as $state}
      {$state.content nofilter}
    {/foreach}
    {/if}
    {if isset($block.states) && $block.states}
    {foreach from=$block.states item=state key=key}
    <div id="block-{$block.id_prettyblocks}-{$key}" class="{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" style="
        {if $state.padding_left}padding-left:{$state.padding_left};{/if}
        {if $state.padding_right}padding-right:{$state.padding_right};{/if}
        {if $state.padding_top}padding-top:{$state.padding_top};{/if}
        {if $state.padding_bottom}padding-bottom:{$state.padding_bottom};{/if}
        {if $state.margin_left}margin-left:{$state.margin_left};{/if}
        {if $state.margin_right}margin-right:{$state.margin_right};{/if}
        {if $state.margin_top}margin-top:{$state.margin_top};{/if}
        {if $state.margin_bottom}margin-bottom:{$state.margin_bottom};{/if}
        {if isset($state.background_color) && $state.background_color}background-color:{$state.background_color};{/if}
        {if isset($state.text_color) && $state.text_color}color:{$state.text_color};{/if}
      ">
        {$state.content nofilter}
    </div>
    {/foreach}
    {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>