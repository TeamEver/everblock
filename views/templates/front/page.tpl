{extends file='page.tpl'}

{block name='page_content'}
  <article class="everblock-page" itemscope itemtype="https://schema.org/Article">
    <header class="everblock-page__header">
      <h1 itemprop="headline">{$everblock_page->title|default:''}</h1>
      {if !empty($everblock_page_image_data.url)}
        <figure class="everblock-page__cover">
            <img src="{$everblock_page_image_data.url|escape:'htmlall':'UTF-8'}"
                 alt="{$everblock_page->title|default:''|escape:'htmlall':'UTF-8'}"
                 loading="lazy"
                 itemprop="image"
                 width="{$everblock_page_image_data.width|intval}"
                 height="{$everblock_page_image_data.height|intval}">
        </figure>
      {/if}
      {if $everblock_page->short_description}
        <div class="everblock-page__intro rte" itemprop="description">
          {$everblock_page->short_description nofilter}
        </div>
      {/if}
      {if $everblock_page->date_add}
        <meta itemprop="datePublished" content="{$everblock_page->date_add|escape:'htmlall':'UTF-8'}">
      {/if}
      {if $everblock_page->date_upd}
        <meta itemprop="dateModified" content="{$everblock_page->date_upd|escape:'htmlall':'UTF-8'}">
      {/if}
    </header>
    <div class="everblock-page__content rte" itemprop="articleBody">
      {$everblock_page_content nofilter}
    </div>
    {if !empty($everblock_page_author)}
      <footer class="everblock-page__footer mt-4">
        <p class="everblock-page__author small text-muted mb-0" itemprop="author" itemscope itemtype="https://schema.org/Person">
          <span class="everblock-page__author-label">{l s='Author' mod='everblock' d='Modules.Everblock.Front'}:</span>
          <span itemprop="name">{$everblock_page_author.name|escape:'htmlall':'UTF-8'}</span>
        </p>
      </footer>
    {/if}
  </article>

  {if $everblock_prettyblocks_enabled}
    {prettyblocks_zone zone_name=$everblock_prettyblocks_zone_name}
  {/if}

  <div class="modal fade everblock-image-modal" id="everblockImageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-body position-relative p-0">
          <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{l s='Close' mod='everblock' d='Modules.Everblock.Front'}"></button>
          <img src="" alt="" class="img-fluid w-100 everblock-image-modal__img" loading="lazy">
          <p class="everblock-image-modal__caption px-4 pb-4 pt-3 mb-0 small text-center text-muted d-none"></p>
        </div>
      </div>
    </div>
  </div>

  {if !empty($everblock_structured_data)}
    <script type="application/ld+json">
      {$everblock_structured_data|json_encode:$smarty.const.JSON_UNESCAPED_SLASHES|replace:'\\/':'/' nofilter}
    </script>
  {/if}
{/block}
