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
{if isset($storeInfos) && $storeInfos}
  <section class="featured-store clearfix mt-3 row justify-content-center">
    {foreach $storeInfos item=store}
      <div class="col-xs-12 col-md-4 store-{$store.id_store}">
        <div class="card">
          <img src="{$store.image_link}" alt="{$store.name}" class="card-img-top lazyload" loading="lazy">
          <div class="card-body text-center">
            <span class="h5" class="card-title text-center">{$store.name}</span>
            {if $store.address1 || $store.address2}
            <address class="card-text">
              {if $store.address1}{$store.address1}<br>{/if}
              {if $store.address2}{$store.address2}<br>{/if}
              {$store.postcode} {$store.city}
            </address>
            {/if}
          </div>
        </div>
      </div>
    {/foreach}
  </section>
{/if}

