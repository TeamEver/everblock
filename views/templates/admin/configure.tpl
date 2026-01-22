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

    {if !isset($everblock_show_hero) || $everblock_show_hero}
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
    {/if}

    <div class="everblock-config__layout">
        <div class="everblock-config__main">
            <div class="everblock-config__card everblock-config__card--update">
                <h3 class="everblock-config__card-title">{l s='Module update' mod='everblock'}</h3>
                <ul class="everblock-config__card-list">
                    <li>
                        <strong>{l s='Installed version' mod='everblock'}:</strong>
                        {$everblock_version|escape:'htmlall':'UTF-8'}
                    </li>
                    <li>
                        <strong>{l s='Latest GitHub version' mod='everblock'}:</strong>
                        {if isset($everblock_latest_release.tag_name) && $everblock_latest_release.tag_name}
                            {$everblock_latest_release.tag_name|escape:'htmlall':'UTF-8'}
                        {else}
                            {l s='Unavailable' mod='everblock'}
                        {/if}
                    </li>
                    <li>
                        <strong>{l s='Status' mod='everblock'}:</strong>
                        {if isset($everblock_update_available) && $everblock_update_available}
                            {l s='An update is available' mod='everblock'}
                        {else}
                            {l s='Your module is up to date' mod='everblock'}
                        {/if}
                    </li>
                </ul>
                {if isset($everblock_latest_release.published_at) && $everblock_latest_release.published_at}
                    <p class="everblock-config__card-meta">
                        {l s='Published on' mod='everblock'}:
                        {$everblock_latest_release.published_at|escape:'htmlall':'UTF-8'}
                    </p>
                {/if}
            </div>
            {if isset($everblock_form)}
                <div class="everblock-config__card everblock-config__card--form">
                    {$everblock_form nofilter}
                </div>
            {/if}
        </div>
    </div>
</div>

{if isset($everblock_preview_contexts)}
    {include file='module:everblock/views/templates/admin/block/preview-modal.tpl'
        preview_contexts=$everblock_preview_contexts
        preview_url=$everblock_preview_url
        preview_available=$everblock_preview_available
    }
{/if}
