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
{if isset($images) && $images}
  <div id="{$carousel_id}" class="carousel slide" data-ride="carousel" data-bs-ride="carousel">
    <div class="carousel-inner">
      {foreach from=$images item=image key=key}
        <div class="carousel-item{if $key == 0} active{/if}">
          <picture>
            <source srcset="{$image.src}" type="image/webp">
            <source srcset="{$image.src|replace:'.webp':'.jpg'}" type="image/jpeg">
            <img src="{$image.src|replace:'.webp':'.jpg'}" class="{$image.class} d-block w-100" width="{$image.width}" height="{$image.height}" alt="{$image.alt}" loading="lazy">
          </picture>
        </div>
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
