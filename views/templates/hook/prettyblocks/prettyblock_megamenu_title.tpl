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
  {assign var='title_label' value=$block.settings.label|default:$block.settings.title|default:''}
  {assign var='title_url' value=$block.settings.url|default:''}
  {assign var='obfme_class' value=''}
  {if $page.page_name|default:'' != 'index'}
    {assign var='obfme_class' value=' obfme'}
  {/if}
  {if $title_label}
    {if $title_url}
      <a class="dropdown-header h6 text-decoration-none{$obfme_class}" href="{$title_url|escape:'htmlall':'UTF-8'}">
        {$title_label|escape:'htmlall':'UTF-8'}
      </a>
    {else}
      <span class="dropdown-header h6">{$title_label|escape:'htmlall':'UTF-8'}</span>
    {/if}
  {/if}
{/if}
