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
    {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
    <div id="block-{$block.id_prettyblocks}-{$key}" class="{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" style="{$prettyblock_state_spacing_style}
        {if isset($state.background_image.url) && $state.background_image.url}background-image:url('{$state.background_image.url|escape:'htmlall':'UTF-8'}');background-size:cover;background-position:center;background-repeat:no-repeat;{/if}
        {if isset($state.background_color) && $state.background_color}background-color:{$state.background_color};{/if}
        {if isset($state.text_color) && $state.text_color}color:{$state.text_color};{/if}
      ">
        {$state.content nofilter}
        {if isset($block.extra.states) && $block.extra.states}
        {foreach $block.extra.states as $extra_state}
          {if isset($extra_state.content)}
            {$extra_state.content nofilter}
          {/if}
        {/foreach}
        {/if}
    </div>
    {/foreach}
    {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
