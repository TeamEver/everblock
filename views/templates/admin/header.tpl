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
<div class="panel row everblock-header">
    <div class="col-md-8">
        <h3 class="mb-3">
            <i class="icon icon-smile"></i>
            {l s='Ever Block' mod='everblock'} {$everblock_version|escape:'htmlall':'UTF-8'}
        </h3>
        <a href="#everlogobottom">
            <img id="everlogotop" class="img-fluid" src="{$everblock_dir|escape:'htmlall':'UTF-8'}logo.png" alt="{l s='Ever Block logo' mod='everblock'}" style="max-width: 120px;" />
        </a>
        <p class="mt-2">{l s='Thanks for using Team Ever\'s modules' mod='everblock'}<br /></p>
    </div>
    <div class="col-md-4 text-right mt-3">
        {if isset($everblock_shortcode_docs) && $everblock_shortcode_docs}
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#everblockShortcodeModal">
                <i class="icon-book"></i> {l s='Shortcode documentation' mod='everblock'}
            </button>
        {/if}
        {if isset($modules_list_link)}
            <a href="{$modules_list_link|escape:'htmlall':'UTF-8'}" class="btn btn-default">
                <i class="process-icon-back"></i> {l s='Back to modules' mod='everblock'}
            </a>
        {/if}
        {if isset($block_admin_link) && $block_admin_link}
            <a href="{$block_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-success">
                {l s='Manage blocks' mod='everblock'}
            </a>
        {/if}
        {if isset($faq_admin_link) && $faq_admin_link}
            <a href="{$faq_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-success">
                {l s='Manage FAQ' mod='everblock'}
            </a>
        {/if}
        {if isset($hook_admin_link) && $hook_admin_link}
            <a href="{$hook_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-success">
                {l s='Manage all hooks' mod='everblock'}
            </a>
        {/if}
        {if isset($shortcode_admin_link) && $shortcode_admin_link}
            <a href="{$shortcode_admin_link|escape:'htmlall':'UTF-8'}" class="btn btn-success">
                {l s='Manage shortcodes' mod='everblock'}
            </a>
        {/if}
        {if isset($donation_link)}
            <a href="{$donation_link|escape:'htmlall':'UTF-8'}" class="btn btn-warning" target="_blank">
                <i class="icon-money"></i> {l s='Make a donation' mod='everblock'}
            </a>
        {/if}
    </div>
</div>
{if isset($everblock_shortcode_docs) && $everblock_shortcode_docs}
    <div class="modal fade" id="everblockShortcodeModal" tabindex="-1" role="dialog" aria-labelledby="everblockShortcodeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' mod='everblock'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="everblockShortcodeModalLabel">
                        <i class="icon-code"></i> {l s='Available shortcodes' mod='everblock'}
                    </h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        {l s='Use these shortcodes inside your blocks, CMS pages or PrettyBlocks zones.' mod='everblock'}
                    </p>
                    {foreach from=$everblock_shortcode_docs item=everblock_shortcode_category}
                        <div class="panel panel-default everblock-shortcode-panel">
                            <div class="panel-heading">
                                <strong>{$everblock_shortcode_category.title|escape:'htmlall':'UTF-8'}</strong>
                            </div>
                            <div class="panel-body">
                                {if isset($everblock_shortcode_category.entries) && $everblock_shortcode_category.entries}
                                    <ul class="list-unstyled everblock-shortcode-list">
                                        {foreach from=$everblock_shortcode_category.entries item=everblock_shortcode_entry}
                                            <li class="everblock-shortcode-list__item">
                                                <div class="everblock-shortcode-list__header">
                                                    <code class="everblock-shortcode-list__code">{$everblock_shortcode_entry.code|escape:'htmlall':'UTF-8'}</code>
                                                </div>
                                                <p class="everblock-shortcode-list__description">
                                                    {$everblock_shortcode_entry.description|escape:'htmlall':'UTF-8'}
                                                </p>
                                                {if isset($everblock_shortcode_entry.parameters) && $everblock_shortcode_entry.parameters}
                                                    <ul class="list-unstyled everblock-shortcode-params">
                                                        {foreach from=$everblock_shortcode_entry.parameters item=everblock_shortcode_param}
                                                            <li class="everblock-shortcode-params__item">
                                                                <span class="label {if $everblock_shortcode_param.required}label-primary{else}label-default{/if}">
                                                                    {if $everblock_shortcode_param.required}{l s='Required' mod='everblock'}{else}{l s='Optional' mod='everblock'}{/if}
                                                                </span>
                                                                <strong class="everblock-shortcode-params__name">{$everblock_shortcode_param.name|escape:'htmlall':'UTF-8'}</strong>
                                                                {if $everblock_shortcode_param.description}
                                                                    <span class="everblock-shortcode-params__description">- {$everblock_shortcode_param.description|escape:'htmlall':'UTF-8'}</span>
                                                                {/if}
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                {/if}
                                            </li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            </div>
                        </div>
                    {/foreach}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {l s='Close' mod='everblock'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}
