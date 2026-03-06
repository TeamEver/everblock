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
{foreach from=$block.states item=$state key=$key}
        {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_row_state_spacing_style'}
        <div class="row {$state.name}{if isset($state.columns_mobile) && $state.columns_mobile} row-cols-{$state.columns_mobile|intval}{/if}{if isset($state.columns_tablet) && $state.columns_tablet} row-cols-sm-{$state.columns_tablet|intval}{/if}{if isset($state.columns_desktop) && $state.columns_desktop} row-cols-lg-{$state.columns_desktop|intval}{/if}{if isset($state.css_class) && $state.css_class} {$state.css_class|escape:'htmlall':'UTF-8'}{/if}" style="{$prettyblock_row_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
                {prettyblocks_zone zone_name="block-row-{$block.id_prettyblocks}-{$key}"}
        </div>
{/foreach}
