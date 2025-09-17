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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  <!-- Module Ever Block -->
  <div class="{if $block.settings.default.container}container{/if}" style="{if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
        <div class="everblock-countdown-wrapper">
            <div class="everblock-countdown d-flex justify-content-center {if $block.settings.css_class}{$block.settings.css_class|escape:'htmlall':'UTF-8'}{/if}" data-target="{$block.settings.target_date|escape:'htmlall':'UTF-8'}">
                <div class="mx-2 text-center">
                    <span class="everblock-countdown-value" data-type="days">00</span>
                    <span class="everblock-countdown-label">{l s='Days' mod='everblock'}</span>
                </div>
                <div class="mx-2 text-center">
                    <span class="everblock-countdown-value" data-type="hours">00</span>
                    <span class="everblock-countdown-label">{l s='Hours' mod='everblock'}</span>
                </div>
                <div class="mx-2 text-center">
                    <span class="everblock-countdown-value" data-type="minutes">00</span>
                    <span class="everblock-countdown-label">{l s='Minutes' mod='everblock'}</span>
                </div>
                <div class="mx-2 text-center">
                    <span class="everblock-countdown-value" data-type="seconds">00</span>
                    <span class="everblock-countdown-label">{l s='Seconds' mod='everblock'}</span>
                </div>
            </div>
            {if $block.settings.completion_message}
                <div class="everblock-countdown-finished-message d-none">
                    {$block.settings.completion_message nofilter}
                </div>
            {/if}
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
