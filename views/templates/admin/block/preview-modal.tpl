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

{assign var='preview_contexts_json' value=$preview_contexts|json_encode}
{assign var='preview_text_select' value={l s='Select a context to load the preview.' mod='everblock'}}
{assign var='preview_text_loading' value={l s='Loading preview...' mod='everblock'}}
{assign var='preview_text_error' value={l s='Unable to load the preview. Please try again.' mod='everblock'}}
{assign var='preview_text_empty' value={l s='Select a context to load the preview.' mod='everblock'}}

<div class="modal fade" id="everblock-preview-modal" tabindex="-1" role="dialog" aria-labelledby="everblockPreviewModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content"
            data-everblock-preview-root="1"
            data-everblock-preview-contexts="{$preview_contexts_json|escape:'htmlall':'UTF-8'}"
            data-everblock-preview-url="{$preview_url|escape:'htmlall':'UTF-8'}"
            data-everblock-preview-available="{$preview_available|intval}"
            data-everblock-preview-text-select="{$preview_text_select|escape:'htmlall':'UTF-8'}"
            data-everblock-preview-text-loading="{$preview_text_loading|escape:'htmlall':'UTF-8'}"
            data-everblock-preview-text-error="{$preview_text_error|escape:'htmlall':'UTF-8'}"
            data-everblock-preview-text-empty="{$preview_text_empty|escape:'htmlall':'UTF-8'}"
        >
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' mod='everblock'}">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="everblockPreviewModalLabel">{l s='Block preview' mod='everblock'}</h4>
            </div>
            <div class="modal-body">
                <div class="everblock-preview__controls">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="everblock-preview-language">{l s='Language' mod='everblock'}</label>
                                <select class="form-control" id="everblock-preview-language" data-everblock-preview-language></select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="everblock-preview-shop">{l s='Shop' mod='everblock'}</label>
                                <select class="form-control" id="everblock-preview-shop" data-everblock-preview-shop></select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="everblock-preview-context">{l s='Page context' mod='everblock'}</label>
                                <select class="form-control" id="everblock-preview-context" data-everblock-preview-context></select>
                            </div>
                        </div>
                    </div>

                    <div data-everblock-preview-fields></div>

                    {if isset($preview_contexts.groups) && $preview_contexts.groups|@count}
                        <div class="alert alert-info everblock-preview__groups" role="alert">
                            <strong>{l s='This block is limited to the following customer groups:' mod='everblock'}</strong>
                            <ul class="list-unstyled m-0">
                                {foreach from=$preview_contexts.groups item=group}
                                    <li><i class="icon-group"></i> {$group.label|escape:'htmlall':'UTF-8'} ({$group.id|intval})</li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}

                    <div class="everblock-preview__frame">
                        <div class="alert alert-danger hide" data-everblock-preview-error></div>
                        <div class="alert alert-info" data-everblock-preview-placeholder>{$preview_text_empty|escape:'htmlall':'UTF-8'}</div>
                        <iframe src="" class="everblock-preview__iframe" data-everblock-preview-frame style="width:100%;min-height:520px;border:0;" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='everblock'}</button>
                <button type="button" class="btn btn-primary" data-everblock-preview-run>{l s='Update preview' mod='everblock'}</button>
            </div>
        </div>
    </div>
</div>
