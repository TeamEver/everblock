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
    <div class="accordion faq-accordion everblock-faq-accordion" id="faqAccordion">
      {foreach from=$everFaqs item=faq name=faqloop}
        <article class="accordion-item card mb-4 everblock-faq-item" itemscope itemtype="https://schema.org/Question">
          <div class="accordion-header card-header p-0 h2" id="headingEverFaq{$faq->id_everblock_faq}">
            <div class="d-flex align-items-center gap-2 px-4 py-3">
              <button class="accordion-button btn btn-link faq-question flex-grow-1 d-flex justify-content-between align-items-center w-100 text-body p-0 {if !$smarty.foreach.faqloop.first}collapsed{/if}" type="button" data-toggle="collapse" data-bs-toggle="collapse" data-target="#collapse{$faq->id_everblock_faq}" data-bs-target="#collapse{$faq->id_everblock_faq}"
                      aria-expanded="{if $smarty.foreach.faqloop.first}true{else}false{/if}" aria-controls="collapse{$faq->id_everblock_faq}" itemprop="name">
                <span>{$faq->title}</span>
                <span class="everblock-faq-icon" aria-hidden="true"></span>
              </button>
              {if isset($faq->tag_link) && $faq->tag_link}
                <a class="badge bg-secondary text-decoration-none everblock-faq-chip" href="{$faq->tag_link|escape:'htmlall':'UTF-8'}" title="{l s='View all questions from the %s group' sprintf=[$faq->tag_name] mod='everblock' d='Modules.Everblock.Front'}">
                  {$faq->tag_name|escape:'htmlall':'UTF-8'}
                </a>
              {/if}
            </div>
          </div>
          <div id="collapse{$faq->id_everblock_faq}" class="accordion-collapse collapse {if $smarty.foreach.faqloop.first}show{/if}"
               aria-labelledby="headingEverFaq{$faq->id_everblock_faq}" data-parent="#faqAccordion" data-bs-parent="#faqAccordion">
            <div class="accordion-body card-body faq-answer" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
              <div itemprop="text">{$faq->content nofilter}</div>
            </div>
          </div>
        </article>
      {/foreach}
    </div>
  </div>
  {if empty($everblock_structured_data)}
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
{/if}
