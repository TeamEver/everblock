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
{capture name='prettyblock_guided_wrapper_style'}
  {$prettyblock_spacing_style}
  {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}
    background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};
  {/if}
{/capture}
{assign var='prettyblock_guided_wrapper_style' value=$smarty.capture.prettyblock_guided_wrapper_style|trim}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if $prettyblock_guided_wrapper_style} style="{$prettyblock_guided_wrapper_style}"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
    <div class="{if $block.settings.default.container}container{/if}">
      {assign var=totalSteps value=$block.extra.states|@count}
      {if $totalSteps}
      <div class="everblock-guided-progress mb-3">
        <div class="progress">
          <div class="progress-bar" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="{$totalSteps}"></div>
        </div>
        <div class="progress-counter text-end mt-1">0/{$totalSteps}</div>
      </div>
      {/if}
      {foreach from=$block.extra.states item=state name=questions}
        <div id="guided-step-{$block.id_prettyblocks}-{$smarty.foreach.questions.index}" class="everblock-guided-step{if !$smarty.foreach.questions.first} d-none{/if}" data-question="{$state.key|escape:'htmlall':'UTF-8'}">
          <p class="guided-question">{$state.question|escape:'htmlall':'UTF-8'}</p>
          <div class="guided-answers">
            {foreach from=$state.answers item=answer}
              <button type="button" class="btn btn-primary guided-answer" data-value="{$answer.value|escape:'htmlall':'UTF-8'}"{if $answer.link} data-url="{$answer.link|escape:'htmlall':'UTF-8'}"{/if}>{$answer.text|escape:'htmlall':'UTF-8'}</button>
            {/foreach}
          </div>
          <div class="guided-nav mt-3">
            <button type="button" class="btn btn-secondary guided-back d-none">{l s='Back' mod='everblock'}</button>
            <button type="button" class="btn btn-link guided-restart">{l s='Restart' mod='everblock'}</button>
          </div>
        </div>
      {/foreach}
      {if $block.settings.fallback_shortcode}
        <div class="everblock-guided-fallback d-none">
          {$block.settings.fallback_shortcode nofilter}
          <div class="guided-nav mt-3">
            <button type="button" class="btn btn-link guided-restart">{l s='Restart' mod='everblock'}</button>
          </div>
        </div>
      {/if}
    </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
