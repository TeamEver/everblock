{*
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
    
  {if $block.settings.default.container}
    <div class="row">
  {/if}
    
  {foreach from=$block.states item=state key=key}
    
    {if $state.order == '100'}
      {assign var="bootstrapClass" value="col-12"}
    {elseif $state.order == '50'}
      {assign var="bootstrapClass" value="col-12 col-md-6"}
    {elseif $state.order == '33,33'}
      {assign var="bootstrapClass" value="col-12 col-md-4"}
    {elseif $state.order == '25'}
      {assign var="bootstrapClass" value="col-12 col-md-3"}
    {elseif $state.order == '16,67'}
      {assign var="bootstrapClass" value="col-12 col-md-2"}
    {else}
      {assign var="bootstrapClass" value="col-12"}
    {/if}

    <div class="{$bootstrapClass} {$state.css_class|escape:'htmlall'}">
        {if $state.link}
            {if $state.obfuscate}
                {assign var="obflink" value=$state.link|base64_encode}
                <span class="obflink obfme" data-obflink obfme="{$obflink}">
            {else}
                <a href="{$state.link}" title="{$state.name}"{if $state.target_blank} target="_blank"{/if}>
            {/if}
        {/if}
      <div class="position-relative overflow-hidden h-100 w-100" style="
        {if $state.padding_left}padding-left:{$state.padding_left};{/if}
        {if $state.padding_right}padding-right:{$state.padding_right};{/if}
        {if $state.padding_top}padding-top:{$state.padding_top};{/if}
        {if $state.padding_bottom}padding-bottom:{$state.padding_bottom};{/if}
        {if $state.margin_left}margin-left:{$state.margin_left};{/if}
        {if $state.margin_right}margin-right:{$state.margin_right};{/if}
        {if $state.margin_top}margin-top:{$state.margin_top};{/if}
        {if $state.margin_bottom}margin-bottom:{$state.margin_bottom};{/if}
      ">

          {if $state.image.url}
            <picture>
              <source srcset="{$state.image.url}" type="image/webp">
              <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
              <img src="{$state.image.url|replace:'.webp':'.jpg'}"
                   alt="{$state.name|escape:'htmlall'}"
                   title="{$state.name|escape:'htmlall'}"
                   class="img-fluid w-100 h-100 object-fit-cover"
                   width="{$state.image_width}"
                   height="{$state.image_height}"
                   loading="lazy">
            </picture>
          {/if}

          {if $state.content}
            <div class="position-absolute top-50 start-50 translate-middle text-center text-white px-3">
              <span class="m-0">{$state.content nofilter}</span>
            </div>
          {/if}

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
    
  {if $block.settings.default.container}
    </div>
  {/if}
</div>
