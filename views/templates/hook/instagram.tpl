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
{if $insta_imgs}
<div class="ever_instagram block_instagram insta-idshop-{$everinsta_shopid} d-none d-md-block">
  <div class="ever-slick-carousel row">
    {assign var='ik' value=0}
    {foreach $insta_imgs as $key => $img}
      {assign var='ik' value=$ik+1}
      {if $ik <= $everinsta_nbr}
        <div class="instagram_item_img col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
          <a href="{$img.permalink}" class="d-block obfme" target="_blank" title="{$img.caption|escape:'html':'UTF-8'}">
            <picture>
              <source srcset="{$img.thumbnail|escape:'quotes':'UTF-8'}" type="image/webp">
              <source srcset="{$img.thumbnail|replace:'.webp':'.jpg'|escape:'quotes':'UTF-8'}" type="image/jpeg">
              <img 
                class="img-fluid" 
                src="{$img.thumbnail|replace:'.webp':'.jpg'|escape:'quotes':'UTF-8'}"
                alt="{$img.caption|escape:'html':'UTF-8'}"
                loading="lazy"
              />
            </picture>
          </a>
        </div>
      {/if}
    {/foreach}
  </div>
</div>
{/if}
