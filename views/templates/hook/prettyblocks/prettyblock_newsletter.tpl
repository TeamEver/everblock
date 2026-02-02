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
{capture name='prettyblock_newsletter_wrapper_style'}
  {$prettyblock_spacing_style}
  {if isset($block.settings.background_gradient) && $block.settings.background_gradient}
    background:{$block.settings.background_gradient|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.text_color) && $block.settings.text_color}
    color:{$block.settings.text_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.subtitle_color) && $block.settings.subtitle_color}
    --prettyblock-newsletter-subtitle-color:{$block.settings.subtitle_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.legal_text_color) && $block.settings.legal_text_color}
    --prettyblock-newsletter-legal-color:{$block.settings.legal_text_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.message_color) && $block.settings.message_color}
    --prettyblock-newsletter-message-color:{$block.settings.message_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.message_success_color) && $block.settings.message_success_color}
    --prettyblock-newsletter-success-color:{$block.settings.message_success_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.message_error_color) && $block.settings.message_error_color}
    --prettyblock-newsletter-error-color:{$block.settings.message_error_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.input_background_color) && $block.settings.input_background_color}
    --prettyblock-newsletter-input-bg:{$block.settings.input_background_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.input_border_color) && $block.settings.input_border_color}
    --prettyblock-newsletter-input-border:{$block.settings.input_border_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.input_text_color) && $block.settings.input_text_color}
    --prettyblock-newsletter-input-color:{$block.settings.input_text_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.input_placeholder_color) && $block.settings.input_placeholder_color}
    --prettyblock-newsletter-placeholder-color:{$block.settings.input_placeholder_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.input_focus_background_color) && $block.settings.input_focus_background_color}
    --prettyblock-newsletter-input-focus-bg:{$block.settings.input_focus_background_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.input_focus_border_color) && $block.settings.input_focus_border_color}
    --prettyblock-newsletter-input-focus-border:{$block.settings.input_focus_border_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.input_focus_shadow_color) && $block.settings.input_focus_shadow_color}
    --prettyblock-newsletter-input-focus-shadow:{$block.settings.input_focus_shadow_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.button_background_color) && $block.settings.button_background_color}
    --prettyblock-newsletter-button-bg:{$block.settings.button_background_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.button_text_color) && $block.settings.button_text_color}
    --prettyblock-newsletter-button-color:{$block.settings.button_text_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.button_border_color) && $block.settings.button_border_color}
    --prettyblock-newsletter-button-border:1px solid {$block.settings.button_border_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.button_hover_background_color) && $block.settings.button_hover_background_color}
    --prettyblock-newsletter-button-hover-bg:{$block.settings.button_hover_background_color|escape:'htmlall':'UTF-8'};
  {/if}
  {if isset($block.settings.button_hover_text_color) && $block.settings.button_hover_text_color}
    --prettyblock-newsletter-button-hover-color:{$block.settings.button_hover_text_color|escape:'htmlall':'UTF-8'};
  {/if}
{/capture}
{assign var='prettyblock_newsletter_wrapper_style' value=$smarty.capture.prettyblock_newsletter_wrapper_style|trim}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.container}container{/if}{$prettyblock_visibility_class}">
  <div class="prettyblock-newsletter{if $block.settings.css_class} {$block.settings.css_class|escape:'htmlall':'UTF-8'}{/if}"{if $prettyblock_newsletter_wrapper_style} style="{$prettyblock_newsletter_wrapper_style}"{/if}>
    <div class="prettyblock-newsletter__inner">
      {if $block.settings.title}
        <h2 class="prettyblock-newsletter__title">{$block.settings.title|escape:'htmlall':'UTF-8'}</h2>
      {/if}
      {if $block.settings.subtitle}
        <p class="prettyblock-newsletter__subtitle">{$block.settings.subtitle|escape:'htmlall':'UTF-8'}</p>
      {/if}

      {*
        Native newsletter flow: submit to ps_emailsubscription controller via AJAX.
        The JS uses psemailsubscription_subscription injected by the module.
      *}
      <form class="prettyblock-newsletter__form" data-newsletter-form method="post" action="{$link->getModuleLink('ps_emailsubscription', 'subscription')|escape:'htmlall':'UTF-8'}">
        <div class="prettyblock-newsletter__input-group">
          <label class="sr-only" for="prettyblock-newsletter-email-{$block.id_prettyblocks}">
            {l s='Email address' mod='everblock'}
          </label>
          <input
            id="prettyblock-newsletter-email-{$block.id_prettyblocks}"
            class="form-control prettyblock-newsletter__input"
            type="email"
            name="email"
            required
            autocomplete="email"
            placeholder="{if $block.settings.placeholder}{$block.settings.placeholder|escape:'htmlall':'UTF-8'}{else}{l s='Your email' mod='everblock'}{/if}"
          >
          <input type="hidden" name="action" value="0">
          <input type="hidden" name="submitNewsletter" value="1">
          <input type="hidden" name="ajax" value="1">
          <input type="hidden" name="blockHookName" value="{if $block.settings.block_hook_name}{$block.settings.block_hook_name|escape:'htmlall':'UTF-8'}{else}displayFooterBefore{/if}">
          <button class="btn prettyblock-newsletter__submit" type="submit">
            {if $block.settings.button_label}{$block.settings.button_label|escape:'htmlall':'UTF-8'}{else}{l s='OK' mod='everblock'}{/if}
          </button>
        </div>
        {if $block.settings.legal_text}
          <p class="prettyblock-newsletter__legal">{$block.settings.legal_text nofilter}</p>
        {/if}
        <p class="prettyblock-newsletter__message" data-newsletter-message role="status" aria-live="polite"></p>
      </form>
    </div>
  </div>
</div>
