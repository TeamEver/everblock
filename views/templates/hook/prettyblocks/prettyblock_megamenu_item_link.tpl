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
  {assign var='link_label' value=$block.settings.label}
  {if is_array($link_label)}
    {assign var='link_label' value=$link_label[$language.id_lang]|default:$link_label|@reset}
  {/if}
  {assign var='link_label' value=$link_label|default:''}
  {assign var='link_url' value=$block.settings.url|default:''}
  {assign var='obfme_class' value=''}
  {if $page.page_name|default:'' != 'index'}
    {assign var='obfme_class' value=' obfme'}
  {/if}
  {if $link_label && $link_url}
    <a class="dropdown-item d-flex align-items-center gap-2{$obfme_class}{if $block.settings.highlight} fw-semibold{/if}" href="{$link_url|escape:'htmlall':'UTF-8'}">
      {if $block.settings.icon}<span class="everblock-megamenu-icon">{$block.settings.icon|escape:'htmlall':'UTF-8'}</span>{/if}
      <span>{$link_label|escape:'htmlall':'UTF-8'}</span>
    </a>
  {/if}
{/if}
