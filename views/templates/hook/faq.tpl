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

{if isset($everFaqs) && $everFaqs}
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="accordion" id="faqAccordion">
          {foreach from=$everFaqs item=faq}
            <div class="card">
              <div class="card-header" id="headingEverFaq{$faq->id_everblock_faq|escape:'htmlall':'UTF-8'}">
                <h5 class="mb-0">
                  <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{$faq->id_everblock_faq|escape:'htmlall':'UTF-8'}" aria-expanded="false" aria-controls="collapse{$faq->id_everblock_faq|escape:'htmlall':'UTF-8'}">
                    {$faq->title|escape:'htmlall':'UTF-8'}
                  </button>
                </h5>
              </div>
              <div id="collapse{$faq->id_everblock_faq|escape:'htmlall':'UTF-8'}" class="collapse" aria-labelledby="headingEverFaq{$faq->id_everblock_faq}" data-parent="#faqAccordion">
                <div class="card-body p-1">
                  {$faq->content nofilter}
                </div>
              </div>
            </div>
          {/foreach}
        </div>
      </div>
    </div>
  </div>
{/if}


