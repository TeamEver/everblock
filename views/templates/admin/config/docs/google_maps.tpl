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
            <i class="icon-map-marker"></i>
            {l s='Google Tools settings' mod='everblock'}
        </h3>
        <p>{l s='Configure the Google APIs used by the store locator and reviews shortcodes.' mod='everblock'}</p>
        <ul>
            <li>{l s='Generate an API key with Places and Maps JavaScript enabled so the autocomplete widget can work on CMS pages.' mod='everblock'}</li>
            <li>{l s='Upload your own SVG marker to match your branding. Leave it empty to keep the default pin.' mod='everblock'}</li>
            <li>{l s='Remember to clear the module cache after changing the marker if the old icon is still displayed.' mod='everblock'}</li>
            <li>{l s='Paste the Google Places API key into the dedicated field and save the settings each time you rotate or restrict the key.' mod='everblock'}</li>
            <li>{l s='Store your Google Place ID in the provided field so the reviews shortcode knows which location to display.' mod='everblock'}</li>
        </ul>
        <p>{l s='Use the Google Place ID Finder from Google Maps Platform to retrieve the identifier that matches the location of your store, then copy it here.' mod='everblock'}</p>
        <p>{l s='If your API key changes or you revoke access from the Google Cloud console, update the key in this tab to keep the autocomplete widget and reviews working.' mod='everblock'}</p>
    </div>
</div>
