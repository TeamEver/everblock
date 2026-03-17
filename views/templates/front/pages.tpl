{extends file='page.tpl'}

{block name='page_title'}
  {$smarty.block.parent}
{/block}

{block name='page_content'}
  {if $everblock_prettyblocks_enabled}
    {prettyblocks_zone zone_name=$everblock_prettyblocks_top_zone_name}
  {/if}

  <section class="everblock-pages-list">
    <form class="everblock-pages-search mb-4" method="get" action="{$link->getModuleLink('everblock', 'pages')|escape:'htmlall':'UTF-8'}">
      <div class="input-group">
        <span class="input-group-text">{l s='Recherche sur les guides' mod='everblock' d='Modules.Everblock.Front'}</span>
        <input type="search"
               class="form-control"
               name="keyword"
               value="{$everblock_keyword|default:''|escape:'htmlall':'UTF-8'}"
               placeholder="{l s='Rechercher par mot-clé' mod='everblock' d='Modules.Everblock.Front'}" />
        <button type="submit" class="btn btn-primary">{l s='Rechercher' mod='everblock' d='Modules.Everblock.Front'}</button>
      </div>
    </form>
    {if $everblock_pages|@count}
      <div class="everblock-guides-stack">
        {foreach from=$everblock_pages item=page}
          <article class="everblock-guide-list-card">
            <div class="row no-gutters align-items-stretch">
              {assign var='coverImage' value=$page->cover_image_data|default:null}
              <div class="col-lg-5">
                {if $coverImage && $coverImage.url}
                  <div class="everblock-guide-list-card__image-wrap">
                    <img src="{$coverImage.url|escape:'htmlall':'UTF-8'}"
                         alt="{$coverImage.alt|default:$page->title|default:''|escape:'htmlall':'UTF-8'}"
                         class="everblock-guide-list-card__image"
                         loading="lazy"
                         width="{$coverImage.width|intval}"
                         height="{$coverImage.height|intval}" />
                  </div>
                {else}
                  <div class="everblock-guide-list-card__image-wrap"></div>
                {/if}
              </div>

              <div class="col-lg-7">
                <div class="everblock-guide-list-card__content position-relative">
                  <h3 class="everblock-guide-list-card__title">
                    <a href="{$everblock_page_links[$page->id]|escape:'htmlall':'UTF-8'}" class="stretched-link text-decoration-none">
                      {$page->title|default:''|escape:'htmlall':'UTF-8'}
                    </a>
                  </h3>
                  <p class="everblock-guide-list-card__date text-primary">{$page->date_upd|date_format:"%d/%m/%Y"}</p>
                  {if $page->short_description}
                    <p class="everblock-guide-list-card__description">{$page->short_description|strip_tags|truncate:220:'...':true}</p>
                  {elseif $page->meta_description}
                    <p class="everblock-guide-list-card__description">{$page->meta_description|truncate:220:'...':true}</p>
                  {/if}
                  <a href="{$everblock_page_links[$page->id]|escape:'htmlall':'UTF-8'}" class="btn btn-primary everblock-guide-list-card__cta">
                    {l s='Lire la suite' mod='everblock' d='Modules.Everblock.Front'}
                  </a>
                </div>
              </div>
            </div>
          </article>
        {/foreach}
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

  {if $everblock_prettyblocks_enabled}
    {prettyblocks_zone zone_name=$everblock_prettyblocks_bottom_zone_name}
  {/if}

  {if !empty($everblock_structured_data)}
    <script type="application/ld+json">
      {$everblock_structured_data|json_encode:$smarty.const.JSON_UNESCAPED_SLASHES|replace:'\/':'/' nofilter}
    </script>
  {/if}
{/block}
