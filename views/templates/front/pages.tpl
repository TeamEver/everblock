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
              <h2 class="h4">{$page->name[$everblock_lang_id] ?? ''}</h2>
              {if $page->short_description[$everblock_lang_id]}
                <p class="everblock-page-excerpt">{$page->short_description[$everblock_lang_id]|strip_tags|truncate:180:'...':true}</p>
              {elseif $page->meta_description[$everblock_lang_id]}
                <p class="everblock-page-excerpt">{$page->meta_description[$everblock_lang_id]|truncate:180:'...':true}</p>
              {/if}
              <div class="everblock-page-meta">
                {if $page->date_add}
                  <time datetime="{$page->date_add|escape:'htmlall':'UTF-8'}" class="everblock-page-date">
                    {$page->date_add|date_format:"%d %B %Y"}
                  </time>
                {/if}
                {if $page->date_upd}
                  <span class="everblock-page-date-updated">
                    {l s='Updated on %s' sprintf=[$page->date_upd|date_format:"%d %B %Y"] mod='everblock' d='Modules.Everblock.Front'}
                  </span>
                {/if}
              </div>
            </a>
          </li>
        {/foreach}
      </ul>
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
