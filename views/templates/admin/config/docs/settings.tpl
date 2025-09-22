{*
 * 2019-2025 Team Ever
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

<div class="card everblock-doc mt-3">
    <div class="card-body">
        <h3 class="card-title">
            <i class="icon-info-circle"></i>
            {l s='Configuration overview' mod='everblock'}
        </h3>
        <p>{l s='Use this tab to configure the global behaviour of Ever Block. Each option helps you fine tune cache, editors, integrations or extra services loaded by the module.' mod='everblock'}</p>

        <h4>{l s='What is a hook?' mod='everblock'}</h4>
        <p>{l s='Hooks are the positions where modules can plug their content. Ever Block lets you attach blocks to any display hook available in your store or in the back office.' mod='everblock'}</p>

        <h4>{l s='Where do I manage my blocks?' mod='everblock'}</h4>
        <p>{l s='All blocks are managed from the “Ever Block” menu in your back office. There you can organise them by device, customer group and many other criteria, as well as create your own shortcodes.' mod='everblock'}</p>

        <h4>{l s='What about overrides?' mod='everblock'}</h4>
        <p>{l s='Ever Block does not rely on overrides. Activating or deactivating options in this tab never alters the core files of your shop.' mod='everblock'}</p>

        <h4>{l s='How can I change blocks design?' mod='everblock'}</h4>
        <p>
            {l s='Every block comes with specific classes and identifiers. Place your custom CSS in' mod='everblock'}
            <code>/themes/YOUR_THEME/assets/css/custom.css</code>
            {l s='or inject HTML in the block editor to match your brand identity.' mod='everblock'}
        </p>

        <h4>{l s='Can the shortcodes be used anywhere on my store?' mod='everblock'}</h4>
        <p>{l s='Yes. On PrestaShop 1.7 and 8, shortcodes rendered by Ever Block are available on all front-office pages.' mod='everblock'}</p>

        <h4>{l s='Is it really free?' mod='everblock'}</h4>
        <p>{l s='Ever Block is and will remain free. You can support ongoing development by using the donation button available below the form.' mod='everblock'}</p>

        <p class="mt-3">
            <a href="{$donation_link|escape:'htmlall':'UTF-8'}" class="btn btn-warning" target="_blank">
                <i class="icon-money"></i> {l s='Make a donation' mod='everblock'}
            </a>
        </p>
    </div>
</div>
