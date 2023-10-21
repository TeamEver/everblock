{*
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div id="everblock-storelocator" class="everblock-storelocator visible">
    {foreach from=$everblock_stores item=item name=store_loop}
        <div class="col-md-4">
            <div class="store-block">
                <h2>{$item.name}</h2>
                <p>Address: {$item.address1}, {$item.city}, {$item.postcode}</p>
                <p>Phone: {$item.phone}</p>
                <!-- Ajoutez d'autres informations ici selon vos besoins -->
                <a href="{url entity='store' id=$item.id}" class="btn btn-primary">Voir plus</a>
            </div>
        </div>
        {if $smarty.foreach.store_loop.iteration % 3 == 0}
            <div class="clearfix"></div>
        {/if}
    {/foreach}
</div>
{$mapCode nofilter}
