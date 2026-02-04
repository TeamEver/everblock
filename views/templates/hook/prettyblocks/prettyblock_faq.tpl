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

{capture name='prettyblock_faq_wrapper_style'}
  {$prettyblock_spacing_style}
  {if isset($block.settings.background_color) && $block.settings.background_color}
    background-color:{$block.settings.background_color|escape:'htmlall':'UTF-8'};
  {/if}
{/capture}
{assign var='prettyblock_faq_wrapper_style' value=$smarty.capture.prettyblock_faq_wrapper_style|trim}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  <div class="{if $block.settings.default.container}container{/if} py-4 py-md-5"{if $prettyblock_faq_wrapper_style} style="{$prettyblock_faq_wrapper_style}"{/if}>
    <div class="prettyblock-faq">
      {if $block.settings.title || $block.settings.subtitle}
        <div class="prettyblock-faq-header mb-4">
          {if $block.settings.title}
            <span class="prettyblock-faq-title">{$block.settings.title|escape:'htmlall':'UTF-8'}</span>
          {/if}
          {if $block.settings.subtitle}
            <p class="prettyblock-faq-subtitle">{$block.settings.subtitle|escape:'htmlall':'UTF-8'}</p>
          {/if}
        </div>
      {/if}

      {if isset($block.states) && $block.states}
        <div class="prettyblock-faq-list">
          {foreach from=$block.states item=state name=faq_items}
            {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_faq_state_spacing_style'}
            {assign var='prettyblock_faq_state_style' value=$prettyblock_faq_state_spacing_style|trim}
            {assign var='prettyblock_faq_question' value=$state.question|default:''}
            {assign var='prettyblock_faq_question' value=$prettyblock_faq_question|regex_replace:"/\\?\\s*$/" : ""}
            {assign var='prettyblock_faq_question' value=$prettyblock_faq_question|escape:'htmlall':'UTF-8'}
            {assign var='prettyblock_faq_highlight_words' value=''}
            {if $state.highlight_words}
              {assign var='prettyblock_faq_highlight_words' value=","|explode:$state.highlight_words}
            {/if}
            {if $prettyblock_faq_highlight_words}
              {foreach from=$prettyblock_faq_highlight_words item=highlight_word}
                {assign var='highlight_word' value=$highlight_word|trim|escape:'htmlall':'UTF-8'}
                {if $highlight_word}
                  {assign var='prettyblock_faq_question' value=$prettyblock_faq_question|replace:$highlight_word:"<span class=\"highlight\">`$highlight_word`</span>"}
                {/if}
              {/foreach}
            {/if}

            <article class="prettyblock-faq-item{if $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}"{if $prettyblock_faq_state_style} style="{$prettyblock_faq_state_style}"{/if}>
              {if $state.question}
                <span class="h2 prettyblock-faq-question">
                  {$prettyblock_faq_question nofilter}
                  <span class="question-mark" aria-hidden="true">?</span>
                </span>
              {/if}
              {if $state.answer}
                <div class="prettyblock-faq-answer">{$state.answer nofilter}</div>
              {/if}
              {if $state.link_url}
                <a class="prettyblock-faq-link" href="{$state.link_url|escape:'htmlall':'UTF-8'}" title="{$state.link_label|default:$module->l('Learn more')|escape:'htmlall':'UTF-8'}">
                  {$state.link_label|default:$module->l('Learn more')|escape:'htmlall':'UTF-8'}
                </a>
              {/if}
            </article>
            {if !$smarty.foreach.faq_items.last}
              <hr class="prettyblock-faq-separator" />
            {/if}
          {/foreach}
        </div>
      {/if}

      {if $block.settings.cta_link}
        <div class="prettyblock-faq-cta-wrapper">
          <a class="prettyblock-faq-cta" href="{$block.settings.cta_link|escape:'htmlall':'UTF-8'}" title="{$block.settings.cta_text|escape:'htmlall':'UTF-8'}">
            <span class="prettyblock-faq-cta-text">{$block.settings.cta_text|escape:'htmlall':'UTF-8'}</span>
            <span class="prettyblock-faq-cta-icon" aria-hidden="true">&rarr;</span>
          </a>
        </div>
      {/if}
    </div>
  </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
