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
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/visibility_class.tpl'}
{include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$block.settings assign='prettyblock_spacing_style'}

{assign var=containerClass value=''}
{if $block.settings.default.force_full_width}
    {assign var=containerClass value='container-fluid px-0 mx-0'}
{elseif $block.settings.default.container}
    {assign var=containerClass value='container'}
{/if}

<div id="block-{$block.id_prettyblocks}" class="{$containerClass}{$prettyblock_visibility_class}"{if isset($block.settings.default.bg_color) && $block.settings.default.bg_color} style="background-color:{$block.settings.default.bg_color|escape:'htmlall':'UTF-8'};"{/if}>
  {if $block.settings.default.force_full_width}
    <div class="row gx-0 no-gutters">
  {elseif $block.settings.default.container}
    <div class="row">
  {/if}
  <div class="{if $block.settings.default.container}container{/if}" style="{$prettyblock_spacing_style}">
    {if $block.settings.default.container}
      <div class="row">
    {/if}
    {if isset($block.states) && $block.states}
      {foreach from=$block.states item=state key=key}
        {include file='module:everblock/views/templates/hook/prettyblocks/_partials/spacing_style.tpl' spacing=$state assign='prettyblock_state_spacing_style'}
        {assign var=show_rating_override value=null}
        {if isset($state.show_rating_override)}
          {assign var=show_rating_override value=$state.show_rating_override}
        {/if}
        {assign var=show_avatar_override value=null}
        {if isset($state.show_avatar_override)}
          {assign var=show_avatar_override value=$state.show_avatar_override}
        {/if}
        {assign var=show_cta_override value=null}
        {if isset($state.show_cta_override)}
          {assign var=show_cta_override value=$state.show_cta_override}
        {/if}
        {assign var=overrides value=[
          'api_key' => $state.api_key_override|default:null,
          'place_id' => $state.place_id_override|default:null,
          'limit' => $state.limit_override|default:null,
          'min_rating' => $state.min_rating_override|default:null,
          'sort' => $state.sort_override|default:null,
          'show_rating' => $show_rating_override,
          'show_avatar' => $show_avatar_override,
          'show_cta' => $show_cta_override,
          'cta_label' => $state.cta_label_override|default:null,
          'cta_url' => $state.cta_url_override|default:null,
          'columns' => $state.columns|default:null,
          'css_class' => $state.css_class|default:'',
          'heading' => $state.title|default:'',
          'intro' => $state.intro|default:''
        ]}
        {assign var=resolved value=EverblockTools::resolveGoogleReviews($overrides)}
        {if !$resolved.options.is_configured}
          {continue}
        {/if}
        {assign var=googleReviewsOptions value=$resolved.options}
        {assign var=googleReviewsData value=$resolved.data}
        {assign var=googleReviewsHeading value=$googleReviewsOptions.heading}
        {assign var=googleReviewsIntro value=$googleReviewsOptions.intro}
        <div id="block-{$block.id_prettyblocks}-{$key}" class="everblock-google-reviews-wrapper{if $googleReviewsOptions.css_class} {$googleReviewsOptions.css_class|escape:'htmlall':'UTF-8'}{/if}" style="{$prettyblock_state_spacing_style}{if isset($state.default.bg_color) && $state.default.bg_color}background-color:{$state.default.bg_color|escape:'htmlall':'UTF-8'};{/if}">
          {include file='module:everblock/views/templates/hook/_partials/google_reviews.tpl' googleReviewsHeading=$googleReviewsHeading googleReviewsIntro=$googleReviewsIntro googleReviewsData=$googleReviewsData googleReviewsOptions=$googleReviewsOptions}
        </div>
      {/foreach}
    {/if}
    {if $block.settings.default.container}
      </div>
    {/if}
  </div>
  {if $block.settings.default.force_full_width || $block.settings.default.container}
    </div>
  {/if}
</div>
