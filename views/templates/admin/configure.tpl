{*
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="alert alert-info">
    <h4>{l s='What is a hook ?' mod='everblock'}</h3>
    <p>{l s='A hook is a location where you can "plug" a module. Very numerous on Prestashop, they are saved in your database, and displayed by your theme and your modules.' mod='everblock'}</p>
    <p>{l s='These hooks are available both on your site, but also in the administration of your store.' mod='everblock'}</p>
    <p>{l s='You can personalize the messages using the secure shortcodes offered by Ever Block' mod='everblock'}</p>
    <p>{l s='You can modify, add and delete display hooks by going to the "Ever Block" tab then "Hook management"' mod='everblock'}</p>
    <br>
    <h4>{l s='Where do I manage my blocks ?' mod='everblock'}</h3>
    <p>{l s='A new tab has appeared in the administration of your store, named "Ever Block"' mod='everblock'}</p>
    <p>{l s='You will be able to manage your blocks according to many criteria, such as the client group, the device used (mobile, tablet, computer)...' mod='everblock'}</p>
    <p>{l s='You can create your own shortcodes to automate content on your store.' mod='everblock'}</p>
    <p>{l s='And you will also be able to manage your hooks, in order to create them with a view to inserting new hooks into your theme.' mod='everblock'}</p>
    <br>
    <h4>{l s='What about overrides ?' mod='everblock'}</h3>
    <p>{l s='This module does not perform any overrides, it does not need any.' mod='everblock'}</p>
    <br>
    <h4>{l s='How can I change blocks design ?' mod='everblock'}</h3>
    <p>{l s='Each block has specific class and identifiers. You can design the blocks with custom CSS code.' mod='everblock'}</p>
    <p>{l s='CSS code should be added on :' mod='everblock'}</p>
    <ul>
        <li><code>/themes/YOUR_THEME/asssets/css/custom.css</code> {l s='for Prestashop 1.7' mod='everblock'}</li>
        <li><code>/themes/YOUR_THEME/css/global.css</code> {l s='for Prestashop 1.6' mod='everblock'}</li>
    </ul>
    <p>{l s='However, you can add your personalized HTML code below, which will be cached and returned to your store while respecting Prestashop standards.' mod='everblock'}</p>
    <br>
    <h4>{l s='Can the shortcodes be used anywhere on my store?' mod='everblock'}</h3>
    <p>{l s='If you are using Prestashop 1.7 or 8, the shortcodes will indeed be accessible on any page of your store' mod='everblock'}</p>
    <br>
    <h4>{l s='Is it really free ?' mod='everblock'}</h3>
    <p>{l s='This module has always been free and will always be.' mod='everblock'}</p>
    <p>{l s='You can support our free modules developpment by making a donation. This will help us make more free modules and help e-merchants. Thanks for your support !' mod='everblock'}</p>
    <br>
    <h4>{l s='Shortcodes list' mod='everblock'}</h3>
    <p>[product 1] {l s='to show product ID 1' mod='everblock'}</p>
    <p>[product 1,2,3] {l s='to show products ID 1, 2 and 3' mod='everblock'}</p>
    <p>[entity_lastname] {l s='for customer lastname' mod='everblock'}</p>
    <p>[entity_firstname] {l s='for customer firstname' mod='everblock'}</p>
    <p>[entity_gender] {l s='for customer gender' mod='everblock'}</p>
    <p>[start_cart_link] {l s='for starting link to cart page' mod='everblock'}</p>
    <p>[end_cart_link] {l s='for ending link to cart page' mod='everblock'}</p>
    <p>[start_shop_link] {l s='for starting link to shop' mod='everblock'}</p>
    <p>[end_shop_link] {l s='for ending link to shop' mod='everblock'}</p>
    <p>[start_contact_link] {l s='for starting link to native contact page' mod='everblock'}</p>
    <p>[end_contact_link] {l s='for ending link to native contact page' mod='everblock'}</p>
    <p>[llorem] {l s='will generate fake text' mod='everblock'}</p>
    <p>[shop_url] {l s='for shop url' mod='everblock'}</p>
    <p>[shop_name] {l s='for shop name' mod='everblock'}</p>
    <p>[theme_uri] {l s='for current theme url' mod='everblock'}</p>
    <p>[category id="8" nb="8"] {l s='to show 8 products from category ID 8' mod='everblock'}</p>
    <p>[manufacturer id="2" nb="8"] {l s='to show 8 products from manufacturer ID 2' mod='everblock'}</p>
    <p>[brands nb="8"] {l s='to show 8 brands name with their associated logos' mod='everblock'}</p>
    <p>[storelocator] {l s='to show a store locator on any CMS page' mod='everblock'}</p>
    <p>{l s='All shortcodes work anywhere on your store. You can also enter them directly into your TPL files, they will also be interpreted.' mod='everblock'}</p>
    <br>
    <h4>{l s='Using PrettyBlocks (from PrestaSafe)' mod='everblock'}</h3>
    <p>{l s='You can also use the shortcodes with the Pretty Blocks page builder' mod='everblock'}</p>
    <p>
    {l s='Pretty Blocks is freely accessible here:' mod='everblock'}
    <a href="https://prettyblocks.io/" target="_blank">https://prettyblocks.io/</a>
    </p>
    <br>
    <h4>{l s='Using QCD ACF (from 410 Gone)' mod='everblock'}</h3>
    <p>{l s='The 410 Gone QCD ACF module allows you to add text, textarea, color, etc. fields to almost all Prestashop elements (order, product, category, characteristic, etc.)' mod='everblock'}</p>
    <p>{l s='If the module is installed on your store, you will be able, for example, to enter the following shortcode:' mod='everblock'}</p>
    <ul>
        <li>[qcdacf text_bas_page category 8]</li>
    </ul>
    <p>{l s='This will display for category ID 8 the QCD ACF content named text_bas_page' mod='everblock'}</p>
    <p>{l s='If no content is found, the shortcode will be hidden.' mod='everblock'}</p>
    <p>
    {l s='410 Gone agency can be contacted here:' mod='everblock'}
    <a href="https://www.410-gone.fr/e-commerce/prestashop.html" target="_blank">https://www.410-gone.fr/e-commerce/prestashop.html</a>
    </p>
    <br>
</div>
<div class="alert alert-warning">
    <h4>{l s='Troubleshooting' mod='everblock'}</h3>
    <p>{l s='If an HTML block is not displayed, first check that the hook is present in your theme files.' mod='everblock'}</p>
    <p>{l s='If a shortcode is not displayed correctly, check the logs in your store\'s advanced settings.' mod='everblock'}</p>
    <p>{l s='If you don\'t see your content or your content hasn\'t changed, make sure to clear your store\'s cache.' mod='everblock'}</p>
    <br>
</div>