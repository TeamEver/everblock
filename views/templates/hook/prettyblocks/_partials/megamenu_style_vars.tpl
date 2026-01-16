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
{strip}
{assign var='style_vars' value=''}

{assign var='text_color' value=$block.settings.text_color|default:''}
{if is_array($text_color)}
  {if isset($language.id_lang) && isset($text_color[$language.id_lang])}
    {assign var='text_color' value=$text_color[$language.id_lang]}
  {else}
    {assign var='text_color' value=$text_color|@reset}
  {/if}
{/if}
{if $text_color}
  {assign var='style_vars' value=$style_vars|cat:'--everblock-megamenu-text:'|cat:$text_color|cat:';'}
{/if}

{assign var='text_color_winter' value=$block.settings.text_color_winter|default:''}
{if is_array($text_color_winter)}
  {if isset($language.id_lang) && isset($text_color_winter[$language.id_lang])}
    {assign var='text_color_winter' value=$text_color_winter[$language.id_lang]}
  {else}
    {assign var='text_color_winter' value=$text_color_winter|@reset}
  {/if}
{/if}
{if $text_color_winter}
  {assign var='style_vars' value=$style_vars|cat:'--everblock-megamenu-text-winter:'|cat:$text_color_winter|cat:';'}
{/if}

{assign var='background_color' value=$block.settings.background_color|default:''}
{if is_array($background_color)}
  {if isset($language.id_lang) && isset($background_color[$language.id_lang])}
    {assign var='background_color' value=$background_color[$language.id_lang]}
  {else}
    {assign var='background_color' value=$background_color|@reset}
  {/if}
{/if}
{if $background_color}
  {assign var='style_vars' value=$style_vars|cat:'--everblock-megamenu-bg:'|cat:$background_color|cat:';'}
{/if}

{assign var='background_color_winter' value=$block.settings.background_color_winter|default:''}
{if is_array($background_color_winter)}
  {if isset($language.id_lang) && isset($background_color_winter[$language.id_lang])}
    {assign var='background_color_winter' value=$background_color_winter[$language.id_lang]}
  {else}
    {assign var='background_color_winter' value=$background_color_winter|@reset}
  {/if}
{/if}
{if $background_color_winter}
  {assign var='style_vars' value=$style_vars|cat:'--everblock-megamenu-bg-winter:'|cat:$background_color_winter|cat:';'}
{/if}

{assign var='hover_text_color' value=$block.settings.hover_text_color|default:''}
{if is_array($hover_text_color)}
  {if isset($language.id_lang) && isset($hover_text_color[$language.id_lang])}
    {assign var='hover_text_color' value=$hover_text_color[$language.id_lang]}
  {else}
    {assign var='hover_text_color' value=$hover_text_color|@reset}
  {/if}
{/if}
{if $hover_text_color}
  {assign var='style_vars' value=$style_vars|cat:'--everblock-megamenu-hover-text:'|cat:$hover_text_color|cat:';'}
{/if}

{assign var='hover_background_color' value=$block.settings.hover_background_color|default:''}
{if is_array($hover_background_color)}
  {if isset($language.id_lang) && isset($hover_background_color[$language.id_lang])}
    {assign var='hover_background_color' value=$hover_background_color[$language.id_lang]}
  {else}
    {assign var='hover_background_color' value=$hover_background_color|@reset}
  {/if}
{/if}
{if $hover_background_color}
  {assign var='style_vars' value=$style_vars|cat:'--everblock-megamenu-hover-bg:'|cat:$hover_background_color|cat:';'}
{/if}

{$style_vars|trim}
{/strip}
