{*
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<!-- Module Ever Block -->
{if $cart.products_count > 0}
  <div id="_desktop_cart" class="ever-shopping-cart dropdown">
    <a rel="nofollow" aria-label="{l s='Shopping cart link containing %nbProducts% product(s)' sprintf=['%nbProducts%' => $cart.products_count] d='Shop.Theme.Checkout'}" href="{$urls.pages.cart}" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown">
      <i class="material-icons shopping-cart" aria-hidden="true">shopping_cart</i>
      <span class="hidden-sm-down">{l s='Cart' d='Shop.Theme.Checkout'}</span>
      <span class="cart-products-count">({$cart.products_count})</span>
    </a>
    <div class="dropdown-menu cart-dropdown-content">
      {foreach from=$cart.products item=product}
        <div class="cart-product">
          <div class="product-details">
            <span class="product-name">{$product.name}</span>
            <span class="product-price">{$product.price}</span>
          </div>
        </div>
      {/foreach}
      <div class="cart-total">
        <span class="total-label">{l s='Total' d='Shop.Theme.Checkout'}</span>
        <span class="total-value">{$cart.totals.total.value}</span>
      </div>
      <a href="{$urls.pages.cart}" class="btn btn-primary btn-block">{l s='View Cart' d='Shop.Theme.Checkout'}</a>
    </div>
  </div>
{/if}

<!-- /Module Ever Block -->