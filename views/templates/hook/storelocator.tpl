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
<div id="everblock-storelist" class="everblock-storelocator visible row">
  {foreach from=$everblock_stores item=item name=store_loop}
    <div class="col-12 col-md-4 mt-3 d-flex">
      <div class="card store-block shadow-sm border-0 w-100 d-flex flex-column h-100">
        <div class="store-image-wrapper" style="height: 200px; overflow: hidden;">
          {if $item.latitude && $item.longitude}
          <a href="https://www.google.com/maps/search/?api=1&query={$item.latitude},{$item.longitude}"
               target="_blank" rel="noopener noreferrer" class="text-muted small text-decoration-none obfme">
          {/if}
          <img src="{$urls.img_store_url|escape:'htmlall':'UTF-8'}{$item.id|escape:'htmlall':'UTF-8'}.jpg"
               class="card-img-top img-fluid w-100 h-100 lazyload"
               style="object-fit: cover;"
               loading="lazy"
               alt="{$item.name|escape:'htmlall':'UTF-8'}">
          {if $item.latitude && $item.longitude}
          </a>
          {/if}
        </div>

        <div class="card-body d-flex flex-column flex-grow-1">
          <p class="store-name fw-bold mb-1">
            {$item.name|escape:'htmlall':'UTF-8'}
          </p>

          <p class="store-address mb-1">
            {$item.address1|escape:'htmlall':'UTF-8'}
            {if $item.address2}<br>{$item.address2|escape:'htmlall':'UTF-8'}{/if}
          </p>
          <p class="store-city mb-1">{$item.postcode|escape:'htmlall':'UTF-8'} {$item.city|escape:'htmlall':'UTF-8'}</p>

          {if $item.phone}
            <p class="store-phone mb-2">
              <a href="tel:{$item.phone|replace:' ':''|trim|escape:'htmlall':'UTF-8'}" class="text-decoration-none">
                <i class="material-icons align-middle me-1">phone</i>{$item.phone|escape:'htmlall':'UTF-8'}
              </a>
            </p>
          {/if}
          {if $item.latitude && $item.longitude}
          <p class="mb-2 gmaps-link">
            <a href="https://www.google.com/maps/search/?api=1&query={$item.latitude},{$item.longitude}"
               target="_blank" rel="noopener noreferrer" class="text-muted small text-decoration-none obfme">
              <i class="material-icons align-middle me-1">location_on</i>
              {l s='Get directions' mod='everblock'}
            </a>
          </p>
          {/if}

          <button class="btn btn-outline-primary w-100 mt-auto" type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapse-{$item.id}"
                  aria-expanded="false"
                  aria-controls="collapse-{$item.id}">
            {l s='See more' mod='everblock'}
          </button>

          <div class="collapse mt-3" id="collapse-{$item.id}">
            <div class="card card-body bg-light border rounded">
              <ul class="list-unstyled mb-0">
                {foreach from=$item.hours_display item=hour}
                  <li>
                    <strong>{$hour.day|escape:'htmlall':'UTF-8'} :</strong>
                    {$hour.hours|escape:'htmlall':'UTF-8'}
                  </li>
                {/foreach}
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    {if $smarty.foreach.store_loop.iteration % 3 == 0}
      <div class="clearfix"></div>
    {/if}
  {/foreach}
</div>
