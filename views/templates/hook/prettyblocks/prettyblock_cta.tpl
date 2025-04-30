<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}

  {if isset($block.states) && $block.states}
    {foreach from=$block.states item=state key=key}
      <div id="block-{$block.id_prettyblocks}-{$key}" class="col-12 text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="
        {if $state.padding_left}padding-left:{$state.padding_left}%;{/if}
        {if $state.padding_right}padding-right:{$state.padding_right}%;{/if}
        {if $state.padding_top}padding-top:{$state.padding_top}%;{/if}
        {if $state.padding_bottom}padding-bottom:{$state.padding_bottom}%;{/if}
        {if $state.margin_left}margin-left:{$state.margin_left}%;{/if}
        {if $state.margin_right}margin-right:{$state.margin_right}%;{/if}
        {if $state.margin_top}margin-top:{$state.margin_top}%;{/if}
        {if $state.margin_bottom}margin-bottom:{$state.margin_bottom}%;{/if}
        {if $state.background_color}background-color:{$state.background_color};{/if}
        {if $state.text_color}color:{$state.text_color};{/if}
      ">
        <div class="mb-3 d-flex flex-column align-items-center justify-content-center px-3">
        {if $state.name}
          <p class="mb-3 h2">{$state.name|escape:'htmlall'}</p>
        {/if}
        {if $state.content}
            {$state.content nofilter}
        {/if}
        {if $state.cta_link && $state.cta_text}
          <a href="{$state.cta_link|escape:'htmlall'}" class="btn btn-primary btn-cta">
            {$state.cta_text|escape:'htmlall'}
          </a>
        {/if}
        </div>
      </div>
    {/foreach}
  {/if}

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
