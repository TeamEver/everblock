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
            <button type="button" class="btn btn-info" data-toggle="modal" data-bs-toggle="modal" data-target="#everblockShortcodeModal" data-bs-target="#everblockShortcodeModal">
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
        {if isset($everblock_guide_front_link) && $everblock_guide_front_link}
            <a href="{$everblock_guide_front_link|escape:'htmlall':'UTF-8'}" class="btn btn-info" target="_blank" rel="noopener">
                <i class="icon-eye-open"></i> {l s='View guides page' mod='everblock'}
            </a>
        {/if}
        {if isset($everblock_faq_front_link) && $everblock_faq_front_link}
            <a href="{$everblock_faq_front_link|escape:'htmlall':'UTF-8'}" class="btn btn-info" target="_blank" rel="noopener">
                <i class="icon-eye-open"></i> {l s='View FAQ page' mod='everblock'}
                {if isset($everblock_faq_front_tag) && $everblock_faq_front_tag}
                    ({$everblock_faq_front_tag|escape:'htmlall':'UTF-8'})
                {/if}
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
    <div class="modal fade everblock-shortcode-modal" id="everblockShortcodeModal" tabindex="-1" role="dialog" aria-labelledby="everblockShortcodeModalLabel" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="everblock-shortcode-modal__header">
                    <div class="everblock-shortcode-modal__icon" aria-hidden="true">
                        <i class="icon-code"></i>
                    </div>
                    <div class="everblock-shortcode-modal__intro">
                        <h3 class="everblock-shortcode-modal__title" id="everblockShortcodeModalLabel">
                            {l s='Available shortcodes' mod='everblock'}
                        </h3>
                        <p class="everblock-shortcode-modal__subtitle">
                            {l s='Use these shortcodes inside your blocks, CMS pages or PrettyBlocks zones.' mod='everblock'}
                        </p>
                    </div>
                    <button type="button" class="everblock-shortcode-modal__close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="{l s='Close' mod='everblock'}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="everblock-shortcode-modal__tools">
                    <label for="everblockShortcodeSearch" class="sr-only visually-hidden">
                        {l s='Search shortcodes' mod='everblock'}
                    </label>
                    <div class="everblock-shortcode-search input-group">
                        <span class="input-group-addon input-group-text"><i class="icon-search"></i></span>
                        <input type="search" class="form-control" id="everblockShortcodeSearch" placeholder="{l s='Search shortcodes…' mod='everblock'}" autocomplete="off">
                        <div class="input-group-btn input-group-append">
                            <button class="btn btn-default btn-secondary everblock-shortcode-clear" type="button">
                                {l s='Clear' mod='everblock'}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="everblock-shortcode-modal__body">
                    <div class="everblock-shortcode-modal__empty">
                        <i class="icon-warning-sign" aria-hidden="true"></i>
                        <p>{l s='No shortcode matches your search. Try different keywords or reset the filters.' mod='everblock'}</p>
                        <button type="button" class="btn btn-link everblock-shortcode-reset">
                            {l s='Show all shortcodes' mod='everblock'}
                        </button>
                    </div>
                    <div class="everblock-shortcode-categories">
                        {foreach from=$everblock_shortcode_docs item=everblock_shortcode_category}
                            {assign var=shortcodeCount value=($everblock_shortcode_category.entries|default:[])|@count}
                            <article class="everblock-shortcode-category-card">
                                <header class="everblock-shortcode-category-card__header">
                                    <div class="everblock-shortcode-category-card__heading">
                                        <h4 class="everblock-shortcode-category-card__title">{$everblock_shortcode_category.title|escape:'htmlall':'UTF-8'}</h4>
                                        {if $shortcodeCount}
                                            <span class="everblock-shortcode-category-card__count" aria-hidden="true">{$shortcodeCount}</span>
                                            <span class="sr-only visually-hidden">
                                                {if $shortcodeCount == 1}
                                                    {l s='1 shortcode in this category' mod='everblock'}
                                                {else}
                                                    {l s='%d shortcodes in this category' sprintf=[$shortcodeCount] mod='everblock'}
                                                {/if}
                                            </span>
                                        {/if}
                                    </div>
                                </header>
                                {if isset($everblock_shortcode_category.entries) && $everblock_shortcode_category.entries}
                                    <div class="everblock-shortcode-category-card__entries">
                                        {foreach from=$everblock_shortcode_category.entries item=everblock_shortcode_entry}
                                            <section class="everblock-shortcode-entry" data-everblock-shortcode-entry data-filter-text="{$everblock_shortcode_entry.code|escape:'htmlall':'UTF-8'} {$everblock_shortcode_entry.description|escape:'htmlall':'UTF-8'}{if isset($everblock_shortcode_entry.parameters) && $everblock_shortcode_entry.parameters}{foreach from=$everblock_shortcode_entry.parameters item=everblock_shortcode_param} {$everblock_shortcode_param.name|escape:'htmlall':'UTF-8'} {$everblock_shortcode_param.description|escape:'htmlall':'UTF-8'}{/foreach}{/if}">
                                                <div class="everblock-shortcode-entry__header">
                                                    <code class="everblock-shortcode-entry__code">{$everblock_shortcode_entry.code|escape:'htmlall':'UTF-8'}</code>
                                                    <button type="button" class="btn btn-link everblock-shortcode-entry__copy" data-everblock-copy="{$everblock_shortcode_entry.code|escape:'htmlall':'UTF-8'}" title="{l s='Copy shortcode' mod='everblock'}">
                                                        <i class="icon-files-o" aria-hidden="true"></i>
                                                    <span class="sr-only visually-hidden">{l s='Copy shortcode' mod='everblock'}</span>
                                                    </button>
                                                </div>
                                                <p class="everblock-shortcode-entry__description">
                                                    {$everblock_shortcode_entry.description|escape:'htmlall':'UTF-8'}
                                                </p>
                                                {if isset($everblock_shortcode_entry.parameters) && $everblock_shortcode_entry.parameters}
                                                    <ul class="everblock-shortcode-entry__params">
                                                        {foreach from=$everblock_shortcode_entry.parameters item=everblock_shortcode_param}
                                                            <li class="everblock-shortcode-entry__param">
                                                                <span class="everblock-shortcode-entry__badge {if $everblock_shortcode_param.required}is-required{else}is-optional{/if}">
                                                                    {if $everblock_shortcode_param.required}{l s='Required' mod='everblock'}{else}{l s='Optional' mod='everblock'}{/if}
                                                                </span>
                                                                <span class="everblock-shortcode-entry__param-name">{$everblock_shortcode_param.name|escape:'htmlall':'UTF-8'}</span>
                                                                {if $everblock_shortcode_param.description}
                                                                    <span class="everblock-shortcode-entry__param-description">{$everblock_shortcode_param.description|escape:'htmlall':'UTF-8'}</span>
                                                                {/if}
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                {/if}
                                            </section>
                                        {/foreach}
                                    </div>
                                {else}
                                    <p class="everblock-shortcode-category-card__empty">
                                        {l s='No shortcodes are currently registered in this category.' mod='everblock'}
                                    </p>
                                {/if}
                            </article>
                        {/foreach}
                    </div>
                </div>
                <div class="everblock-shortcode-modal__footer">
                    <button type="button" class="btn btn-default btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">
                        {l s='Close' mod='everblock'}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        (function ($) {
            $(function () {
                var $modal = $('#everblockShortcodeModal');
                var $search = $('#everblockShortcodeSearch');
                var $clearBtn = $modal.find('.everblock-shortcode-clear');
                var $resetBtn = $modal.find('.everblock-shortcode-reset');
                var $entries = $modal.find('[data-everblock-shortcode-entry]');
                var $categories = $modal.find('.everblock-shortcode-category-card');
                var $emptyState = $modal.find('.everblock-shortcode-modal__empty');

                var updateClearButtonState = function () {
                    var hasValue = $.trim($search.val()).length > 0;
                    $clearBtn.prop('disabled', !hasValue).toggleClass('is-disabled', !hasValue);
                };

                var toggleEmptyState = function () {
                    var visibleEntries = $entries.filter(':visible').length;
                    if (visibleEntries === 0) {
                        $emptyState.addClass('is-visible');
                    } else {
                        $emptyState.removeClass('is-visible');
                    }
                };

                var refreshCategoriesVisibility = function () {
                    $categories.each(function () {
                        var $category = $(this);
                        var totalEntries = $category.find('[data-everblock-shortcode-entry]').length;
                        if (totalEntries === 0) {
                            $category.removeClass('is-hidden');
                            return;
                        }
                        var hasVisible = $category.find('[data-everblock-shortcode-entry]:visible').length > 0;
                        $category.toggleClass('is-hidden', !hasVisible);
                    });
                };

                var performSearch = function (query) {
                    var term = $.trim(query).toLowerCase();
                    if (term.length === 0) {
                        $entries.show();
                    } else {
                        $entries.each(function () {
                            var $entry = $(this);
                            var text = ($entry.data('filterText') || '').toLowerCase();
                            $entry.toggle(text.indexOf(term) !== -1);
                        });
                    }

                    refreshCategoriesVisibility();
                    toggleEmptyState();
                    updateClearButtonState();
                };

                var resetSearch = function () {
                    $search.val('');
                    performSearch('');
                    $search.focus();
                };

                $search.on('input', function () {
                    performSearch($(this).val());
                });

                $clearBtn.on('click', function (event) {
                    event.preventDefault();
                    resetSearch();
                });

                $resetBtn.on('click', function (event) {
                    event.preventDefault();
                    resetSearch();
                });

                $modal.on('shown.bs.modal', function () {
                    resetSearch();
                });

                var showCopyFeedback = function ($button, message) {
                    var original = $button.data('originalLabel');
                    if (!original) {
                        original = $button.html();
                        $button.data('originalLabel', original);
                    }
                    $button.addClass('is-active').html('<i class="icon-ok" aria-hidden="true"></i> ' + message);
                    setTimeout(function () {
                        $button.removeClass('is-active').html(original);
                    }, 2000);
                };

                $modal.on('click', '[data-everblock-copy]', function (event) {
                    event.preventDefault();
                    var $button = $(this);
                    var shortcode = $button.data('everblockCopy');
                    if (!shortcode) {
                        return;
                    }

                    var feedback = function (success) {
                        var message = success ? "{l s='Copied!' mod='everblock' js=1}" : "{l s='Press Ctrl+C to copy' mod='everblock' js=1}";
                        showCopyFeedback($button, message);
                    };

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(shortcode).then(function () {
                            feedback(true);
                        }).catch(function () {
                            feedback(false);
                        });
                    } else {
                        var temp = $('<input type="text" class="everblock-shortcode-copy-temp" />');
                        temp.val(shortcode).appendTo('body');
                        temp[0].select();
                        try {
                            var success = document.execCommand('copy');
                            feedback(success);
                        } catch (error) {
                            feedback(false);
                        }
                        temp.remove();
                    }
                });

                refreshCategoriesVisibility();
                toggleEmptyState();
                updateClearButtonState();
            });
        })(jQuery);
    </script>
{/if}
