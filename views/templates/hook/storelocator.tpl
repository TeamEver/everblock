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
<div id="store-search-block" class="mb-3 text-center">
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-center">
    <label for="store_search" class="me-md-2 mb-2 mb-md-0 fw-bold">{l s='Find a store' mod='everblock'}</label>
    <input type="text" class="form-control mb-2 mb-md-0 me-md-2 w-100" name="store_search" id="store_search" placeholder="{l s='Search for a store' mod='everblock'}" autocomplete="on">
    <button type="button" id="store_search_btn" class="btn btn-primary">{l s='Search' mod='everblock'}</button>
  </div>
</div>
{hook h='displayBeforeStoreLocator'}
<div id="everblock-storelocator-wrapper" class="mb-3">
  <ul class="nav nav-tabs d-md-none" id="storeLocatorTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="tab-map" data-bs-toggle="tab" data-bs-target="#pane-map" type="button" role="tab" aria-controls="pane-map" aria-selected="true">{l s='Map' mod='everblock'}</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-list" data-bs-toggle="tab" data-bs-target="#pane-list" type="button" role="tab" aria-controls="pane-list" aria-selected="false">{l s='Stores' mod='everblock'}</button>
    </li>
  </ul>
  <div class="tab-content row">
    <div class="tab-pane fade show active col-12 col-md-8 d-md-block order-md-2" id="pane-map" role="tabpanel" aria-labelledby="tab-map">
      <div id="everblock-storelocator" class="everblock-storelocator w-100 h-100"></div>
    </div>
    <div class="tab-pane fade col-12 col-md-4 d-md-block order-md-1" id="pane-list" role="tabpanel" aria-labelledby="tab-list">
      <div id="everblock-storelist" class="row" style="max-height:500px; overflow-y:auto;">
        {foreach from=$everblock_stores item=item name=store_loop}
        {assign var="hasCoordinates" value=(isset($item.latitude) && isset($item.longitude) && $item.latitude != '' && $item.longitude != '')}
        <div class="col-12 everblock-store-item" data-lat="{$item.latitude}" data-lng="{$item.longitude}">
          <div class="d-flex align-items-start">
            <div class="flex-shrink-0 me-3">
              <img src="{$urls.img_store_url|escape:'htmlall':'UTF-8'}{$item.id|escape:'htmlall':'UTF-8'}.jpg"
                   class="rounded" alt="{$item.name|escape:'htmlall':'UTF-8'}"
                   style="width: 80px; height: 80px; object-fit: cover;">
            </div>
            <div class="flex-grow-1">
              <h6 class="fw-bold mb-1 mt-0">
                {if $item.cms_link}
                  <a href="{$item.cms_link|escape:'htmlall':'UTF-8'}" class="text-dark text-decoration-none">
                    {$item.name|escape:'htmlall':'UTF-8'}
                  </a>
                {else}
                  {$item.name|escape:'htmlall':'UTF-8'}
                {/if}
              </h6>
              <p class="mb-0 small">
                {$item.address1|escape:'htmlall':'UTF-8'}<br>
                {if $item.address2}{$item.address2|escape:'htmlall':'UTF-8'}<br>{/if}
                {$item.postcode} {$item.city}
              </p>
              {if $item.phone}
                <p class="mb-1 small">
                  <span>{l s='Tel:' mod='everblock'}</span>
                  <a href="tel:{$item.phone|replace:' ':''|escape:'htmlall':'UTF-8'}" class="text-dark text-decoration-none">+{$item.phone|escape:'htmlall':'UTF-8'}</a>
                </p>
              {/if}

              {* Status d'ouverture *}
              <p class="mb-1 small d-flex align-items-center">
                {if $item.is_open}
                  <span class="d-inline-block rounded-circle me-2" style="width:8px; height:8px; background-color: #28a745;"></span>
                  {l s='Open today until %s' sprintf=[$item.open_until] mod='everblock'}
                {elseif $item.opens_at}
                  <span class="d-inline-block rounded-circle me-2" style="width:8px; height:8px; background-color: #ffc107;"></span>
                  {l s='Open today at %s' sprintf=[$item.opens_at] mod='everblock'}
                {else}
                  <span class="d-inline-block rounded-circle me-2" style="width:8px; height:8px; background-color: #dc3545;"></span>
                  {l s='Closed' mod='everblock'}
                {/if}
              </p>

              {* Voir les horaires (ouvre une modal Bootstrap) *}
              <p class="mb-0 small">
                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#storeHoursModal{$item.id}">
                  <u>{l s='See hours' mod='everblock'}</u> &gt;
                </a>
              </p>
            </div>
          </div>
          {hook h='displayAfterLocatorStore' store=$item}
          {if $has_prettyblocks}
            {prettyblocks_zone zone_name="displayPrettyBlocksStoreLocator{$item.id}"}
          {/if}

          {* Modal horaires *}
          <div class="modal fade" id="storeHoursModal{$item.id}" tabindex="-1" aria-labelledby="storeHoursModalLabel{$item.id}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content border-0 shadow">
                <div class="modal-header">
                  <h5 class="modal-title" id="storeHoursModalLabel{$item.id}">
                    {l s='Hours from %s' sprintf=[$item.name|escape:'htmlall':'UTF-8'] mod='everblock'}
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{l s='Close' mod='everblock'}"></button>
                </div>
                <div class="modal-body">
                  <ul class="list-unstyled mb-3">
                    {foreach from=$item.hours_display item=hour}
                      <li>
                        <strong>{$hour.day|escape:'htmlall':'UTF-8'} :</strong>
                        {$hour.hours|escape:'htmlall':'UTF-8'}
                      </li>
                    {/foreach}
                  </ul>

                  {if $hasCoordinates}
                    <div class="text-center">
                      <a href="https://www.google.com/maps/search/?api=1&query={$item.latitude},{$item.longitude}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary d-inline-flex align-items-center">
                        <i class="material-icons me-2">location_on</i>
                        {l s='Get directions' mod='everblock'}
                      </a>
                    </div>
                  {/if}
                </div>
              </div>
            </div>
          </div>
        </div>
      {/foreach}
      </div>
    </div>
  </div>
</div>
{hook h='displayAfterStoreLocator'}
