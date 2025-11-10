{* Auto generated WordPress posts template *}
{if isset($everblock_wp_posts) && $everblock_wp_posts|@count > 0}
    <div class="row row-cols-1 row-cols-md-3 g-4 everblock-wp-posts">
        {foreach from=$everblock_wp_posts item=post}
            <div class="col">
                <div class="card h-100">
                    {if $post.featured_image}
                        <a href="{$post.link|escape:'htmlall':'UTF-8'}" class="obfme" target="_blank" rel="noopener" title="{$post.title|escape:'htmlall':'UTF-8'}">
                            <img src="{$post.featured_image|escape:'htmlall':'UTF-8'}" width="{$post.featured_image_width|intval}" height="{$post.featured_image_height|intval}" loading="lazy" alt="{$post.title|escape:'htmlall':'UTF-8'}" class="card-img-top img-fluid" />
                        </a>
                    {/if}
                    <div class="card-body">
                        <h5 class="card-title">{$post.title|escape:'htmlall':'UTF-8'}</h5>
                        <p class="card-text">{$post.excerpt|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{else}
    <div class="everblock-wp-posts"></div>
{/if}
