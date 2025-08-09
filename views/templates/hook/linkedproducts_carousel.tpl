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
{if isset($everPresentProducts) && $everPresentProducts}
  <div id="{$carousel_id}" class="carousel slide" data-ride="carousel" data-bs-ride="carousel">
    <div class="carousel-inner">
      {assign var="numProductsPerSlide" value=4}
      {foreach from=$everPresentProducts item=product name=products}
        {if $product@index % $numProductsPerSlide == 0}
          <div class="carousel-item{if $product@first} active{/if}">
            <div class="row">
        {/if}
        <div class="col-md-3">
          {include file="catalog/_partials/miniatures/product.tpl" product=$product}
        </div>
        {if ($product@index + 1) % $numProductsPerSlide == 0 || $product@last}
            </div>
          </div>
        {/if}
      {/foreach}
    </div>
    <a class="carousel-control-prev" href="#{$carousel_id}" role="button" data-slide="prev" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only visually-hidden">Previous</span>
    </a>
    <a class="carousel-control-next" href="#{$carousel_id}" role="button" data-slide="next" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only visually-hidden">Next</span>
    </a>
  </div>
{/if}
