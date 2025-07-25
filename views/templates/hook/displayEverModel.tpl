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
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if isset($ever_model)}
<div class="ever-model-container container">
    <div class="ever-model-product card border border-secondary shadow everblock-highlight my-4">
        <div class="card-body row">
            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                <a href="{$ever_model.url}" title="{$ever_model.name|escape:'htmlall'}">
                    <picture>
                      <source srcset="{$ever_model.cover.bySize.medium_default.url}" type="image/webp">
                      <source srcset="{$ever_model.cover.bySize.medium_default.url|replace:'.webp':'.jpg'}" type="image/jpeg">
                      <img class="img-fluid mx-auto" src="{$ever_model.cover.bySize.medium_default.url|replace:'.webp':'.jpg'}" alt="{$ever_model.name|escape:'htmlall'}" loading="lazy" />
                    </picture>
                </a>
            </div>
            <div class="col-12 col-md-6">
                <p class="h3 product-title mb-2 text-left">
                    <a href="{$ever_model.url}" title="{$ever_model.name|escape:'htmlall'}">{$ever_model.name}</a>
                </p>
                <p class="product-price font-weight-bold mb-2">
                    {if isset($ever_model.price_amount)}
                        {$ever_model.price}
                    {else}
                        {l s='Price not available'}
                    {/if}
                </p>
                <div class="product-description mb-3">
                    {$ever_model.description_short nofilter}
                </div>

                <form method="post" action="{$link->getPageLink('cart', true)}" class="add-to-cart-form">
                    <input type="hidden" name="add" value="1" />
                    <input type="hidden" name="id_product" value="{$ever_model.id_product}" />
                    <input type="hidden" name="token" value="{$static_token}" />
                    <input type="hidden" name="qty" value="1" />

                    {if isset($ever_model.combinations) && $ever_model.combinations|@count > 0}
                        <div class="form-group">
                            <label for="ever_model_combination">{l s='Choose an option'}:</label>
                            <select class="form-control" name="id_product_attribute" id="ever_model_combination">
                                {foreach from=$ever_model.combinations item=comb}
                                    <option value="{$comb.id_product_attribute}" {if $comb.id_product_attribute == $ever_model.id_product_attribute}selected{/if}>
                                        {$comb.attributes}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    {else}
                        <input type="hidden" name="id_product_attribute" value="{$ever_model.id_product_attribute}" />
                    {/if}

                    <button type="submit" class="btn btn-primary">
                        <span class="fa fa-shopping-cart mr-1" aria-hidden="true"></span>
                        {l s='Add to cart'}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
{/if}
