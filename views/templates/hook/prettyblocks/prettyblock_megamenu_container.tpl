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


  {assign var='collapse_id' value="everblock-megamenu-collapse-`$block.id_prettyblocks`"}
  {assign var='fallback_label' value=$block.settings.fallback_label|default:$block.settings.menu_label|default:'Menu'}
  <nav class="navbar navbar-expand-lg navbar-light everblock-megamenu{$prettyblock_visibility_class}" aria-label="{$block.settings.menu_label|default:'Menu'|escape:'htmlall':'UTF-8'}">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#{$collapse_id}" aria-controls="{$collapse_id}" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="{$collapse_id}">
      <ul class="navbar-nav w-100">
        {if isset($block.extra.items) && $block.extra.items}
          {foreach from=$block.extra.items item=item}
            {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_item.tpl' block=$item from_parent=true}
          {/foreach}
        {else}
          <li class="nav-item">
            <a class="nav-link" href="#">{$fallback_label|escape:'htmlall':'UTF-8'}</a>
          </li>
        {/if}
      </ul>
    </div>
  </nav>

  <style>
    @media (min-width: 992px) {
      .everblock-megamenu .dropdown:hover > .dropdown-menu,
      .everblock-megamenu .dropdown:focus-within > .dropdown-menu {
        display: block;
      }

      .everblock-megamenu .dropdown-menu {
        margin-top: 0;
      }
    }

    .everblock-megamenu .nav-link.btn {
      padding: 0;
    }

    .everblock-megamenu .everblock-megamenu-dropdown {
      padding: 1.5rem 0;
    }

    .everblock-megamenu .everblock-megamenu-icon {
      font-size: 0.9em;
      line-height: 1;
    }
  </style>
