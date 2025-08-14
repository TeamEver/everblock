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

{if isset($everblock_modal) && $everblock_modal}
<div class="modal fade everblockModal" id="everblockModal" tabindex="-1" role="dialog" aria-labelledby="everblockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content"
            {if isset($everblock_modal->background) && $everblock_modal->background} 
            style="background-color:{$everblock_modal->background|escape:'htmlall':'UTF-8'};" 
            {/if}>
            {* SEO : modal must have titles *}
            <p id="everblockModalLabel" class="h5 modal-title d-none">
                {l s='Modal' mod='everblock'}
            </p>
            <!-- Contenu de la modal -->
            <div class="modal-body">
                <!-- Bouton de fermeture aligné à droite -->
                <button type="button" class="close float-right" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{l s='Close' mod='everblock'}">
                    <span aria-hidden="true">&times;</span>
                </button>
                {$everblock_modal->content nofilter}
                {if isset($everblock_modal->file) && $everblock_modal->file}
                    <p><a href="{$everblock_modal->file|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Download file' mod='everblock'}</a></p>
                {/if}
            </div>
        </div>
    </div>
</div>
{/if}


