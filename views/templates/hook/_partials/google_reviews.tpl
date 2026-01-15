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
{assign var=columns value=$googleReviewsOptions.columns|default:3}
{assign var=columns value=$columns|intval}
{if $columns < 1}{assign var=columns value=1}{/if}
{if $columns > 6}{assign var=columns value=6}{/if}
<div class="everblock-google-reviews{if $googleReviewsOptions.css_class} {$googleReviewsOptions.css_class|escape:'htmlall':'UTF-8'}{/if}"{if isset($googleReviewsOptions.place_id) && $googleReviewsOptions.place_id} data-place-id="{$googleReviewsOptions.place_id|escape:'htmlall':'UTF-8'}"{/if}>
  <div class="d-flex flex-column flex-lg-row gap-4 gap-lg-5">
    <aside class="everblock-google-reviews__aside flex-shrink-0">
      <header class="d-flex flex-column gap-2">
        {if $googleReviewsHeading}
          <h3 class="everblock-google-reviews__title h4 mb-0">{$googleReviewsHeading|escape:'htmlall':'UTF-8'}</h3>
        {/if}
        {if $googleReviewsIntro}
          <div class="everblock-google-reviews__intro text-muted">{$googleReviewsIntro nofilter}</div>
        {/if}
      </header>
      {if $googleReviewsOptions.show_rating && $googleReviewsData.rating}
        <div class="everblock-google-reviews__summary border rounded-4 p-3 d-flex flex-column gap-2">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="everblock-google-reviews__rating fs-3 fw-bold">{$googleReviewsData.rating|number_format:1}</span>
            <span class="everblock-google-reviews__stars" aria-hidden="true">
              {section name=globalStar loop=5}
                {assign var=position value=$smarty.section.globalStar.index+1}
                <span class="everblock-google-reviews__star{if $googleReviewsData.rating >= $position} is-filled{/if}">★</span>
              {/section}
            </span>
            <span class="sr-only">{l s='%1$s out of %2$s' sprintf=[$googleReviewsData.rating|number_format:1, 5] mod='everblock'}</span>
          </div>
          {if $googleReviewsData.user_ratings_total}
            <p class="everblock-google-reviews__total mb-0 text-muted">
              {l s='Based on %s reviews' sprintf=[$googleReviewsData.user_ratings_total] mod='everblock'}
            </p>
          {/if}
        </div>
      {/if}
      <div class="everblock-google-reviews__provider d-inline-flex align-items-center gap-1" aria-label="Google">
        <span class="everblock-google-reviews__logo-letter is-blue">G</span>
        <span class="everblock-google-reviews__logo-letter is-red">o</span>
        <span class="everblock-google-reviews__logo-letter is-yellow">o</span>
        <span class="everblock-google-reviews__logo-letter is-blue">g</span>
        <span class="everblock-google-reviews__logo-letter is-green">l</span>
        <span class="everblock-google-reviews__logo-letter is-red">e</span>
      </div>
      {if $googleReviewsOptions.show_cta && $googleReviewsOptions.cta_url}
        <div class="everblock-google-reviews__cta">
          <a class="btn btn-primary btn-lg" href="{$googleReviewsOptions.cta_url|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener nofollow" aria-label="{l s='Read all reviews on Google' mod='everblock'}">
            {if $googleReviewsOptions.cta_label}{$googleReviewsOptions.cta_label|escape:'htmlall':'UTF-8'}{else}{l s='Read all reviews on Google' mod='everblock'}{/if}
          </a>
        </div>
      {/if}
    </aside>
    <div class="everblock-google-reviews__content flex-grow-1">
      {if $reviews}
        <div class="everblock-google-reviews__list row row-cols-1 row-cols-md-2 row-cols-lg-{$columns} g-4">
          {foreach from=$reviews item=review}
            <div class="col">
              <article class="card h-100 border-0 shadow-sm everblock-google-reviews__card">
                <div class="card-body d-flex gap-3">
                  {if $googleReviewsOptions.show_avatar && $review.profile_photo_url}
                    <div class="everblock-google-reviews__avatar flex-shrink-0">
                      <img src="{$review.profile_photo_url|escape:'htmlall':'UTF-8'}" alt="{$review.author_name|escape:'htmlall':'UTF-8'}" loading="lazy" class="img-fluid rounded-circle" width="56" height="56">
                    </div>
                  {elseif $googleReviewsOptions.show_avatar}
                    <div class="everblock-google-reviews__avatar is-placeholder flex-shrink-0" aria-hidden="true">
                      <span>{$review.author_name|default:'?'|truncate:1:""|escape:'htmlall':'UTF-8'}</span>
                    </div>
                  {/if}
                  <div class="everblock-google-reviews__body flex-grow-1">
                    <header class="everblock-google-reviews__header d-flex flex-column gap-1 mb-2">
                      {if $review.author_name}
                        <p class="everblock-google-reviews__author mb-0">
                          {if $review.author_url}
                            <a href="{$review.author_url|escape:'htmlall':'UTF-8'}" rel="noopener nofollow" target="_blank">{$review.author_name|escape:'htmlall':'UTF-8'}</a>
                          {else}
                            {$review.author_name|escape:'htmlall':'UTF-8'}
                          {/if}
                        </p>
                      {/if}
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        {if $review.rating}
                          <div class="everblock-google-reviews__rating" aria-label="{l s='%1$s out of %2$s' sprintf=[$review.rating|number_format:1, 5] mod='everblock'}">
                            {section name=item loop=5}
                              {assign var=position value=$smarty.section.item.index+1}
                              <span class="everblock-google-reviews__star{if $review.rating >= $position} is-filled{/if}">★</span>
                            {/section}
                          </div>
                        {/if}
                        {if $review.relative_time_description}
                          <p class="everblock-google-reviews__time mb-0">{$review.relative_time_description|escape:'htmlall':'UTF-8'}</p>
                        {/if}
                      </div>
                    </header>
                    {if $review.text}
                      <p class="everblock-google-reviews__text mb-0">{$review.text|escape:'htmlall':'UTF-8'}</p>
                    {/if}
                  </div>
                </div>
              </article>
            </div>
          {/foreach}
        </div>
      {elseif $googleReviewsOptions.is_configured}
        <p class="everblock-google-reviews__empty text-muted mb-0">{l s='No Google reviews available yet.' mod='everblock'}</p>
      {/if}
    </div>
  </div>
</div>
