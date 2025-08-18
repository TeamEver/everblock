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
    <div class="row row-cols-1 row-cols-md-5 gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  {if isset($block.states) && $block.states}
    {foreach from=$block.states item=state key=key}
      {if isset($state.category.id) && $state.category.id}
        {assign var=category_obj value=new Category($state.category.id, $block.id_lang)}
        {assign var=category_link value=Context::getContext()->link->getCategoryLink($state.category.id)}
        {if $state.image.url}
          {assign var=image_url value=$state.image.url}
        {else}
          {assign var=image_url value=Context::getContext()->link->getCatImageLink(ImageType::getFormattedName('category'), $state.category.id)}
        {/if}
        {assign var=title value=$state.name|default:$category_obj->name}
        {assign var=products value=Product::getProducts($block.id_lang, 0, 1, 'price', 'asc', $state.category.id, true)}
        {if $products|@count}
          {assign var=min_price value=$products[0]['price']}
        {else}
          {assign var=min_price value=false}
        {/if}
      {else}
        {assign var=category_link value="#"}
        {assign var=image_url value=""}
        {assign var=title value=$state.name}
        {assign var=min_price value=false}
      {/if}
      <div id="block-{$block.id_prettyblocks}-{$key}" class="col text-center{if $state.css_class} {$state.css_class|escape:'htmlall'}{/if}" style="{if $state.padding_left}padding-left:{$state.padding_left};{/if}{if $state.padding_right}padding-right:{$state.padding_right};{/if}{if $state.padding_top}padding-top:{$state.padding_top};{/if}{if $state.padding_bottom}padding-bottom:{$state.padding_bottom};{/if}{if $state.margin_left}margin-left:{$state.margin_left};{/if}{if $state.margin_right}margin-right:{$state.margin_right};{/if}{if $state.margin_top}margin-top:{$state.margin_top};{/if}{if $state.margin_bottom}margin-bottom:{$state.margin_bottom};{/if}">
        <a href="{$category_link}" class="d-block text-decoration-none">
          {if $image_url}
            <img src="{$image_url|escape:'htmlall'}" alt="{$title|escape:'htmlall'}" class="img-fluid mb-2" loading="lazy">
          {/if}
          {if $title}
            <p class="h6">{$title|escape:'htmlall'}</p>
          {/if}
          {if $min_price !== false}
            <span class="small">{l s='From' mod='everblock'} {Tools::displayPrice($min_price)}</span>
          {/if}
        </a>
      </div>
    {/foreach}
  {/if}
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
