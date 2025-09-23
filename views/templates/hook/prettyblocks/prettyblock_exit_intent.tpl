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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {foreach from=$block.states item=state key=key}
    <div class="modal fade ever-exit-intent-modal" id="everExitIntent-{$block.id_prettyblocks}-{$key}" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body text-center">
            {if $state.image.url}
              <img src="{$state.image.url}" class="img-fluid mb-3" alt="" />
            {/if}
            {if $state.title}<h4>{$state.title}</h4>{/if}
            {if $state.message}<p>{$state.message}</p>{/if}
            {if $state.cta_label}
              <a href="{$state.cta_url}" class="btn btn-primary">{$state.cta_label}</a>
            {/if}
          </div>
        </div>
      </div>
    </div>
  {/foreach}
</div>
