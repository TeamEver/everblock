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

<!-- Module Ever Block -->
<div class="mt-2{if $block.settings.default.container} container{/if}" {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    <div id="gallery-{$block.id_prettyblocks}" class="everblock-gallery">
        <div class="container mt-2">
            {assign var="numPerRow" value=4}
            {assign var="count" value=0}
            {foreach from=$block.states item=state key=key}
                {if $count % $numPerRow == 0}
                    <div class="row justify-content-center">
                {/if}
                
                <div class="col-md-3">
                    <div class="card">
                        <img src="{$state.image.url}" class="img img-fluid card-img-top cursor-pointer lazyload" alt="{$state.name}" title="{$state.name}"
                        data-toggle="modal" data-target="#imageModal-{$block.id_prettyblocks}" data-src="{$state.image.url}" data-slide-to="{$key}" loading="lazy">
                    </div>
                </div>
                
                {assign var="count" value=$count+1}
                
                {if $count % $numPerRow == 0 || $smarty.foreach.states.last}
                    </div>
                {/if}
            {/foreach}
        </div>
        <!-- Modal -->
        <div class="modal fade everblock-gallery-modal" id="imageModal-{$block.id_prettyblocks}" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title h5" id="imageModalLabel"></span>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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



