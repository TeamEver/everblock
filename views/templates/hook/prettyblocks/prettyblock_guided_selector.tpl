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
<div id="block-{$block.id_prettyblocks}" class="{if $block.settings.default.force_full_width}container-fluid px-0 mx-0{elseif $block.settings.default.container}container{/if}">
  <div class="everblock-product-quiz">
    {if isset($block.states) && $block.states}
      {foreach from=$block.states item=state key=key}
        <div class="quiz-question {if $key > 0}d-none{/if}">
          {if $state.question}
            <p>{$state.question|escape:'htmlall':'UTF-8'}</p>
          {/if}
          <div class="quiz-options">
            {assign var=options value=$state.answers|@explode:"\n"}
            {foreach from=$options item=option}
              {assign var=parts value=$option|@explode:"|"}
              {if $parts[0]}
                <button type="button" class="btn btn-primary quiz-answer" data-url="{$parts[1]|default:''|trim|escape:'htmlall':'UTF-8'}">{$parts[0]|trim|escape:'htmlall':'UTF-8'}</button>
              {/if}
            {/foreach}
          </div>
        </div>
      {/foreach}
    {/if}
  </div>
</div>
