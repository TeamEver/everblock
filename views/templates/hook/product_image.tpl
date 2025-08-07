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
{if isset($productImage) && $productImage}
  <div class="everblock-product-image">
    <picture>
      <source srcset="{$productImage.image_url}" type="image/webp">
      <source srcset="{$productImage.image_url|replace:'.webp':'.jpg'}" type="image/jpeg">
      <img src="{$productImage.image_url|replace:'.webp':'.jpg'}" 
           alt="{$productImage.image_alt|escape:'htmlall':'UTF-8'}" 
           title="{$productImage.product_name|escape:'htmlall':'UTF-8'}"
           class="img-fluid lazyload everblock-product-image-img" 
           loading="lazy"
           data-product-id="{$productImage.id_product|escape:'htmlall':'UTF-8'}"
           data-image-number="{$productImage.image_number|escape:'htmlall':'UTF-8'}"
           data-total-images="{$productImage.total_images|escape:'htmlall':'UTF-8'}">
    </picture>
  </div>
{/if}
