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

<div class="panel everblock-footer text-center">
    <h3><i class="icon icon-smile"></i> {l s='Ever Block' mod='everblock'}</h3>
    <a href="#everlogotop" class="d-block mb-2">
        <img id="everlogobottom" class="img-fluid" src="{$everblock_dir|escape:'htmlall':'UTF-8'}logo.png" alt="{l s='Ever Block logo' mod='everblock'}" style="max-width: 120px;" />
    </a>
    <p>
        <strong>{l s='Thank you for your confidence :-)' mod='everblock'}</strong><br />
        {l s='Feel free to contact us for more support or help' mod='everblock'}
    </p>
    <p class="mt-2">
        <a href="#everlogotop" class="btn btn-default">
            <i class="process-icon-arrow-up" aria-hidden="true"></i> {l s='Back to top' mod='everblock'}
        </a>
        {if isset($donation_link)}
            <a href="{$donation_link|escape:'htmlall':'UTF-8'}" class="btn btn-warning" target="_blank">
                <i class="icon-money"></i> {l s='Make a donation' mod='everblock'}
            </a>
        {/if}
    </p>
</div>