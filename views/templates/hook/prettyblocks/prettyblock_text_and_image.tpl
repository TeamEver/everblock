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
    {foreach from=$block.states item=state key=key}
    <div class="row" {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
    {if $state.link}
    {if $state.obfuscate}
    {assign var="obflink" value=$state.link|base64_encode}
    <span class="obflink" data-obflink="{$obflink}">
    {else}
    <a href="{$state.link}" title="{$state.name}"{if $state.target_blank} target="_blank"{/if}>
    {/if}
    {/if}
    <div class="col-12 col-md-6 {$state.css_class|escape:'htmlall':'UTF-8'} {$state.bootstrap_class|escape:'htmlall':'UTF-8'}">
      {if $state.order == 1}
      <img src="{$state.image.url}" alt="{$state.name}" title="{$state.name}" class="img img-fluid rounded mx-auto d-block"{if $state.image.width > 0} width="{$state.image.width}"{/if}{if $state.image.height > 0} height="{$state.image.height}"{/if}>
      {else}
      {$state.content nofilter}
      {/if}
    </div>
    <div class="col-12 col-md-6">
      {if $state.order == 1}
      {$state.content nofilter}
      {else}
      <img src="{$state.image.url}" alt="{$state.name}" title="{$state.name}" class="img img-fluid rounded mx-auto d-block"{if $state.image.width > 0} width="{$state.image.width}"{/if}{if $state.image.height > 0} height="{$state.image.height}"{/if}>
      {/if}
    </div>
    {if $state.link}
    {if $state.obfuscate}
    </span>
    {else}
    </a>
    {/if}
    {/if}
    </div>
    {/foreach}
</div>
<!-- /Module Ever Block -->