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

<div class="card col-lg-6 p-3">
    <h3 class="bootstrap cardheader everblock">
        {l s='Connect as this customer' mod='everblock'}
    </h3>
    <div class="bootstrap cardbody everblock">
        <div class="panel-heading">
        {if isset($login_link) && $login_customer}
        <p><a href="{$login_link|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-info btn-lg"><strong>{l s='Click here to log as' mod='everblock'} {$login_customer->firstname|escape:'htmlall':'UTF-8'} {$login_customer->lastname|escape:'htmlall':'UTF-8'}</strong></a></p>
        {/if}
        </div>
    </div>
</div>