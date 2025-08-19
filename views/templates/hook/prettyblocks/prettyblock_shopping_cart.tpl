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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
{prettyblocks_zone zone_name="block-shoppingcart-{$block.id_prettyblocks}-before"}
<div id="_desktop_cart" class="ever-shopping-cart dropdown"  style="{if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
  <a rel="nofollow" aria-label="{l s='Shopping cart link containing nbProducts product(s)' sprintf=['nbProducts' => $cart.products_count] mod='everblock'}" href="{$urls.pages.cart}" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown">
    <i class="material-icons shopping-cart" aria-hidden="true">shopping_cart</i>
    <span class="hidden-sm-down">{l s='Cart' mod='everblock'}</span>
    <span class="cart-products-count">({$cart.products_count})</span>
  </a>
  <div class="dropdown-menu cart-dropdown-content">
    {foreach from=$cart.products item=product}
      <div class="cart-product">
        <div class="product-details row">
        <span class="product-image col-6" data-obf="{$product.url|base64_encode}">
          {assign var=cover value=(isset($product.default_image)) ? $product.default_image : $product.cover}
          {if $cover}
            <img src="{$cover.bySize.cart_default.url}" alt="{if $cover.legend}{$cover.legend}{else}{$product.name}{/if}" loading="lazy" decoding="async"
                 width="{$cover.bySize.cart_default.width}" height="{$cover.bySize.cart_default.height}">
          {elseif isset($urls.no_picture_image)}
            <img src="{$urls.no_picture_image.bySize.cart_default.url}" alt="" loading="lazy" decoding="async"
                 width="{$urls.no_picture_image.bySize.cart_default.width}" height="{$urls.no_picture_image.bySize.cart_default.height}">
          {/if}
        </span>
          <span class="product-name col-6">{$product.name} : {$product.price}</span>
        </div>
      </div>
    {/foreach}
    <div class="cart-total">
      <span class="total-label">{l s='Total' mod='everblock'}</span>
      <span class="total-value">{$cart.totals.total.value}</span>
    </div>
    <a href="{$urls.pages.cart}" class="btn btn-primary btn-block w-100 text-white">{l s='View Cart' mod='everblock'}</a>
  </div>
</div>
{prettyblocks_zone zone_name="block-shoppingcart-{$block.id_prettyblocks}-after"}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
