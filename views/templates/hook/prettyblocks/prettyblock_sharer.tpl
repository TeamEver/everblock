{*
 * 2019-2024 Team Ever
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
 *  @copyright 2019-2024 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<!-- Module Ever Block -->
<div class="{if $block.settings.default.container}container{/if}">
    {if $block.settings.default.container}
        <div class="row">
    {/if}
      <div class="everblock-sharer {$block.settings.css_class|escape:'htmlall':'UTF-8'} {$block.settings.bootstrap_class|escape:'htmlall':'UTF-8'}">
        <!-- Bouton de partage Facebook -->
        <div class="social-share-button">
          <a href="https://www.facebook.com/sharer/sharer.php?u={$urls.current_url nofilter}" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#1877f2" width="24" height="24">
              <path d="M20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h8v-7h-3v-3h3V9.364C11 6.364 12.79 5 15.5 5c1.77 0 3.25.66 3.5 1.5h2V8h-2c-.28 0-.5-.22-.5-.5V5h3l-.362 3H19v3h3l-.5 3h-2v7h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
            </svg>
          </a>
        </div>

        <!-- Bouton de partage Twitter -->
        <div class="social-share-button">
          <a href="https://twitter.com/intent/tweet?url={$urls.current_url nofilter}" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#1da1f2" width="24" height="24">
              <path d="M23.954 4.563c-.885.389-1.83.652-2.825.773 1.014-.611 1.795-1.574 2.164-2.723-.95.564-2.004.974-3.125 1.194-.894-.957-2.165-1.555-3.574-1.555-2.704 0-4.896 2.19-4.896 4.884 0 .383.043.756.127 1.117-4.068-.204-7.674-2.149-10.084-5.106-.42.724-.661 1.566-.661 2.465 0 1.7.865 3.195 2.174 4.075-.801-.025-1.553-.246-2.214-.612v.062c0 2.365 1.681 4.336 3.92 4.783-.41.11-.844.169-1.291.169-.314 0-.615-.03-.918-.086.622 1.92 2.429 3.32 4.572 3.357-1.675 1.311-3.787 2.09-6.077 2.09-.395 0-.787-.023-1.174-.068 2.163 1.387 4.73 2.198 7.5 2.198 9.001 0 13.92-7.464 13.92-13.926 0-.21-.005-.419-.014-.627.955-.692 1.794-1.557 2.458-2.542l-.047-.02z"/>
            </svg>
          </a>
        </div>

        <!-- Bouton de partage LinkedIn -->
        <div class="social-share-button">
          <a href="https://www.linkedin.com/shareArticle?url={$urls.current_url nofilter}" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#bd081c" width="24" height="24">
              <path d="M12 0c6.628 0 12 5.372 12 12 0 5.237-3.348 9.683-8 11.313-.117-1.001.238-2.663.557-3.981.063-.256.031-.332-.167-.523-.537-.532-1.043-1.28-1.043-2.211 0-1.718 1.235-3.001 2.764-3.001 1.307 0 1.938.982 1.938 2.162 0 1.318-.838 3.284-1.273 5.111-.361 1.517.765 2.742 2.266 2.742 2.718 0 4.548-3.559 4.548-7.891 0-3.325-2.338-5.68-5.634-5.68-4.093 0-6.524 3.078-6.524 6.27 0 1.273.52 2.66 1.16 3.394.129.152.147.213.1.389-.088.344-.289 1.084-.329 1.24-.045.165-.152.201-.334.12-1.08-.49-1.766-1.96-1.766-3.407 0-2.662 1.936-5.013 5.505-5.013 2.853 0 4.96 1.924 4.96 4.486 0 2.995-1.756 5.312-4.324 5.312-.845 0-1.639-.44-1.91-1.037 0 0-.443 1.688-.54 2.06-.203.769-.753 1.534-1.276 2.135-.942.962-2.234 1.726-3.582 1.958-.668.118-1.336.07-1.998-.102-.293-.069-.352-.16-.33-.453.027-.648.074-1.284.135-1.917.074-.803-1.354-14.07-1.354-14.07-.24-1.081.006-2.216.727-3.083.655-.808 1.503-1.3 2.412-1.393 2.062-.18 4.105-.154 6.158-.156z"/>
            </svg>
          </a>
        </div>
      </div>
    {if $block.settings.default.container}
        </div>
    {/if}
</div>
<!-- /Module Ever Block -->