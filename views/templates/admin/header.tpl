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
        {if isset($everblock_tabs) && $everblock_tabs}
            <ul class="nav nav-tabs everblock-config-nav mt-2" role="tablist">
                {foreach from=$everblock_tabs key=tabId item=tab name=everblockTabs}
                    <li class="nav-item">
                        <a class="nav-link{if $smarty.foreach.everblockTabs.first} active{/if}" data-toggle="tab" role="tab" href="#{$tabId|escape:'htmlall':'UTF-8'}">
                            {if isset($tab.icon) && $tab.icon}
                                <i class="{$tab.icon|escape:'htmlall':'UTF-8'}"></i>
                            {/if}
                            {$tab.title|escape:'htmlall':'UTF-8'}
                        </a>
                    </li>
                {/foreach}
            </ul>
        {/if}
    </div>
    <div class="col-md-4 text-right mt-3">
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
{if isset($everblock_tabs) && $everblock_tabs}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var headerLinks = Array.prototype.slice.call(document.querySelectorAll('.everblock-config-nav .nav-link'));
        if (!headerLinks.length) {
            return;
        }

        function setActiveLink(target) {
            headerLinks.forEach(function (link) {
                if (link.getAttribute('href') === target) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }

        headerLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                var target = this.getAttribute('href');
                var helperLink = document.querySelector('#configuration_form .nav-tabs a[href="' + target + '"]');
                if (helperLink) {
                    helperLink.click();
                }
                setActiveLink(target);
            });
        });

        var helperTabs = Array.prototype.slice.call(document.querySelectorAll('#configuration_form .nav-tabs a'));
        helperTabs.forEach(function (link) {
            link.addEventListener('click', function () {
                setActiveLink(this.getAttribute('href'));
            });
        });

        var activeHelper = document.querySelector('#configuration_form .nav-tabs li.active a');
        if (activeHelper) {
            setActiveLink(activeHelper.getAttribute('href'));
        } else {
            setActiveLink(headerLinks[0].getAttribute('href'));
        }
    });
</script>
{/if}
