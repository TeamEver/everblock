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
    <div class="everblock-slider-container">
        <ul class="instagram_list_img home-insta-idshop-{$everinsta_shopid}">
            {assign var='ik' value=0}
            {foreach $insta_imgs as $key => $img}
                {assign var='ik' value=$ik+1}
                {if $ik <= $everinsta_nbr}
                    <li class="instagram_item_img col-xs-4 col-sm-4 col-md-3 col-lg-2 col-insta-{$img.id} li-insta-idshop-{$everinsta_shopid}">
                        <span class="ever_instagram obfme"
                           data-obf="{$img.permalink}"
                           data-target="_blank" 
                           data-media-type="{if $img.is_video}video{else}image{/if}"
                           title="{if $img.is_video}{l s='Click to view full video' mod='everblock'}{else}{l s='Click to view full image' mod='everblock'}{/if}"
                        >
                            <picture>
                              <source srcset="{$img.thumbnail|escape:'quotes':'UTF-8'}" type="image/webp">
                              <source srcset="{$img.thumbnail|replace:'.webp':'.jpg'|escape:'quotes':'UTF-8'}" type="image/jpeg">
                              <img {if $img.caption}alt="{$img.caption|escape:'html':'UTF-8'}"{/if} src="{$img.thumbnail|replace:'.webp':'.jpg'|escape:'quotes':'UTF-8'}" loading="lazy" />
                            </picture>
                        </span>
                        {if $img.is_video}
                            <video controls style="display: none; padding: 0; width: auto;" id="ever_insta_video_{$key+1|escape:'quotes':'UTF-8'}">
                                <source src="{$img.standard_resolution|escape:'quotes':'UTF-8'}" type="video/mp4">
                                Your browser doesn't support HTML5 video tag.
                            </video>
                        {/if}
                    </li>
                {/if}
            {/foreach}
        </ul>
    </div>
</div>
<div class="modal fade" id="everImageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="" id="everModalImage" class="img-fluid" alt="Responsive image">
            </div>
        </div>
    </div>
</div>
{/if}