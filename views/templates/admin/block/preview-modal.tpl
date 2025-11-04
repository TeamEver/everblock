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
{if isset($everblock_preview_available) && $everblock_preview_available}
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-eye"></i> {l s='Block preview' mod='everblock'}
        </div>
        <div class="panel-body text-center">
            <button 
                type="button" 
                class="btn btn-primary everblock-preview-button" 
                data-everblock-preview-open="1" 
                data-everblock-preview-url="{$everblock_preview_url|escape:'htmlall':'UTF-8'}"
            >
                <i class="icon-eye"></i> {l s='Preview this block' mod='everblock'}
            </button>
        </div>
    </div>
{/if}

{assign var='preview_contexts_json' value=$preview_contexts|json_encode}
{assign var='preview_text_select' value={l s='Select a context to load the preview.' mod='everblock'}}
{assign var='preview_text_loading' value={l s='Loading preview...' mod='everblock'}}
{assign var='preview_text_error' value={l s='Unable to load the preview. Please try again.' mod='everblock'}}
{assign var='preview_text_empty' value={l s='Select a context to load the preview.' mod='everblock'}}

<div class="modal fade" id="everblock-preview-modal" tabindex="-1" role="dialog" aria-labelledby="everblockPreviewModalLabel">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="everblockPreviewModalLabel">
          <i class="icon-eye"></i> {l s='Block preview' mod='everblock'}
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' mod='everblock'}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0" style="height: 85vh;">
        <iframe id="everblock-preview-iframe"
                src=""
                frameborder="0"
                style="width:100%; height:100%; border:0;"
                loading="lazy"></iframe>
      </div>
    </div>
  </div>
</div>
