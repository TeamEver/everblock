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
{if isset($block.extra.presenteds) && $block.extra.presenteds}
    <div class="container mt-4">
        {if isset($block.settings.is_slider) && $block.settings.is_slider}
            <div id="productSlideshow-{$block.id_prettyblocks}" class="carousel slide" data-ride="carousel">
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
                                <img src="{$product.cover.bySize.home_default.url}" class="card-img-top lazyload" alt="{$product.name}" loading="lazy">
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
                <a class="carousel-control-prev" href="#productSlideshow-{$block.id_prettyblocks}" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#productSlideshow-{$block.id_prettyblocks}" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        {else}
        <div class="products row align-items-center grid">
            {foreach from=$block.extra.presenteds item=product key=key}
              {block name='product_miniature'}
                {include file='catalog/_partials/miniatures/product.tpl' product=$product}
              {/block}
            {/foreach}
        </div>
        {/if}
    </div>
{/if}