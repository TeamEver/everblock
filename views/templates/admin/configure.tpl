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

<div class="everblock-config-wrapper">
    {if isset($everblock_notifications) && $everblock_notifications}
        <div class="everblock-config__notifications">
            {$everblock_notifications nofilter}
        </div>
    {/if}

    <section class="everblock-config__hero">
        <div class="everblock-config__hero-main">
            <span class="everblock-config__badge">{l s='Module' mod='everblock'}</span>
            <h2 class="everblock-config__hero-title">
                {$module_name|escape:'htmlall':'UTF-8'}
            </h2>
            <p class="everblock-config__hero-description">
                {l s='Fine-tune the behaviour, integrations and automation rules of Ever Block from a single, curated control centre.' mod='everblock'}
            </p>
            <div class="everblock-config__hero-meta">
                <span class="everblock-chip">
                    <i class="icon-tag"></i>
                    {l s='Version' mod='everblock'} {$everblock_version|escape:'htmlall':'UTF-8'}
                </span>
                <span class="everblock-chip">
                    <i class="icon-check"></i>
                    {l s='Content managed (total)' mod='everblock'}: {$everblock_stats.blocks_total|intval}
                </span>
            </div>
        </div>

        <div class="everblock-config__hero-stats">
            <div class="everblock-config__stat">
                <div class="everblock-config__stat-value">{$everblock_stats.blocks_active|intval}</div>
                <div class="everblock-config__stat-label">{l s='Active blocks' mod='everblock'}</div>
            </div>
            <div class="everblock-config__stat">
                <div class="everblock-config__stat-value">{$everblock_stats.shortcodes|intval}</div>
                <div class="everblock-config__stat-label">{l s='Shortcodes' mod='everblock'}</div>
            </div>
            <div class="everblock-config__stat">
                <div class="everblock-config__stat-value">{$everblock_stats.flags|intval}</div>
                <div class="everblock-config__stat-label">{l s='Flags' mod='everblock'}</div>
            </div>
            <div class="everblock-config__stat">
                <div class="everblock-config__stat-value">{$everblock_stats.tabs|intval}</div>
                <div class="everblock-config__stat-label">{l s='Product tabs' mod='everblock'}</div>
            </div>
        </div>
    </section>

    <div class="everblock-config__layout">
        <div class="everblock-config__main">
            {if isset($display_upgrade) && $display_upgrade}
                <div class="everblock-config__card">
                    {include file='module:everblock/views/templates/admin/upgrade.tpl'}
                </div>
            {/if}

            {if isset($everblock_form)}
                <div class="everblock-config__card everblock-config__card--form">
                    {$everblock_form nofilter}
                </div>
            {/if}
        </div>

        <aside class="everblock-config__aside">
            <div class="everblock-sidecard">
                <h3 class="everblock-sidecard__title">
                    <i class="icon-th-large"></i>
                    {l s='Quick actions' mod='everblock'}
                </h3>
                <p class="everblock-sidecard__subtitle">
                    {l s='Jump straight into your daily tasks with shortcuts curated for content managers.' mod='everblock'}
                </p>
                <ul class="everblock-sidecard__actions">
                    {if isset($block_admin_link) && $block_admin_link}
                        <li>
                            <a class="everblock-sidecard__link" href="{$block_admin_link|escape:'htmlall':'UTF-8'}">
                                <span class="everblock-sidecard__icon"><i class="icon-puzzle-piece"></i></span>
                                <span>
                                    <strong>{l s='Manage blocks' mod='everblock'}</strong>
                                    <small>{l s='Create, schedule and personalise content blocks.' mod='everblock'}</small>
                                </span>
                            </a>
                        </li>
                    {/if}
                    {if isset($shortcode_admin_link) && $shortcode_admin_link}
                        <li>
                            <a class="everblock-sidecard__link" href="{$shortcode_admin_link|escape:'htmlall':'UTF-8'}">
                                <span class="everblock-sidecard__icon"><i class="icon-code"></i></span>
                                <span>
                                    <strong>{l s='Manage shortcodes' mod='everblock'}</strong>
                                    <small>{l s='Build reusable snippets for your CMS and product pages.' mod='everblock'}</small>
                                </span>
                            </a>
                        </li>
                    {/if}
                    {if isset($faq_admin_link) && $faq_admin_link}
                        <li>
                            <a class="everblock-sidecard__link" href="{$faq_admin_link|escape:'htmlall':'UTF-8'}">
                                <span class="everblock-sidecard__icon"><i class="icon-question-sign"></i></span>
                                <span>
                                    <strong>{l s='Manage FAQ' mod='everblock'}</strong>
                                    <small>{l s='Update customer-facing answers in a few clicks.' mod='everblock'}</small>
                                </span>
                            </a>
                        </li>
                    {/if}
                    {if isset($hook_admin_link) && $hook_admin_link}
                        <li>
                            <a class="everblock-sidecard__link" href="{$hook_admin_link|escape:'htmlall':'UTF-8'}">
                                <span class="everblock-sidecard__icon"><i class="icon-sitemap"></i></span>
                                <span>
                                    <strong>{l s='Manage hooks' mod='everblock'}</strong>
                                    <small>{l s='Control where content appears across your store.' mod='everblock'}</small>
                                </span>
                            </a>
                        </li>
                    {/if}
                </ul>
            </div>

            <div class="everblock-sidecard">
                <h3 class="everblock-sidecard__title">
                    <i class="icon-bullhorn"></i>
                    {l s='Support & resources' mod='everblock'}
                </h3>
                <p class="everblock-sidecard__subtitle">
                    {l s='Need inspiration? Explore documentation, automations and best practices shipped with Ever Block.' mod='everblock'}
                </p>
                <ul class="everblock-sidecard__badges">
                    <li>
                        <span class="everblock-sidecard__badge">
                            <i class="icon-bar-chart"></i>
                            {l s='Total blocks: %s' sprintf=[$everblock_stats.blocks_total|intval] mod='everblock'}
                        </span>
                    </li>
                    <li>
                        <span class="everblock-sidecard__badge">
                            <i class="icon-comments"></i>
                            {l s='FAQ entries: %s' sprintf=[$everblock_stats.faqs|intval] mod='everblock'}
                        </span>
                    </li>
                    <li>
                        <span class="everblock-sidecard__badge">
                            <i class="icon-gamepad"></i>
                            {l s='Gamification sessions: %s' sprintf=[$everblock_stats.game_sessions|intval] mod='everblock'}
                        </span>
                    </li>
                </ul>
                {if isset($donation_link)}
                    <a class="everblock-sidecard__cta" href="{$donation_link|escape:'htmlall':'UTF-8'}" target="_blank">
                        <i class="icon-heart"></i>
                        {l s='Support ongoing development' mod='everblock'}
                    </a>
                {/if}
            </div>
        </aside>
    </div>
</div>
