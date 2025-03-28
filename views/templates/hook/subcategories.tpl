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
{if isset($everSubCategories) && $everSubCategories}
<section class="featured-subcategories clearfix mt-3">
    <div class="subcategories row">
    {foreach $everSubCategories item=subcategory}
    	<div class="col-12 col-md-4 subcategory-{$subcategory.id_category}">
    		<a href="{$subcategory.link}" title="{$subcategory.meta_description}">
	    		<p class="h3 text-center">{$subcategory.name}</p>
	    		{if isset($subcategory.image_link) && $subcategory.image_link}
	    		<img src="{$subcategory.image_link}" alt="{$subcategory.meta_description}" class="text-center lazyload img img-fluid" loading="lazy">
	    		{/if}
	    	</a>
    	</div>
    {/foreach}
    </div>
</section>
{/if}