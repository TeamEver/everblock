{assign var='everblockLoginTitle' value=$title|default:{l s='Login' mod='everblock'}}

<section class="everblock-login-block">
  <h3>{$everblockLoginTitle|escape:'htmlall':'UTF-8'}</h3>
  <p>
    <a class="btn btn-primary" href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">
      {l s='Go to login page' mod='everblock'}
    </a>
  </p>
</section>
