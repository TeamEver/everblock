{if isset($block.repeater) && $block.repeater}
<div class="everblock-product-comparison" id="everblock-{$block.id_block|escape:'htmlall':'UTF-8'}">
  {foreach from=$block.repeater item=product}
    <div class="comparison-item">
      {if $product.product_name}
        <h3 class="product-name">{$product.product_name|escape:'htmlall':'UTF-8'}</h3>
      {/if}
      {if $product.features}
        {assign var=featuresList value=explode("\n", $product.features)}
        <ul class="product-features">
          {foreach from=$featuresList item=feat}
            {if trim($feat) != ''}
              <li>{$feat|escape:'htmlall':'UTF-8'}</li>
            {/if}
          {/foreach}
        </ul>
      {/if}
      {if $product.price}
        <div class="product-price">{$product.price|escape:'htmlall':'UTF-8'}</div>
      {/if}
      {if $product.cta_label && $product.cta_url}
        <a href="{$product.cta_url|escape:'htmlall':'UTF-8'}" class="btn btn-primary">{$product.cta_label|escape:'htmlall':'UTF-8'}</a>
      {/if}
    </div>
  {/foreach}
</div>
{/if}

