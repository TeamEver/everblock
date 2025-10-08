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

<section class="panel everblock-admin-footer text-center">
    <div class="everblock-admin-footer__inner">
        <div class="everblock-admin-footer__brand">
            <a href="#everlogotop" class="everblock-admin-footer__logo">
                <img id="everlogobottom" class="everblock-admin-footer__image img-fluid" src="{$everblock_dir|escape:'htmlall':'UTF-8'}logo.png" alt="{l s='Ever Block logo' mod='everblock'}" />
            </a>
            <h2 class="everblock-admin-footer__title">
                <i class="icon icon-smile" aria-hidden="true"></i>
                {l s='Ever Block' mod='everblock'}
            </h2>
        </div>
        <p class="everblock-admin-footer__gratitude">
            <strong>{l s='Thank you for your confidence :-)' mod='everblock'}</strong>
        </p>
        <p class="everblock-admin-footer__description">
            {l s='Feel free to contact us for more support or help' mod='everblock'}
        </p>
        <div class="everblock-admin-footer__cta">
            <a href="#everlogotop" class="btn btn-default">
                <i class="process-icon-arrow-up" aria-hidden="true"></i>
                <span>{l s='Back to top' mod='everblock'}</span>
            </a>
            {if isset($donation_link)}
                <a href="{$donation_link|escape:'htmlall':'UTF-8'}" class="btn btn-warning everblock-admin-footer__donation" target="_blank">
                    <i class="icon-money" aria-hidden="true"></i>
                    <span>{l s='Make a donation' mod='everblock'}</span>
                </a>
            {/if}
        </div>
    </div>
</section>
</div>
