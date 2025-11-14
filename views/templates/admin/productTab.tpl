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
 *  @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
*}
<div id="everblock" class="panel">
    <fieldset class="form-group">
        {if isset($everblock_faq_selector)}
        <div class="container border rounded p-3 mb-3 everblock-faq-selector-card">
            <div class="row">
                <div class="col-lg-12 col-xl-12">
                    {assign var=faqSelectedCount value=0}
                    {if isset($everblock_faq_selector.selected_options)}
                        {assign var=faqSelectedCount value=$everblock_faq_selector.selected_options|@count}
                    {/if}
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-2">
                        <div>
                            <h4 class="h4 mb-2">{l s='FAQ associations' mod='everblock'}</h4>
                            <p class="text-muted mb-0">{l s='Select the FAQ entries that should be displayed with this product. Use the search bar to quickly find them by tag or title.' mod='everblock'}</p>
                        </div>
                        {if $faqSelectedCount > 0}
                            <span class="badge bg-primary everblock-faq-selected-counter">{l s='%d FAQ selected' sprintf=[$faqSelectedCount] mod='everblock'}</span>
                        {/if}
                    </div>
                    <div class="everblock-faq-search-tip alert alert-info py-2 px-3 mb-3">
                        <i class="icon-search" aria-hidden="true"></i>
                        <span>{l s='Start typing to highlight the search bar and filter the FAQs by tag or title.' mod='everblock'}</span>
                    </div>
                    <div class="everblock-faq-selector-wrapper">
                        <label for="everblock_faq_selector" class="form-label fw-semibold">{l s='Search and select FAQ entries' mod='everblock'}</label>
                        <select
                            id="everblock_faq_selector"
                            name="everblock_faq_ids[]"
                            class="form-control js-everblock-faq-selector"
                            multiple="multiple"
                            data-ajax-url="{$everblock_faq_selector.ajax_url|escape:'htmlall':'UTF-8'}"
                            data-placeholder="{$everblock_faq_selector.placeholder|escape:'htmlall':'UTF-8'}"
                            aria-describedby="everblock_faq_selector_hint"
                        >
                            {if isset($everblock_faq_selector.selected_options)}
                                {foreach from=$everblock_faq_selector.selected_options item=faqOption}
                                    <option value="{$faqOption.id|intval}" selected="selected" data-active="{if $faqOption.active}1{else}0{/if}">
                                        {$faqOption.text|escape:'htmlall':'UTF-8'}{if !$faqOption.active} ({l s='Inactive' mod='everblock'}){/if}
                                    </option>
                                {/foreach}
                            {/if}
                        </select>
                        <p id="everblock_faq_selector_hint" class="help-block mt-2 mb-0">{l s='The order of the selected items will be used when displaying the FAQ on the product page.' mod='everblock'}</p>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {foreach from=$tabsData key=tabNumber item=everpstabs}
        <div class="container border rounded p-3 mb-3">
            <div class="row">
                <div class="col-lg-12 col-xl-12">
                    <input type="hidden" name="{$tabNumber|escape:'htmlall':'UTF-8'}_everblock_id" value="{if isset($everpstabs->id_tab) && $everpstabs->id_tab}{$everpstabs->id_tab|escape:'htmlall':'UTF-8'}{/if}">
                    <div class="translations">
                        {foreach from=$ever_languages item=language}
                            <div class="form-group">
                                <label for="{$tabNumber|escape:'htmlall':'UTF-8'}_everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Tab title for lang' mod='everblock'} {$language.iso_code} ({l s='Tab number' mod='everblock'} {$tabNumber|escape:'htmlall':'UTF-8'})</label>
                                <input type="text" id="{$tabNumber|escape:'htmlall':'UTF-8'}_everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="{$tabNumber|escape:'htmlall':'UTF-8'}_everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control" {if isset($everpstabs->title[$language.id_lang]) && $everpstabs->title[$language.id_lang]}value="{$everpstabs->title[$language.id_lang]|escape:'htmlall':'UTF-8'}"{/if}>
                            </div>
                            <div class="form-group">
                                <label for="{$tabNumber|escape:'htmlall':'UTF-8'}_everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Tab content for lang' mod='everblock'} {$language.iso_code} ({l s='Tab number' mod='everblock'} {$tabNumber|escape:'htmlall':'UTF-8'})</label>
                                <textarea id="{$tabNumber|escape:'htmlall':'UTF-8'}_everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="{$tabNumber|escape:'htmlall':'UTF-8'}_everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control autoload_rte">{if isset($everpstabs->content[$language.id_lang]) && $everpstabs->content[$language.id_lang] != ''}{$everpstabs->content[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}</textarea>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
        {/foreach}

        {foreach from=$flagsData key=flagNumber item=everpsflags}
        <div class="container border rounded p-3 mb-3">
            <div class="row">
                <div class="col-lg-12 col-xl-12">
                    <input type="hidden" name="{$flagNumber|escape:'htmlall':'UTF-8'}_everflag_id" value="{if isset($everpsflags->id_flag) && $everpsflags->id_flag}{$everpsflags->id_flag|escape:'htmlall':'UTF-8'}{/if}">
                    <div class="translations">
                        {foreach from=$ever_languages item=language}
                            <div class="form-group">
                                <label for="{$flagNumber|escape:'htmlall':'UTF-8'}_everflag_title_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Flag HTML class for lang' mod='everblock'} {$language.iso_code} ({l s='Flag number' mod='everblock'} {$flagNumber|escape:'htmlall':'UTF-8'})</label>
                                <input type="text" id="{$flagNumber|escape:'htmlall':'UTF-8'}_everflag_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="{$flagNumber|escape:'htmlall':'UTF-8'}_everflag_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control" {if isset($everpsflags->title[$language.id_lang]) && $everpsflags->title[$language.id_lang]}value="{$everpsflags->title[$language.id_lang]|escape:'htmlall':'UTF-8'}"{/if}>
                            </div>
                            <div class="form-group">
                                <label for="{$flagNumber|escape:'htmlall':'UTF-8'}_everflag_content_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Flag content for lang' mod='everblock'} {$language.iso_code} ({l s='Flag number' mod='everblock'} {$flagNumber|escape:'htmlall':'UTF-8'})</label>
                                <textarea id="{$flagNumber|escape:'htmlall':'UTF-8'}_everflag_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="{$flagNumber|escape:'htmlall':'UTF-8'}_everflag_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control">{if isset($everpsflags->content[$language.id_lang]) && $everpsflags->content[$language.id_lang] != ''}{$everpsflags->content[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}</textarea>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
        {/foreach}
    </fieldset>
</div>
