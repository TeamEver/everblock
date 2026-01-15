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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}

{if !isset($block.settings.active) || $block.settings.active}
  {assign var='menu_label' value=$block.settings.label|default:''}
  {assign var='menu_url' value=$block.settings.url|default:''}
  {assign var='menu_toggle_id' value="everblock-megamenu-toggle-`$block.id_prettyblocks`"}
  <li id="block-{$block.id_prettyblocks}" class="nav-item{if $block.settings.is_mega} dropdown{/if}{$prettyblock_visibility_class} everblock-megamenu-item">
    {if $menu_url}
      <a class="nav-link{if $block.settings.is_mega} dropdown-toggle{/if}" href="{$menu_url|escape:'htmlall':'UTF-8'}"{if $block.settings.is_mega} id="{$menu_toggle_id}" role="button" data-bs-toggle="dropdown" aria-expanded="false"{/if}>
        {$menu_label|escape:'htmlall':'UTF-8'}
      </a>
    {else}
      <button class="nav-link btn btn-link{if $block.settings.is_mega} dropdown-toggle{/if}" type="button"{if $block.settings.is_mega} id="{$menu_toggle_id}" data-bs-toggle="dropdown" aria-expanded="false"{/if}>
        {$menu_label|escape:'htmlall':'UTF-8'}
      </button>
    {/if}

    {if $block.settings.is_mega}
      <div class="dropdown-menu everblock-megamenu-dropdown{if $block.settings.full_width} w-100{/if}" aria-labelledby="{$menu_toggle_id}">
        <div class="{if $block.settings.full_width}container-fluid{else}container{/if}">
          <div class="d-none d-lg-block">
            <div class="row g-4">
              {foreach from=$block.extra.columns item=column}
                {assign var='column_width' value=$column.settings.width|default:3}
                <div class="col-12 col-lg-{$column_width|escape:'htmlall':'UTF-8'}">
                  {if $column.settings.title}
                    {if $column.settings.title_url}
                      <a class="h6 d-block mb-2 text-decoration-none" href="{$column.settings.title_url|escape:'htmlall':'UTF-8'}">
                        {$column.settings.title|escape:'htmlall':'UTF-8'}
                      </a>
                    {else}
                      <span class="h6 d-block mb-2">{$column.settings.title|escape:'htmlall':'UTF-8'}</span>
                    {/if}
                  {/if}

                  {if $column.links}
                    <ul class="list-unstyled mb-3">
                      {foreach from=$column.links item=item}
                        <li class="mb-1{if $item.settings.highlight} fw-semibold{/if}">
                          <a class="text-decoration-none d-inline-flex align-items-center gap-2" href="{$item.settings.url|escape:'htmlall':'UTF-8'}">
                            {if $item.settings.icon}<span class="everblock-megamenu-icon">{$item.settings.icon|escape:'htmlall':'UTF-8'}</span>{/if}
                            <span>{$item.settings.label|escape:'htmlall':'UTF-8'}</span>
                          </a>
                        </li>
                      {/foreach}
                    </ul>
                  {/if}

                  {if $column.images}
                    {foreach from=$column.images item=image}
                      {if isset($image.settings.image.url) && $image.settings.image.url}
                        <div class="everblock-megamenu-image mb-3">
                          <a href="{$image.settings.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none d-block">
                            <img src="{$image.settings.image.url|escape:'htmlall':'UTF-8'}" alt="{$image.settings.title|escape:'htmlall':'UTF-8'}" class="img-fluid rounded">
                            {if $image.settings.title}
                              <div class="mt-2 fw-semibold">{$image.settings.title|escape:'htmlall':'UTF-8'}</div>
                            {/if}
                            {if $image.settings.cta_label}
                              <span class="btn btn-sm btn-outline-primary mt-2">{$image.settings.cta_label|escape:'htmlall':'UTF-8'}</span>
                            {/if}
                          </a>
                        </div>
                      {/if}
                    {/foreach}
                  {/if}
                </div>
              {/foreach}
            </div>
          </div>

          <div class="d-lg-none accordion" id="everblock-megamenu-accordion-{$block.id_prettyblocks}">
            {foreach from=$block.extra.columns item=column name=mobile_columns}
              <div class="accordion-item">
                <h2 class="accordion-header" id="everblock-megamenu-heading-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#everblock-megamenu-collapse-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}" aria-expanded="false" aria-controls="everblock-megamenu-collapse-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}">
                    {$column.settings.title|default:$menu_label|escape:'htmlall':'UTF-8'}
                  </button>
                </h2>
                <div id="everblock-megamenu-collapse-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}" class="accordion-collapse collapse" aria-labelledby="everblock-megamenu-heading-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}" data-bs-parent="#everblock-megamenu-accordion-{$block.id_prettyblocks}">
                  <div class="accordion-body">
                    {if $column.links}
                      <ul class="list-unstyled mb-3">
                        {foreach from=$column.links item=item}
                          <li class="mb-1{if $item.settings.highlight} fw-semibold{/if}">
                            <a class="text-decoration-none d-inline-flex align-items-center gap-2" href="{$item.settings.url|escape:'htmlall':'UTF-8'}">
                              {if $item.settings.icon}<span class="everblock-megamenu-icon">{$item.settings.icon|escape:'htmlall':'UTF-8'}</span>{/if}
                              <span>{$item.settings.label|escape:'htmlall':'UTF-8'}</span>
                            </a>
                          </li>
                        {/foreach}
                      </ul>
                    {/if}

                    {if $column.images}
                      {foreach from=$column.images item=image}
                        {if isset($image.settings.image.url) && $image.settings.image.url}
                          <div class="everblock-megamenu-image mb-3">
                            <a href="{$image.settings.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none d-block">
                              <img src="{$image.settings.image.url|escape:'htmlall':'UTF-8'}" alt="{$image.settings.title|escape:'htmlall':'UTF-8'}" class="img-fluid rounded">
                              {if $image.settings.title}
                                <div class="mt-2 fw-semibold">{$image.settings.title|escape:'htmlall':'UTF-8'}</div>
                              {/if}
                              {if $image.settings.cta_label}
                                <span class="btn btn-sm btn-outline-primary mt-2">{$image.settings.cta_label|escape:'htmlall':'UTF-8'}</span>
                              {/if}
                            </a>
                          </div>
                        {/if}
                      {/foreach}
                    {/if}
                  </div>
                </div>
              </div>
            {/foreach}
          </div>
        </div>
      </div>
    {/if}
  </li>

  <style>
    #block-{$block.id_prettyblocks}.everblock-megamenu-item .everblock-megamenu-dropdown {
      padding: 1.5rem 0;
    }

    #block-{$block.id_prettyblocks}.everblock-megamenu-item .everblock-megamenu-icon {
      font-size: 0.9em;
      line-height: 1;
    }
  </style>
{/if}
