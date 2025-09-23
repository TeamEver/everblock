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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  {if isset($block.states) && $block.states}
    {foreach from=$block.states item=state key=key}
      <div id="block-{$block.id_prettyblocks}-{$key}" class="block-special-event col-12{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{if $state.padding_left}padding-left:{$state.padding_left};{/if}{if $state.padding_right}padding-right:{$state.padding_right};{/if}{if $state.padding_top}padding-top:{$state.padding_top};{/if}{if $state.padding_bottom}padding-bottom:{$state.padding_bottom};{/if}{if $state.margin_left}margin-left:{$state.margin_left};{/if}{if $state.margin_right}margin-right:{$state.margin_right};{/if}{if $state.margin_top}margin-top:{$state.margin_top};{/if}{if $state.margin_bottom}margin-bottom:{$state.margin_bottom};{/if}{if isset($state.background_image.url) && $state.background_image.url}background-image:url('{$state.background_image.url|escape:'htmlall'}');background-size:cover;background-position:center;{/if}{if $state.background_color}background-color:{$state.background_color};{/if}{if $state.text_color}color:{$state.text_color};{/if}">
        <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 w-100" style="z-index:1;">
          {if $state.title}
            <h2 class="mb-3" style="{if $state.text_color}color:{$state.text_color}!important;{/if}">{$state.title nofilter}</h2>
          {/if}
          {if $state.cta_link && $state.cta_text}
            <a href="{$state.cta_link|escape:'htmlall'}" class="btn btn-primary mb-3">{$state.cta_text nofilter}</a>
          {/if}
          {if $state.target_date}
            <div class="everblock-countdown d-flex justify-content-center" data-target="{$state.target_date|escape:'htmlall'}">
              <div class="mx-2 text-center">
                <span class="everblock-countdown-value" data-type="days">00</span>
                <span class="everblock-countdown-label">{l s='Days' mod='everblock'}</span>
              </div>
              <div class="mx-2 text-center">
                <span class="everblock-countdown-value" data-type="hours">00</span>
                <span class="everblock-countdown-label">{l s='Hours' mod='everblock'}</span>
              </div>
              <div class="mx-2 text-center">
                <span class="everblock-countdown-value" data-type="minutes">00</span>
                <span class="everblock-countdown-label">{l s='Minutes' mod='everblock'}</span>
              </div>
              <div class="mx-2 text-center">
                <span class="everblock-countdown-value" data-type="seconds">00</span>
                <span class="everblock-countdown-label">{l s='Seconds' mod='everblock'}</span>
              </div>
            </div>
          {/if}
          {if isset($block.extra.products[$key]) && $block.extra.products[$key]}
            <div class="mt-3 w-100">
              {include file="module:everblock/views/templates/hook/ever_presented_products.tpl" everPresentProducts=$block.extra.products[$key] shortcodeClass='special-event-products'}
            </div>
          {/if}
        </div>
      </div>
    {/foreach}
  {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
