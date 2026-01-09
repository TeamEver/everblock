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
            <span class="h3 fw-bold mb-2">{$block.settings.title|escape:'htmlall':'UTF-8'}</span>
          {/if}
          {if $block.settings.subtitle}
            <p class="text-muted mb-0">{$block.settings.subtitle|escape:'htmlall':'UTF-8'}</p>
          {/if}
        </div>
      {/if}

      {if isset($block.states) && $block.states}
        <div class="prettyblock-faq-list d-flex flex-column gap-3">
          {foreach from=$block.states item=state}
            {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_faq_state_spacing_style'}
            {capture name='prettyblock_faq_badge_style'}
              {if $state.badge_background}
                background-color:{$state.badge_background|escape:'htmlall':'UTF-8'};
              {elseif $block.settings.badge_background}
                background-color:{$block.settings.badge_background|escape:'htmlall':'UTF-8'};
              {/if}
              {if $state.badge_text_color}
                color:{$state.badge_text_color|escape:'htmlall':'UTF-8'};
              {elseif $block.settings.badge_text_color}
                color:{$block.settings.badge_text_color|escape:'htmlall':'UTF-8'};
              {/if}
            {/capture}
            {assign var='prettyblock_faq_badge_style' value=$smarty.capture.prettyblock_faq_badge_style|trim}
            {assign var='prettyblock_faq_badge_text' value=$state.badge_label|default:($state.question|substr:0:1)}
            {assign var='prettyblock_faq_state_style' value=$prettyblock_faq_state_spacing_style|trim}

            <article class="prettyblock-faq-item border border-light-subtle rounded-4 shadow-sm bg-white p-4 p-md-5 d-flex align-items-start gap-3{if $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}"{if $prettyblock_faq_state_style} style="{$prettyblock_faq_state_style}"{/if}>
              <div class="prettyblock-faq-badge flex-shrink-0"{if $prettyblock_faq_badge_style} style="{$prettyblock_faq_badge_style}"{/if} aria-hidden="true">
                {$prettyblock_faq_badge_text|escape:'htmlall':'UTF-8'}
              </div>
              <div class="prettyblock-faq-content">
                {if $state.question}
                  <span class="prettyblock-faq-question h4 fw-semibold mb-3">{$state.question|escape:'htmlall':'UTF-8'}</span>
                {/if}
                {if $state.answer}
                  <div class="prettyblock-faq-answer text-body-secondary mb-3">{$state.answer nofilter}</div>
                {/if}
                {if $state.link_url}
                  <a class="prettyblock-faq-link d-inline-flex align-items-center fw-semibold" href="{$state.link_url|escape:'htmlall':'UTF-8'}" title="{$state.link_label|default:$module->l('Learn more')|escape:'htmlall':'UTF-8'}">
                    <span>{$state.link_label|default:$module->l('Learn more')|escape:'htmlall':'UTF-8'}</span>
                    <span class="ms-2" aria-hidden="true">&rarr;</span>
                  </a>
                {/if}
              </div>
            </article>
          {/foreach}
        </div>
      {/if}

      {if $block.settings.cta_link}
        <div class="mt-4 text-center">
          <a class="btn btn-dark rounded-pill px-4 py-3 prettyblock-faq-cta" href="{$block.settings.cta_link|escape:'htmlall':'UTF-8'}" title="{$block.settings.cta_text|escape:'htmlall':'UTF-8'}">
            {$block.settings.cta_text|escape:'htmlall':'UTF-8'}
          </a>
        </div>
      {/if}

      {if isset($block.states) && $block.states}
        <script type="application/ld+json">
          {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
              {assign var='faq_entity_index' value=0}
              {foreach from=$block.states item=state}
                {if $state.question && $state.answer}
                  {if $faq_entity_index gt 0},{/if}
                  {
                    "@type": "Question",
                    "name": "{$state.question|escape:'javascript'}",
                    "acceptedAnswer": {
                      "@type": "Answer",
                      "text": "{$state.answer|strip_tags|escape:'javascript'}"
                    }
                  }
                  {assign var='faq_entity_index' value=$faq_entity_index+1}
                {/if}
              {/foreach}
            ]
          }
        </script>
      {/if}
    </div>
  </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
