{extends file='page.tpl'}

{block name='page_title'}
  {$smarty.block.parent}
{/block}

{block name='page_content'}
  <section class="everblock-faqs-list" aria-label="{l s='Frequently asked questions' mod='everblock' d='Modules.Everblock.Front'}">
    <header class="mb-4">
      <div class="everblock-faqs-hero">
        <div>
          {if $everblock_is_all_faqs_page}
            <h1 class="h2 mb-2">{l s='FAQ' mod='everblock' d='Modules.Everblock.Front'}</h1>
            <p class="text-muted mb-0">{l s='Browse every question and answer available across all groups.' mod='everblock' d='Modules.Everblock.Front'}</p>
          {else}
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
              <h1 class="h2 mb-0">{l s='FAQ' mod='everblock' d='Modules.Everblock.Front'}</h1>
              <span class="badge bg-primary everblock-faqs-tag" aria-label="{l s='Current FAQ tag' mod='everblock' d='Modules.Everblock.Front'}">{$everblock_tag_name|escape:'htmlall':'UTF-8'}</span>
            </div>
            <p class="text-muted mb-0">{l s='All frequently asked questions grouped by this tag.' mod='everblock' d='Modules.Everblock.Front'}</p>
          {/if}
        </div>

        {if $everblock_faqs|@count}
          <div class="everblock-faqs-meta">
            <div class="everblock-faqs-meta__item">
              <span class="everblock-faqs-meta__label">{l s='Questions' mod='everblock' d='Modules.Everblock.Front'}</span>
              <strong class="everblock-faqs-meta__value">{$everblock_faqs|@count}</strong>
            </div>
            {if !$everblock_is_all_faqs_page && isset($everblock_tag_name)}
              <div class="everblock-faqs-meta__item">
                <span class="everblock-faqs-meta__label">{l s='Group' mod='everblock' d='Modules.Everblock.Front'}</span>
                <strong class="everblock-faqs-meta__value">{$everblock_tag_name|escape:'htmlall':'UTF-8'}</strong>
              </div>
            {/if}
          </div>
        {/if}
      </div>
    </header>

    {if $everblock_faqs|@count}
      <div class="everblock-faqs-nav mb-4" aria-label="{l s='Jump to a question' mod='everblock' d='Modules.Everblock.Front'}">
        <h2 class="h5 mb-3">{l s='Quick navigation' mod='everblock' d='Modules.Everblock.Front'}</h2>
        <div class="everblock-faqs-nav__list" role="list">
          {foreach from=$everblock_faqs item=faq}
            <a class="everblock-faqs-nav__link" href="#headingEverFaq{$faq->id_everblock_faq}" role="listitem">
              <span class="everblock-faqs-nav__title">{$faq->title}</span>
              <span class="everblock-faqs-nav__pill">{l s='View answer' mod='everblock' d='Modules.Everblock.Front'}</span>
            </a>
          {/foreach}
        </div>
      </div>

      {assign var='everFaqs' value=$everblock_faqs}
      {include file='module:everblock/views/templates/hook/faq.tpl'}

      {if isset($everblock_pagination.total_pages) && $everblock_pagination.total_pages > 1}
        <nav class="mt-4" aria-label="{l s='Pagination' mod='everblock' d='Modules.Everblock.Front'}">
          <ul class="pagination justify-content-center">
            <li class="page-item{if !$everblock_pagination.has_previous} disabled{/if}">
              <a class="page-link" href="{if $everblock_pagination.has_previous}{$everblock_pagination.previous_link|escape:'htmlall':'UTF-8'}{else}#{/if}" aria-label="{l s='Previous' mod='everblock' d='Modules.Everblock.Front'}">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>
            {foreach from=$everblock_pagination.pages item=paginationPage}
              <li class="page-item{if $paginationPage.active} active{/if}">
                <a class="page-link" href="{$paginationPage.link|escape:'htmlall':'UTF-8'}">{$paginationPage.number}</a>
              </li>
            {/foreach}
            <li class="page-item{if !$everblock_pagination.has_next} disabled{/if}">
              <a class="page-link" href="{if $everblock_pagination.has_next}{$everblock_pagination.next_link|escape:'htmlall':'UTF-8'}{else}#{/if}" aria-label="{l s='Next' mod='everblock' d='Modules.Everblock.Front'}">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>
      {/if}
    {else}
      <p class="alert alert-info">{l s='No FAQ available at the moment.' mod='everblock' d='Modules.Everblock.Front'}</p>
    {/if}
  </section>

  {if !empty($everblock_structured_data)}
    <script type="application/ld+json">
      {$everblock_structured_data|json_encode:$smarty.const.JSON_UNESCAPED_SLASHES|replace:'\/':'/' nofilter}
    </script>
  {elseif !empty($everblock_faqs)}
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
          {foreach from=$everblock_faqs item=faq name=faqjson}
            {
              "@type": "Question",
              "name": "{$faq->title|escape:'javascript'}",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "{strip_tags($faq->content)|escape:'javascript'}"
              }
            }{if !$smarty.foreach.faqjson.last},{/if}
          {/foreach}
        ]
      }
    </script>
  {/if}
{/block}
