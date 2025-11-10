{if isset($everblock_wp_posts) && $everblock_wp_posts|@count > 0}
<section class="everblock-wp-section text-center my-5">
  <h2 class="section-title text-uppercase mb-4">
    <span>Les dernières actus de notre blog</span>
  </h2>

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
                  {$post.title|escape:'htmlall':'UTF-8'}
                </div>
                <div class="card-divider mb-2"></div>
                <p class="card-text text-muted small mb-0 line-clamp-5">
                  {$post.excerpt|strip_tags|truncate:100:'…'|escape:'htmlall':'UTF-8'}
                </p>
              </div>
            </div>
          </div>
        </div>
      {/foreach}
    </div>

    <div class="text-center mt-3">
      <a href="/blog" class="btn btn-warning fw-bold text-uppercase px-4 py-2 rounded-pill">
        Visiter notre blog
      </a>
    </div>
  </div>
</section>
{else}
<div class="everblock-wp-posts"></div>
{/if}
