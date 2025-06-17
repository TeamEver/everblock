{*
 * Product highlight block
*}
{if $block.extra.product}
  {assign var="product" value=$block.extra.product}
  <div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}<div class="row align-items-center">{/if}
      <div class="col-12 col-md-6 mb-3 mb-md-0 text-center">
        <img src="{$product.cover.bySize.large_default.url|replace:'.webp':'.jpg'}" alt="{$product.name|escape:'htmlall'}" class="img-fluid w-100" loading="lazy">
      </div>
      <div class="col-12 col-md-6 text-center text-md-start">
        <div class="mb-2 text-danger"><i class="fa fa-heart me-2"></i>{l s='Coup de coeur du moment' mod='everblock'}</div>
        <h3 class="h4">{$product.name}</h3>
        {$block.settings.custom_text nofilter}
        <a href="{$product.url}" class="btn btn-primary mt-3">{l s='Voir le produit' mod='everblock'}</a>
      </div>
    {if $block.settings.default.container}</div>{/if}
  </div>
{/if}
