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
        {if isset($block.extra.presenteds) && $block.extra.presenteds}
            <div class="mt-4" {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
                <div id="everProductSlideshow{$block.id_prettyblocks}" class="carousel slide {$block.settings.css_class|escape:'htmlall':'UTF-8'} {$block.settings.bootstrap_class|escape:'htmlall':'UTF-8'}" data-ride="carousel" {if isset($block.settings.slide_duration) && $block.settings.slide_duration} data-duration="{$block.settings.slide_duration}"{else} data-duration="2000"{/if}>
                    <div class="direction" aria-label="Boutons du carrousel">
                        <a class="left carousel-control" href="#everProductSlideshow{$block.id_prettyblocks}" role="button" data-slide="prev" aria-label="Précédent">
                            <span class="icon-prev" aria-hidden="true">
                                <i class="material-icons">&#xE5CB;</i>
                            </span>
                        </a>
                        <a class="right carousel-control" href="#everProductSlideshow{$block.id_prettyblocks}" role="button" data-slide="next" aria-label="Suivant">
                            <span class="icon-next" aria-hidden="true">
                                <i class="material-icons">&#xE5CC;</i>
                            </span>
                        </a>
                    </div>
                    
                    <div class="carousel-inner h-100">
                        {assign var="numProductsPerSlide" value=4}
                        {foreach from=$block.extra.presenteds item=product key=key}
                            {if $key % $numProductsPerSlide == 0}
                                {if $key == 0}
                                    <div class="carousel-item active">
                                {else}
                                    <div class="carousel-item">
                                {/if}
                                <div class="row">
                            {/if}

                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body">
                                        {include file="catalog/_partials/miniatures/product.tpl" product=$product}
                                    </div>
                                </div>
                            </div>
                            {if ($key + 1) % $numProductsPerSlide == 0 || $key == count($block.extra.presenteds) - 1}
                                </div>
                            </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            </div>
        {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->
