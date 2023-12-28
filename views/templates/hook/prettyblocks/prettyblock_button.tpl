{*
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="{if $block.settings.default.container}container{/if}" {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {foreach from=$block.states item=state key=key}
        <div id="block-{$block.id_prettyblocks}-{$key}" class="everblock col-12 d-flex justify-content-center text-center{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}">
            {if $state.obfuscate}
            {assign var="obflink" value=$state.button_link|base64_encode}
            <span class="obflink class="btn btn-{$state.button_type} data-obflink="{$obflink}"{if isset($state.color) && $state.color} style="color:{$state.color|escape:'htmlall':'UTF-8'};"{/if}>
                {$state.button_content nofilter}
            </span>
            {else}
            <a href="{$state.button_link}" class="btn btn-{$state.button_type}"{if isset($state.color) && $state.color} style="color:{$state.color|escape:'htmlall':'UTF-8'};"{/if}>
                {$state.button_content nofilter}
            </a>
            {/if}
        </div>
    {/foreach}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>