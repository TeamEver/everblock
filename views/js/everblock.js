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
    var $forms = $('.evercontactform');
    $forms.each(function() {
        var $form = $(this);
        $form.on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: evercontact_link,
                type: 'POST',
                data: $form.serialize(),
                success: function(modal) {
                    $(modal).insertAfter($form);
                    $('#evercontactModal').modal('show');
                    $('#evercontactModal').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                    // $(this).slideUp();
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        });
    });
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
});