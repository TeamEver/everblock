{*
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if isset($brands) && $brands}
  <section class="featured-brands clearfix mt-3">
    <div class="brands row owl-carousel">
      {foreach from=$brands item=brand}
        <div class="col-md-3 mb-3">
          <a href="{$brand.url|escape:'htmlall':'UTF-8'}" title="{$brand.name|escape:'htmlall':'UTF-8'}" class="brand-link">
            <img src="{$brand.logo|escape:'htmlall':'UTF-8'}" alt="{$brand.name|escape:'htmlall':'UTF-8'}" class="brand-image lazyload" loading="lazy">
            <div class="brand-name">{$brand.name|escape:'htmlall':'UTF-8'}</div>
          </a>
        </div>
      {/foreach}
    </div>
  </section>
{/if}


