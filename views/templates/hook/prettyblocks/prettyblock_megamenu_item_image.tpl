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
{if isset($from_parent) && $from_parent && (!isset($block.settings.active) || $block.settings.active)}
  {assign var='obfme_class' value=''}
  {if $page.page_name|default:'' != 'index'}
    {assign var='obfme_class' value=' obfme'}
  {/if}
  {assign var='image_value' value=$block.settings.image|default:''}
  {assign var='image_url' value=''}
  {assign var='image_width' value=$block.settings.image_width|default:400|intval}
  {assign var='image_height' value=$block.settings.image_height|default:300|intval}
  {if $image_width <= 0}
    {assign var='image_width' value=400}
  {/if}
  {if $image_height <= 0}
    {assign var='image_height' value=300}
  {/if}
  {if is_array($image_value)}
    {if isset($image_value.url)}
      {assign var='image_url' value=$image_value.url}
    {elseif isset($image_value.value.url)}
      {assign var='image_url' value=$image_value.value.url}
    {/if}
  {elseif $image_value}
    {assign var='image_url' value=$image_value}
  {/if}
  {assign var='image_title' value=$block.settings.title|default:$block.settings.cta_label|default:$block.settings.url|default:'Menu image'}
  {if $image_url}
    <div class="everblock-megamenu-image mb-3">
      <a href="{$block.settings.url|escape:'htmlall':'UTF-8'}" class="text-decoration-none d-block{$obfme_class}" title="{$image_title|escape:'htmlall':'UTF-8'}">
        <img src="{$image_url|escape:'htmlall':'UTF-8'}" alt="{$block.settings.title|escape:'htmlall':'UTF-8'}" width="{$image_width}" height="{$image_height}" class="img-fluid rounded">
        {if $block.settings.title}
          <div class="mt-2 fw-semibold">{$block.settings.title|escape:'htmlall':'UTF-8'}</div>
        {/if}
        {if $block.settings.cta_label}
          <span class="btn btn-sm btn-outline-primary mt-2">{$block.settings.cta_label|escape:'htmlall':'UTF-8'}</span>
        {/if}
      </a>
    </div>
  {/if}
{/if}
