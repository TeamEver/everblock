{extends file='page.tpl'}

{block name='page_title'}
  {$smarty.block.parent}
{/block}

{block name='page_content'}
  {if $everblock_prettyblocks_enabled}
    {prettyblocks_zone zone_name=$everblock_prettyblocks_top_zone_name}
  {/if}

  <section class="everblock-pages-list">
    <div class="everpsguide-category-header container-fluid p-0 mb-4">
      <div class="everpsguide-category-hero">
        <div class="everpsguide-category-hero-overlay">
          <h1 class="everpsguide-category-title m-0">{l s='Guides et tutoriels' mod='everblock' d='Modules.Everblock.Front'}</h1>
        </div>
      </div>
    </div>

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
          {assign var='coverImage' value=$page->cover_image_data|default:null}
          <article class="col-12 mb-4 article everpsguide" id="everpsguide-{$page->id|intval}">
            <div class="card h-100 shadow-sm border-0 everpsguide everpsguide-listing-card overflow-hidden">
              <div class="row g-0 h-100 align-items-stretch">
                <div class="col-12 col-lg-6">
                  <div class="article-img text-center mb-0 h-100">
                    <div class="everpsguide-image-wrapper position-relative overflow-hidden h-100" style="aspect-ratio: 16 / 9;">
                      {if $coverImage && $coverImage.url}
                        <a href="{$everblock_page_links[$page->id]|escape:'htmlall':'UTF-8'}" title="{$page->title|default:''|escape:'htmlall':'UTF-8'}" class="d-block h-100">
                          <img src="{$coverImage.url|escape:'htmlall':'UTF-8'}"
                               width="{$coverImage.width|intval}"
                               height="{$coverImage.height|intval}"
                               class="img-fluid w-100 h-100 mx-auto d-block"
                               style="object-fit: cover;"
                               alt="{$coverImage.alt|default:$page->title|default:''|escape:'htmlall':'UTF-8'}"
                               title="{$page->title|default:''|escape:'htmlall':'UTF-8'}"
                               loading="lazy" />
                        </a>
                      {/if}
                    </div>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="card-body d-flex flex-column h-100 p-4">
                    <h2 class="everpsguide article-content h2 mb-3" id="everpsguide-post-title-{$page->id|intval}">
                      <a href="{$everblock_page_links[$page->id]|escape:'htmlall':'UTF-8'}" title="{$page->title|default:''|escape:'htmlall':'UTF-8'}" class="default text-dark text-decoration-none">
                        {$page->title|default:''|escape:'htmlall':'UTF-8'}
                      </a>
                    </h2>
                    <p class="h4 fw-bold text-primary mb-3 text-center text-md-start">{$page->date_upd|date_format:"%d/%m/%Y"}</p>
                    {if $page->short_description}
                      <div class="everpsguidecontent rte mb-3" id="everpsguide-post-content-{$page->id|intval}">{$page->short_description|strip_tags|truncate:220:'...':true}</div>
                    {elseif $page->meta_description}
                      <div class="everpsguidecontent rte mb-3" id="everpsguide-post-content-{$page->id|intval}">{$page->meta_description|truncate:220:'...':true}</div>
                    {/if}
                    <div class="mt-auto text-center text-lg-start">
                      <a href="{$everblock_page_links[$page->id]|escape:'htmlall':'UTF-8'}" class="btn btn-primary rounded-pill px-4 default fw-semibold" title="{$page->title|default:''|escape:'htmlall':'UTF-8'}">
                        {l s='Lire la suite' mod='everblock' d='Modules.Everblock.Front'}
                        <i class="material-icons" aria-hidden="true">chevron_right</i>
                      </a>
                    </div>
                  </div>
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
