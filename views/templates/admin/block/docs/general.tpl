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
            {l s='General tab essentials' mod='everblock'}
        </h3>
        <p>
            {l s='This tab groups every mandatory field required to create your HTML block: its internal name, the hook to use and the actual content displayed on the storefront.' mod='everblock'}
        </p>
        <ul>
            <li>{l s='Use the “Name” as an internal reminder only. Customers will never see it.' mod='everblock'}</li>
            <li>{l s='The WYSIWYG editor accepts HTML, shortcodes and Smarty variables. Enable or disable TinyMCE from the module configuration if you prefer raw HTML editing.' mod='everblock'}</li>
            <li>{l s='Select the right hook to control where the block is injected. The preview link near each hook helps you understand its position.' mod='everblock'}</li>
            <li>{l s='Toggle the “Active” switch to publish or temporarily hide the block without deleting it.' mod='everblock'}</li>
        </ul>
        <p class="mt-3">
            {l s='Remember that each block can be duplicated from the list view. Duplicate an existing block to reuse its HTML or hook configuration without starting from scratch.' mod='everblock'}
        </p>
    </div>
</div>
