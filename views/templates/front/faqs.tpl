{extends file='page.tpl'}

{block name='page_title'}
  {if $everblock_is_all_faqs_page}
    {l s='FAQ' d='Modules.Everblock.Front'}
  {else}
    {l s='FAQ' d='Modules.Everblock.Front'} - {$everblock_tag_name|escape:'html':'UTF-8'}
  {/if}
{/block}

{block name='page_content'}
  <section class="everblock-faqs-list d-flex flex-column gap-4" aria-label="{l s='Frequently asked questions' d='Modules.Everblock.Front'}">

    {if !empty($everblock_faqs)}
      {assign var='everFaqs' value=$everblock_faqs}
      {include file='module:everblock/views/templates/hook/faq.tpl'}
    {else}
      <p class="alert alert-info">
        {l s='No FAQ available at the moment.' d='Modules.Everblock.Front'}
      </p>
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
