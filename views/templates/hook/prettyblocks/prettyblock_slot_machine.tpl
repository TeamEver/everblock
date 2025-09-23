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
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{assign var=symbols value=$block.states|default:[]}
{assign var=isEmployee value=false}
{if isset($everblock_is_employee) && $everblock_is_employee}
    {assign var=isEmployee value=true}
{/if}
{capture assign=preStartMessageHtml}{$block.settings.pre_start_message nofilter}{/capture}
{capture assign=postEndMessageHtml}{$block.settings.post_end_message nofilter}{/capture}
{assign var=startDate value=$block.settings.start_date|default:''}
{assign var=endDate value=$block.settings.end_date|default:''}
{capture assign=defaultPreStartMessage}{l s='The game has not started yet.' mod='everblock'}{/capture}
{capture assign=defaultPostEndMessage}{l s='The game is over.' mod='everblock'}{/capture}
{assign var=loginRequired value=$block.settings.require_login|default:true}
{assign var=loginMessage value=$block.settings.login_message|default:''}
{assign var=resultTitle value=$block.settings.result_title|default:{l s='Result' mod='everblock'}}
{assign var=defaultCouponName value=$block.settings.default_coupon_name|default:'Slot machine reward'}
{assign var=defaultCouponPrefix value=$block.settings.default_coupon_prefix|default:'SLOT'}
{assign var=defaultCouponValidity value=$block.settings.default_coupon_validity|default:30}
{assign var=defaultCouponType value=$block.settings.default_coupon_type|default:'percent'}
{assign var=defaultMaxWinners value=$block.settings.default_max_winners|default:0}
{assign var=winningCombinations value=$block.settings.winning_combinations|default:'[]'}
{assign var=countdownLabel value={l s='Game starts in:' mod='everblock'}}
{assign var=slotConfig value=[
    'symbols' => $symbols,
    'winningCombinations' => $winningCombinations,
    'spinUrl' => $link->getModuleLink('everblock','slotmachine'),
    'token' => $static_token,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'preStartMessage' => $preStartMessageHtml,
    'postEndMessage' => $postEndMessageHtml,
    'defaultPreStartMessage' => $defaultPreStartMessage,
    'defaultPostEndMessage' => $defaultPostEndMessage,
    'countdownLabel' => $countdownLabel,
    'defaultCouponName' => $defaultCouponName,
    'defaultCouponPrefix' => $defaultCouponPrefix,
    'defaultCouponValidity' => $defaultCouponValidity,
    'defaultCouponType' => $defaultCouponType,
    'defaultMaxWinners' => $defaultMaxWinners,
    'loginRequired' => $loginRequired,
    'isEmployee' => $isEmployee
]}
{assign var=encodedConfig value=$slotConfig|json_encode|base64_encode|escape:'htmlall':'UTF-8'}
{assign var=containerClass value=''}
{if $block.settings.default.force_full_width}
    {assign var=containerClass value='container-fluid px-0 mx-0'}
{elseif $block.settings.default.container}
    {assign var=containerClass value='container'}
{/if}
<div id="block-{$block.id_prettyblocks}" class="{$containerClass}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if}" style="{if isset($block.settings.padding_left) && $block.settings.padding_left}padding-left:{$block.settings.padding_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_right) && $block.settings.padding_right}padding-right:{$block.settings.padding_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_top) && $block.settings.padding_top}padding-top:{$block.settings.padding_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.padding_bottom) && $block.settings.padding_bottom}padding-bottom:{$block.settings.padding_bottom|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_left) && $block.settings.margin_left}margin-left:{$block.settings.margin_left|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_right) && $block.settings.margin_right}margin-right:{$block.settings.margin_right|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_top) && $block.settings.margin_top}margin-top:{$block.settings.margin_top|escape:'htmlall':'UTF-8'};{/if}{if isset($block.settings.margin_bottom) && $block.settings.margin_bottom}margin-bottom:{$block.settings.margin_bottom|escape:'htmlall':'UTF-8'};{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
        <div class="ever-slot-machine" data-block-id="{$block.id_prettyblocks}" data-config="{$encodedConfig}">
            {if $block.settings.title}<h3>{$block.settings.title|escape:'htmlall':'UTF-8'}</h3>{/if}
            <div class="ever-slot-status-message" style="display:none;">
                <div class="ever-slot-status-text"></div>
                <div class="ever-slot-countdown" style="display:none;">
                    <span class="ever-slot-countdown-label">{$countdownLabel|escape:'htmlall':'UTF-8'}</span>
                    <span class="ever-slot-countdown-value"></span>
                </div>
            </div>
            <div class="ever-slot-content">
                {if !$loginRequired || $customer.is_logged || $isEmployee}
                    {if $block.settings.instructions}<div class="ever-slot-instructions mb-3">{$block.settings.instructions nofilter}</div>{/if}
                    <div class="ever-slot-reels" role="group" aria-label="{l s='Slot machine reels' mod='everblock'}">
                        <div class="ever-slot-reel" data-reel-index="0" aria-live="polite"></div>
                        <div class="ever-slot-reel" data-reel-index="1" aria-live="polite"></div>
                        <div class="ever-slot-reel" data-reel-index="2" aria-live="polite"></div>
                    </div>
                    <div class="ever-slot-actions mt-4">
                        <button class="btn btn-primary ever-slot-spin">{$block.settings.spin_button_label|escape:'htmlall':'UTF-8'}</button>
                    </div>
                {else}
                    {if $block.settings.instructions}<div class="ever-slot-instructions mb-3">{$block.settings.instructions nofilter}</div>{/if}
                    <div class="ever-slot-reels ever-slot-reels--disabled" aria-hidden="true">
                        <div class="ever-slot-reel" aria-hidden="true"></div>
                        <div class="ever-slot-reel" aria-hidden="true"></div>
                        <div class="ever-slot-reel" aria-hidden="true"></div>
                    </div>
                    <div class="ever-slot-login mt-3">
                        <button class="btn btn-primary ever-slot-login-btn" data-target="#everSlotLoginModal-{$block.id_prettyblocks}">{l s='Log in to play' mod='everblock'}</button>
                        <div class="ever-slot-login-message mt-2">{$loginMessage nofilter}</div>
                    </div>
                    <div class="modal fade ever-slot-login-modal" id="everSlotLoginModal-{$block.id_prettyblocks}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{l s='Connexion' mod='everblock'}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{l s='Close' mod='everblock'}"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{$link->getPageLink('authentication', true)|escape:'htmlall':'UTF-8'}?back={$urls.current_url|escape:'htmlall':'UTF-8'}" method="post" class="ever-slot-login-form">
                                        <div class="form-group">
                                            <label for="ever-slot-login-email-{$block.id_prettyblocks}">{l s='Email' mod='everblock'}</label>
                                            <input id="ever-slot-login-email-{$block.id_prettyblocks}" class="form-control" type="email" name="email" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ever-slot-login-password-{$block.id_prettyblocks}">{l s='Password' mod='everblock'}</label>
                                            <input id="ever-slot-login-password-{$block.id_prettyblocks}" class="form-control js-visible-password" type="password" name="password" required>
                                        </div>
                                        <input type="hidden" name="submitLogin" value="1">
                                        <input type="hidden" name="back" value="{$urls.current_url|escape:'htmlall':'UTF-8'}">
                                        <button class="btn btn-primary btn-block" type="submit">{l s='Sign in' mod='everblock'}</button>
                                    </form>
                                    <div class="text-center mt-3">
                                        <a href="{$link->getPageLink('authentication', true)|escape:'htmlall':'UTF-8'}?create_account=1&back={$urls.current_url|escape:'htmlall':'UTF-8'}">{l s='Create account' mod='everblock'}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
            <div class="ever-slot-result mt-4" aria-live="polite" aria-atomic="true">
                <h4 class="ever-slot-result-title">{$resultTitle|escape:'htmlall':'UTF-8'}</h4>
                <div class="ever-slot-result-message"></div>
                <div class="ever-slot-result-details"></div>
                <div class="ever-slot-coupon" style="display:none;">
                    <span class="ever-slot-coupon-code"></span>
                    <button type="button" class="btn btn-secondary btn-sm ever-slot-copy">{l s='Copy code' mod='everblock'}</button>
                    <span class="ever-slot-copy-feedback ms-2" style="display:none;"></span>
                </div>
            </div>
        </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
