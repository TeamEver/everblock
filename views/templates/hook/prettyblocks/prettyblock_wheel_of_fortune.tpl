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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}

{assign var=wheelSegments value=$block.states|default:[]}
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
{capture assign=countdownLabel}{l s='Game starts in:' mod='everblock'}{/capture}
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
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
        {assign var=wheelConfig value=[
            'segments' => $wheelSegments,
            'spinUrl' => $link->getModuleLink('everblock','wheel'),
            'token' => $static_token,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'preStartMessage' => $preStartMessageHtml,
            'postEndMessage' => $postEndMessageHtml,
            'defaultPreStartMessage' => $defaultPreStartMessage,
            'defaultPostEndMessage' => $defaultPostEndMessage,
            'countdownLabel' => $countdownLabel,
            'isEmployee' => $isEmployee
        ]}
        <div class="ever-wheel-of-fortune text-center" data-block-id="{$block.id_prettyblocks}" data-config="{$wheelConfig|json_encode|base64_encode|escape:'htmlall':'UTF-8'}">
            {if $block.settings.title}<h3>{$block.settings.title|escape:'htmlall':'UTF-8'}</h3>{/if}
            <div class="ever-wheel-status-message" style="display:none;">
                <div class="ever-wheel-status-text"></div>
                <div class="ever-wheel-countdown" style="display:none;">
                    <span class="ever-wheel-countdown-label">{$countdownLabel|escape:'htmlall':'UTF-8'}</span>
                    <span class="ever-wheel-countdown-value"></span>
                </div>
            </div>
            <div class="ever-wheel-content">
                {if $customer.is_logged}
                    {if $block.settings.top_text}<div class="ever-wheel-top-text mb-4">{$block.settings.top_text nofilter}</div>{/if}
                    <div class="ever-wheel-wrapper mb-4 mt-4">
                        <canvas class="ever-wheel-canvas"></canvas>
                        <div class="ever-wheel-arrow"></div>
                    </div>
                    <div class="ever-wheel-spin-container">
                        <button class="btn btn-primary ever-wheel-spin">{$block.settings.button_label|escape:'htmlall':'UTF-8'}</button>
                    </div>
                    {if $block.settings.bottom_text}<div class="ever-wheel-bottom-text">{$block.settings.bottom_text nofilter}</div>{/if}
                {else}
                    {if $block.settings.top_text}<div class="ever-wheel-top-text mb-4">{$block.settings.top_text nofilter}</div>{/if}
                    <div class="ever-wheel-wrapper mb-4 mt-4 ever-wheel-disabled" style="filter: grayscale(100%);opacity:0.5;pointer-events:none;">
                        <canvas class="ever-wheel-canvas"></canvas>
                        <div class="ever-wheel-arrow"></div>
                    </div>
                    <div class="ever-wheel-spin-container">
                        <button class="btn btn-primary ever-wheel-login-btn">{l s='Connectez-vous pour jouer' mod='everblock'}</button>
                    </div>
                    {if $block.settings.bottom_text}<div class="ever-wheel-bottom-text">{$block.settings.bottom_text nofilter}</div>{/if}
                    <div class="modal fade" id="everWheelLoginModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{l s='Connexion' mod='everblock'}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{l s='Close' mod='everblock'}"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{$link->getPageLink('authentication', true)|escape:'htmlall':'UTF-8'}?back={$urls.current_url|escape:'htmlall':'UTF-8'}" method="post" class="ever-wheel-login-form">
                                        <div class="form-group">
                                            <label for="ever-wheel-login-email">{l s='Email' mod='everblock'}</label>
                                            <input id="ever-wheel-login-email" class="form-control" type="email" name="email" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ever-wheel-login-password">{l s='Password' mod='everblock'}</label>
                                            <input id="ever-wheel-login-password" class="form-control js-visible-password" type="password" name="password" required>
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
