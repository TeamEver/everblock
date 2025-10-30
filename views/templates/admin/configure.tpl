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
    </div>
</div>
