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
<div class="mt-2{if $block.settings.default.container} container{/if}"  style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
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
                        {$state.content nofilter}
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
