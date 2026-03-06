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

<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<!-- Module Ever Block -->
<div class="mt-2 {if $block.settings.default.container} container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
    {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}

    <div id="testimonial-{$block.id_prettyblocks}" class="everblock-testimonial" style="{$prettyblock_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
        <div class="container">
            <div class="text-center row">
                <div class="col-md-12">
                    <span class="mb-2 h2">{$block.settings.name|default:''}</span>
                </div>
            </div>
            <div class="row">
                {foreach from=$block.states item=state key=key}
                <div class="col-md-4">
                    <div class="testimonial">
                        <div class="testimonial-author text-center">
                            <p class="author-name">{$state.name|default:''}</p>
                            <picture>
                              <source srcset="{$state.image.url|default:''}" type="image/webp">
                              <source srcset="{$state.image.url|default:''|replace:'.webp':'.jpg'}" type="image/jpeg">
                              <img src="{$state.image.url|default:''|replace:'.webp':'.jpg'}" alt="{$state.name|default:''}" title="{$state.name|default:''}" class="rounded-circle img img-fluid lazyload" loading="lazy" width="60">
                            </picture>
                        </div>
                        <div class="testimonial-content text-center">
                            <p>{$state.content nofilter}</p>
                        </div>
                    </div>
                </div>
                {/foreach}
            </div>
        </div>
    {/if}
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->




  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
