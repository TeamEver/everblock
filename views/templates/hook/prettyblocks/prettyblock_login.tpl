{*
 * 2019-2023 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<!-- Module Ever Block -->
{if !$customer.is_logged}
<div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
    {* Si le client n'est PAS connecté *}
    <div class="card card-block">
        {if isset($block.settings.title) && $block.settings.title}
        <span class="h2 login-section-title text-center">
        {$block.settings.title}
        </span>
        {/if}
      {* On crée un formulaire qui renvoie vers la page de connexion native de Prestashop, avec un paramètre de retour *}
      <form action="{$link->getPageLink('authentication', true)|escape:'htmlall':'UTF-8'}?back={$urls.current_url|escape:'htmlall':'UTF-8'}" method="post" id="login-form" class="box">
          <p class="text-uppercase h6 hidden-sm-down">{l s='' mod='everblock'}</p>
          <div class="form_content clearfix">
              <div class="form-group">
                  <label>{l s='Email' mod='everblock'}</label> 
                  <input class="is_required validate account_input form-control" id="email" name="email" value="" type="text" />
              </div>
          <div class="form-group">
              <label>{l s='Mot de passe' mod='everblock'}</label>
              <input class="form-control js-child-focus js-visible-password" type="password" id="password" name="password" value="" />
          </div>
          {* Le lien "Mot de passe oublié ?" pour les patates *}
          <p class="lost_password form-group">
              <a href="{$link->getPageLink('password', true)|escape:'htmlall':'UTF-8'}" title="{l s='Recover your forgotten password' mod='everblock'}">{l s='Mot de passe oublié ?' mod='everblock'}</a>
          </p>
          <p class="submit">
              <input type="hidden" name="submitLogin" value="1">
              {* On se passe en planqué l'URL de retour après connexion *}
              <input type="hidden" class="hidden" name="back" value="{$urls.current_url|escape:'htmlall':'UTF-8'}" />
              <button id="submit-login" class="btn btn-primary btn-blog-primary" data-link-action="sign-in" type="submit">
              {l s='Connexion' mod='everblock'}
            </button>
          </p>
          </div>
      </form>
    </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
{/if}
<!-- /Module Ever Block -->