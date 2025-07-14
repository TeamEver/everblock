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
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
    {if $block.settings.default.force_full_width}
      <div class="row row-cols-1 row-cols-md-5 gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  {foreach from=$block.states item=state key=key}
    {if isset($state.category.id) && $state.category.id}
    {assign var='category_link' value=Context::getContext()->link->getCategoryLink($state.category.id)}
    {else}
    {assign var='category_link' value='#'}
    {/if}
    {* Bootstrap column class based on selected layout width *}
    {assign var="bootstrapClass" value="col-12"}
    {if $state.order == '50'}
      {assign var="bootstrapClass" value="col-12 col-md-6"}
    {elseif $state.order == '33,33'}
      {assign var="bootstrapClass" value="col-12 col-md-4"}
    {elseif $state.order == '25'}
      {assign var="bootstrapClass" value="col-12 col-md-3"}
    {elseif $state.order == '16,67'}
      {assign var="bootstrapClass" value="col-12 col-md-2"}
    {/if}

    <div id="block-{$block.id_prettyblocks}-{$key}" class="col {$state.css_class|escape:'htmlall'}">
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
        {if $state.obfuscate}
          {assign var="obflink" value=$category_link|base64_encode}
          <span class="obflink obfme d-block h-100 w-100" data-obflink obfme="{$obflink}">
        {else}
          <a href="{$category_link}" title="{$state.name}" class="d-block h-100 w-100 text-decoration-none text-white"{if $state.target_blank} target="_blank"{/if}>
        {/if}
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

        {if $state.name}
          <div class="position-absolute bottom-0 end-0 text-white category_state_name">
            <h2 class="m-0 text-white">{$state.name nofilter}</h2>
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