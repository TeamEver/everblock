{*
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if isset($everblock_modal) && $everblock_modal}
<div class="modal fade everblockModal" id="everblockModal" tabindex="-1" role="dialog" aria-labelledby="everblockModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-center"{if isset($everblock_modal->background) && $everblock_modal->background} style="background-color:{$everblock_modal->background|escape:'htmlall':'UTF-8'};"{/if}>
            <div class="modal-body text-center">
                {$everblock_modal->content nofilter}
            </div>
        </div>
    </div>
</div>
{/if}
