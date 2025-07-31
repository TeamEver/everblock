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
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{assign var="type" value=$field.type}
{assign var="label" value=$field.label}
{assign var="required" value=$field.required}
{assign var="values" value=$field.values}
{assign var="value" value=$field.value}
{assign var="unique" value=$field.unique}
{assign var="id" value=$field.id}
{assign var="class" value=$field.class}

{if $type == 'sento'}
  <input type="hidden" name="everHide" value="{$label|base64_encode}">
{elseif in_array($type, ['password','tel','email','datetime-local','date','text','number'])}
  <div class="form-group mb-4{if $class} {$class|escape:'htmlall':'UTF-8'}{/if}">
    <label for="{$id}" class="d-none">{$label nofilter}</label>
    <input type="{$type}" class="form-control" name="{$label}" id="{$id}" placeholder="{$label}"{if $value} value="{$value|escape:'htmlall':'UTF-8'}"{/if}{if $required} required{/if}>
  </div>
{elseif $type == 'textarea'}
  <div class="form-group mb-4{if $class} {$class|escape:'htmlall':'UTF-8'}{/if}">
    <label for="{$id}" class="d-none">{$label nofilter}</label>
    <textarea class="form-control" name="{$label}" id="{$id}" placeholder="{$label}"{if $required} required{/if}>{$value|escape:'htmlall':'UTF-8'}</textarea>
  </div>
{elseif $type == 'select'}
  <div class="form-group mb-4{if $class} {$class|escape:'htmlall':'UTF-8'}{/if}">
    <label for="{$id}" class="d-none">{$label nofilter}</label>
    <select class="form-control" name="{$label}" id="{$id}"{if $required} required{/if}>
      <option value="" disabled selected>{$label}</option>
      {foreach from=$values item=val}
        {assign var='trimmed' value=$val|trim}
        <option value="{$trimmed}"{if $value == $trimmed} selected{/if}>{$trimmed}</option>
      {/foreach}
    </select>
  </div>
{elseif $type == 'multiselect'}
  <div class="form-group mb-4{if $class} {$class|escape:'htmlall':'UTF-8'}{/if}">
    <label for="{$id}" class="d-none">{$label nofilter}</label>
    {assign var='selectedValues' value=","|explode:$value}
    <select class="form-control" name="{$label}[]" id="{$id}" multiple{if $required} required{/if}>
      {foreach from=$values item=val}
        {assign var='trimmed' value=$val|trim}
        <option value="{$trimmed}"{if in_array($trimmed,$selectedValues)} selected{/if}>{$trimmed}</option>
      {/foreach}
    </select>
  </div>
{elseif $type == 'radio'}
  <div class="form-group mb-4{if $class} {$class|escape:'htmlall':'UTF-8'}{/if}">
    <label>{$label nofilter}</label>
    <div class="form-check">
      {foreach from=$values item=val}
        {assign var='trimmed' value=$val|trim}
        {assign var='radioId' value="radio_`$unique`_`$trimmed`"}
        <div class="form-check-inline">
          <input type="radio" class="form-check-input" name="{$label}" value="{$trimmed}" id="{$radioId}"{if $value == $trimmed} checked{/if}{if $required} required{/if}>
          <label class="form-check-label" for="{$radioId}">{$trimmed}</label>
        </div>
      {/foreach}
    </div>
  </div>
{elseif $type == 'checkbox'}
  <div class="form-group mb-4{if $class} {$class|escape:'htmlall':'UTF-8'}{/if}">
    <label class="d-none">{$label nofilter}</label>
    <div class="form-check">
      {assign var='checkedValues' value=","|explode:$value}
      {foreach from=$values item=val}
        {assign var='trimmed' value=$val|trim}
        {assign var='checkboxId' value="checkbox_`$unique`_`$trimmed`"}
        <div class="form-check-inline">
          <input type="checkbox" class="form-check-input" name="{$label}[]" value="{$trimmed}" id="{$checkboxId}"{if in_array($trimmed,$checkedValues)} checked{/if}{if $required} required{/if}>
          <label class="form-check-label" for="{$checkboxId}">{$trimmed}</label>
        </div>
      {/foreach}
    </div>
  </div>
{elseif $type == 'file'}
  <div class="form-group mb-4{if $class} {$class|escape:'htmlall':'UTF-8'}{/if}">
    <label for="{$id}" class="d-none">{$label nofilter}</label>
    <input type="file" class="form-control-file" name="{$label}" id="{$id}"{if $required} required{/if}>
  </div>
{elseif $type == 'submit'}
  <button type="submit" class="btn btn-primary evercontactsubmit{if $class} {$class|escape:'htmlall':'UTF-8'}{/if}">{$label}</button>
{elseif $type == 'hidden'}
  <input type="hidden" name="hidden" value="{$label}"{if $class} class="{$class|escape:'htmlall':'UTF-8'}"{/if}>
{/if}
