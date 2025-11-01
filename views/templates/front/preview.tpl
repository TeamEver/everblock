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

{extends file='page.tpl'}

{block name='page_title'}
    {if isset($everblock_preview_block->name) && $everblock_preview_block->name}
        {l s='Preview for "%s"' sprintf=[$everblock_preview_block->name] mod='everblock'}
    {else}
        {l s='Block preview' mod='everblock'}
    {/if}
{/block}

{block name='page_content'}
    <section class="everblock-preview">
        {if isset($everblock_preview_error) && $everblock_preview_error}
            <div class="alert alert-danger" role="alert">
                {$everblock_preview_error|escape:'htmlall':'UTF-8'}
            </div>
        {else}
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="h5 mb-3">{l s='Preview context' mod='everblock'}</h2>
                    <dl class="row mb-0">
                        {if isset($everblock_preview_hook) && $everblock_preview_hook}
                            <dt class="col-sm-4 text-muted">{l s='Hook:' mod='everblock'}</dt>
                            <dd class="col-sm-8">{$everblock_preview_hook|escape:'htmlall':'UTF-8'}</dd>
                        {/if}
                        {if isset($everblock_preview_info.controller)}
                            <dt class="col-sm-4 text-muted">{l s='Controller:' mod='everblock'}</dt>
                            <dd class="col-sm-8">{$everblock_preview_info.controller|escape:'htmlall':'UTF-8'}</dd>
                        {/if}
                        {if isset($everblock_preview_info.page_name) && $everblock_preview_info.page_name}
                            <dt class="col-sm-4 text-muted">{l s='Page name:' mod='everblock'}</dt>
                            <dd class="col-sm-8">{$everblock_preview_info.page_name|escape:'htmlall':'UTF-8'}</dd>
                        {/if}
                        {if isset($everblock_preview_info.language->name)}
                            <dt class="col-sm-4 text-muted">{l s='Language:' mod='everblock'}</dt>
                            <dd class="col-sm-8">
                                {$everblock_preview_info.language->name|escape:'htmlall':'UTF-8'}
                                {if isset($everblock_preview_info.language->iso_code)}
                                    <span class="badge badge-secondary ml-2">{$everblock_preview_info.language->iso_code|escape:'htmlall':'UTF-8'}</span>
                                {/if}
                            </dd>
                        {/if}
                        {if isset($everblock_preview_info.currency->name)}
                            <dt class="col-sm-4 text-muted">{l s='Currency:' mod='everblock'}</dt>
                            <dd class="col-sm-8">
                                {$everblock_preview_info.currency->name|escape:'htmlall':'UTF-8'}
                                {if isset($everblock_preview_info.currency->iso_code)}
                                    <span class="badge badge-secondary ml-2">{$everblock_preview_info.currency->iso_code|escape:'htmlall':'UTF-8'}</span>
                                {/if}
                            </dd>
                        {/if}
                        {if isset($everblock_preview_info.shop->name)}
                            <dt class="col-sm-4 text-muted">{l s='Shop:' mod='everblock'}</dt>
                            <dd class="col-sm-8">{$everblock_preview_info.shop->name|escape:'htmlall':'UTF-8'}</dd>
                        {/if}
                        {if isset($everblock_preview_info.ids) && $everblock_preview_info.ids}
                            <dt class="col-sm-4 text-muted">{l s='Identifiers:' mod='everblock'}</dt>
                            <dd class="col-sm-8">
                                <ul class="list-unstyled mb-0">
                                    {foreach from=$everblock_preview_info.ids key=identifier item=value}
                                        <li><strong>{$identifier|escape:'htmlall':'UTF-8'}:</strong> {$value|intval}</li>
                                    {/foreach}
                                </ul>
                            </dd>
                        {/if}
                        {if isset($everblock_preview_info.groups) && $everblock_preview_info.groups}
                            <dt class="col-sm-4 text-muted">{l s='Simulated customer groups:' mod='everblock'}</dt>
                            <dd class="col-sm-8">
                                <ul class="list-unstyled mb-0">
                                    {foreach from=$everblock_preview_info.groups item=group}
                                        <li>
                                            {$group.name|escape:'htmlall':'UTF-8'}
                                            <span class="badge badge-secondary ml-2">#{$group.id|intval}</span>
                                        </li>
                                    {/foreach}
                                </ul>
                            </dd>
                        {/if}
                        {if isset($everblock_preview_info.customer) && isset($everblock_preview_info.customer->id) && $everblock_preview_info.customer->id}
                            <dt class="col-sm-4 text-muted">{l s='Simulated customer:' mod='everblock'}</dt>
                            <dd class="col-sm-8">
                                #{$everblock_preview_info.customer->id|intval}
                                {if isset($everblock_preview_info.customer->firstname) || isset($everblock_preview_info.customer->lastname)}
                                    - {$everblock_preview_info.customer->firstname|escape:'htmlall':'UTF-8'} {$everblock_preview_info.customer->lastname|escape:'htmlall':'UTF-8'}
                                {/if}
                            </dd>
                        {/if}
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3">{l s='Generated HTML' mod='everblock'}</h2>
                    {if isset($everblock_preview_html) && $everblock_preview_html}
                        <div class="everblock-preview-render">
                            {$everblock_preview_html nofilter}
                        </div>
                    {else}
                        <p class="text-muted mb-0">{l s='The block did not return any content for this context.' mod='everblock'}</p>
                    {/if}
                </div>
            </div>
        {/if}

        <div class="mt-4">
            <a class="btn btn-secondary" href="{$everblock_preview_return_url|escape:'htmlall':'UTF-8'}">
                {l s='Back to the administration' mod='everblock'}
            </a>
        </div>
    </section>
{/block}
