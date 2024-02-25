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
    $(document).on('submit', '.evercontactform', function(e) {
        e.preventDefault();
        var $form = $(this);
        $.ajax({
            url: evercontact_link,
            type: 'POST',
            data: $form.serialize(),
            success: function(modal) {
                $('#everblockModal').remove();
                $('body').append(modal);
                $('#evercontactModal').modal('show');
                $('#evercontactModal').on('hidden.bs.modal', function () {
                    $(this).remove();
                    $('.modal-backdrop').remove();
                });
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
    $('div[data-evermodal]').each(function() {
        var blockId = $(this).attr('id').replace('everblock-', '');
        $.ajax({
            url: evermodal_link,
            type: 'POST',
            data: { id_everblock: blockId },
            success: function(modal) {
                $(modal).insertAfter($('body'));
                var $modal = $('#everblockModal');
                $modal.modal('show');
                
                $modal.on('shown.bs.modal', function () {
                    var windowHeight = $(window).height();
                    var modalHeaderHeight = $(this).find('.modal-header').outerHeight() || 0; // S'il y a un en-tête
                    var modalFooterHeight = $(this).find('.modal-footer').outerHeight() || 0; // S'il y a un pied de page
                    var modalBodyPadding = parseInt($(this).find('.modal-body').css('padding-top')) + parseInt($(this).find('.modal-body').css('padding-bottom'));
                    
                    var maxModalBodyHeight = windowHeight - modalHeaderHeight - modalFooterHeight - modalBodyPadding - 20; // 20px pour un peu d'espace
                    
                    $(this).find('.modal-body').css({
                        'max-height': maxModalBodyHeight + 'px',
                        'overflow-x': 'hidden',
                        'overflow-y': 'auto'
                    });
                });

                $modal.on('hidden.bs.modal', function () {
                    $(this).remove();
                });
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
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
});