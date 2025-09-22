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
            <i class="icon-eye"></i>
            {l s='Display options' mod='everblock'}
        </h3>
        <p>
            {l s='Define how the block is rendered in your theme. These options change the container but never touch the content itself.' mod='everblock'}
        </p>
        <ul>
            <li>{l s='Custom CSS class and data attributes are added to the outer wrapper. They are perfect for design adjustments or JavaScript triggers.' mod='everblock'}</li>
            <li>{l s='Bootstrap size lets you quickly align several blocks on the same hook. Choose “None” to keep the default theme layout.' mod='everblock'}</li>
            <li>{l s='Position controls the manual order when several blocks share the same hook. Lower numbers appear first.' mod='everblock'}</li>
            <li>{l s='Obfuscation prevents bots from crawling the links inside the block by wrapping them with JavaScript.' mod='everblock'}</li>
        </ul>
        <p class="mt-3">
            {l s='Remember to clear the module cache after heavy layout changes to make sure your visitors see the latest version.' mod='everblock'}
        </p>
    </div>
</div>
