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

<section class="everblock-admin-surface">
    {if isset($everblock_notifications) && $everblock_notifications}
        <div class="everblock-admin-alerts">
            {$everblock_notifications nofilter}
        </div>
    {/if}

    {if isset($display_upgrade) && $display_upgrade}
        <div class="everblock-admin-upgrade">
            {include file='module:everblock/views/templates/admin/upgrade.tpl'}
        </div>
    {/if}

    {if isset($everblock_form)}
        <div class="everblock-admin-main">
            {$everblock_form nofilter}
        </div>
    {/if}
</section>
