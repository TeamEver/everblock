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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}
{capture name='prettyblock_lookbook_wrapper_style'}
  {$prettyblock_spacing_style}
{/capture}
{assign var='prettyblock_lookbook_wrapper_style' value=$smarty.capture.prettyblock_lookbook_wrapper_style|trim}

{assign var=columns value=$block.settings.columns|default:'1'}
<div class="prettyblock-lookbook columns-{$columns}{$prettyblock_visibility_class}"{if $prettyblock_lookbook_wrapper_style} style="{$prettyblock_lookbook_wrapper_style}"{/if}>
  <div class="lookbook-item lookbook-item--before">
    {prettyblocks_zone zone_name="block-lookbook-{$block.id_prettyblocks}-before"}
  </div>
  <div class="lookbook-item lookbook-item--main">
    {capture name='prettyblock_lookbook_inner_style'}
      {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}
        background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};
      {/if}
    {/capture}
    {assign var='prettyblock_lookbook_inner_style' value=$smarty.capture.prettyblock_lookbook_inner_style|trim}
    <div id="block-{$block.id_prettyblocks}" data-lookbook-url="{$link->getModuleLink('everblock', 'lookbook', ['token' => $static_token])|escape:'html':'UTF-8'}" class="{if $block.settings.default.force_full_width|default:false}container-fluid px-0 mx-0{elseif $block.settings.default.container|default:false}container{/if}"{if $prettyblock_lookbook_inner_style} style="{$prettyblock_lookbook_inner_style}"{/if}>
      {if $block.settings.default.force_full_width|default:false}
        <div class="row gx-0 no-gutters">
      {elseif $block.settings.default.container|default:false}
        <div class="row">
      {/if}

      <div class="{if $block.settings.default.container|default:false}container{/if} text-center lookbook-main-wrapper">
        {if $block.settings.title}
          <h2 class="mb-3">{$block.settings.title|escape:'htmlall':'UTF-8'}</h2>
        {/if}
        <div class="lookbook-image position-relative d-inline-block">
          {if isset($block.settings.image.url) && $block.settings.image.url}
            <img src="{$block.settings.image.url}" alt="{$block.settings.title|escape:'htmlall':'UTF-8'}" class="img-fluid w-100" loading="lazy">
          {/if}
          {if isset($block.states) && $block.states}
            {foreach from=$block.states item=state}
              {if isset($state.product.id) && $state.product.id}
                <button type="button" class="lookbook-marker position-absolute" style="top:{$state.top|default:'0%'|escape:'htmlall'};left:{$state.left|default:'0%'|escape:'htmlall'};transform:translate(-50%,-50%);" data-product-id="{$state.product.id}">
                  <span class="visually-hidden">{l s='View product' mod='everblock'}</span>
                </button>
              {/if}
            {/foreach}
          {/if}
          <div class="lookbook-helper" role="note">
            <span class="lookbook-helper-icon" aria-hidden="true">
              <svg class="lookbook-helper-svg" width="24" height="24" viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                <path d="M12 2a1 1 0 0 1 1 1v9.25l.586-.586a2 2 0 0 1 3.414 1.414v2.5A5.5 5.5 0 0 1 11.5 21H9a5 5 0 0 1-5-5v-3.586l-1.293 1.293a1 1 0 0 1-1.414-1.414l3.75-3.75A1 1 0 0 1 6.75 9H9V5a1 1 0 0 1 2 0V3a1 1 0 0 1 1-1zm-6 9.5a.5.5 0 0 0-.5.5v5a3.5 3.5 0 0 0 3.5 3.5h2.5a3.5 3.5 0 0 0 3.5-3.5v-2.5a1 1 0 0 0-1.707-.707l-1.586 1.586A1 1 0 0 1 11 15v-5a1 1 0 0 0-2 0v1.5a.5.5 0 0 1-.5.5H9a1 1 0 0 0-.707.293l-2.25 2.25A1 1 0 0 1 4 14.5v-3z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"/>
              </svg>
            </span>
            <span class="lookbook-helper-text">{l s='Cliquez sur un point pour voir le produit' mod='everblock'}</span>
          </div>
        </div>
      </div>

      {if $block.settings.default.force_full_width|default:false || $block.settings.default.container|default:false}
        </div>
      {/if}
    </div>
  </div>
  <div class="lookbook-item lookbook-item--after">
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

