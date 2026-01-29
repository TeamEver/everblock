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

{assign var='heroeLoop' value=(isset($block.settings.loop) && $block.settings.loop)}
{assign var='showArrows' value=(isset($block.settings.show_arrows) && $block.settings.show_arrows)}
{assign var='visibleStatesCount' value=0}

{if isset($block.states) && $block.states}
  {foreach from=$block.states item=state}
    {assign var='isStateVisible' value=true}
    {if (not isset($state.image.url) or $state.image.url eq '') and (not isset($state.image_mobile.url) or $state.image_mobile.url eq '')}
      {assign var='isStateVisible' value=false}
    {/if}
    {if $isStateVisible}
      {assign var='visibleStatesCount' value=$visibleStatesCount+1}
    {/if}
  {/foreach}
{/if}

{if $visibleStatesCount > 0}
  {assign var='lastVisibleIndex' value=$visibleStatesCount-1}
  <section id="block-{$block.id_prettyblocks}" class="everblock-heroe-carousel{$prettyblock_visibility_class}" data-loop="{if $heroeLoop}1{else}0{/if}" data-show-arrows="{if $showArrows}1{else}0{/if}" style="{$prettyblock_spacing_style}{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
    <div class="heroe-carousel-track">
      {assign var='slideIndex' value=0}
      {foreach from=$block.states item=state key=key}
        {assign var='isStateVisible' value=true}
        {if (not isset($state.image.url) or $state.image.url eq '') and (not isset($state.image_mobile.url) or $state.image_mobile.url eq '')}
          {assign var='isStateVisible' value=false}
        {/if}
        {if $isStateVisible}
          {assign var='desktopImageUrl' value=$state.image.url|default:''}
          {assign var='desktopImageJpgUrl' value=$desktopImageUrl|replace:'.webp':'.jpg'}
          {assign var='mobileImageUrl' value=$state.image_mobile.url|default:''}
          {assign var='mobileImageJpgUrl' value=$mobileImageUrl|replace:'.webp':'.jpg'}
          {if $mobileImageUrl eq '' && $desktopImageUrl ne ''}
            {assign var='mobileImageUrl' value=$desktopImageUrl}
            {assign var='mobileImageJpgUrl' value=$desktopImageJpgUrl}
          {/if}
          {assign var='fallbackImageUrl' value=$desktopImageUrl|default:$mobileImageUrl}
          {assign var='fallbackImageJpgUrl' value=$desktopImageJpgUrl|default:$mobileImageJpgUrl}
          {assign var='fallbackWidth' value=''}
          {assign var='fallbackHeight' value=''}
          {if isset($state.image.width) && $state.image.width > 0}
            {assign var='fallbackWidth' value=$state.image.width|intval}
          {elseif isset($state.image_mobile.width) && $state.image_mobile.width > 0}
            {assign var='fallbackWidth' value=$state.image_mobile.width|intval}
          {/if}
          {if isset($state.image.height) && $state.image.height > 0}
            {assign var='fallbackHeight' value=$state.image.height|intval}
          {elseif isset($state.image_mobile.height) && $state.image_mobile.height > 0}
            {assign var='fallbackHeight' value=$state.image_mobile.height|intval}
          {/if}
          <article class="heroe-slide{if $slideIndex == 0} is-active{elseif $slideIndex == 1} is-next{elseif $slideIndex == $lastVisibleIndex} is-prev{/if}" data-slide-index="{$slideIndex}">
            <div class="heroe-media">
              <picture>
                {if $mobileImageUrl ne ''}
                  <source media="(max-width: 767.98px)" srcset="{$mobileImageUrl}" type="image/webp">
                  <source media="(max-width: 767.98px)" srcset="{$mobileImageJpgUrl}" type="image/jpeg">
                {/if}
                {if $desktopImageUrl ne ''}
                  <source media="(min-width: 768px)" srcset="{$desktopImageUrl}" type="image/webp">
                  <source media="(min-width: 768px)" srcset="{$desktopImageJpgUrl}" type="image/jpeg">
                {/if}
                <img src="{$fallbackImageJpgUrl}" alt="{$state.title|default:''|escape:'htmlall':'UTF-8'}"{if $fallbackWidth ne ''} width="{$fallbackWidth}"{/if}{if $fallbackHeight ne ''} height="{$fallbackHeight}"{/if}>
              </picture>
            </div>
            <div class="heroe-content">
              {if $state.title}
                <h2 class="heroe-title">{$state.title|escape:'htmlall':'UTF-8'}</h2>
              {/if}
              {if $state.content}
                <div class="heroe-text">{$state.content nofilter}</div>
              {/if}
              {if $state.cta_label && $state.cta_link}
                <a class="heroe-cta" href="{$state.cta_link|escape:'htmlall':'UTF-8'}" title="{$state.cta_label|escape:'htmlall':'UTF-8'}">{$state.cta_label|escape:'htmlall':'UTF-8'}</a>
              {/if}
            </div>
          </article>
          {assign var='slideIndex' value=$slideIndex+1}
        {/if}
      {/foreach}
    </div>
    {if $showArrows && $visibleStatesCount > 1}
      <button class="heroe-nav heroe-prev" type="button" aria-label="{l s='Previous' mod='everblock'}"></button>
      <button class="heroe-nav heroe-next" type="button" aria-label="{l s='Next' mod='everblock'}"></button>
    {/if}
  </section>
{/if}
