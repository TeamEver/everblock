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
<div class="{if $block.settings.default.container}container{/if}"  style="{if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {foreach from=$block.states item=state key=key}
        <div id="block-{$block.id_prettyblocks}-{$key}" class="{if isset($state.css_class) && $state.css_class}{$state.css_class|escape:'htmlall':'UTF-8'}{/if}" style="{if $state.padding_left}padding-left:{$state.padding_left};{/if}{if $state.padding_right}padding-right:{$state.padding_right};{/if}{if $state.padding_top}padding-top:{$state.padding_top};{/if}{if $state.padding_bottom}padding-bottom:{$state.padding_bottom};{/if}{if $state.margin_left}margin-left:{$state.margin_left};{/if}{if $state.margin_right}margin-right:{$state.margin_right};{/if}{if $state.margin_top}margin-top:{$state.margin_top};{/if}{if $state.margin_bottom}margin-bottom:{$state.margin_bottom};{/if}">
            <div class="progress">
                <div class="progress-bar {$state.style}" role="progressbar" aria-valuenow="{$state.value}" aria-valuemin="0" aria-valuemax="100" style="width: {$state.value}%">
                    {$state.text}
                </div>
            </div>
        </div>
    {/foreach}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
