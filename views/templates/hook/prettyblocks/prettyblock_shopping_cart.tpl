{*
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{prettyblocks_zone zone_name="block-shoppingcart-{$block.id_prettyblocks}-before"}
<div id="_desktop_cart" class="ever-shopping-cart dropdown" {if isset($block.settings.bg_color) && $block.settings.bg_color} style="background-color:{$block.settings.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  <a rel="nofollow" aria-label="{l s='Shopping cart link containing %nbProducts% product(s)' sprintf=['%nbProducts%' => $cart.products_count] d='Shop.Theme.Checkout'}" href="{$urls.pages.cart}" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown">
    <i class="material-icons shopping-cart" aria-hidden="true">shopping_cart</i>
    <span class="hidden-sm-down">{l s='Cart' d='Shop.Theme.Checkout'}</span>
    <span class="cart-products-count">({$cart.products_count})</span>
  </a>
  <div class="dropdown-menu cart-dropdown-content">
    {foreach from=$cart.products item=product}
      <div class="cart-product">
        <div class="product-details row">
        <span class="product-image col-6" data-ob="{$product.url|base64_encode}">
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
      <span class="total-label">{l s='Total' d='Shop.Theme.Checkout'}</span>
      <span class="total-value">{$cart.totals.total.value}</span>
    </div>
    <a href="{$urls.pages.cart}" class="btn btn-primary btn-block text-white">{l s='View Cart' d='Shop.Theme.Checkout'}</a>
  </div>
</div>
{prettyblocks_zone zone_name="block-shoppingcart-{$block.id_prettyblocks}-after"}