{extends file='page.tpl'}

{block name='page_content'}
  <article class="everblock-page" itemscope itemtype="https://schema.org/Article">
    <header class="everblock-page__header">
      <h1 itemprop="headline">{$everblock_page->title|default:''}</h1>
      {if $everblock_page_image}
        <figure class="everblock-page__cover">
            <img src="{$everblock_page_image}" alt="{$everblock_page->title|default:''|escape:'htmlall':'UTF-8'}" loading="lazy" itemprop="image">
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
  </article>

  {if $everblock_prettyblocks_enabled}
    {prettyblocks_zone zone_name=$everblock_prettyblocks_zone_name}
  {/if}

  {if !empty($everblock_structured_data)}
    <script type="application/ld+json">
      {$everblock_structured_data|json_encode:$smarty.const.JSON_UNESCAPED_SLASHES|replace:'\/':'/' nofilter}
    </script>
  {/if}
{/block}
