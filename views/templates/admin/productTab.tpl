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
        {foreach from=$tabsRange item=tab}
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-xl-12">
                    <input type="hidden" name="{$tab|escape:'htmlall':'UTF-8'}_everblock_id" value="{if isset($everpstabs->id) && $everpstabs->id}{$everpstabs->id|escape:'htmlall':'UTF-8'}{/if}">
                    <div class="translations">
                        {foreach from=$ever_languages item=language}
                            <div class="form-group">
                                <label for="{$tab|escape:'htmlall':'UTF-8'}_everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Tab title for lang' mod='everblock'} {$language.iso_code} ({l s='Tab number' mod='everblock'} {$tab|escape:'htmlall':'UTF-8'})</label>
                                <input type="text" id="{$tab|escape:'htmlall':'UTF-8'}_everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="{$tab|escape:'htmlall':'UTF-8'}_everblock_title_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control" {if isset($everpstabs->title[$language.id_lang]) && $everpstabs->title[$language.id_lang]}value="{$everpstabs->title[$language.id_lang]|escape:'htmlall':'UTF-8'}"{/if}>
                            </div>
                            <div class="form-group">
                                <label for="{$tab|escape:'htmlall':'UTF-8'}_everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}">{l s='Tab content for lang' mod='everblock'} {$language.iso_code} ({l s='Tab number' mod='everblock'} {$tab|escape:'htmlall':'UTF-8'})</label>
                                <textarea id="{$tab|escape:'htmlall':'UTF-8'}_everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="{$tab|escape:'htmlall':'UTF-8'}_everblock_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control autoload_rte">{if isset($everpstabs->content[$language.id_lang]) && $everpstabs->content[$language.id_lang] != ''}{$everpstabs->content[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}</textarea>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
        {/foreach}
    </fieldset>
</div>
