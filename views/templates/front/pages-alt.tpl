{if $everblock_pages|@count}
  <section class="everblock-pages-list everblock-pages-list--builder">
    <div class="row">
      {include file='module:everblock/views/templates/front/_partials/pages-list-items.tpl'}
    </div>
  </section>
{else}
  <p class="alert alert-info">{l s='No page available yet.' mod='everblock' d='Modules.Everblock.Front'}</p>
{/if}
