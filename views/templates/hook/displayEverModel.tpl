{if isset($ever_model)}
<div class="ever-model-container container">
    <div class="ever-model-product card border border-secondary shadow everblock-highlight my-4">
        <div class="card-body row">
            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                <a href="{$ever_model.url}" title="{$ever_model.name|escape:'htmlall'}">
                    <img class="img-fluid mx-auto" src="{$ever_model.cover.bySize.medium_default.url}" alt="{$ever_model.name|escape:'htmlall'}" />
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
