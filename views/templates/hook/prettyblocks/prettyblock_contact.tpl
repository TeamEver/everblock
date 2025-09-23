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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}
{capture name='prettyblock_contact_wrapper_style'}
  {$prettyblock_spacing_style}
  {if isset($block.settings.default.bg_color) && $block.settings.default.bg_color}
    background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};
  {/if}
{/capture}
{assign var='prettyblock_contact_wrapper_style' value=$smarty.capture.prettyblock_contact_wrapper_style|trim}

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if $prettyblock_contact_wrapper_style} style="{$prettyblock_contact_wrapper_style}"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}

{prettyblocks_zone zone_name="block-contact-{$block.id_prettyblocks}-before"}
{widget name="contactform"}
{prettyblocks_zone zone_name="block-contact-{$block.id_prettyblocks}-after"}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
