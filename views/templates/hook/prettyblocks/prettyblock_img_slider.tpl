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
<div class="mt-2{if $block.settings.default.container} container{/if}"  style="{if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
        {assign var="slideCount" value=0}
        <div class="pretty-gallery">
            <div id="imgGallerySlideshow{$block.id_prettyblocks}" class="carousel slide ever-slide" data-ride="carousel" data-duration="2000">
                <div class="direction" aria-label="Boutons du carrousel">
                    <a class="left carousel-control" href="#imgGallerySlideshow{$block.id_prettyblocks}" role="button" data-slide="prev" aria-label="Précédent">
                        <span class="icon-prev" aria-hidden="true">
                            <i class="material-icons">&#xE5CB;</i>
                        </span>
                    </a>
                    <a class="right carousel-control" href="#imgGallerySlideshow{$block.id_prettyblocks}" role="button" data-slide="next" aria-label="Suivant">
                        <span class="icon-next" aria-hidden="true">
                            <i class="material-icons">&#xE5CC;</i>
                        </span>
                    </a>
                </div>
                <div class="carousel-inner h-100">
                    {foreach from=$block.states item=state key=key}
                        <div class="carousel-item{if $slideCount == 0} active{/if}" style="{if isset($state.padding_left) && $state.padding_left}padding-left:{$state.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($state.padding_right) && $state.padding_right}padding-right:{$state.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($state.padding_top) && $state.padding_top}padding-top:{$state.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($state.padding_bottom) && $state.padding_bottom}padding-bottom:{$state.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($state.margin_left) && $state.margin_left}margin-left:{$state.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($state.margin_right) && $state.margin_right}margin-right:{$state.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($state.margin_top) && $state.margin_top}margin-top:{$state.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($state.margin_bottom) && $state.margin_bottom}margin-bottom:{$state.margin_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
                            <div class="col-12">
                                {if $state.link}
                                {if $state.obfuscate}
                                {assign var="obflink" value=$state.link|base64_encode}
                                <span class="obflink" data-obflink="{$obflink}">
                                {else}
                                <a href="{$state.link}" title="{$state.name}" {if $state.target_blank} target="_blank"{/if}>
                                {/if}
                                {/if}
                                    <img src="{$state.image.url}" title="{$state.name}" alt="{$state.name}" class="img img-fluid lazyload" loading="lazy">
                                {if $state.link}
                                {if $state.obfuscate}
                                </span>
                                {else}
                                </a>
                                {/if}
                                {/if}
                            </div>
                        </div>
                        {assign var="slideCount" value=$slideCount+1}
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
