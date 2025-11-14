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

{if isset($everFaqs) && $everFaqs}
  <div id="everblockFaq">
    <div class="accordion faq-accordion" id="faqAccordion">
      {foreach from=$everFaqs item=faq name=faqloop}
        <div class="accordion-item card mb-4">
          <div class="accordion-header card-header p-0 h2" id="headingEverFaq{$faq->id_everblock_faq}">
            <button class="accordion-button btn btn-link faq-question d-flex justify-content-between align-items-center w-100 text-body py-3 px-4 {if !$smarty.foreach.faqloop.first}collapsed{/if}" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#collapse{$faq->id_everblock_faq}" data-bs-target="#collapse{$faq->id_everblock_faq}"
                    aria-expanded="{if $smarty.foreach.faqloop.first}true{else}false{/if}" aria-controls="collapse{$faq->id_everblock_faq}">
              <span>{$faq->title}</span><i class="icon-angle-down" aria-hidden="true"></i>
            </button>
          </div>
          <div id="collapse{$faq->id_everblock_faq}" class="accordion-collapse collapse {if $smarty.foreach.faqloop.first}show{/if}"
               aria-labelledby="headingEverFaq{$faq->id_everblock_faq}" data-parent="#faqAccordion" data-bs-parent="#faqAccordion">
            <div class="accordion-body card-body faq-answer">{$faq->content nofilter}</div>
          </div>
        </div>
      {/foreach}
    </div>
  </div>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {foreach from=$everFaqs item=faq name=faqjson}
      {
        "@type": "Question",
        "name": "{$faq->title|escape:'htmlall':'UTF-8'}",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "{strip_tags($faq->content)|escape:'htmlall':'UTF-8'}"
        }
      }{if !$smarty.foreach.faqjson.last},{/if}
      {/foreach}
    ]
  }
  </script>
{/if}
