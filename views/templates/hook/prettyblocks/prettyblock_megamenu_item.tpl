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
{if isset($from_parent) && $from_parent && (!isset($block.settings.active) || $block.settings.active)}
  {include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}

  {assign var='menu_label' value=$block.settings.label|default:$block.settings.fallback_label|default:'Menu'}
  {assign var='menu_url' value=$block.settings.url|default:''}
  {assign var='menu_toggle_id' value="everblock-megamenu-toggle-`$block.id_prettyblocks`"}
  {assign var='has_dropdown' value=($block.settings.is_mega && ($block.extra.columns|@count))}
  <li id="block-{$block.id_prettyblocks}" class="nav-item{if $has_dropdown} dropdown{/if}{$prettyblock_visibility_class} everblock-megamenu-item">
    {if $menu_url}
      <a class="nav-link{if $has_dropdown} dropdown-toggle{/if}" href="{$menu_url|escape:'htmlall':'UTF-8'}"{if $has_dropdown} id="{$menu_toggle_id}" role="button" data-bs-toggle="dropdown" aria-expanded="false"{/if}>
        {$menu_label|escape:'htmlall':'UTF-8'}
      </a>
    {else}
      <button class="nav-link btn btn-link{if $has_dropdown} dropdown-toggle{/if}" type="button"{if $has_dropdown} id="{$menu_toggle_id}" data-bs-toggle="dropdown" aria-expanded="false"{/if}>
        {$menu_label|escape:'htmlall':'UTF-8'}
      </button>
    {/if}

    {if $has_dropdown}
      <div class="dropdown-menu everblock-megamenu-dropdown{if $block.settings.full_width} w-100{/if}" aria-labelledby="{$menu_toggle_id}" data-bs-popper="static">
        <div class="{if $block.settings.full_width}container-fluid{else}container{/if}">
          <div class="d-none d-lg-block">
            <div class="row g-4">
              {foreach from=$block.extra.columns item=column}
                {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_column.tpl' block=$column from_parent=true render_title=true}
              {/foreach}
            </div>
          </div>

          <div class="d-lg-none accordion" id="everblock-megamenu-accordion-{$block.id_prettyblocks}">
            {foreach from=$block.extra.columns item=column name=mobile_columns}
              {assign var='column_title' value=$column.extra.title_label|default:$column.settings.title|default:$menu_label}
              <div class="accordion-item">
                <h2 class="accordion-header" id="everblock-megamenu-heading-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#everblock-megamenu-collapse-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}" aria-expanded="false" aria-controls="everblock-megamenu-collapse-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}">
                    {$column_title|escape:'htmlall':'UTF-8'}
                  </button>
                </h2>
                <div id="everblock-megamenu-collapse-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}" class="accordion-collapse collapse" aria-labelledby="everblock-megamenu-heading-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}" data-bs-parent="#everblock-megamenu-accordion-{$block.id_prettyblocks}">
                  <div class="accordion-body">
                    <div class="row g-3">
                      {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_column.tpl' block=$column from_parent=true render_title=false}
                    </div>
                  </div>
                </div>
              </div>
            {/foreach}
          </div>
        </div>
      </div>
    {/if}
  </li>
{/if}
