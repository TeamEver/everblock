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
{assign var=wheelSegments value=$block.states|default:[]}
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
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
        <div class="ever-wheel-of-fortune text-center" data-segments="{$wheelSegments|json_encode|escape:'htmlall':'UTF-8'}" data-spin-url="{$link->getModuleLink('everblock','wheel')|escape:'htmlall':'UTF-8'}" data-coupon-prefix="{$block.settings.coupon_prefix|escape:'htmlall':'UTF-8'}" data-coupon-validity="{$block.settings.coupon_validity|intval}" data-coupon-type="{$block.settings.coupon_type|escape:'htmlall':'UTF-8'}" data-coupon-name="{$block.settings.coupon_name|escape:'htmlall':'UTF-8'}">
            {if $block.settings.title}<h3>{$block.settings.title|escape:'htmlall':'UTF-8'}</h3>{/if}
            {if $logged}
                <canvas class="ever-wheel-canvas mb-3" style="width:100%;height:auto;"></canvas>
                <button class="btn btn-primary ever-wheel-spin">{$block.settings.button_label|escape:'htmlall':'UTF-8'}</button>
            {else}
                <div class="ever-wheel-forms mt-2 row justify-content-center">
                    <div class="col-md-5">
                        <form action="{$link->getPageLink('authentication', true)|escape:'htmlall':'UTF-8'}?back={$urls.current_url|escape:'htmlall':'UTF-8'}" method="post" class="card card-block ever-wheel-login-form">
                            <h4 class="card-title">{l s='Sign in to play' mod='everblock'}</h4>
                            <div class="card-body">
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
                            </div>
                        </form>
                    </div>
                    <div class="col-md-5">
                        <form action="{$link->getPageLink('authentication', true)|escape:'htmlall':'UTF-8'}?create_account=1&back={$urls.current_url|escape:'htmlall':'UTF-8'}" method="post" class="card card-block ever-wheel-register-form">
                            <h4 class="card-title">{l s='Create account to play' mod='everblock'}</h4>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="ever-wheel-register-firstname">{l s='First name' mod='everblock'}</label>
                                    <input id="ever-wheel-register-firstname" class="form-control" type="text" name="firstname" required>
                                </div>
                                <div class="form-group">
                                    <label for="ever-wheel-register-lastname">{l s='Last name' mod='everblock'}</label>
                                    <input id="ever-wheel-register-lastname" class="form-control" type="text" name="lastname" required>
                                </div>
                                <div class="form-group">
                                    <label for="ever-wheel-register-email">{l s='Email' mod='everblock'}</label>
                                    <input id="ever-wheel-register-email" class="form-control" type="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="ever-wheel-register-password">{l s='Password' mod='everblock'}</label>
                                    <input id="ever-wheel-register-password" class="form-control js-visible-password" type="password" name="password" required>
                                </div>
                                <div class="form-group form-check">
                                    <input class="form-check-input" type="checkbox" id="ever-wheel-register-newsletter" name="newsletter" value="1">
                                    <label class="form-check-label" for="ever-wheel-register-newsletter">{l s='Subscribe to newsletter' mod='everblock'}</label>
                                </div>
                                <input type="hidden" name="submitCreate" value="1">
                                <input type="hidden" name="back" value="{$urls.current_url|escape:'htmlall':'UTF-8'}">
                                <button class="btn btn-primary btn-block" type="submit">{l s='Create account' mod='everblock'}</button>
                            </div>
                        </form>
                    </div>
                </div>
            {/if}
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
