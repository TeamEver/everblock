{extends file='page.tpl'}

{block name='page_content'}
  <section class="everblock-pages-list">
    {if $everblock_pages|@count}
      <div class="row">
        {include file='module:everblock/views/templates/front/_partials/pages-list-items.tpl'}
      </div>

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
      <p class="alert alert-info">{l s='No page available yet.' mod='everblock' d='Modules.Everblock.Front'}</p>
    {/if}
  </section>


  {if !empty($everblock_structured_data)}
    <script type="application/ld+json">
      {$everblock_structured_data|json_encode:$smarty.const.JSON_UNESCAPED_SLASHES|replace:'\/':'/' nofilter}
    </script>
  {/if}
{/block}
