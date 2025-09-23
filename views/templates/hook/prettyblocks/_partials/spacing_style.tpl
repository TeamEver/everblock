{* Generates inline spacing styles for blocks and repeater states *}
{capture name='spacing_styles'}
{if isset($spacing) && $spacing}
  {assign var='spacingMap' value=[
    'padding_left' => 'padding-left',
    'padding_right' => 'padding-right',
    'padding_top' => 'padding-top',
    'padding_bottom' => 'padding-bottom',
    'margin_left' => 'margin-left',
    'margin_right' => 'margin-right',
    'margin_top' => 'margin-top',
    'margin_bottom' => 'margin-bottom',
  ]}
  {foreach from=$spacingMap key=spacingKey item=cssProperty}
    {if isset($spacing[$spacingKey]) && $spacing[$spacingKey]}
      {$cssProperty}:{$spacing[$spacingKey]|escape:'htmlall':'UTF-8'};
    {/if}
  {/foreach}
  {assign var='mobileSpacingMap' value=[
    'margin_left_mobile' => '--margin-left-mobile',
    'margin_right_mobile' => '--margin-right-mobile',
    'margin_top_mobile' => '--margin-top-mobile',
    'margin_bottom_mobile' => '--margin-bottom-mobile',
  ]}
  {foreach from=$mobileSpacingMap key=spacingKey item=cssProperty}
    {if isset($spacing[$spacingKey]) && $spacing[$spacingKey]}
      {$cssProperty}:{$spacing[$spacingKey]|escape:'htmlall':'UTF-8'};
    {/if}
  {/foreach}
{/if}
{/capture}
{$smarty.capture.spacing_styles|trim}
