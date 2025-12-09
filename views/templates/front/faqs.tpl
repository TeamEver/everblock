{extends file='page.tpl'}

{block name='page_title'}
  {$smarty.block.parent}
{/block}

{block name='page_content'}
  <section class="everblock-faqs-list">
    <header class="mb-4">
      {if $everblock_is_all_faqs_page}
        <h1 class="h2 mb-2">{l s='FAQ' mod='everblock' d='Modules.Everblock.Front'}</h1>
        <p class="text-muted mb-0">{l s='All frequently asked questions across every available group.' mod='everblock' d='Modules.Everblock.Front'}</p>
      {else}
        <h1 class="h2 mb-2">{l s='FAQ' mod='everblock' d='Modules.Everblock.Front'} - {$everblock_tag_name|escape:'htmlall':'UTF-8'}</h1>
        <p class="text-muted mb-0">{l s='All frequently asked questions grouped by this tag.' mod='everblock' d='Modules.Everblock.Front'}</p>
      {/if}
    </header>

    {if $everblock_faqs|@count}
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
