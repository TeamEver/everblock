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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  {if isset($block.settings.title) && $block.settings.title}
    <span class="h2 pb-downloads-title">{$block.settings.title|escape:'htmlall'}</span>
  {/if}
  {if isset($block.states) && $block.states}
    <ul class="pb-downloads-list">
      {foreach from=$block.states item=state}
        <li class="pb-download-item d-flex align-items-start">
          {if isset($state.icon.url) && $state.icon.url}
            <img src="{$state.icon.url|escape:'htmlall'}" alt="{$state.title|escape:'htmlall'}" class="pb-download-icon"/>
          {/if}
          <div>
            <a href="{$state.file.url|escape:'htmlall'}" class="pb-download-link" download>{$state.title|escape:'htmlall'}</a>
            {if $state.description}
              <p class="pb-download-description">{$state.description|escape:'htmlall'}</p>
            {/if}
          </div>
        </li>
      {/foreach}
    </ul>
  {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<!-- /Module Ever Block -->
