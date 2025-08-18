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
<div id="block-{$block.id_prettyblocks}" class="prettyblock-cover">
{if isset($block.states) && $block.states}
  {assign var='use_slider' value=(isset($block.settings.slider) && $block.settings.slider && $block.states|@count > 1)}
  {if $use_slider}
    <div class="ever-cover-carousel">
      {foreach from=$block.states item=state key=key}
        <div id="block-{$block.id_prettyblocks}-{$key}"
             class="prettyblock-cover-item{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if} text-center"
             style="{if isset($state.background_image.url) && $state.background_image.url}background-image: url('{$state.background_image.url|escape:'htmlall'}'); background-size: cover; background-position: center;{/if}">
          {if $state.title}
            <{$state.title_tag|default:'h2'}>{$state.title|escape:'htmlall'}</{$state.title_tag|default:'h2'}>
          {/if}
          {if $state.content}
            <div class="prettyblock-cover-content">
              {$state.content nofilter}
            </div>
          {/if}
          {if ($state.btn1_text && $state.btn1_link) || ($state.btn2_text && $state.btn2_link)}
            <div class="mt-3 d-flex justify-content-center gap-2">
              {if $state.btn1_text && $state.btn1_link}
                <a href="{$state.btn1_link|escape:'htmlall'}" class="btn btn-{$state.btn1_type|escape:'htmlall'}">{$state.btn1_text|escape:'htmlall'}</a>
              {/if}
              {if $state.btn2_text && $state.btn2_link}
                <a href="{$state.btn2_link|escape:'htmlall'}" class="btn btn-{$state.btn2_type|escape:'htmlall'}">{$state.btn2_text|escape:'htmlall'}</a>
              {/if}
            </div>
          {/if}
        </div>
      {/foreach}
    </div>
  {else}
    {foreach from=$block.states item=state key=key}
      <div id="block-{$block.id_prettyblocks}-{$key}"
           class="prettyblock-cover-item{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if} text-center"
           style="{if isset($state.background_image.url) && $state.background_image.url}background-image: url('{$state.background_image.url|escape:'htmlall'}'); background-size: cover; background-position: center;{/if}">
        {if $state.title}
          <{$state.title_tag|default:'h2'}>{$state.title|escape:'htmlall'}</{$state.title_tag|default:'h2'}>
        {/if}
        {if $state.content}
          <div class="prettyblock-cover-content">
            {$state.content nofilter}
          </div>
        {/if}
        {if ($state.btn1_text && $state.btn1_link) || ($state.btn2_text && $state.btn2_link)}
          <div class="mt-3 d-flex justify-content-center gap-2">
            {if $state.btn1_text && $state.btn1_link}
              <a href="{$state.btn1_link|escape:'htmlall'}" class="btn btn-{$state.btn1_type|escape:'htmlall'}">{$state.btn1_text|escape:'htmlall'}</a>
            {/if}
            {if $state.btn2_text && $state.btn2_link}
              <a href="{$state.btn2_link|escape:'htmlall'}" class="btn btn-{$state.btn2_type|escape:'htmlall'}">{$state.btn2_text|escape:'htmlall'}</a>
            {/if}
          </div>
        {/if}
      </div>
    {/foreach}
  {/if}
{/if}
</div>
