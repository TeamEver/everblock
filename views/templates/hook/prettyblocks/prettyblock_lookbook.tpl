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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}

  {if isset($block.states) && $block.states}
    <div class="{if $block.settings.default.container}container{/if}">
      {foreach from=$block.states item=state key=key}
        <div class="mb-4 {if $state.css_class}{$state.css_class}{/if}"
             style="{if $state.padding_left}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'};{/if}{if $state.padding_right}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'};{/if}{if $state.padding_top}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'};{/if}{if $state.padding_bottom}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'};{/if}{if $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'};{/if}{if $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'};{/if}{if $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if $state.background_color}background-color:{$state.background_color|escape:'htmlall':'UTF-8'};{/if}{if $state.text_color}color:{$state.text_color|escape:'htmlall':'UTF-8'};{/if}">
          {if $state.title}
            <h2 class="text-center mb-4">{$state.title|escape:'htmlall':'UTF-8'}</h2>
          {/if}
          <div class="lookbook-image position-relative mb-3">
            <img src="{$state.image.url}" alt="{$state.title|escape:'htmlall':'UTF-8'}" class="img-fluid w-100" loading="lazy" usemap="#ever-lookbook-map-{$block.id_prettyblocks}-{$key}">
            {if isset($block.extra.products[$key]) && $block.extra.products[$key]}
              <map name="ever-lookbook-map-{$block.id_prettyblocks}-{$key}">
                {foreach from=$block.extra.products[$key] item=product}
                  <area shape="circle" coords="0,0,0" class="ever-lookbook-hotspot"
                        data-product="{$product.id_product}" data-block="{$block.id_prettyblocks}"
                        data-x="{$product.lookbook_x|default:50}" data-y="{$product.lookbook_y|default:50}" data-r="15" href="#" />
                {/foreach}
              </map>
              {foreach from=$block.extra.products[$key] item=product}
                <span class="position-absolute translate-middle" style="top:{$product.lookbook_y|default:50}%;left:{$product.lookbook_x|default:50}%;pointer-events:none;">
                  <span class="d-inline-flex rounded-circle bg-white shadow" style="width:1.5rem;height:1.5rem;align-items:center;justify-content:center;">
                    <span class="d-block rounded-circle bg-primary" style="width:0.5rem;height:0.5rem;"></span>
                  </span>
                </span>
              {/foreach}
            {/if}
          </div>
          <div class="text-center mt-3">
            <div class="d-inline-flex align-items-center px-3 py-2 bg-light border rounded-pill">
              <span class="small text-muted">{l s='Cliquez sur un point pour voir le produit' mod='everblock'}</span>
            </div>
          </div>
        </div>
      {/foreach}
    </div>
  {/if}

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
