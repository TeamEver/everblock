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
            <i class="icon-comments"></i>
            {l s='Modal behaviour' mod='everblock'}
        </h3>
        <p>
            {l s='Enable the modal option to transform the block into a popup window. The original hook will only be used to load the modal assets.' mod='everblock'}
        </p>
        <ul>
            <li>{l s='Cookie lifetime defines how long a visitor will stop seeing the popup after closing it. Use 0 to show the modal on each page load.' mod='everblock'}</li>
            <li>{l s='Delay is expressed in milliseconds. For example, 5000 equals 5 seconds.' mod='everblock'}</li>
            <li>{l s='Store locator and hook placeholders are ignored inside modals to keep the popup lightweight.' mod='everblock'}</li>
            <li>{l s='Use your theme or custom CSS to adjust the modal width. The HTML editor remains the same as in the General tab.' mod='everblock'}</li>
        </ul>
        <p class="mt-3">
            {l s='Combine modals with targeting rules to display contextual popups based on the page or audience.' mod='everblock'}
        </p>
    </div>
</div>
