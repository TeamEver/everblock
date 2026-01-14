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
 *  @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
*}
{if isset($everblock_modal_id) && $everblock_modal_id}
    {assign var='everblockModalButtonLabel' value=$everblock_modal_button_label|default:''}
    {assign var='everblockModalButtonFileUrl' value=$everblock_modal_button_file_url|default:''}
<button type="button" class="btn everblock-modal-button{if $everblockModalButtonFileUrl} btn-link p-0 border-0 everblock-modal-button--image{else} btn-secondary{/if}" data-evermodal="{$everblock_modal_id|escape:'htmlall':'UTF-8'}">
    {if $everblockModalButtonFileUrl}
        <img src="{$everblockModalButtonFileUrl|escape:'htmlall':'UTF-8'}" alt="{$everblockModalButtonLabel|strip_tags|escape:'htmlall':'UTF-8'}" class="everblock-modal-button-image" loading="lazy" />
        {if $everblockModalButtonLabel}
            <span class="visually-hidden">{$everblockModalButtonLabel|strip_tags|escape:'htmlall':'UTF-8'}</span>
        {/if}
    {elseif $everblockModalButtonLabel}
        {$everblockModalButtonLabel|escape:'htmlall':'UTF-8'}
    {else}
        {l s='Show modal' mod='everblock'}
    {/if}
</button>
{/if}
