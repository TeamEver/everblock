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
{if isset($checkoutSessionData) && $checkoutSessionData}
<table class="table table-striped table-hover">
<thead class="thead-dark">
    <tr>
        <th>{l s='Option' mod='everblock'}</th>
        <th>{l s='Value' mod='everblock'}</th>
    </tr>
</thead>
<tbody>
{foreach $checkoutSessionData as $k => $v}
    <tr>
        <td>{$k|replace:'_':' '}</td>
        <td>
            {if is_array($v)}
                <ul class="list-unstyled">
                {foreach $v as $elem}
                    <li>{$elem}{if not $elem@last}, {/if}</li>
                {/foreach}
                </ul>
            {else}
                {$v}
            {/if}
        </td>
    </tr>
{/foreach}
</tbody>
</table>
{/if}