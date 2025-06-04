{*
 * Linked products carousel template
 *}
{if isset($everPresentProducts) && $everPresentProducts}
  <div id="{$carousel_id}" class="carousel slide" data-ride="carousel" data-bs-ride="carousel">
    <div class="carousel-inner">
      {assign var="numProductsPerSlide" value=4}
      {foreach from=$everPresentProducts item=product key=key}
        {if $key % $numProductsPerSlide == 0}
          <div class="carousel-item{if $key == 0} active{/if}">
            <div class="row">
        {/if}
        <div class="col-md-3">
          {include file="catalog/_partials/miniatures/product.tpl" product=$product}
        </div>
        {if ($key + 1) % $numProductsPerSlide == 0 || $key == count($everPresentProducts) - 1}
            </div>
          </div>
        {/if}
      {/foreach}
    </div>
    <a class="carousel-control-prev" href="#{$carousel_id}" role="button" data-slide="prev" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only visually-hidden">Previous</span>
    </a>
    <a class="carousel-control-next" href="#{$carousel_id}" role="button" data-slide="next" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only visually-hidden">Next</span>
    </a>
  </div>
{/if}
