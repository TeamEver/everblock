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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}w-100 px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
{if $block.extra.product}
  {assign var="product" value=$block.extra.product}
  <div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
      <div class="row align-items-center border rounded p-4 shadow-sm" style="background-color: #fff;">
    {/if}

      <div class="col-12 col-md-5 mb-3 mb-md-0 text-center">
        <img src="{$product.cover.bySize.large_default.url|replace:'.webp':'.jpg'}"
             alt="{$product.name|escape:'htmlall'}"
             class="img-fluid w-100"
             style="max-width: 260px"
             loading="lazy">
      </div>

      <div class="col-12 col-md-7 text-start">

        <div class="d-inline-block px-3 py-1 mb-4 rounded-pill bg-light fw-bold text-dark" style="font-size: 1rem;">
          ❤️ {$block.settings.badge_text|escape:'htmlall':'UTF-8'}
        </div>

        <div class="h5 fw-bold mb-2">{$product.name}</div>

        <p class="fst-italic mb-3">
          {$block.settings.custom_text nofilter}
        </p>

        <a href="{$product.url}" class="btn fw-bold rounded-pill px-4 py-2"
           style="background-color: #C6F219; color: #000; border: none;">
          {l s='See the product' mod='everblock'}
        </a>
      </div>

    {if $block.settings.default.container}</div>{/if}
  </div>
{/if}

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
