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
    <div class="featured-products clearfix mt-3 everblock {$block.settings.css_class|escape:'htmlall':'UTF-8'} {$block.settings.bootstrap_class|escape:'htmlall':'UTF-8'}" {if isset($block.settings.bg_color) && $block.settings.bg_color} style="background-color:{$block.settings.bg_color|escape:'htmlall':'UTF-8'};"{/if}>

        <div class="products row">
            {if isset($block.extra.presenteds) && $block.extra.presenteds}
            {include file="catalog/_partials/productlist.tpl" products=$block.extra.presenteds cssClass="row" productClass="col-xs-12 col-sm-6 col-lg-4 col-xl-3"}
            {/if}
        </div>
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
{if isset($block.extra.presenteds) && $block.extra.presenteds}
    <div class="container mt-4">
        <div id="productSlideshow" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
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
                            <img src="{$product.cover.bySize.home_default.url}" class="card-img-top" alt="{$product.name}">
                            <div class="card-body">
                                <h5 class="card-title">{$product.name}</h5>
                                <p class="card-text">Price: {$product.price}</p>
                            </div>
                        </div>
                    </div>

                    {if ($key + 1) % $numProductsPerSlide == 0 || $key == count($block.extra.presenteds) - 1}
                        </div>
                    </div>
                    {/if}
                {/foreach}
            </div>
            <a class="carousel-control-prev" href="#productSlideshow" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#productSlideshow" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>
{/if}

<!-- /Module Ever Block -->