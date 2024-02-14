/**
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
 */
$(document).ready(function(){
    if ($('.everblock').length && $('.everblock').data('everhook') == 'hookDisplayBanner') {
        // Add your own code here depending on hook
    }
    // Recherchez tous les éléments obfusqués au premier chargement
    $('[data-ob]').attr('tabindex', 0);
    clickAndMousedownActions();
    $('.everModalAutoTrigger').modal('show');

    // Sélectionner tous les éléments avec la classe "ever-slide"
    var sliders = $('.ever-slide');

    // Parcourir chaque élément slider
    sliders.each(function() {
        // Récupérer la valeur de data-duration en tant qu'attribut de l'élément
        var durationAttr = $(this).data('duration');

        // Convertir en entier en utilisant parseInt
        var intervalDuration = parseInt(durationAttr);

        // Initialiser le slider Bootstrap avec l'intervalle personnalisé
        $(this).carousel({
            interval: intervalDuration,
            wrap: true
        });
    });
    // Gallery modals
    $('.everblock-gallery img').on('click', function() {
        var imageSrc = $(this).attr('data-src');
        var imageAlt = $(this).attr('alt');
        var modalId = $(this).closest('.everblock-gallery').find('.modal').attr('id');

        $('#' + modalId + ' img').attr('src', imageSrc);
        $('#' + modalId + ' .modal-title').text(imageAlt); // Mets à jour le titre de la modal
    });

    $('.everblock-gallery .modal').modal({
        backdrop: true,
        show: false
    });
    // Parallax
    $('.everblock-parallax .parallax-container').each(function() {
        var $container = $(this);
        var $bg = $container.find('.parallax-bg');
        var containerTop = $container.offset().top;
        var windowHeight = $(window).height();

        $(window).on('scroll', function() {
            var scrollPosition = $(this).scrollTop();
            var parallaxOffset = (scrollPosition - containerTop) * 0.2;

            $bg.css({
                'transform': 'translateY(' + parallaxOffset + 'px)',
                'height': windowHeight + parallaxOffset + 'px'
            });
        });
    });


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
          var encodedLink;

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
            var decodedLink = atob(encodedLink);

            // Vérifiez si l'élément a un attribut data-target="_blank"
            var targetAttribute = $(this).attr('data-target');
            if (typeof encodedLink !== 'undefined') {
                var targetAttribute = $(this).attr('target');
            }
            if (typeof encodedLink !== 'undefined') {
                var targetAttribute = $(this).hasClass('blank') ? '_blank' : undefined;
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
          var encodedLink;

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
            var decodedLink = atob(encodedLink);

            // Vérifiez si l'élément a un attribut data-target="_blank"
            var targetAttribute = $(this).attr('data-target');

            // Vérifiez si la touche CTRL est enfoncée avec un clique gauche
            // OU si le clique est effectué avec le bouton centrale de la souris
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
