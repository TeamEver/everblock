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
{if isset($everPresentProducts) && $everPresentProducts}
  <section class="ever-featured-products featured-products clearfix mt-3{if isset($shortcodeClass)} {$shortcodeClass|escape:'htmlall':'UTF-8'}{/if}">
    <div class="products row {if isset($carousel) && $carousel}ever-slick-carousel{/if}">
      {hook h='displayBeforeProductMiniature' products=$everPresentProducts origin=$shortcodeClass|default:'' page_name=$page.page_name}
      {foreach $everPresentProducts item=product}
        {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses="col-xs-12 col-sm-6 col-lg-4 col-xl-3"}
      {/foreach}
      {hook h='displayAfterProductMiniature' products=$everPresentProducts origin=$shortcodeClass|default:'' page_name=$page.page_name}
    </div>
  </section>
{/if}
