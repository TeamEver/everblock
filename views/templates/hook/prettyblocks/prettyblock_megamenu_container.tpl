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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/megamenu_style_vars.tpl' block=$block assign='megamenu_style_vars'}


  {assign var='collapse_id' value="everblock-megamenu-collapse-`$block.id_prettyblocks`"}
  {assign var='menu_label' value=$block.settings.menu_label}
  {if is_array($menu_label)}
    {if isset($menu_label[$language.id_lang])}
      {assign var='menu_label' value=$menu_label[$language.id_lang]}
    {else}
      {assign var='menu_label' value=$menu_label|@reset}
    {/if}
  {/if}
  {assign var='menu_label' value=$menu_label|default:'Menu'}
  {assign var='fallback_label' value=$block.settings.fallback_label|default:$menu_label}
  {assign var='obfme_class' value=''}
  {if $page.page_name|default:'' != 'index'}
    {assign var='obfme_class' value=' obfme'}
  {/if}
  <nav class="navbar navbar-expand-lg navbar-light everblock-megamenu{if $everblock_winter_mode} everblock-megamenu--winter{/if}{$prettyblock_visibility_class}" aria-label="{$menu_label|escape:'htmlall':'UTF-8'}"{if $megamenu_style_vars} style="{$megamenu_style_vars|escape:'htmlall':'UTF-8'}"{/if}>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#{$collapse_id}" aria-controls="{$collapse_id}" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse everblock-megamenu-collapse w-100" id="{$collapse_id}">
      <ul class="navbar-nav w-100">
        {if isset($block.extra.items) && $block.extra.items}
          {foreach from=$block.extra.items item=item}
            {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_item.tpl' block=$item from_parent=true}
          {/foreach}
        {else}
          <li class="nav-item">
            <a class="nav-link{$obfme_class}" href="#" title="{$fallback_label|escape:'htmlall':'UTF-8'}">{$fallback_label|escape:'htmlall':'UTF-8'}</a>
          </li>
        {/if}
      </ul>
    </div>
  </nav>
