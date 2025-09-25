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
            <i class="icon-shield"></i>
            {l s='Invisible reCAPTCHA protection' mod='everblock'}
        </h3>
        <p>{l s='Ever Block can secure its custom contact forms with Google reCAPTCHA v3. Enable the feature and paste your site/secret keys to start blocking automated submissions.' mod='everblock'}</p>
        <ul>
            <li>{l s='Keys must be created from the Google reCAPTCHA admin console using the “v3” type.' mod='everblock'}</li>
            <li>{l s='You can fine-tune the minimum score for the Ever Block contact forms. Start with 0.5 and increase it if you still receive spam.' mod='everblock'}</li>
            <li>{l s='The error message is displayed to customers when verification fails, both on Ajax popups and full page forms.' mod='everblock'}</li>
            <li>{l s='Failed checks are logged in the PrestaShop log table with the score returned by Google to help you tune the threshold.' mod='everblock'}</li>
        </ul>
        <p>{l s='Remember to update your privacy policy to mention Google reCAPTCHA when the protection is enabled.' mod='everblock'}</p>
    </div>
</div>
