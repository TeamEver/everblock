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
  {assign var='column_width' value=$block.settings.width|default:3}
  {assign var='render_title' value=$render_title|default:true}
  {assign var='obfme_class' value=''}
  {if $page.page_name|default:'' != 'index'}
    {assign var='obfme_class' value=' obfme'}
  {/if}
  <div class="col col-12 col-lg-{$column_width|escape:'htmlall':'UTF-8'}">
    {if $render_title}
      {assign var='column_title' value=$block.settings.title}
      {if is_array($column_title)}
        {if isset($column_title[$language.id_lang])}
          {assign var='column_title' value=$column_title[$language.id_lang]}
        {else}
          {assign var='column_title' value=$column_title|@reset}
        {/if}
      {/if}
      {if $block.extra.titles}
        {foreach from=$block.extra.titles item=title}
          {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_title.tpl' block=$title from_parent=true}
        {/foreach}
      {elseif $column_title}
        {if $block.settings.title_url}
          <a class="dropdown-header h6 text-decoration-none{$obfme_class}" href="{$block.settings.title_url|escape:'htmlall':'UTF-8'}">
            {$column_title|escape:'htmlall':'UTF-8'}
          </a>
        {else}
          <span class="dropdown-header h6">{$column_title|escape:'htmlall':'UTF-8'}</span>
        {/if}
      {/if}
    {/if}

    {if $block.extra.links}
      <div class="dropdown-megamenu-links mb-3">
        {foreach from=$block.extra.links item=item}
          {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_item_link.tpl' block=$item from_parent=true}
        {/foreach}
      </div>
    {/if}

    {if $block.extra.images}
      {foreach from=$block.extra.images item=image}
        {include file='module:everblock/views/templates/hook/prettyblocks/prettyblock_megamenu_item_image.tpl' block=$image from_parent=true}
      {/foreach}
    {/if}
  </div>
{/if}
