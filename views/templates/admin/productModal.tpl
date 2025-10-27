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
<div class="panel everblock-modal-panel" data-everblock-modal="1" data-ever-ajax-url="{$ever_ajax_url|escape:'htmlall':'UTF-8'}" data-ever-product-id="{$ever_product_id|intval}" data-ever-no-file-text="{l s='No file uploaded yet.' mod='everblock'|escape:'htmlall':'UTF-8'}" data-ever-error-text="{l s='An error occurred while updating the modal file.' mod='everblock'|escape:'htmlall':'UTF-8'}">
    <div class="panel-heading">
        {l s='Everblock modal content' mod='everblock'}
    </div>
    <div class="panel-body">
        <div class="translations">
            {foreach from=$ever_languages item=language}
                <div class="form-group">
                    <label for="everblock_modal_content_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Modal content' mod='everblock'} {$language.iso_code|escape:'htmlall':'UTF-8'}</label>
                    <textarea id="everblock_modal_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="everblock_modal_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control autoload_rte">{if isset($modal->content[$language.id_lang])}{$modal->content[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}</textarea>
                </div>
            {/foreach}
        </div>
        <div class="form-group">
            <label for="everblock_modal_file">{l s='Modal file' mod='everblock'}</label>
            <div class="everblock-modal-file-wrapper">
                {if $modal_file_url}
                    <p class="everblock-modal-current-file">
                        <a href="{$modal_file_url|escape:'htmlall':'UTF-8'}" target="_blank" class="everblock-modal-file-link">{$modal_file_name|escape:'htmlall':'UTF-8'}</a>
                    </p>
                {else}
                    <p class="everblock-modal-current-file text-muted">{l s='No file uploaded yet.' mod='everblock'}</p>
                {/if}
            </div>
            <div class="everblock-modal-delete-wrapper"{if !$modal_file_url} style="display:none;"{/if}>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="everblock_modal_file_delete" value="1" />
                        {l s='Delete file' mod='everblock'}
                    </label>
                </div>
            </div>
            <input type="file" name="everblock_modal_file" id="everblock_modal_file" class="form-control" />
            <input type="hidden" name="everblock_modal_file_payload" id="everblock_modal_file_payload" value="" />
            <input type="hidden" name="everblock_modal_file_name" id="everblock_modal_file_name" value="" />
            <div class="everblock-modal-feedback alert d-none" role="alert"></div>
        </div>
    </div>
</div>
