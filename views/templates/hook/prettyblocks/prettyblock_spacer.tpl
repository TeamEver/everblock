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

<div id="block-{$block.id_prettyblocks}" class="everblock-spacer {$block.settings.css_class|escape:'htmlall':'UTF-8'}" style="{if isset($block.settings.space_top) && $block.settings.space_top}margin-top:{$block.settings.space_top|escape:'htmlall':'UTF-8'}rem;{/if}{if isset($block.settings.space_bottom) && $block.settings.space_bottom}margin-bottom:{$block.settings.space_bottom|escape:'htmlall':'UTF-8'}rem;{/if}height:0;"></div>

  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
