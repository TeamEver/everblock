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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<!-- Module Ever Block -->
<div class="everblock-video-gallery{if $block.settings.default.container} container{/if}" data-carousel="{$block.settings.use_carousel}" style="{if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
        {if $block.settings.use_carousel}
            <div class="everblock-video-container">
                {foreach from=$block.states item=state key=key}
                    <div class="everblock-video-item" data-block="{$block.id_prettyblocks}" data-video="{$state.video_url|escape:'htmlall':'UTF-8'}">
                        <img src="{$state.thumbnail.url|replace:'.webp':'.jpg'}" class="img-fluid" alt="{$state.title|escape:'htmlall':'UTF-8'}" loading="lazy">
                        {if isset($state.title) && $state.title}
                            <h3 class="everblock-video-title">{$state.title|escape:'htmlall':'UTF-8'}</h3>
                        {/if}
                        {if isset($state.description) && $state.description}
                            <div class="everblock-video-desc">{$state.description nofilter}</div>
                        {/if}
                    </div>
                {/foreach}
            </div>
        {else}
            <div class="row everblock-video-container">
                {foreach from=$block.states item=state key=key}
                    <div class="col-md-3 everblock-video-item" data-block="{$block.id_prettyblocks}" data-video="{$state.video_url|escape:'htmlall':'UTF-8'}" style="{if isset($state.padding_left) && $state.padding_left}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($state.padding_right) && $state.padding_right}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($state.padding_top) && $state.padding_top}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($state.padding_bottom) && $state.padding_bottom}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($state.margin_left) && $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($state.margin_right) && $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($state.margin_top) && $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($state.margin_bottom) && $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
                        <img src="{$state.thumbnail.url|replace:'.webp':'.jpg'}" class="img-fluid" alt="{$state.title|escape:'htmlall':'UTF-8'}" loading="lazy">
                        {if isset($state.title) && $state.title}
                            <h3 class="everblock-video-title">{$state.title|escape:'htmlall':'UTF-8'}</h3>
                        {/if}
                        {if isset($state.description) && $state.description}
                            <div class="everblock-video-desc">{$state.description nofilter}</div>
                        {/if}
                    </div>
                {/foreach}
            </div>
        {/if}
        <div class="modal fade everblock-video-modal" id="videoModal-{$block.id_prettyblocks}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="ratio ratio-16x9">
                            <iframe src="" frameborder="0" allowfullscreen></iframe>
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
