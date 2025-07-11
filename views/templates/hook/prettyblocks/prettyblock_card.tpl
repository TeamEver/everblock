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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
    {if isset($block.states) && $block.states}
      {foreach from=$block.states item=state key=key}
        <div class="col-md-4 mb-3 {if $state.css_class}{$state.css_class|escape:'htmlall'}{/if}">
          <div class="card h-100">
            {if isset($state.image.url) && $state.image.url}
              <picture>
                <source srcset="{$state.image.url}" type="image/webp">
                <source srcset="{$state.image.url|replace:'.webp':'.jpg'}" type="image/jpeg">
                <img src="{$state.image.url|replace:'.webp':'.jpg'}" class="card-img-top img img-fluid lazyload"
                     loading="lazy"{if $state.image_width} width="{$state.image_width|escape:'htmlall'}"{/if}{if $state.image_height} height="{$state.image_height|escape:'htmlall'}"{/if}
                     alt="{if $state.alt}{$state.alt|escape:'htmlall'}{else}{$state.title|escape:'htmlall'}{/if}" title="{$state.title|escape:'htmlall'}">
              </picture>
            {/if}
            <div class="card-body">
              {if $state.title}<h5 class="card-title">{$state.title|escape:'htmlall'}</h5>{/if}
              {if $state.content}<div class="card-text">{$state.content nofilter}</div>{/if}
            </div>
            {if $state.button_link && $state.button_text}
              <div class="card-footer bg-transparent border-0">
                <a href="{$state.button_link|escape:'htmlall'}" class="btn btn-primary" title="{$state.title|escape:'htmlall'}">
                  {$state.button_text|escape:'htmlall'}
                </a>
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
