{*
* Project : everpsorderoptions
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}
<div class="panel everheader">
    <div class="panel-body">
        <div class="col-md-12">
            <div class="table-responsive">
                <table>
                    <tr class="center small bold center">
                        <td>{l s='Ordered Options' mod='everpsorderoptions'}</td>
                    </tr>
                </table>
                <table id="everpsorderoptions" class="display responsive nowrap dataTable no-footer dtr-inline collapsed table">
                    <thead>
                        <tr class="center small grey bold center">
                            <th>{l s='Option' mod='everblock'}</th>
                            <th>{l s='Value' mod='everblock'}</th>
                        </tr>
                    </thead>
                    <tbody>
                    {foreach $checkoutSessionData as $k => $v}
                    <tr>
                        <td class="option_name center small">{$k|replace:'_':' '}</td>
                        <td class="option_name center small">
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
            </div>
        </div>
    </div>
</div>
