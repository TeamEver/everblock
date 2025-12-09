{extends file='page.tpl'}

{block name='page_title'}
  {$smarty.block.parent}
{/block}

{block name='page_content'}
  <section class="everblock-pages-list">
    {if $everblock_pages|@count}
      <ul class="everblock-pages">
        {foreach from=$everblock_pages item=page}
          <li class="everblock-page-item">
            <a href="{$everblock_page_links[$page->id]|escape:'htmlall':'UTF-8'}" class="everblock-page-link">
              <h2 class="h4">{$page->name[$everblock_lang_id]|default:''}</h2>
              {if $page->meta_description[$everblock_lang_id]}
                <p class="everblock-page-excerpt">{$page->meta_description[$everblock_lang_id]|truncate:180:'...':true}</p>
              {/if}
            </a>
          </li>
        {/foreach}
      </ul>
    {else}
      <p class="alert alert-info">{l s='No page available yet.' mod='everblock' d='Modules.Everblock.Front'}</p>
    {/if}
  </section>
{/block}
