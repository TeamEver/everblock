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
{* products_by_tag.tpl - renders products filtered by PrestaShop tags *}
{if $products}
  <section class="ever-products-by-tag clearfix">
    <div class="products row{if isset($cols)} cols-{$cols|intval}{/if}">
      {foreach from=$products item=product}
        {include file='catalog/_partials/miniatures/product.tpl' product=$product}
      {/foreach}
    </div>
  </section>
{else}
  <p class="alert alert-info">{l s='No products found.' mod='everblock'}</p>
{/if}
