<div class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  {foreach from=$block.states item=state}
    {if isset($state.category.id) && $state.category.id}
    {assign var='category_link' value=Context::getContext()->link->getCategoryLink($state.category.id)}
    {else}
    {assign var='category_link' value='#'}
    {/if}
    {* Bootstrap column class based on selected layout width *}
    {assign var="bootstrapClass" value="col-12"}
    {if $state.order == '50%'}
      {assign var="bootstrapClass" value="col-12 col-md-6"}
    {elseif $state.order == '33,33%'}
      {assign var="bootstrapClass" value="col-12 col-md-4"}
    {elseif $state.order == '25%'}
      {assign var="bootstrapClass" value="col-12 col-md-3"}
    {elseif $state.order == '16,67%'}
      {assign var="bootstrapClass" value="col-12 col-md-2"}
    {/if}

    <div class="{$bootstrapClass} {$state.css_class|escape:'htmlall'}">
      <div class="position-relative overflow-hidden h-100 w-100" style="
        {if $state.padding_left}padding-left:{$state.padding_left}%;{/if}
        {if $state.padding_right}padding-right:{$state.padding_right}%;{/if}
        {if $state.padding_top}padding-top:{$state.padding_top}%;{/if}
        {if $state.padding_bottom}padding-bottom:{$state.padding_bottom}%;{/if}
        {if $state.margin_left}margin-left:{$state.margin_left}%;{/if}
        {if $state.margin_right}margin-right:{$state.margin_right}%;{/if}
        {if $state.margin_top}margin-top:{$state.margin_top}%;{/if}
        {if $state.margin_bottom}margin-bottom:{$state.margin_bottom}%;{/if}
      ">
        {if $state.obfuscate}
          {assign var="obflink" value=$category_link|base64_encode}
          <span class="obflink obfme d-block h-100 w-100" data-obflink obfme="{$obflink}">
        {else}
          <a href="{$category_link}" title="{$state.name}" class="d-block h-100 w-100 text-decoration-none text-white"{if $state.target_blank} target="_blank"{/if}>
        {/if}
          {if $state.image.url}
            <img src="{$state.image.url}" 
                 alt="{$state.name|escape:'htmlall'}"
                 title="{$state.name|escape:'htmlall'}"
                 class="img-fluid w-100 h-100 object-fit-cover"
                 width="{$state.image_width}" 
                 height="{$state.image_height}" 
                 loading="lazy">
          {/if}

          {if $state.name}
            <div class="position-absolute top-50 start-50 translate-middle text-center text-white px-3">
              <span class="m-0">{$state.name nofilter}</span>
            </div>
          {/if}

        {if $state.obfuscate}
          </span>
        {else}
          </a>
        {/if}
      </div>
    </div>
  {/foreach}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>