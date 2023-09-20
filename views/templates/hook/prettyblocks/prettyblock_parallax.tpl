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
    <div id="parallax-{$block.id_prettyblocks}" class="everblock-parallax">
        {if isset($block.states) && $block.states}
        {foreach from=$block.states item=state key=key}
        <div class="parallax-container">
            {assign var="imageUrl" value=$state.image.url|replace:'\\':'/'|escape:'htmlall':'UTF-8'}
            <div class="parallax-bg" style="background-image: url('{$imageUrl|escape:'htmlall':'UTF-8'}');"></div>
            <div class="parallax-content text-center">
                <span class="h2" {if isset($state.title_color) && $state.title_color} style="color:{$state.title_color};"{/if}>
                {$state.name}
                </span>
                <p>{$state.content nofilter}</p>
            </div>
        </div>
        {/foreach}
        {/if}
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->
