/**
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
 */
$(document).ready(function(){
  // Recherchez tous les éléments obfusqués au premier chargement
  $('[data-ob]').attr('tabindex', 0);
  clickAndMousedownActions();

  prestashop.on('updateProductList', function() {
    clickAndMousedownActions('#js-product-list');
  });

  prestashop.on('updatedProduct', function() {
    clickAndMousedownActions('.product-actions.js-product-actions');
  });

  prestashop.on('updatedCart', function() {
    clickAndMousedownActions();
  });
  prestashop.on('updateShoppingCart', function() {
    clickAndMousedownActions('.blockcart');
  });
});

function clickAndMousedownActions(selector) {
  let globalSelector = '';
  // si pas de selecteur on est dans un contexte global
  if (typeof selector === 'undefined') {
    globalSelector = '[data-obflink], [data-ob], span.url-obf';
  } else {
    globalSelector = selector + ' [data-obflink], ' + selector + ' [data-ob], ' + selector + ' span.url-obf';
  }
  let isDragging = false;

  $(globalSelector)
      .on('mousedown', function(event) {
        // cas spécifique du CTRL + click gauche et bouton central souris catch par mousedown
        isDragging = false;
        if (!$(this).hasClass('no-redirect')) {
          event.preventDefault(); // Empêche le comportement par défaut du lien

          // Initialisez la valeur du lien encodé
          let encodedLink;

          // Vérifiez si 'data-obflink' ou 'data-ob' ou 'data-href' sont définis
          if (typeof $(this).attr('data-obflink') !== 'undefined') {
            encodedLink = $(this).attr('data-obflink');
          } else if (typeof $(this).data('ob') !== 'undefined') {
            encodedLink = $(this).data('ob');
          } else if (typeof $(this).attr('data-href') !== 'undefined') {
            encodedLink = $(this).attr('data-href');
          }

          if (typeof encodedLink !== 'undefined') {
            // Décodez l'URL à partir de base64
            let decodedLink = atob(encodedLink);

            // Vérifiez si l'élément a un attribut data-target="_blank"
            let targetAttribute = $(this).attr('data-target');
            if (typeof encodedLink !== 'undefined') {
                let targetAttribute = $(this).attr('target');
            }
            if (typeof encodedLink !== 'undefined') {
                let targetAttribute = $(this).hasClass('blank') ? '_blank' : undefined;
            }

            // Vérifiez si la touche CTRL est enfoncée avec un clique gauche
            // OU si le clique est effectué avec le bouton centrale de la souris
            // OU si target _blank
            if (( (event.ctrlKey || event.metaKey ) && event.button === 0 ) || event.button === 1 || targetAttribute === '_blank') {
              // Ouvrez l'URL décodée dans un nouvel onglet
              window.open(decodedLink, targetAttribute);
            }
          }
        }
      })
      .on('mousemove', function() {
        isDragging = true;
      })
      .on('mouseup', function() {
        isDragging = false;
      })
      .on('click', function(event) {
        if (!$(this).hasClass('no-redirect') && !isDragging) {
          event.preventDefault(); // Empêche le comportement par défaut du lien

          // Initialisez la valeur du lien encodé
          let encodedLink;

          // Vérifiez si 'data-obflink' ou 'data-ob' ou 'data-href' sont définis
          if (typeof $(this).attr('data-obflink') !== 'undefined') {
            encodedLink = $(this).attr('data-obflink');
          } else if (typeof $(this).data('ob') !== 'undefined') {
            encodedLink = $(this).data('ob');
          } else if (typeof $(this).attr('data-href') !== 'undefined') {
            encodedLink = $(this).attr('data-href');
          }

          if (typeof encodedLink !== 'undefined') {
            // Décodez l'URL à partir de base64
            let decodedLink = atob(encodedLink);

            // Vérifiez si l'élément a un attribut data-target="_blank"
            let targetAttribute = $(this).attr('data-target');

            // Vérifiez si la touche CTRL est enfoncée avec un clic gauche
            // OU si le clic est effectué avec le bouton centrale de la souris
            // OU si target _blank
            if (( (event.ctrlKey || event.metaKey ) && event.button === 0 ) || event.button === 1 || targetAttribute === '_blank') {
              // Ouvrez l'URL décodée dans un nouvel onglet
              window.open(decodedLink, targetAttribute);
            } else if (event.button !== 2) {
              // Redirigez l'internaute vers l'URL décodée
              window.location.href = decodedLink;
            }
          }
        }
      });
}
