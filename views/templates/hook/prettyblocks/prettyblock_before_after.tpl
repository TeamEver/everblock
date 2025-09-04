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
{if isset($block.states) && $block.states}
<link rel="stylesheet" href="{$module_dir}views/css/before-after.css">
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  <section class="everblock-before-after-wrapper w-100">
    {foreach from=$block.states item=state key=key}
      <div id="block-{$block.id_prettyblocks}-{$key}" class="everblock-before-after position-relative overflow-hidden">
        <picture>
          <source srcset="{$state.image_before.url}" type="image/webp">
          <source srcset="{$state.image_before.url|replace:'.webp':'.jpg'}" type="image/jpeg">
          <img src="{$state.image_before.url|replace:'.webp':'.jpg'}" alt="{$state.label_before|escape:'htmlall':'UTF-8'}" class="img img-fluid" loading="lazy">
        </picture>
        <div class="eba-after">
          <picture>
            <source srcset="{$state.image_after.url}" type="image/webp">
            <source srcset="{$state.image_after.url|replace:'.webp':'.jpg'}" type="image/jpeg">
            <img src="{$state.image_after.url|replace:'.webp':'.jpg'}" alt="{$state.label_after|escape:'htmlall':'UTF-8'}" class="img img-fluid" loading="lazy">
          </picture>
        </div>
        <input type="range" min="0" max="100" value="50" class="eba-range">
        {if $state.label_before}
          <span class="eba-label eba-label-before">{$state.label_before|escape:'htmlall':'UTF-8'}</span>
        {/if}
        {if $state.label_after}
          <span class="eba-label eba-label-after">{$state.label_after|escape:'htmlall':'UTF-8'}</span>
        {/if}
      </div>
    {/foreach}
  </section>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
<script src="{$module_dir}views/js/before-after.js"></script>
{/if}
