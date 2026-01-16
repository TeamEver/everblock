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
  {include file='module:everblock/views/templates/hook/prettyblocks/_partials/megamenu_style_vars.tpl' block=$block assign='megamenu_style_vars'}

  {assign var='menu_label' value=$block.settings.label}
  {if is_array($menu_label)}
    {if isset($language.id_lang) && isset($menu_label[$language.id_lang])}
      {assign var='menu_label' value=$menu_label[$language.id_lang]}
    {else}
      {assign var='menu_label' value=$menu_label|@reset}
    {/if}
  {/if}
  {assign var='menu_label' value=$menu_label|default:$block.settings.fallback_label|default:'Menu'}
  {assign var='menu_url' value=$block.settings.url|default:''}
  {assign var='menu_toggle_id' value="everblock-megamenu-toggle-`$block.id_prettyblocks`"}
  {assign var='has_dropdown' value=($block.settings.is_mega && ($block.extra.columns|@count))}
  {assign var='obfme_class' value=''}
  {if $page.page_name|default:'' != 'index'}
    {assign var='obfme_class' value=' obfme'}
  {/if}
  <li id="block-{$block.id_prettyblocks}" class="nav-item{if $has_dropdown} dropdown{/if}{$prettyblock_visibility_class} everblock-megamenu-item"{if $megamenu_style_vars} style="{$megamenu_style_vars|escape:'htmlall':'UTF-8'}"{/if}>
    <div class="everblock-megamenu-item-header d-flex d-lg-block align-items-center justify-content-between">
      {if $menu_url}
        <a class="nav-link everblock-megamenu-item-link{$obfme_class}" href="{$menu_url|escape:'htmlall':'UTF-8'}" title="{$menu_label|escape:'htmlall':'UTF-8'}">
          {$menu_label|escape:'htmlall':'UTF-8'}
        </a>
      {else}
        <span class="nav-link everblock-megamenu-item-link" aria-label="{$menu_label|escape:'htmlall':'UTF-8'}">
          {$menu_label|escape:'htmlall':'UTF-8'}
        </span>
      {/if}
      {if $has_dropdown}
        <button class="btn everblock-megamenu-toggle dropdown-toggle d-lg-none" type="button" id="{$menu_toggle_id}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{$menu_label|escape:'htmlall':'UTF-8'}">
          <span class="everblock-megamenu-toggle-icon" aria-hidden="true"></span>
        </button>
      {/if}
    </div>

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

          <div class="d-lg-none everblock-megamenu-mobile">
            {foreach from=$block.extra.columns item=column name=mobile_columns}
              {assign var='column_title' value=$column.extra.title_label|default:$column.settings.title|default:''}
              {if is_array($column_title)}
                {if isset($language.id_lang) && isset($column_title[$language.id_lang])}
                  {assign var='column_title' value=$column_title[$language.id_lang]}
                {else}
                  {assign var='column_title' value=$column_title|@reset}
                {/if}
              {/if}
              {assign var='column_title' value=$column_title|default:''}
              {assign var='has_column_title' value=($column_title|trim != '')}
              {assign var='column_collapse_id' value="everblock-megamenu-collapse-`$block.id_prettyblocks`-`$smarty.foreach.mobile_columns.iteration`"}
              <div class="everblock-megamenu-mobile-column{if $has_column_title} is-collapsible{/if}">
                {if $has_column_title}
                  <div class="everblock-megamenu-mobile-header" id="everblock-megamenu-heading-{$block.id_prettyblocks}-{$smarty.foreach.mobile_columns.iteration}">
                    <button class="everblock-megamenu-mobile-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#{$column_collapse_id}" aria-expanded="false" aria-controls="{$column_collapse_id}">
                      <span class="everblock-megamenu-mobile-title">{$column_title|escape:'htmlall':'UTF-8'}</span>
                      <span class="everblock-megamenu-mobile-icon" aria-hidden="true"></span>
                    </button>
                  </div>
                  <div id="{$column_collapse_id}" class="collapse">
                    <div class="everblock-megamenu-mobile-body">
                      <div class="row g-3">
                        {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_column.tpl' block=$column from_parent=true render_title=false}
                      </div>
                    </div>
                  </div>
                {else}
                  <div class="everblock-megamenu-mobile-body">
                    <div class="row g-3">
                      {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_column.tpl' block=$column from_parent=true render_title=false}
                    </div>
                  </div>
                {/if}
              </div>
            {/foreach}
          </div>
        </div>
      </div>
    {/if}
  </li>
{/if}
