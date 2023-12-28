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
<!-- Module Ever Block -->
<div class="mt-2 {if $block.settings.default.container} container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    <div id="testimonial-{$block.id_prettyblocks}" class="everblock-testimonial" {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
        <div class="container">
            <div class="text-center row">
                <div class="col-md-12">
                    <span class="mb-2 h2">{$block.settings.name}</span>
                </div>
            </div>
            <div class="row">
                {foreach from=$block.states item=state key=key}
                <div class="col-md-4">
                    <div class="testimonial">
                        <div class="testimonial-author text-center">
                            <p class="author-name">{$state.name}</p>
                            <img src="{$state.image.url}" alt="{$state.name}" title="{$state.name}" class="rounded-circle img img-fluid lazyload" loading="lazy" width="60">
                        </div>
                        <div class="testimonial-content text-center">
                            <p>{$state.content nofilter}</p>
                        </div>
                    </div>
                </div>
                {/foreach}
            </div>
        </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->



