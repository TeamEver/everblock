{*
 * 2019-2025 Team Ever
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

<div class="card everblock-doc mt-3">
    <div class="card-body">
        <h3 class="card-title">
            <i class="icon-wordpress"></i>
            {l s='WordPress integration' mod='everblock'}
        </h3>
        <p>{l s='Provide the credentials of your WordPress REST API to display the latest posts inside Ever Block widgets.' mod='everblock'}</p>
        <ul>
            <li>{l s='API URL must target the posts endpoint, for example: https://example.com/wp-json/wp/v2/posts' mod='everblock'}</li>
            <li>{l s='User and password are used for Basic Auth when the WordPress site requires authentication.' mod='everblock'}</li>
            <li>{l s='Use the “Number of blog posts to display” field to limit the feed when rendering the shortcode.' mod='everblock'}</li>
        </ul>
        <p>{l s='Once saved, the module can fetch and cache the remote posts that you will embed through the [everwp] shortcode.' mod='everblock'}</p>
    </div>
</div>
