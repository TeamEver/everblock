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
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}
{capture name='prettyblock_faq_selection_wrapper_style'}
  {$prettyblock_spacing_style}
{/capture}
{assign var='prettyblock_faq_selection_wrapper_style' value=$smarty.capture.prettyblock_faq_selection_wrapper_style|trim}

<section id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}{$prettyblock_visibility_class}"{if $prettyblock_faq_selection_wrapper_style} style="{$prettyblock_faq_selection_wrapper_style}"{/if}>
  {if $block.settings.title}
    <h2 class="mb-4">{$block.settings.title|escape:'htmlall':'UTF-8'}</h2>
  {/if}
  {assign var=faqIds value=[]}
  {if isset($block.states) && $block.states}
    {foreach from=$block.states item=state}
      {assign var=faqId value=0}
      {if isset($state.faq.id) && $state.faq.id}
        {assign var=faqId value=$state.faq.id|intval}
      {elseif isset($state.faq.id_everblock_faq) && $state.faq.id_everblock_faq}
        {assign var=faqId value=$state.faq.id_everblock_faq|intval}
      {elseif $state.faq|default:''}
        {assign var=faqId value=$state.faq|intval}
      {/if}
      {if $faqId}
        {assign var=faqIds value=$faqIds|@array_merge:[$faqId]}
      {/if}
    {/foreach}
  {/if}
  {assign var=faqIds value=$faqIds|@array_unique}
  {if $faqIds}
    {assign var=context value=Context::getContext()}
    {assign var=everFaqs value=EverblockFaq::getByIds($faqIds, (int) $context->language->id, (int) $context->shop->id, true)}
    {if $everFaqs}
      {include file='module:everblock/views/templates/hook/faq.tpl'}
    {/if}
  {/if}
</section>
