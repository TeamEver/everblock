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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {assign var=brands value=[]}
  {if isset($block.states) && $block.states}
    {foreach from=$block.states item=state}
      {if isset($state.brand.id) && $state.brand.id}
        {assign var=brandData value=EverblockTools::getBrandDataById($state.brand.id, Context::getContext())}
        {if $brandData}
          {assign var=brands value=$brands|@array_merge:[$brandData]}
        {/if}
      {/if}
    {/foreach}
  {/if}
  {if $brands}
    {include file='module:everblock/views/templates/hook/ever_brand.tpl' brands=$brands carousel=$block.settings.slider brandsPerSlide=$block.settings.brands_per_slide}
  {/if}
</div>

