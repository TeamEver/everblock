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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
    <div class="{if $block.settings.default.container}container{/if}">
      {foreach from=$block.states item=state name=questions}
        {assign var=questionKey value=$state.question|link_rewrite}
        <div id="guided-step-{$block.id_prettyblocks}-{$smarty.foreach.questions.index}" class="everblock-guided-step{if !$smarty.foreach.questions.first} d-none{/if}" data-question="{$questionKey|escape:'htmlall':'UTF-8'}">
          <p class="guided-question">{$state.question|escape:'htmlall':'UTF-8'}</p>
          <div class="guided-answers">
            {foreach from=$state.answers item=answer}
              {if $answer.text}
                {assign var=answerValue value=$answer.text|link_rewrite}
                <button type="button" class="btn btn-primary guided-answer" data-value="{$answerValue|escape:'htmlall':'UTF-8'}"{if $answer.link} data-url="{$answer.link|escape:'htmlall':'UTF-8'}"{/if}>{$answer.text|escape:'htmlall':'UTF-8'}</button>
              {/if}
            {/foreach}
          </div>
        </div>
      {/foreach}
    </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
