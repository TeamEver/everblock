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

{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
        <div class="custom-checkout-step">
            <form
                    method="POST"
                    action="{$urls.pages.order}"
                    data-refresh-url="{url entity='order' params=['ajax' => 1, 'action' => 'customStep']}"
            >
                {$fields nofilter}
                <footer class="clearfix">
                    <input type="submit" name="submitCustomStep" value="{l s='Submit' mod='everblock'}"
                           class="btn btn-primary continue float-xs-right"/>
                </footer>
            </form>
        </div>
{/block}
