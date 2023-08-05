/**
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
 *  @copyright 2019-2023 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
$(document).ready(function(){
    if ($('.everblock').length && $('.everblock').data('everhook') == 'hookDisplayBanner') {
        // Add your own code here depending on hook
    }
    // Recherchez tous les éléments avec l'attribut data-obflink
    $('[data-obflink]').click(function(event) {
        event.preventDefault(); // Empêche le comportement par défaut du lien

        // Récupérez l'URL encodée en base64 depuis l'attribut data-obflink
        var encodedLink = $(this).attr('data-obflink');

        // Décodez l'URL à partir de base64
        var decodedLink = atob(encodedLink);

        // Redirigez l'internaute vers l'URL décodée
        window.location.href = decodedLink;
    });
    $('.everModalAutoTrigger').modal('show');
});