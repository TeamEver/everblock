{extends file='page.tpl'}

{block name='page_title'}
  {$everblock_page->title[$everblock_lang_id] ?? ''}
{/block}

{block name='page_content'}
  <article class="everblock-page" itemscope itemtype="https://schema.org/Article">
    <header class="everblock-page__header">
      <h1 itemprop="headline">{$everblock_page->name[$everblock_lang_id] ?? ''}</h1>
      {if $everblock_page_image}
        <figure class="everblock-page__cover">
          <img src="{$everblock_page_image}" alt="{$everblock_page->title[$everblock_lang_id]|escape:'htmlall':'UTF-8'}" loading="lazy" itemprop="image">
        </figure>
      {/if}
    </header>
    <div class="everblock-page__content rte" itemprop="articleBody">
      {$everblock_page_content nofilter}
    </div>
  </article>
{/block}
