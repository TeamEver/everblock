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
 *  @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
*}

<div id="everblock" class="panel">
    <fieldset class="form-group">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-xl-12">
                    <div class="translations">
                        <div id="loader" style="display: none;">{l s='Loading...' mod='everblock'}</div>
                        {if isset($use_gpt) && $use_gpt}
                        <div class="form-group gpt_group">
                            <label for="everblock_use_gpt">{l s='Use GPT for content generation' mod='everblock'}</label>
                            <button type="button" id="everblock_use_gpt" name="everblock_use_gpt" class="btn btn-primary" data-id_product="{$ever_product_id}" data-link="{$ever_ajax_url}" data-type="EverblockTabsClass">{l s='Toggle GPT' mod='everblock'}</button>
                        </div>
                        {/if}
                        {foreach from=$ever_languages item=language}
                            <div class="form-group">
                                <label for="everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Tab title for lang' mod='everblock'} {$language.iso_code}</label>
                                <input type="text" id="everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control" {if isset($everpstabs->title[$language.id_lang]) && $everpstabs->title[$language.id_lang]}value="{$everpstabs->title[$language.id_lang]|escape:'htmlall':'UTF-8'}"{/if}>
                            </div>
                            <div class="form-group">
                                <label for="everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Tab content for lang' mod='everblock'} {$language.iso_code}</label>
                                <textarea id="everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control autoload_rte">{if isset($everpstabs->content[$language.id_lang]) && $everpstabs->content[$language.id_lang] != ''}{$everpstabs->content[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}</textarea>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
</div>
