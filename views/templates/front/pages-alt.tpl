{if $everblock_pages|@count}
  <section class="everblock-pages-list everblock-pages-list--builder">
    <div class="row">
      {foreach from=$everblock_pages item=page}
        <div class="col-md-6 col-lg-4 mb-4">
          <article class="card h-100 shadow-sm border-0">
            {assign var='coverImage' value=$page->cover_image_data|default:null}
            {if $coverImage && $coverImage.url}
              <div class="position-relative overflow-hidden rounded-top"
                   style="aspect-ratio: {$coverImage.width|intval}/{$coverImage.height|intval};">
                <img src="{$coverImage.url|escape:'htmlall':'UTF-8'}"
                     alt="{$coverImage.alt|default:$page->title|default:''|escape:'htmlall':'UTF-8'}"
                     class="w-100 h-100"
                     style="object-fit: cover;"
                     loading="lazy"
                     width="{$coverImage.width|intval}"
                     height="{$coverImage.height|intval}" />
              </div>
            {/if}
            <div class="card-body d-flex flex-column">
              <h3 class="h5 card-title text-primary">
                <a href="{$everblock_page_links[$page->id]|escape:'htmlall':'UTF-8'}" class="stretched-link text-decoration-none">
                  {$page->title|default:''|escape:'htmlall':'UTF-8'}
                </a>
              </h3>
              {if $page->short_description}
                <p class="card-text text-muted">{$page->short_description|strip_tags|truncate:180:'...':true}</p>
              {elseif $page->meta_description}
                <p class="card-text text-muted">{$page->meta_description|truncate:180:'...':true}</p>
              {/if}
            </div>
          </article>
        </div>
      {/foreach}
    </div>
  </section>
{else}
  <p class="alert alert-info">{l s='No page available yet.' mod='everblock' d='Modules.Everblock.Front'}</p>
{/if}
