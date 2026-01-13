{if isset($images) && $images}
  <div id="{$carousel_id}" class="carousel slide" data-bs-ride="carousel" data-bs-wrap="true">
    
    {* ✅ Dots (indicateurs) *}
    <div class="carousel-indicators">
      {foreach from=$images item=image name=slides}
        <button 
          type="button" 
          data-bs-target="#{$carousel_id}" 
          data-bs-slide-to="{$smarty.foreach.slides.index}" 
          class="{if $smarty.foreach.slides.first}active{/if}" 
          {if $smarty.foreach.slides.first}aria-current="true"{/if}
          aria-label="Slide {$smarty.foreach.slides.iteration}">
        </button>
      {/foreach}
    </div>

    {* ✅ Slides *}
    <div class="carousel-inner">
      {foreach from=$images item=image name=carousel}
        <div class="carousel-item{if $smarty.foreach.carousel.first} active{/if}">
          <picture>
            <source srcset="{$image.src}" type="image/webp">
            <img 
              src="{$image.src}" 
              class="{$image.class} d-block w-100"
              width="{$image.width}" 
              height="{$image.height}" 
              alt="{$image.alt|escape:'html'}" 
              loading="lazy">
          </picture>
        </div>
      {/foreach}
    </div>
  </div>
{/if}
