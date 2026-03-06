{assign var='prettyblock_visibility' value=$block.settings.display_on|default:'Mobile and desktop'}
{assign var='prettyblock_visibility_class' value='' scope='parent'}
{assign var='prettyblock_is_allowed_group' value=true scope='parent'}

{* Hide blocks when the current customer group is not allowed *}
{assign var='prettyblock_allowed_groups' value=$block.settings.allowed_customer_groups|default:[]}

{if $prettyblock_allowed_groups|@count}
  {assign var='prettyblock_is_allowed_group' value=false scope='parent'}
  {if isset($customer) && $customer.id_default_group}
    {if in_array($customer.id_default_group, $prettyblock_allowed_groups)}
      {assign var='prettyblock_is_allowed_group' value=true scope='parent'}
    {/if}
  {/if}
{/if}

{if $prettyblock_visibility === 'Mobile only'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-mobile' scope='parent'}
{elseif $prettyblock_visibility === 'Desktop only'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-desktop' scope='parent'}
{elseif $prettyblock_visibility === 'Nowhere'}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-none' scope='parent'}
{/if}

{if !$prettyblock_is_allowed_group}
  {assign var='prettyblock_visibility_class' value=' everblock-visibility-none' scope='parent'}
{/if}
