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
    <div class="col-12 col-lg-4 pb-toc-summary">
      {if isset($block.settings.title) && $block.settings.title}
        <span class="h2 pb-toc-title">{$block.settings.title|escape:'htmlall'}</span>
      {/if}
      <ul class="list-unstyled">
        {foreach from=$block.states item=state}
          <li><a href="#{$state.anchor|escape:'htmlall'}">{$state.title|escape:'htmlall'}</a></li>
        {/foreach}
      </ul>
    </div>
    <div class="col-12 col-lg-8 pb-toc-content">
      {foreach from=$block.states item=state}
        <div id="{$state.anchor|escape:'htmlall'}" class="pb-toc-section">
          {$state.content nofilter}
        </div>
      {/foreach}
    </div>
  {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<!-- /Module Ever Block -->

