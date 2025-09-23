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
<div class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
      {foreach from=$block.states item=state key=key}
            <div class="col-12{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if} style="
                {if $state.padding_left}padding-left:{$state.padding_left};{/if}
                {if $state.padding_right}padding-right:{$state.padding_right};{/if}
                {if $state.padding_top}padding-top:{$state.padding_top};{/if}
                {if $state.padding_bottom}padding-bottom:{$state.padding_bottom};{/if}
                {if $state.margin_left}margin-left:{$state.margin_left};{/if}
                {if $state.margin_right}margin-right:{$state.margin_right};{/if}
                {if $state.margin_top}margin-top:{$state.margin_top};{/if}
                {if $state.margin_bottom}margin-bottom:{$state.margin_bottom};{/if}
              ">
            {if isset($state.obfuscate) && $state.obfuscate}
            {assign var="obflink" value=$state.button_link|base64_encode}
            <span class="obflink class="btn btn-{$state.button_type} data-obflink="{$obflink}"{if isset($state.color) && $state.color} style="color:{$state.color|escape:'htmlall':'UTF-8'};"{/if}>
                {$state.button_content nofilter}
            </span>
            {else}
            <a href="{$state.button_link}" class="btn btn-{$state.button_type} {if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}"{if isset($state.color) && $state.color} style="color:{$state.color|escape:'htmlall':'UTF-8'};"{/if}>
                {$state.button_content nofilter}
            </a>
            {/if}
        </div>
    {/foreach}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>