{*
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if isset($everblock) && $everblock}
  {foreach from=$everblock item=item}
    {$item.block.custom_code nofilter}
    {if isset($item.block.add_container) && $item.block.add_container == 1}
      <div class="container">
    {/if}
    {if isset($is_bypassed) && $is_bypassed}
    {$item.block.content nofilter}
    {else}
      {if isset($item.block.modal) && $item.block.modal}
        <div class="everblock everblock-{$item.block.id_everblock|escape:'htmlall':'UTF-8'} {$item.block.css_class|escape:'htmlall':'UTF-8'} {$item.block.bootstrap_class|escape:'htmlall':'UTF-8'} everhook-{$everhook|escape:'htmlall':'UTF-8'}" id="everblock-{$item.block.id_everblock|escape:'htmlall':'UTF-8'}" data-everposition="{$item.block.position|escape:'htmlall':'UTF-8'}" data-everhook="{$everhook|escape:'htmlall':'UTF-8'}"{if isset($item.block.background) && $item.block.background} style="background-color:{$item.block.background|escape:'htmlall':'UTF-8'};"{/if} {if isset($item.block.modal) && $item.block.modal} data-evermodal="{$item.block.modal|escape:'htmlall':'UTF-8'}" data-evertimeout="{$item.block.timeout|escape:'htmlall':'UTF-8'}"{/if}></div>
        {else}
        <div class="everblock everblock-{$item.block.id_everblock|escape:'htmlall':'UTF-8'} {$item.block.css_class|escape:'htmlall':'UTF-8'} {$item.block.bootstrap_class|escape:'htmlall':'UTF-8'} everhook-{$everhook|escape:'htmlall':'UTF-8'}" id="everblock-{$item.block.id_everblock|escape:'htmlall':'UTF-8'}" data-everposition="{$item.block.position|escape:'htmlall':'UTF-8'}" data-everhook="{$everhook|escape:'htmlall':'UTF-8'}"{if isset($item.block.background) && $item.block.background} style="background-color:{$item.block.background|escape:'htmlall':'UTF-8'};"{/if} {if isset($item.block.modal) && $item.block.modal} data-evermodal="{$item.block.modal|escape:'htmlall':'UTF-8'}"{/if}>
          {$item.block.content nofilter}
        </div>
        {/if}
    {/if}
  {if isset($item.block.add_container) && $item.block.add_container == 1}
    </div>
  {/if}
  {/foreach}
{/if}
