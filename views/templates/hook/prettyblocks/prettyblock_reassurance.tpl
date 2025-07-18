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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0 mt-20px{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row g-10px">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}

  {if isset($block.states) && $block.states}
    {foreach from=$block.states item=state key=key}
      {* Génère l'URL de l'icône depuis le nom brut *}
      {assign var="icon_url" value=false}
      {if isset($state.icon.url) && $state.icon.url}
        {assign var="icon_url" value=$state.icon.url}
      {elseif isset($state.icon) && is_string($state.icon)}
        {assign var="icon_url" value=$smarty.const._MODULE_DIR_|cat:'everblock/views/img/svg/'|cat:$state.icon|cat:'.svg'}
      {/if}

      <div id="block-{$block.id_prettyblocks}-{$key}" class="text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="
        {if $state.padding_left}padding-left:{$state.padding_left};{/if}
        {if $state.padding_right}padding-right:{$state.padding_right};{/if}
        {if $state.padding_top}padding-top:{$state.padding_top};{/if}
        {if $state.padding_bottom}padding-bottom:{$state.padding_bottom};{/if}
        {if $state.margin_left}margin-left:{$state.margin_left};{/if}
        {if $state.margin_right}margin-right:{$state.margin_right};{/if}
        {if $state.margin_top}margin-top:{$state.margin_top};{/if}
        {if $state.margin_bottom}margin-bottom:{$state.margin_bottom};{/if}
        {if $state.background_color}background-color:{$state.background_color};{/if}
        {if $state.text_color}color:{$state.text_color};{/if}
      ">
        {if $icon_url}
            <div class="mb-2">
              <picture>
                <source srcset="{$icon_url|escape:'htmlall'}" type="image/webp">
                <source srcset="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" type="image/jpeg">
                <img src="{$icon_url|replace:'.webp':'.jpg'|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" loading="lazy" class="img-fluid" width="45" height="45">
              </picture>
            </div>
        {/if}

        {if $state.title}
          <p class="h6 fw-bold mb-1">{$state.title|escape:'htmlall'}</p>
        {/if}

        {if $state.text}
          <p class="small m-0">{$state.text nofilter}</p>
        {/if}
      </div>
    {/foreach}
  {/if}

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
