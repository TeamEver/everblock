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
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}

{assign var=columns value=$block.settings.columns|default:'1'}
<div class="prettyblock-lookbook columns-{$columns}{$prettyblock_visibility_class}">
  <div class="lookbook-item">
    {prettyblocks_zone zone_name="block-lookbook-{$block.id_prettyblocks}-before"}
  </div>
  <div class="lookbook-item">
    <div id="block-{$block.id_prettyblocks}" data-lookbook-url="{$link->getModuleLink('everblock', 'lookbook', ['token' => $static_token])|escape:'html':'UTF-8'}" class="{if $block.settings.default.force_full_width|default:false}container-fluid px-0 mx-0{elseif $block.settings.default.container|default:false}container{/if}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
      {if $block.settings.default.force_full_width|default:false}
        <div class="row gx-0 no-gutters">
      {elseif $block.settings.default.container|default:false}
        <div class="row">
      {/if}

      <div class="{if $block.settings.default.container|default:false}container{/if} text-center">
        {if $block.settings.title}
          <h2 class="mb-3">{$block.settings.title|escape:'htmlall':'UTF-8'}</h2>
        {/if}
        <div class="lookbook-image position-relative mb-3 d-inline-block">
          {if isset($block.settings.image.url) && $block.settings.image.url}
            <img src="{$block.settings.image.url}" alt="{$block.settings.title|escape:'htmlall':'UTF-8'}" class="img-fluid w-100" loading="lazy">
          {/if}
          {if isset($block.states) && $block.states}
            {foreach from=$block.states item=state}
              {if isset($state.product.id) && $state.product.id}
                <button type="button" class="btn btn-light rounded-circle lookbook-marker position-absolute" style="top:{$state.top|default:'0%'|escape:'htmlall'};left:{$state.left|default:'0%'|escape:'htmlall'};transform:translate(-50%,-50%);" data-product-id="{$state.product.id}">
                  <span class="visually-hidden">{l s='View product' mod='everblock'}</span>
                </button>
              {/if}
            {/foreach}
          {/if}
        </div>
        <div class="mt-3">
          <small class="text-muted">{l s='Cliquez sur un point pour voir le produit' mod='everblock'}</small>
        </div>
      </div>

      {if $block.settings.default.force_full_width|default:false || $block.settings.default.container|default:false}
        </div>
      {/if}
    </div>
  </div>
  <div class="lookbook-item">
    {prettyblocks_zone zone_name="block-lookbook-{$block.id_prettyblocks}-after"}
  </div>
</div>

<div class="modal fade" id="lookbook-modal-{$block.id_prettyblocks}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body"></div>
    </div>
  </div>
</div>

