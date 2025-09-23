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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  {if isset($block.settings.title) && $block.settings.title}
    <h2 class="pb-podcasts-title">{$block.settings.title|escape:'htmlall'}</h2>
  {/if}
  {if isset($block.states) && $block.states}
    <div class="everblock-podcasts">
      {foreach from=$block.states item=state}
        <div class="podcast-item">
          {if isset($state.cover_image.url) && $state.cover_image.url}
            <img src="{$state.cover_image.url|escape:'htmlall'}" alt="{$state.episode_title|escape:'htmlall'}" class="podcast-cover" loading="lazy" />
          {/if}
          {if $state.episode_title}
            <h3 class="podcast-episode-title">{$state.episode_title|escape:'htmlall'}</h3>
          {/if}
          {if $state.duration}
            <span class="podcast-duration">{$state.duration|escape:'htmlall'}</span>
          {/if}
          {if $state.description}
            <p class="podcast-description">{$state.description nofilter}</p>
          {/if}
          {if $state.audio_url}
            <audio controls src="{$state.audio_url|escape:'htmlall'}" class="podcast-audio">
              Your browser does not support the audio element.
            </audio>
          {/if}
        </div>
      {/foreach}
    </div>
  {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<!-- /Module Ever Block -->
