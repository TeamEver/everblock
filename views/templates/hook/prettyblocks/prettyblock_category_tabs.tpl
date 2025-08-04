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
            <ul class="nav nav-tabs" role="tablist">
                {foreach from=$block.states item=state key=key}
                    <li class="nav-item">
                        <a class="nav-link {if $key == 0}active{/if}" data-toggle="tab" data-bs-toggle="tab" href="#tab-{$block.id_prettyblocks}-{$key}" role="tab" aria-controls="tab-{$block.id_prettyblocks}-{$key}" aria-selected="{if $key == 0}true{else}false{/if}">
                            {$state.name}
                        </a>
                    </li>
                {/foreach}
            </ul>
            <div class="tab-content">
                {foreach from=$block.states item=state key=key}
                    <div class="tab-pane {if $key == 0}active{/if}" id="tab-{$block.id_prettyblocks}-{$key}" role="tabpanel" aria-labelledby="tab-{$block.id_prettyblocks}-{$key}-tab">
                        {assign var=rawProducts value=EverblockTools::getProductsByCategoryId($state.id_category, 0)}
                        {assign var=everPresentProducts value=EverblockTools::everPresentProducts(array_column($rawProducts, 'id_product'), Context::getContext())}
                        {include file='module:everblock/views/templates/hook/ever_presented_products.tpl' everPresentProducts=$everPresentProducts carousel=false shortcodeClass='category_tabs'}
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
