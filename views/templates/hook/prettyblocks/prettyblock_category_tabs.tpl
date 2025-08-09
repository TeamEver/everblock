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
    <div class="mt-2 col-12 d-flex justify-content-center text-center">
        <div class="tab-container">
            <ul class="nav nav-tabs justify-content-center" role="tablist">
                {foreach from=$block.states item=state key=key name=categorytabs}
                    <li class="nav-item">
                        <a class="nav-link {if $smarty.foreach.categorytabs.first}active{/if}" data-toggle="tab" data-bs-toggle="tab" href="#tab-{$block.id_prettyblocks}-{$key}" role="tab" aria-controls="tab-{$block.id_prettyblocks}-{$key}" aria-selected="{if $smarty.foreach.categorytabs.first}true{else}false{/if}">
                            {$state.name}
                        </a>
                    </li>
                {/foreach}
            </ul>
            <div class="tab-content">
                {foreach from=$block.states item=state key=key name=categorytabs}
                    <div class="tab-pane {if $smarty.foreach.categorytabs.first}show active{/if}" id="tab-{$block.id_prettyblocks}-{$key}" role="tabpanel" aria-labelledby="tab-{$block.id_prettyblocks}-{$key}-tab">
                        {if isset($block.extra.products[$key]) && $block.extra.products[$key]}
                        <section class="ever-featured-products featured-products clearfix mt-3 category_tabs">
                            <div class="products row">
                                {hook h='displayBeforeProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                                {foreach from=$block.extra.products[$key] item=product}
                                    {include file="catalog/_partials/miniatures/product.tpl" product=$product productClasses="col-xs-12 col-sm-6 col-lg-4 col-xl-3"}
                                {/foreach}
                                {hook h='displayAfterProductMiniature' products=$block.extra.products[$key] origin='category_tabs' page_name=$page.page_name}
                            </div>
                        </section>
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
