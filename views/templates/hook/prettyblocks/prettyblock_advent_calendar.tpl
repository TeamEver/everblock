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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}

{assign var=isEmployee value=false}
{if isset($everblock_is_employee) && $everblock_is_employee}
    {assign var=isEmployee value=true}
{/if}
{assign var=currentLangId value=$context.language.id}
{capture assign=lockedMessageHtml}{$block.settings.locked_message nofilter}{/capture}
{capture assign=instructionsHtml}{$block.settings.instructions nofilter}{/capture}
{assign var=startDate value=$block.settings.start_date|default:''}
{assign var=restrictToCurrent value=$block.settings.restrict_to_current_day|default:true}
{assign var=openedLabel value=$block.settings.opened_label|default:{l s='Opened' mod='everblock'}}
{assign var=snowEnabled value=$block.settings.snow_enabled|default:true}
{assign var=adventConfig value=[
    'idBlock' => $block.id_prettyblocks,
    'playUrl' => $link->getModuleLink('everblock','advent'),
    'token' => $static_token,
    'startDate' => $startDate,
    'restrictToCurrentDay' => (bool) $restrictToCurrent,
    'lockedMessage' => $lockedMessageHtml,
    'openedLabel' => $openedLabel,
    'snowEnabled' => (bool) $snowEnabled,
    'isEmployee' => (bool) $isEmployee,
    'langId' => $currentLangId,
    'emptyMessage' => {l s='This window is not configured yet.' mod='everblock'},
    'fallbackLockedMessage' => {l s='Come back on %s to open this window.' mod='everblock'},
    'errorMessage' => {l s='An error occurred. Please try again later.' mod='everblock'},
    'missingContentMessage' => {l s='The surprise for this window is not available right now.' mod='everblock'}
]}
{assign var=encodedConfig value=$adventConfig|json_encode|base64_encode|escape:'htmlall':'UTF-8'}
{assign var=containerClass value=''}
{if $block.settings.default.force_full_width}
    {assign var=containerClass value='container-fluid px-0 mx-0'}
{elseif $block.settings.default.container}
    {assign var=containerClass value='container'}
{/if}
{assign var=calendarBackgroundColor value=$block.settings.calendar_background_color|default:''}
<div id="block-{$block.id_prettyblocks}" class="{$containerClass}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if}" style="{$prettyblock_spacing_style}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
        <div class="ever-advent-calendar" data-block-id="{$block.id_prettyblocks}" data-config="{$encodedConfig}"{if $calendarBackgroundColor} style="background-color:{$calendarBackgroundColor|escape:'htmlall':'UTF-8'};"{/if}>
            {if $block.settings.title}<h3 class="ever-advent-calendar__title">{$block.settings.title|escape:'htmlall':'UTF-8'}</h3>{/if}
            {if $instructionsHtml}
                <div class="ever-advent-calendar__instructions">{$instructionsHtml}</div>
            {/if}
            <div class="ever-advent-calendar__status" role="status" aria-live="polite" style="display:none;"></div>
            <div class="ever-advent-calendar__content">
                {if $customer.is_logged || $isEmployee}
                    <div class="ever-advent-calendar__grid" aria-live="polite">
                        {section name=advent start=1 loop=25}
                            <button type="button" class="ever-advent-calendar__window" data-day="{$smarty.section.advent.index}" aria-label="{l s='Open window %s' sprintf=[$smarty.section.advent.index] mod='everblock'}">
                                <span class="ever-advent-calendar__front" aria-hidden="true">
                                    <span class="ever-advent-calendar__number">{$smarty.section.advent.index}</span>
                                    <span class="ever-advent-calendar__badge" aria-hidden="true"></span>
                                </span>
                                <span class="ever-advent-calendar__back" hidden>
                                    <span class="ever-advent-calendar__reveal"></span>
                                </span>
                            </button>
                        {/section}
                    </div>
                {else}
                    <div class="ever-advent-calendar__grid ever-advent-calendar__grid--locked" aria-hidden="true">
                        {section name=locked start=1 loop=25}
                            <span class="ever-advent-calendar__window ever-advent-calendar__window--locked">
                                <span class="ever-advent-calendar__front" aria-hidden="true">
                                    <span class="ever-advent-calendar__number">{$smarty.section.locked.index}</span>
                                    <span class="ever-advent-calendar__badge" aria-hidden="true"></span>
                                </span>
                            </span>
                        {/section}
                    </div>
                    <div class="ever-advent-calendar__login mt-3">
                        <button class="btn btn-primary ever-advent-calendar__login-btn" data-target="#everAdventLoginModal-{$block.id_prettyblocks}">{l s='Log in to open a window' mod='everblock'}</button>
                    </div>
                    <div class="modal fade ever-advent-login-modal" id="everAdventLoginModal-{$block.id_prettyblocks}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{l s='Connexion' mod='everblock'}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{l s='Close' mod='everblock'}"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="ever-advent-calendar__login-message mb-3">{l s='Create or log in to your account to reveal today\'s surprise!' mod='everblock'}</p>
                                    <form action="{$link->getPageLink('authentication', true)|escape:'htmlall':'UTF-8'}?back={$urls.current_url|escape:'htmlall':'UTF-8'}" method="post" class="ever-advent-calendar__login-form">
                                        <div class="form-group">
                                            <label for="ever-advent-login-email-{$block.id_prettyblocks}">{l s='Email' mod='everblock'}</label>
                                            <input id="ever-advent-login-email-{$block.id_prettyblocks}" class="form-control" type="email" name="email" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ever-advent-login-password-{$block.id_prettyblocks}">{l s='Password' mod='everblock'}</label>
                                            <input id="ever-advent-login-password-{$block.id_prettyblocks}" class="form-control js-visible-password" type="password" name="password" required>
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
