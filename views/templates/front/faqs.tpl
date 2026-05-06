{extends file='page.tpl'}

{block name='page_title'}
  {if isset($everblock_is_all_faqs_page) && $everblock_is_all_faqs_page}
    {l s='FAQ' d='Modules.Everblock.Front'}
  {else}
    {l s='FAQ' d='Modules.Everblock.Front'} - {$everblock_tag_name|escape:'html':'UTF-8'}
  {/if}
{/block}

{block name='page_content'}
  <section class="d-flex flex-column gap-4" aria-label="{l s='Frequently asked questions' d='Modules.Everblock.Front'}">

    <div class="card border-0 shadow-sm">
      <div class="card-body p-4 p-md-5">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">

          <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">
              {l s='Help center' d='Modules.Everblock.Front'}
            </p>

            <p class="h2 fw-bold mb-3">
              {if isset($everblock_is_all_faqs_page) && $everblock_is_all_faqs_page}
                {l s='Frequently asked questions' d='Modules.Everblock.Front'}
              {else}
                {l s='FAQ' d='Modules.Everblock.Front'}
                <span class="badge rounded-pill text-bg-primary align-middle">
                  {$everblock_tag_name|escape:'html':'UTF-8'}
                </span>
              {/if}
            </p>

            <p class="text-muted mb-0">
              {if isset($everblock_is_all_faqs_page) && $everblock_is_all_faqs_page}
                {l s='Find answers to the most common questions across all topics.' d='Modules.Everblock.Front'}
              {else}
                {l s='Find answers related to this FAQ group.' d='Modules.Everblock.Front'}
              {/if}
            </p>
          </div>

          {if !empty($everblock_faqs)}
            <div class="border rounded-4 bg-light px-4 py-3 text-center flex-shrink-0">
              <span class="d-block text-muted small">
                {l s='Questions' d='Modules.Everblock.Front'}
              </span>
              <strong class="h2 fw-bold mb-0 d-block">
                {$everblock_faqs|count}
              </strong>
            </div>
          {/if}

        </div>
      </div>
    </div>

    {if !empty($everblock_faqs)}
      <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
          {assign var='everFaqs' value=$everblock_faqs}
          {include file='module:everblock/views/templates/hook/faq.tpl'}
        </div>
      </div>
    {else}
      <div class="alert alert-info mb-0" role="alert">
        <strong>{l s='No FAQ available.' d='Modules.Everblock.Front'}</strong>
        <div>{l s='No question has been published at the moment.' d='Modules.Everblock.Front'}</div>
      </div>
    {/if}

    {if isset($everblock_pagination.total_pages) && $everblock_pagination.total_pages > 1}
      <nav aria-label="{l s='Pagination' d='Modules.Everblock.Front'}">
        <ul class="pagination justify-content-center flex-wrap mb-0">

          <li class="page-item{if !$everblock_pagination.has_previous} disabled{/if}">
            <a class="page-link"
               href="{if $everblock_pagination.has_previous}{$everblock_pagination.previous_link|escape:'html':'UTF-8'}{else}#{/if}"
               aria-label="{l s='Previous' d='Modules.Everblock.Front'}">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>

          {foreach from=$everblock_pagination.pages item=paginationPage}
            <li class="page-item{if $paginationPage.active} active{/if}">
              <a class="page-link" href="{$paginationPage.link|escape:'html':'UTF-8'}">
                {$paginationPage.number}
              </a>
            </li>
          {/foreach}

          <li class="page-item{if !$everblock_pagination.has_next} disabled{/if}">
            <a class="page-link"
               href="{if $everblock_pagination.has_next}{$everblock_pagination.next_link|escape:'html':'UTF-8'}{else}#{/if}"
               aria-label="{l s='Next' d='Modules.Everblock.Front'}">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>

        </ul>
      </nav>
    {/if}

    {if !empty($everblock_structured_data)}
      <script type="application/ld+json">
        {$everblock_structured_data|json_encode:$smarty.const.JSON_UNESCAPED_SLASHES|replace:'\/':'/' nofilter}
      </script>
    {/if}

  </section>
{/block}