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
<!-- Module Ever Block -->
<div class="mt-2{if $block.settings.default.container} container{/if}"  style="
    {if $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    <div id="testimonialCarousel-{$block.id_prettyblocks}" class="carousel slide everblock-testimonial" data-ride="carousel" data-bs-ride="carousel">
        <div class="carousel-inner">
            {foreach from=$block.states item=state key=key}
                <div class="carousel-item {if $key == 0}active{/if}" style="
                    {if $state.padding_left}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'};{/if}
                    {if $state.padding_right}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'};{/if}
                    {if $state.padding_top}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'};{/if}
                    {if $state.padding_bottom}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
                    {if $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'};{/if}
                    {if $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'};{/if}
                    {if $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'};{/if}
                    {if $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
                    {if $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
                    <div class="testimonial text-center">
                        <p class="author-name h3 mb-2">{$state.name}</p>
                        <picture>
                          <source srcset="{$state.image.url}" type="image/webp">
                          <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
                          <img src="{$state.image.url|replace:'.webp':'.jpg'}" alt="{$state.name}" title="{$state.name}" class="rounded-circle img img-fluid lazyload" loading="lazy" width="80">
                        </picture>
                        <div class="testimonial-content mt-3">
                            <p>{$state.content nofilter}</p>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
        <a class="carousel-control-prev" href="#testimonialCarousel-{$block.id_prettyblocks}" role="button" data-slide="prev" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only visually-hidden">Previous</span>
        </a>
        <a class="carousel-control-next" href="#testimonialCarousel-{$block.id_prettyblocks}" role="button" data-slide="next" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only visually-hidden">Next</span>
        </a>
    </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->
