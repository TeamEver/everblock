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
<div id="everblock-storelocator" class="everblock-storelocator visible row">
  map
</div>
<div id="store-search-block" class="mt-3">
  <div class="input-group">
    <input type="text" class="form-control" name="store_search" id="store_search" placeholder="{l s='Search for a store' mod='everblock'}" autocomplete="on">
  </div>
</div>
<div id="everblock-storelist" class="everblock-storelocator visible row">
  {foreach from=$everblock_stores item=item name=store_loop}
    <div class="col-12 col-md-4 mt-3">
      <div class="card card-block store-block store-ever-id-{$item.city|escape:'htmlall':'UTF-8'}">
        <h2>{$item.name|escape:'htmlall':'UTF-8'}</h2>
        <p class="store-address">{$item.address1|escape:'htmlall':'UTF-8'}</p>
        <p class="store-city">{$item.postcode|escape:'htmlall':'UTF-8'} {$item.city|escape:'htmlall':'UTF-8'}</p>
        <p class="store-phone">{$item.phone|escape:'htmlall':'UTF-8'}</p>
        <img src="{$urls.img_store_url|escape:'htmlall':'UTF-8'}{$item.id|escape:'htmlall':'UTF-8'}.jpg" class="everblock-store-{$item.id|escape:'htmlall':'UTF-8'} img img-fluid w-100 lazyload" loading="lazy">
        <a href="{url entity='store' id=$item.id}" class="btn btn-primary text-center text-white">
          {l s='See more' mod='everblock'}
        </a>
      </div>
    </div>
    {if $smarty.foreach.store_loop.iteration % 3 == 0}
      <div class="clearfix"></div>
    {/if}
  {/foreach}
</div>
