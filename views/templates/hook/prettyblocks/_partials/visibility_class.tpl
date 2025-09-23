{assign var='prettyblock_visibility' value=$block.settings.display_on|default:'Mobile and desktop'}
{assign var='prettyblock_visibility_class' value='' scope='parent'}
{if $prettyblock_visibility === 'Mobile only'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-mobile' scope='parent'}
{elseif $prettyblock_visibility === 'Desktop only'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-desktop' scope='parent'}
{elseif $prettyblock_visibility === 'Nowhere'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-none' scope='parent'}
{/if}
