{if isset($block.states) && $block.states}
  <section class="prettyblocks-slider container-fluid px-0 mt-3 {if $block.settings.default.container}container{/if}" style="
    {if $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
    {if $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
  ">
    <div class="ever-wrapper overflow-auto px-2 px-md-0 pb-2">
      <div class="d-flex flex-nowrap gap-3 pe-1">
        {foreach from=$block.states item=state}
          <div class="flex-shrink-0 prettyblocks-slider-item" style="
            width: 90%; /* Mobile fallback */
            max-width: 90%;
          ">
            <div class="w-100" style="
              {if $state.padding_left}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'};{/if}
              {if $state.padding_right}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'};{/if}
              {if $state.padding_top}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'};{/if}
              {if $state.padding_bottom}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
              {if $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'};{/if}
              {if $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'};{/if}
              {if $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'};{/if}
              {if $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
              {if $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}
            ">
              {if $state.link}
                {if $state.obfuscate}
                  {assign var="obflink" value=$state.link|base64_encode}
                  <span class="obflink d-block" data-obflink="{$obflink}">
                {else}
                  <a href="{$state.link}" title="{$state.name}" {if $state.target_blank}target="_blank"{/if} class="d-block">
                {/if}
              {/if}
                  <img src="{$state.image.url}" title="{$state.name}" alt="{$state.name}" class="img img-fluid lazyload" loading="lazy">
              {if $state.link}
                {if $state.obfuscate}
                  </span>
                {else}
                  </a>
                {/if}
              {/if}
            </div>
          </div>
        {/foreach}
      </div>
    </div>
  </section>

  {* Ajout d’un style responsive pour forcer 4 par ligne en desktop *}
  <style>
    @media (min-width: 768px) {
      .prettyblocks-slider-item {
        width: 25% !important;
        max-width: 25% !important;
      }
    }
  </style>
{/if}
