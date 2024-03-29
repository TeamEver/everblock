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
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<!-- Module Ever Block -->
{if isset($block.settings.manufacturer) && isset($smarty.get.id_manufacturer) && $smarty.get.id_manufacturer == $block.settings.manufacturer.id}
<div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    <div class="everblock {$block.settings.css_class|escape:'htmlall':'UTF-8'} {$block.settings.bootstrap_class|escape:'htmlall':'UTF-8'}" {if isset($block.settings.bg_color) && $block.settings.bg_color} style="background-color:{$block.settings.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
        {$block.settings.content nofilter}
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
{/if}
<!-- /Module Ever Block -->