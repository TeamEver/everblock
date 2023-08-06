{*
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<!-- Module Ever Block -->
<div class="modal fade {if $block.settings.auto_trigger_modal == 'Auto'}everModalAutoTrigger{/if}" id="everModal-{$block.id_prettyblocks}">
  <div class="modal-dialog">
    <div class="modal-content" {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>

      <!-- En-tête de la Modal -->
      <div class="modal-header">
        <h4 class="modal-title" {if isset($block.settings.modal_title_color) && $block.settings.modal_title_color}style="color:{$block.settings.modal_title_color};"{/if}>{$block.settings.name}</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Corps de la Modal -->
      <div class="modal-body">
        {$block.settings.content nofilter}
      </div>

      <!-- Pied de la Modal -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{$block.settings.close_name}</button>
      </div>

    </div>
  </div>
</div>
{if $block.settings.auto_trigger_modal != 'Auto'}
<div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    <button type="button" class="btn btn-primary {$block.settings.css_class|escape:'htmlall':'UTF-8'} {$block.settings.bootstrap_class|escape:'htmlall':'UTF-8'}" data-toggle="modal" data-target="#everModal-{$block.id_prettyblocks}" {if isset($block.settings.open_modal_button_bg_color) && $block.settings.open_modal_button_bg_color} style="background-color:{$block.settings.open_modal_button_bg_color|escape:'htmlall':'UTF-8'};"{/if}>
      {$block.settings.open_name}
    </button>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
{/if}
<!-- /Module Ever Block -->