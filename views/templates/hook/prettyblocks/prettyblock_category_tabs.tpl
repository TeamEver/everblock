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

<div id="block-{$block.id_prettyblocks}" class="prettyblock-category-tabs{if $block.settings.default.force_full_width} container-fluid px-0 mx-0{elseif $block.settings.default.container} container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<div class="mt-2{if $block.settings.default.container} container{/if}"  style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    <div class="mt-2 col-12 d-flex justify-content-center text-center">
        <div class="tab-container">
            <ul class="nav nav-tabs justify-content-center" role="tablist">
                {foreach from=$block.states item=state key=key name=categorytabs}
                    {assign var='tabId' value="tab-"|cat:$block.id_prettyblocks|cat:"-"|cat:$key}
                    <li class="nav-item">
                        <a class="nav-link {if $smarty.foreach.categorytabs.first}active{/if}" id="{$tabId}-tab" data-toggle="tab" data-bs-toggle="tab" data-bs-target="#{$tabId}" href="#{$tabId}" role="tab" aria-controls="{$tabId}" aria-selected="{if $smarty.foreach.categorytabs.first}true{else}false{/if}">
                            {$state.name}
                        </a>
                    </li>
                {/foreach}
            </ul>
            <div class="tab-content">
                {foreach from=$block.states item=state key=key name=categorytabs}
                    {assign var='tabId' value="tab-"|cat:$block.id_prettyblocks|cat:"-"|cat:$key}
                    {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
                    <div class="tab-pane {if $smarty.foreach.categorytabs.first}show active{/if}{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" id="{$tabId}" role="tabpanel" aria-labelledby="{$tabId}-tab" style="{$prettyblock_state_spacing_style}{if isset($state.background_color) && $state.background_color}background-color:{$state.background_color|escape:'htmlall':'UTF-8'};{/if}{if isset($state.text_color) && $state.text_color}color:{$state.text_color|escape:'htmlall':'UTF-8'};{/if}">
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

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
