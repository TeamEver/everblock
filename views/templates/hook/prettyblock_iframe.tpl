{*
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    <div class="everblock {$block.settings.css_class|escape:'htmlall':'UTF-8'} {$block.settings.bootstrap_class|escape:'htmlall':'UTF-8'}" {if isset($block.settings.bg_color) && $block.settings.bg_color} style="background-color:{$block.settings.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
      {if $block.settings.iframe_source == 'youtube'}
      <iframe width="{if isset($block.settings.width) && $block.settings.width}{$block.settings.width}{else}100%{/if}" height="{if isset($block.settings.height) && $block.settings.height}{$block.settings.height}{else}315{/if}" src="{$block.settings.iframe_link}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
      {elseif $block.settings.iframe_source == 'vimeo'}
      <iframe src="https://player.vimeo.com/video/' . $matches[1] . '?color=ffffff&title=0&byline=0&portrait=0" width="{if isset($block.settings.width) && $block.settings.width}{$block.settings.width}{else}100%{/if}" height="{if isset($block.settings.height) && $block.settings.height}{$block.settings.height}{else}360{/if}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
      {elseif $block.settings.iframe_source == 'dailymotion'}
      <iframe frameborder="0" width="{if isset($block.settings.width) && $block.settings.width}{$block.settings.width}{else}100%{/if}" height="{if isset($block.settings.height) && $block.settings.height}{$block.settings.height}{else}270{/if}" src="{$block.settings.iframe_link}" allowfullscreen></iframe>
      {elseif $block.settings.iframe_source == 'vidyard'}
      <iframe src="{$block.settings.iframe_link}" width="{if isset($block.settings.width) && $block.settings.width}{$block.settings.width}{else}100%{/if}" height="{if isset($block.settings.height) && $block.settings.height}{$block.settings.height}{else}360{/if}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
      {/if}
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->