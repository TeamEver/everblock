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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}

<!-- Module Ever Block -->
<div class="mt-2{if $block.settings.default.container} container{/if}"  style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    <div id="gallery-{$block.id_prettyblocks}" class="everblock-gallery">
        <div class="container mt-2">
            {assign var="numPerRow" value=4}
            {assign var="count" value=0}
            {foreach from=$block.states item=state key=key}
                {if $count && $numPerRow == 0}
                    <div class="row justify-content-center row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 gx-0 no-gutters">
                {/if}

                {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}


                <div class="col mb-4 col-md-3" style="{$prettyblock_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
                    <div class="card">
                        <picture>
                          <source srcset="{$state.image.url}" type="image/webp">
                          <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
                          <img src="{$state.image.url|replace:'.webp':'.jpg'}" class="img img-fluid card-img-top cursor-pointer lazyload" alt="{$state.name}" title="{$state.name}"
                          data-toggle="modal" data-target="#imageModal-{$block.id_prettyblocks}" data-bs-toggle="modal" data-bs-target="#imageModal-{$block.id_prettyblocks}" data-src="{$state.image.url}" data-slide-to="{$key}" data-bs-slide-to="{$key}" loading="lazy">
                        </picture>
                    </div>
                </div>

                {assign var="count" value=$count+1}

                {if $count && $numPerRow == 0 || $smarty.foreach.states.last}
                    </div>
                {/if}
            {/foreach}
        </div>
        <!-- Modal -->
        <div class="modal fade everblock-gallery-modal" id="imageModal-{$block.id_prettyblocks}" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title h5" id="imageModalLabel"></span>
                        <button type="button" class="close btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <img id="modalImage-{$block.id_prettyblocks}" class="img-fluid" alt="Image">
                    </div>
                </div>
            </div>
        </div>
    </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->




  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
