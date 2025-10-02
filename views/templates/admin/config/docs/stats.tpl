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

<div class="card everblock-doc mt-3">
    <div class="card-body">
        <h3 class="card-title">
            <i class="icon-bar-chart"></i>
            {l s='Module statistics' mod='everblock'}
        </h3>
        <p class="mb-4">
            {l s='Get a quick overview of the content managed by Everblock directly from the configuration page.' mod='everblock'}
        </p>
        <div class="row text-center">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="everblock-stat">
                    <div class="everblock-stat__value h3 mb-1">{$everblock_stats.blocks_total|intval}</div>
                    <div class="everblock-stat__label text-muted">{l s='Blocks' mod='everblock'}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="everblock-stat">
                    <div class="everblock-stat__value h3 mb-1">{$everblock_stats.blocks_active|intval}</div>
                    <div class="everblock-stat__label text-muted">{l s='Active blocks' mod='everblock'}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="everblock-stat">
                    <div class="everblock-stat__value h3 mb-1">{$everblock_stats.shortcodes|intval}</div>
                    <div class="everblock-stat__label text-muted">{l s='Shortcodes' mod='everblock'}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="everblock-stat">
                    <div class="everblock-stat__value h3 mb-1">{$everblock_stats.faqs|intval}</div>
                    <div class="everblock-stat__label text-muted">{l s='FAQ entries' mod='everblock'}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="everblock-stat">
                    <div class="everblock-stat__value h3 mb-1">{$everblock_stats.tabs|intval}</div>
                    <div class="everblock-stat__label text-muted">{l s='Product tabs' mod='everblock'}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="everblock-stat">
                    <div class="everblock-stat__value h3 mb-1">{$everblock_stats.flags|intval}</div>
                    <div class="everblock-stat__label text-muted">{l s='Flags' mod='everblock'}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="everblock-stat">
                    <div class="everblock-stat__value h3 mb-1">{$everblock_stats.modals|intval}</div>
                    <div class="everblock-stat__label text-muted">{l s='Modals' mod='everblock'}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="everblock-stat">
                    <div class="everblock-stat__value h3 mb-1">{$everblock_stats.game_sessions|intval}</div>
                    <div class="everblock-stat__label text-muted">{l s='Game sessions' mod='everblock'}</div>
                </div>
            </div>
        </div>
    </div>
</div>
