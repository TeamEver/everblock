{if isset($everblock_wp_posts) && $everblock_wp_posts|@count > 0}
<section class="everblock-wp-section text-center my-5">
  <div class="h2 section-title text-uppercase mb-4">
    <span>{l s='Latest news from our blog' mod='everblock'}</span>
  </div>

  <div class="everblock-wp-posts container">
    <div class="row justify-content-center align-items-stretch">
      {foreach from=$everblock_wp_posts item=post}
        <div class="col-12 col-sm-6 col-md-4 mb-4 d-flex">
          <div class="card blog-card flex-fill border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            {if $post.featured_image}
              <div class="blog-image-wrapper overflow-hidden">
                <a href="{$post.link|escape:'htmlall':'UTF-8'}"
                   target="_blank"
                   rel="noopener"
                   title="{$post.title|escape:'htmlall':'UTF-8'}">
                  <img src="{$post.featured_image|escape:'htmlall':'UTF-8'}"
                       width="{$post.featured_image_width|intval}"
                       height="{$post.featured_image_height|intval}"
                       loading="lazy"
                       alt="{$post.title|escape:'htmlall':'UTF-8'}"
                       class="card-img-top img-fluid" />
                </a>
              </div>
            {/if}

            <div class="card-body d-flex flex-column justify-content-between text-start p-3">
              <div>
                <div class="h5 fw-bold fs-6 mb-2 text-dark blog-title position-relative">
                  <a href="{$post.link|escape:'htmlall':'UTF-8'}"
                     target="_blank"
                     rel="noopener"
                     title="{$post.title|escape:'htmlall':'UTF-8'}"
                     class="text-dark text-decoration-none">
                    {$post.title|escape:'htmlall':'UTF-8'}
                  </a>
                </div>
                <div class="card-divider mb-2"></div>
                <p class="card-text text-muted small mb-0 line-clamp-5">
                  <a href="{$post.link|escape:'htmlall':'UTF-8'}"
                     target="_blank"
                     rel="noopener"
                     title="{$post.title|escape:'htmlall':'UTF-8'}"
                     class="text-muted text-decoration-none">
                    {$post.excerpt|strip_tags|truncate:100:'…'|escape:'htmlall':'UTF-8'}
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>
      {/foreach}
    </div>

    <div class="text-center mt-3">
      <a href="{$everblock_wp_blog_url|escape:'htmlall':'UTF-8'}" title="{l s='Visit our blog' mod='everblock'}" target="_blank" class="btn btn-warning text-white fw-bold text-uppercase px-4 py-2 rounded-pill">
        {l s='Visit our blog' mod='everblock'}
      </a>
    </div>
  </div>
</section>
{else}
<div class="everblock-wp-posts"></div>
{/if}
