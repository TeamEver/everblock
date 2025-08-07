{*
 * 2019-2025 Team Ever
 * LICENSE INFO
*}
<div class="{if $block.settings.default.container}container{/if}" style="
    {if isset($block.settings.padding_left)}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.padding_right)}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.padding_top)}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.padding_bottom)}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.margin_left)}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.margin_right)}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.margin_top)}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.margin_bottom)}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}
    {if isset($block.settings.default.bg_color)}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    
    <div class="row">
    {foreach from=$block.states item=state key=key}
        <div class="{$state.css_class|escape:'htmlall':'UTF-8'} col-12 mb-4" style="
            {if isset($state.default.bg_color)}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
            
            {if $state.link}
                {if $state.obfuscate}
                    {assign var="obflink" value=$state.link|base64_encode}
                    <span class="obflink" data-obflink="{$obflink}">
                {else}
                    <a href="{$state.link}" title="{$state.name}"{if $state.target_blank} target="_blank"{/if}>
                {/if}
            {/if}
            
            {* Row responsive, image à gauche ou à droite selon ordre *}
            <div class="row align-items-center {if $state.order == 'First text, then image'}flex-md-row-reverse{/if}">
                
                {* IMAGE *}
                <div class="col-md-6 mb-3 mb-md-0 text-center">
                    {if $state.image.url}
                        {if $state.parallax}
                            <div class="rounded mx-auto d-block" role="img" aria-label="{$state.name|escape:'htmlall'}" title="{$state.name|escape:'htmlall'}"
                                 style="background-image:url('{$state.image.url|replace:'.webp':'.jpg'}');
                                        background-size:cover;background-position:center;background-attachment:fixed;width:100%;
                                        {if $state.image.width > 0}max-width:{$state.image.width}px;{/if}
                                        {if $state.image.height > 0}height:{$state.image.height}px;{/if}">
                            </div>
                        {else}
                            <picture>
                                <source srcset="{$state.image.url}" type="image/webp">
                                <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
                                <img src="{$state.image.url|replace:'.webp':'.jpg'}" alt="{$state.name}" title="{$state.name}"
                                     class="img img-fluid rounded mx-auto d-block lazyload"
                                     {if $state.image.width > 0} width="{$state.image.width}"{/if}
                                     {if $state.image.height > 0} height="{$state.image.height}"{/if}
                                     loading="lazy">
                            </picture>
                        {/if}
                    {/if}
                </div>

                {* CONTENU *}
                <div class="col-md-6">
                    <div class="state-content px-3">
                        {$state.content nofilter}
                    </div>
                </div>
            </div>

            {if $state.link}
                {if $state.obfuscate}
                    </span>
                {else}
                    </a>
                {/if}
            {/if}

        </div>
    {/foreach}
    </div>
</div>
