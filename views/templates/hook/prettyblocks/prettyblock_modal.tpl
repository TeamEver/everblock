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
<div class="mt-2 mb-2{if $block.settings.default.container} container{/if}" {if isset($block.settings.bg_color) && $block.settings.bg_color} style="background-color:{$block.settings.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
    {if $block.settings.default.container}
        <div class="row">
    {/if}
      {foreach from=$block.states item=state key=key}
          <div class="block-{$block.id_prettyblocks}-{$key} everblock-modal">
            <div class="modal fade{if $state.auto_trigger_modal == 'Auto'} everModalAutoTrigger{/if}" id="everModal-{$block.id_prettyblocks}">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title" {if isset($state.modal_title_color) && $state.modal_title_color}style="color:{$state.modal_title_color};"{/if}>{$state.name}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>
                  <div class="modal-body">
                    {$state.content nofilter}
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{$state.close_name}</button>
                  </div>

                </div>
              </div>
            </div>
          </div>
      {/foreach}
      <div class="block-{$block.id_prettyblocks}-{$key} everblock {$state.css_class|escape:'htmlall':'UTF-8'}">
      {foreach from=$block.states item=state key=key}
      {if $state.auto_trigger_modal != 'Auto'}
          <div class="modal-button {$state.css_class|escape:'htmlall':'UTF-8'}">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#everModal-{$block.id_prettyblocks}" {if isset($state.open_modal_button_bg_color) && $state.open_modal_button_bg_color} style="background-color:{$state.open_modal_button_bg_color|escape:'htmlall':'UTF-8'};"{/if}>
              {$state.open_name}
            </button>
          </div>
      {/if}
      {/foreach}
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
