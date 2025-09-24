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
<div class="{if $block.settings.default.container}container{/if}"  style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    <div class="accordion everblock-accordeon" id="prettyAccordion-{$block.id_prettyblocks}">
      {assign var="counter" value=1}
      {foreach from=$block.states item=$state key=$key}
        <div class="card">
          {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}

          <div class="card-header" id="heading{$counter}"  style="{$prettyblock_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
            <span class="mb-0 h2">
              <button class="btn btn-link btn-block w-100 text-left" type="button" data-toggle="collapse" data-target="#everblock-{$block.id_prettyblocks}-collapse{$counter}" data-bs-toggle="collapse" data-bs-target="#everblock-{$block.id_prettyblocks}-collapse{$counter}" aria-expanded="true" aria-controls="everblock-{$block.id_prettyblocks}-collapse{$counter}">
                {if isset($state.title_color) && $state.title_color}
                <span style="background-color:{$state.title_color};">
                {/if}
                {$state.name}
                {if isset($state.title_color) && $state.title_color}
                </span>
                {/if}
              </button>
            </span>
          </div>
          <div id="everblock-{$block.id_prettyblocks}-collapse{$counter}" class="collapse {if $key == 1}show{/if}" aria-labelledby="heading{$counter}" data-parent="#prettyAccordion-{$block.id_prettyblocks}">
            <div class="card-body">
              {$state.content nofilter}
            </div>
          </div>
        </div>
        {assign var="counter" value=$counter+1}
      {/foreach}
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
