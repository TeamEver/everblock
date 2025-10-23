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

{if !empty($faq_tags)}
<div class="panel everblock-faq-filter" data-everblock-faq-filter>
    <div class="panel-heading">
        <i class="icon-filter"></i>
        {$filter_label|escape:'htmlall':'UTF-8'}
    </div>
    <div class="panel-body">
        <div class="form-group">
            <label class="control-label col-lg-2" for="everblock-faq-filter-select">
                {l s='Tag' mod='everblock'}
            </label>
            <div class="col-lg-10">
                <select id="everblock-faq-filter-select" class="form-control js-everblock-faq-filter">
                    <option value="">{$filter_all_label|escape:'htmlall':'UTF-8'}</option>
                    {foreach from=$faq_tags item=faq_tag}
                        <option value="{$faq_tag|escape:'htmlall':'UTF-8'}">
                            {$faq_tag|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
</div>
{/if}
