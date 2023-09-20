{*
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
        <div class="col d-flex justify-content-center text-center">
            {if $block.settings.obfuscate}
            {assign var="obflink" value=$block.settings.button_link|base64_encode}
            <span class="obflink class="btn btn-{$block.settings.button_type} btn-{$block.settings.button_size} data-obflink="{$obflink}">
                {$block.settings.button_content nofilter}
            </span>
            {else}
            <a href="{$block.settings.button_link}" class="btn btn-{$block.settings.button_type} btn-{$block.settings.button_size}">
                {$block.settings.button_content nofilter}
            </a>
            {/if}
        </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->