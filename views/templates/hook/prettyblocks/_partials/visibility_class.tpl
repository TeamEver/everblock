{assign var='prettyblock_visibility' value=$block.settings.default.display_on|default:'all'}
{assign var='prettyblock_visibility_class' value=''}
{if $prettyblock_visibility === 'mobile'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-mobile'}
{elseif $prettyblock_visibility === 'desktop'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-desktop'}
{elseif $prettyblock_visibility === 'none'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-none'}
{/if}
