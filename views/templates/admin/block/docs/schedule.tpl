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
            <i class="icon-calendar"></i>
            {l s='Schedule your block' mod='everblock'}
        </h3>
        <p>
            {l s='Pick optional start and end dates to automate the publication of the block. Outside of this window the block is hidden but remains editable.' mod='everblock'}
        </p>
        <ul>
            <li>{l s='Leave both dates empty to display the block permanently.' mod='everblock'}</li>
            <li>{l s='Only the shop timezone is used. Make sure your server and shop are correctly configured.' mod='everblock'}</li>
            <li>{l s='The block turns inactive as soon as the end date is reached. You do not need to disable it manually.' mod='everblock'}</li>
            <li>{l s='Scheduling works together with modal and targeting options, allowing seasonal popups or promotions.' mod='everblock'}</li>
        </ul>
        <p class="mt-3">
            {l s='Need recurring campaigns? Duplicate the block and adjust the dates for each event to keep statistics clean.' mod='everblock'}
        </p>
    </div>
</div>
