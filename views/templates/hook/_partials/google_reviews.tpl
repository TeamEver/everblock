{*
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{assign var=reviews value=$googleReviewsData.reviews|default:[]}
<div class="everblock-google-reviews bg-white border rounded-4 p-4 shadow-sm{if $googleReviewsOptions.css_class} {$googleReviewsOptions.css_class|escape:'htmlall':'UTF-8'}{/if}"{if isset($googleReviewsOptions.place_id) && $googleReviewsOptions.place_id} data-place-id="{$googleReviewsOptions.place_id|escape:'htmlall':'UTF-8'}"{/if}>
  <div class="row g-4 align-items-start">
    <aside class="everblock-google-reviews__aside col-12 col-lg-4 col-xl-3 d-flex flex-column gap-3 align-items-start align-self-start">
      <header class="d-flex flex-column gap-2">
        {if $googleReviewsHeading}
          <h3 class="h4 fw-bold mb-0">{$googleReviewsHeading|escape:'htmlall':'UTF-8'}</h3>
        {/if}
        {if $googleReviewsIntro}
          <div class="text-muted">{$googleReviewsIntro nofilter}</div>
        {/if}
      </header>
      {if $googleReviewsOptions.show_rating && $googleReviewsData.rating}
        <div class="border rounded-3 p-3 bg-light d-flex flex-column gap-2">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fs-3 fw-bold">{$googleReviewsData.rating|number_format:1}</span>
            <span aria-hidden="true">
              {section name=globalStar loop=5}
                {assign var=position value=$smarty.section.globalStar.index+1}
                <span class="{if $googleReviewsData.rating >= $position}text-warning{else}text-muted{/if}">★</span>
              {/section}
            </span>
            <span class="sr-only">{l s='%1$s out of %2$s' sprintf=[$googleReviewsData.rating|number_format:1, 5] mod='everblock'}</span>
          </div>
          {if $googleReviewsData.user_ratings_total}
            <p class="mb-0 text-muted small">
              {l s='Based on %s reviews' sprintf=[$googleReviewsData.user_ratings_total] mod='everblock'}
            </p>
          {/if}
        </div>
      {/if}
      <div class="d-inline-flex align-items-center gap-1 fw-bold fs-4 bg-light border rounded-pill px-2 py-1" aria-label="Google">
        <span class="text-primary">G</span>
        <span class="text-danger">o</span>
        <span class="text-warning">o</span>
        <span class="text-primary">g</span>
        <span class="text-success">l</span>
        <span class="text-danger">e</span>
      </div>
      {if $googleReviewsOptions.show_cta && $googleReviewsOptions.cta_url}
        <div class="everblock-google-reviews__cta">
          <a class="btn btn-primary btn-lg" href="{$googleReviewsOptions.cta_url|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener nofollow" aria-label="{l s='Read all reviews on Google' mod='everblock'}">
            {if $googleReviewsOptions.cta_label}{$googleReviewsOptions.cta_label|escape:'htmlall':'UTF-8'}{else}{l s='Read all reviews on Google' mod='everblock'}{/if}
          </a>
        </div>
      {/if}
    </aside>
    <div class="everblock-google-reviews__content col-12 col-lg-8 col-xl-9">
      {if $reviews}
        {assign var=reviewCount value=$reviews|count}
        {assign var=carouselSuffix value=$googleReviewsOptions.place_id|default:$smarty.now}

        <div id="everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-lg" class="carousel slide d-none d-lg-block" data-bs-interval="false" data-bs-pause="hover" aria-label="{l s='Google reviews carousel' mod='everblock'}">
          <div class="carousel-inner">
            {foreach from=$reviews|@array_chunk:3 item=reviewGroup name=reviewsLg}
              <div class="carousel-item{if $smarty.foreach.reviewsLg.first} active{/if}">
                <div class="row g-4">
                  {foreach from=$reviewGroup item=review}
                    <div class="col-12 col-lg-4">
                      <article class="card h-100 d-flex flex-column border-0 shadow-sm everblock-google-reviews__card">
                        <div class="card-body d-flex flex-column gap-3">
                          <header class="everblock-google-reviews__header d-flex gap-3">
                            {if $googleReviewsOptions.show_avatar && $review.profile_photo_url}
                              <div class="everblock-google-reviews__avatar flex-shrink-0 overflow-hidden rounded-circle bg-light">
                                <img src="{$review.profile_photo_url|escape:'htmlall':'UTF-8'}" alt="{$review.author_name|escape:'htmlall':'UTF-8'}" loading="lazy" class="img-fluid rounded-circle" width="56" height="56">
                              </div>
                            {elseif $googleReviewsOptions.show_avatar}
                              <div class="everblock-google-reviews__avatar flex-shrink-0 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-semibold" aria-hidden="true">
                                <span>{$review.author_name|default:'?'|truncate:1:""|escape:'htmlall':'UTF-8'}</span>
                              </div>
                            {/if}
                            <div class="everblock-google-reviews__body flex-grow-1">
                              {if $review.author_name}
                                <p class="mb-1 fw-semibold">
                                  {if $review.author_url}
                                    <a href="{$review.author_url|escape:'htmlall':'UTF-8'}" rel="noopener nofollow" target="_blank">{$review.author_name|escape:'htmlall':'UTF-8'}</a>
                                  {else}
                                    {$review.author_name|escape:'htmlall':'UTF-8'}
                                  {/if}
                                </p>
                              {/if}
                              {if $review.relative_time_description}
                                <p class="mb-2 small text-muted">{$review.relative_time_description|escape:'htmlall':'UTF-8'}</p>
                              {/if}
                              {if $review.rating}
                                <div class="everblock-google-reviews__score d-flex align-items-center gap-2" aria-label="{l s='%1$s out of %2$s' sprintf=[$review.rating|number_format:1, 5] mod='everblock'}">
                                  <span class="fw-semibold">{$review.rating|number_format:1}</span>
                                  <span aria-hidden="true">
                                    {section name=item loop=5}
                                      {assign var=position value=$smarty.section.item.index+1}
                                      <span class="{if $review.rating >= $position}text-warning{else}text-muted{/if}">★</span>
                                    {/section}
                                  </span>
                                </div>
                              {/if}
                            </div>
                          </header>
                          {if $review.text}
                            <div class="flex-grow-1">
                              <p class="everblock-google-reviews__text mb-0">{$review.text|escape:'htmlall':'UTF-8'}</p>
                            </div>
                          {/if}
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0"></div>
                      </article>
                    </div>
                  {/foreach}
                </div>
              </div>
            {/foreach}
          </div>
          {if $reviewCount > 3}
            <button class="carousel-control-prev" type="button" data-bs-target="#everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-lg" data-bs-slide="prev" aria-label="{l s='Previous reviews' mod='everblock'}">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-lg" data-bs-slide="next" aria-label="{l s='Next reviews' mod='everblock'}">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
            </button>
          {/if}
        </div>

        <div id="everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-md" class="carousel slide d-none d-md-block d-lg-none" data-bs-interval="false" data-bs-pause="hover" aria-label="{l s='Google reviews carousel' mod='everblock'}">
          <div class="carousel-inner">
            {foreach from=$reviews|@array_chunk:2 item=reviewGroup name=reviewsMd}
              <div class="carousel-item{if $smarty.foreach.reviewsMd.first} active{/if}">
                <div class="row g-4">
                  {foreach from=$reviewGroup item=review}
                    <div class="col-12 col-md-6">
                      <article class="card h-100 d-flex flex-column border-0 shadow-sm everblock-google-reviews__card">
                        <div class="card-body d-flex flex-column gap-3">
                          <header class="everblock-google-reviews__header d-flex gap-3">
                            {if $googleReviewsOptions.show_avatar && $review.profile_photo_url}
                              <div class="everblock-google-reviews__avatar flex-shrink-0 overflow-hidden rounded-circle bg-light">
                                <img src="{$review.profile_photo_url|escape:'htmlall':'UTF-8'}" alt="{$review.author_name|escape:'htmlall':'UTF-8'}" loading="lazy" class="img-fluid rounded-circle" width="56" height="56">
                              </div>
                            {elseif $googleReviewsOptions.show_avatar}
                              <div class="everblock-google-reviews__avatar flex-shrink-0 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-semibold" aria-hidden="true">
                                <span>{$review.author_name|default:'?'|truncate:1:""|escape:'htmlall':'UTF-8'}</span>
                              </div>
                            {/if}
                            <div class="everblock-google-reviews__body flex-grow-1">
                              {if $review.author_name}
                                <p class="mb-1 fw-semibold">
                                  {if $review.author_url}
                                    <a href="{$review.author_url|escape:'htmlall':'UTF-8'}" rel="noopener nofollow" target="_blank">{$review.author_name|escape:'htmlall':'UTF-8'}</a>
                                  {else}
                                    {$review.author_name|escape:'htmlall':'UTF-8'}
                                  {/if}
                                </p>
                              {/if}
                              {if $review.relative_time_description}
                                <p class="mb-2 small text-muted">{$review.relative_time_description|escape:'htmlall':'UTF-8'}</p>
                              {/if}
                              {if $review.rating}
                                <div class="everblock-google-reviews__score d-flex align-items-center gap-2" aria-label="{l s='%1$s out of %2$s' sprintf=[$review.rating|number_format:1, 5] mod='everblock'}">
                                  <span class="fw-semibold">{$review.rating|number_format:1}</span>
                                  <span aria-hidden="true">
                                    {section name=item loop=5}
                                      {assign var=position value=$smarty.section.item.index+1}
                                      <span class="{if $review.rating >= $position}text-warning{else}text-muted{/if}">★</span>
                                    {/section}
                                  </span>
                                </div>
                              {/if}
                            </div>
                          </header>
                          {if $review.text}
                            <div class="flex-grow-1">
                              <p class="everblock-google-reviews__text mb-0">{$review.text|escape:'htmlall':'UTF-8'}</p>
                            </div>
                          {/if}
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0"></div>
                      </article>
                    </div>
                  {/foreach}
                </div>
              </div>
            {/foreach}
          </div>
          {if $reviewCount > 2}
            <button class="carousel-control-prev" type="button" data-bs-target="#everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-md" data-bs-slide="prev" aria-label="{l s='Previous reviews' mod='everblock'}">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-md" data-bs-slide="next" aria-label="{l s='Next reviews' mod='everblock'}">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
            </button>
          {/if}
        </div>

        <div id="everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-sm" class="carousel slide d-md-none" data-bs-interval="false" data-bs-pause="hover" aria-label="{l s='Google reviews carousel' mod='everblock'}">
          <div class="carousel-inner">
            {foreach from=$reviews|@array_chunk:1 item=reviewGroup name=reviewsSm}
              <div class="carousel-item{if $smarty.foreach.reviewsSm.first} active{/if}">
                <div class="row g-4">
                  {foreach from=$reviewGroup item=review}
                    <div class="col-12">
                      <article class="card h-100 d-flex flex-column border-0 shadow-sm everblock-google-reviews__card">
                        <div class="card-body d-flex flex-column gap-3">
                          <header class="everblock-google-reviews__header d-flex gap-3">
                            {if $googleReviewsOptions.show_avatar && $review.profile_photo_url}
                              <div class="everblock-google-reviews__avatar flex-shrink-0 overflow-hidden rounded-circle bg-light">
                                <img src="{$review.profile_photo_url|escape:'htmlall':'UTF-8'}" alt="{$review.author_name|escape:'htmlall':'UTF-8'}" loading="lazy" class="img-fluid rounded-circle" width="56" height="56">
                              </div>
                            {elseif $googleReviewsOptions.show_avatar}
                              <div class="everblock-google-reviews__avatar flex-shrink-0 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-semibold" aria-hidden="true">
                                <span>{$review.author_name|default:'?'|truncate:1:""|escape:'htmlall':'UTF-8'}</span>
                              </div>
                            {/if}
                            <div class="everblock-google-reviews__body flex-grow-1">
                              {if $review.author_name}
                                <p class="mb-1 fw-semibold">
                                  {if $review.author_url}
                                    <a href="{$review.author_url|escape:'htmlall':'UTF-8'}" rel="noopener nofollow" target="_blank">{$review.author_name|escape:'htmlall':'UTF-8'}</a>
                                  {else}
                                    {$review.author_name|escape:'htmlall':'UTF-8'}
                                  {/if}
                                </p>
                              {/if}
                              {if $review.relative_time_description}
                                <p class="mb-2 small text-muted">{$review.relative_time_description|escape:'htmlall':'UTF-8'}</p>
                              {/if}
                              {if $review.rating}
                                <div class="everblock-google-reviews__score d-flex align-items-center gap-2" aria-label="{l s='%1$s out of %2$s' sprintf=[$review.rating|number_format:1, 5] mod='everblock'}">
                                  <span class="fw-semibold">{$review.rating|number_format:1}</span>
                                  <span aria-hidden="true">
                                    {section name=item loop=5}
                                      {assign var=position value=$smarty.section.item.index+1}
                                      <span class="{if $review.rating >= $position}text-warning{else}text-muted{/if}">★</span>
                                    {/section}
                                  </span>
                                </div>
                              {/if}
                            </div>
                          </header>
                          {if $review.text}
                            <div class="flex-grow-1">
                              <p class="everblock-google-reviews__text mb-0">{$review.text|escape:'htmlall':'UTF-8'}</p>
                            </div>
                          {/if}
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0"></div>
                      </article>
                    </div>
                  {/foreach}
                </div>
              </div>
            {/foreach}
          </div>
          {if $reviewCount > 1}
            <button class="carousel-control-prev" type="button" data-bs-target="#everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-sm" data-bs-slide="prev" aria-label="{l s='Previous reviews' mod='everblock'}">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">{l s='Previous' mod='everblock'}</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#everblock-google-reviews-carousel-{$carouselSuffix|escape:'htmlall':'UTF-8'}-sm" data-bs-slide="next" aria-label="{l s='Next reviews' mod='everblock'}">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">{l s='Next' mod='everblock'}</span>
            </button>
          {/if}
        </div>
      {elseif $googleReviewsOptions.is_configured}
        <p class="everblock-google-reviews__empty text-muted mb-0">{l s='No Google reviews available yet.' mod='everblock'}</p>
      {/if}
    </div>
  </div>
</div>
