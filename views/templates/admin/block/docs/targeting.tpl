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
            <i class="icon-target"></i>
            {l s='Targeting rules explained' mod='everblock'}
        </h3>
        <p>
            {l s='Ever Block can display a single HTML block to very specific audiences. Combine several rules to match devices, customer groups or catalog entities.' mod='everblock'}
        </p>
        <ul>
            <li>{l s='Device selection defines where the block is rendered. Mobile, tablet and desktop rely on PrestaShop device detection.' mod='everblock'}</li>
            <li>{l s='Customer groups restrict visibility after login. Guests always inherit the “Visitor” group.' mod='everblock'}</li>
            <li>{l s='Category, manufacturer, supplier and CMS filters accept multiple selections. Leave them empty to display the block everywhere.' mod='everblock'}</li>
            <li>{l s='When “Only on homepage” is enabled the other contextual restrictions are ignored.' mod='everblock'}</li>
        </ul>
        <p class="mt-3">
            {l s='Need to mix several contexts? Duplicate the block and adjust the targeting on each copy to keep things readable.' mod='everblock'}
        </p>
    </div>
</div>
