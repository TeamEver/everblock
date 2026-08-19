{if isset($everblock_pages)}
  {assign var='everblock_pages_for_builder' value=$everblock_pages}
{elseif isset($attributes.everblock_pages)}
  {assign var='everblock_pages_for_builder' value=$attributes.everblock_pages}
{else}
  {assign var='everblock_pages_for_builder' value=[]}
{/if}

{if isset($everblock_page_links)}
  {assign var='everblock_page_links_for_builder' value=$everblock_page_links}
{elseif isset($attributes.everblock_page_links)}
  {assign var='everblock_page_links_for_builder' value=$attributes.everblock_page_links}
{else}
  {assign var='everblock_page_links_for_builder' value=[]}
{/if}

{if $everblock_pages_for_builder}
  <section class="qcd-block mb-3 qcd-block-cards qcd-block-cards--mobile-stack qcd-dodo qcd-dodo-guides everblock-pages-list everblock-pages-list--builder"
           role="region"
           aria-label="{l s='Guides' d='Modules.Everblock.Front'}"
           style="--qcd-cards-columns-desktop: 3; --qcd-cards-columns-tablet: 1; --qcd-cards-columns-mobile: 1; --qcd-cards-gap: 16px;">
    <div class="qcd-block-cards__grid">
      {foreach from=$everblock_pages_for_builder item=page name=everblockBuilderPages}
        {assign var='pageLink' value='#'}
        {if isset($everblock_page_links_for_builder[$page->id])}
          {assign var='pageLink' value=$everblock_page_links_for_builder[$page->id]}
        {/if}
        {assign var='pageTitle' value=''}
        {if $page->title}
          {assign var='pageTitle' value=$page->title}
        {elseif $page->name}
          {assign var='pageTitle' value=$page->name}
        {/if}
        {assign var='pageDescription' value=''}
        {if $page->short_description}
          {assign var='pageDescription' value=$page->short_description|strip_tags|truncate:150:'...':true}
        {elseif $page->meta_description}
          {assign var='pageDescription' value=$page->meta_description|strip_tags|truncate:150:'...':true}
        {/if}

        <article class="qcd-block-cards__item card h-100">
          <div class="card-body">
            <div class="qcd-block-cards__badge">
              {l s='GUIDE' d='Modules.Everblock.Front'} {$smarty.foreach.everblockBuilderPages.iteration|string_format:"%02d"}
            </div>
            {if $pageTitle}
              <div class="h5 card-title h3">{$pageTitle|escape:'htmlall':'UTF-8'}</div>
            {/if}
            {if $pageDescription}
              <p class="card-text">{$pageDescription|escape:'htmlall':'UTF-8'}</p>
            {/if}
            <a class="btn btn-outline-primary btn-sm"
               href="{$pageLink|escape:'htmlall':'UTF-8'}"
               title="{l s='See the guide' d='Modules.Everblock.Front'}{if $pageTitle} {$pageTitle|escape:'htmlall':'UTF-8'}{/if}"
               aria-label="{l s='See the guide' d='Modules.Everblock.Front'}{if $pageTitle} {$pageTitle|escape:'htmlall':'UTF-8'}{/if}">
              {l s='See the guide' d='Modules.Everblock.Front'}
            </a>
          </div>
        </article>
      {/foreach}
    </div>
  </section>
{else}
  <p class="alert alert-info">{l s='No page available yet.' d='Modules.Everblock.Front'}</p>
{/if}
