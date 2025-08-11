{if $products|@count}
  <div class="eb-low-stock cols-{$cols|default:4}">
    {foreach $products as $product}
      {include file='catalog/_partials/miniatures/product.tpl' product=$product}
    {/foreach}
  </div>
{else}
  <div class="eb-low-stock--empty">{l s='No low stock products.' mod='everblock'}</div>
{/if}

