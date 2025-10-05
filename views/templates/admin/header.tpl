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
<div class="everblock-admin">
    <section class="panel everblock-admin-hero">
        <div class="everblock-admin-hero__inner">
            <div class="everblock-admin-hero__branding">
                <span class="everblock-admin-hero__eyebrow">
                    {l s='Modular content suite' mod='everblock'}
                </span>
                <h1 class="everblock-admin-hero__title">
                    <i class="icon icon-smile" aria-hidden="true"></i>
                    {l s='Ever Block' mod='everblock'}
                </h1>
                <p class="everblock-admin-hero__tagline">
                    {l s='Thanks for using Team Ever\'s modules' mod='everblock'}
                </p>
                <div class="everblock-admin-hero__meta">
                    <span class="everblock-admin-hero__version">
                        <i class="icon icon-flag" aria-hidden="true"></i>
                        {l s='Version %s' sprintf=[$everblock_version] mod='everblock'}
                    </span>
                    <span class="everblock-admin-hero__author">
                        <i class="icon icon-users" aria-hidden="true"></i>
                        {l s='Crafted by Team Ever' mod='everblock'}
                    </span>
                </div>
                <ul class="everblock-admin-hero__features">
                    <li>
                        <span class="everblock-admin-hero__feature-title">
                            {l s='Flexible content blocks' mod='everblock'}
                        </span>
                        <span class="everblock-admin-hero__feature-text">
                            {l s='Publish HTML, shortcodes and widgets everywhere in your shop.' mod='everblock'}
                        </span>
                    </li>
                    <li>
                        <span class="everblock-admin-hero__feature-title">
                            {l s='Precise targeting & scheduling' mod='everblock'}
                        </span>
                        <span class="everblock-admin-hero__feature-text">
                            {l s='Decide when and where blocks appear with audience filters and dates.' mod='everblock'}
                        </span>
                    </li>
                    <li>
                        <span class="everblock-admin-hero__feature-title">
                            {l s='Built-in modal experiences' mod='everblock'}
                        </span>
                        <span class="everblock-admin-hero__feature-text">
                            {l s='Turn any block into a popup and control the display frequency with cookies.' mod='everblock'}
                        </span>
                    </li>
                </ul>
            </div>
            <div class="everblock-admin-hero__visual">
                <div class="everblock-admin-hero__logo">
                    <a href="#everlogobottom">
                        <img id="everlogotop" class="everblock-admin-hero__image img-fluid" src="{$everblock_dir|escape:'htmlall':'UTF-8'}logo.png" alt="{l s='Ever Block logo' mod='everblock'}" />
                    </a>
                </div>
            </div>
        </div>
        <nav class="everblock-admin-hero__actions">
            {if isset($modules_list_link)}
                <a href="{$modules_list_link|escape:'htmlall':'UTF-8'}" class="btn btn-default everblock-admin-hero__action everblock-admin-hero__action--ghost">
                    <i class="process-icon-back" aria-hidden="true"></i>
                    <span>{l s='Back to modules' mod='everblock'}</span>
                </a>
            {/if}
            {if isset($block_admin_link) && $block_admin_link}
                <a href="{$block_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-success everblock-admin-hero__action everblock-admin-hero__action--primary">
                    <i class="icon icon-cubes" aria-hidden="true"></i>
                    <span>{l s='Manage blocks' mod='everblock'}</span>
                </a>
            {/if}
            {if isset($faq_admin_link) && $faq_admin_link}
                <a href="{$faq_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-success everblock-admin-hero__action everblock-admin-hero__action--primary">
                    <i class="icon icon-question-circle" aria-hidden="true"></i>
                    <span>{l s='Manage FAQ' mod='everblock'}</span>
                </a>
            {/if}
            {if isset($hook_admin_link) && $hook_admin_link}
                <a href="{$hook_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-success everblock-admin-hero__action everblock-admin-hero__action--primary">
                    <i class="icon icon-code" aria-hidden="true"></i>
                    <span>{l s='Manage all hooks' mod='everblock'}</span>
                </a>
            {/if}
            {if isset($shortcode_admin_link) && $shortcode_admin_link}
                <a href="{$shortcode_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-success everblock-admin-hero__action everblock-admin-hero__action--primary">
                    <i class="icon icon-puzzle-piece" aria-hidden="true"></i>
                    <span>{l s='Manage shortcodes' mod='everblock'}</span>
                </a>
            {/if}
            {if isset($donation_link)}
                <a href="{$donation_link|escape:'htmlall':'UTF-8'}" class="btn btn-warning everblock-admin-hero__action everblock-admin-hero__action--accent" target="_blank">
                    <i class="icon-money" aria-hidden="true"></i>
                    <span>{l s='Make a donation' mod='everblock'}</span>
                </a>
            {/if}
        </nav>
    </section>

