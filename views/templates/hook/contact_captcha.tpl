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
{if isset($captcha)}
  <div class="form-group mb-4 evercontact-captcha">
    <label for="{$captcha.field_id|escape:'htmlall':'UTF-8'}" class="form-label fw-bold d-block">{$captcha.label|escape:'htmlall':'UTF-8'}</label>
    <p class="small text-muted">{$captcha.helper|escape:'htmlall':'UTF-8'}</p>
    <div class="input-group">
      <span class="input-group-text">{$captcha.question|escape:'htmlall':'UTF-8'}</span>
      <input type="text" name="evercaptcha_answer" id="{$captcha.field_id|escape:'htmlall':'UTF-8'}" class="form-control" required>
    </div>
    <input type="hidden" name="evercaptcha_token" value="{$captcha.token|escape:'htmlall':'UTF-8'}">
  </div>
{/if}
