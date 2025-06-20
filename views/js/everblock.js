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
    if ($.fn.slick) {
        $('.ever-slick-carousel:not(.slick-initialised)').slick({
            infinite: true,
            arrows: false,
            dots: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    infinite: true,
                    dots: true
                }
            }, {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    dots: true
                }
            }, {
                breakpoint: 300,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    dots: true
                }
            }]
        });
        $('.ever-slick-carousel').on('setPosition', function(event, slick) {
            $(slick.$slider).find('.slick-track').addClass('row');
        });
    }
    $('.ever_instagram img').on('click', function() {
        // Mettre à jour le src de l'image dans la modal
        var imageSrc = $(this).attr('src');
        $('#everModalImage').attr('src', imageSrc);

        // Ouvrir la modal
        $('#everImageModal').modal('show');
    });
    $(document).on('submit', '.evercontactform', function(e) {
        e.preventDefault();
        let $form = $(this);
        let formData = new FormData(this);

        $.ajax({
            url: atob(evercontact_link),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(modal) {
                $('#everblockModal').remove();
                $('body').append(modal);
                $('#evercontactModal').modal('show');
                $('#evercontactModal').on('hidden.bs.modal', function () {
                    $(this).remove();
                    $('.modal-backdrop').remove();
                });
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });
    $('div[data-evermodal]').each(function() {
        let blockId = $(this).attr('id').replace('everblock-', '');
        let timeout = $(this).data('evertimeout');
        $.ajax({
            url: atob(evermodal_link),
            type: 'POST',
            data: { id_everblock: blockId, token: everblock_token },
            success: function(modal) {
                $(modal).insertAfter($('body'));
                let $modal = $('#everblockModal');
                setTimeout(function() {
                    $modal.modal('show');
                }, timeout);
                $modal.on('shown.bs.modal', function () {
                    let windowHeight = $(window).height();
                    let modalHeaderHeight = $(this).find('.modal-header').outerHeight() || 0; // S'il y a un en-tête
                    let modalFooterHeight = $(this).find('.modal-footer').outerHeight() || 0; // S'il y a un pied de page
                    let modalBodyPadding = parseInt($(this).find('.modal-body').css('padding-top')) + parseInt($(this).find('.modal-body').css('padding-bottom'));
                    
                    let maxModalBodyHeight = windowHeight - modalHeaderHeight - modalFooterHeight - modalBodyPadding - 20; // 20px pour un peu d'espace
                    
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

    $(document).on('click', '.everblock-modal-button', function(e) {
        e.preventDefault();
        let blockId = $(this).data('everclickmodal');
        let cmsId = $(this).data('evercms');
        if (!blockId && !cmsId) {
            return;
        }
        let data = { token: everblock_token, force: 1 };
        if (blockId) {
            data.id_everblock = blockId;
        }
        if (cmsId) {
            data.id_cms = cmsId;
        }
        $.ajax({
            url: atob(evermodal_link),
            type: 'POST',
            data: data,
            success: function(modal) {
                $('#everblockModal').remove();
                $('body').append(modal);
                $('#everblockModal').modal('show');
                $('#everblockModal').on('hidden.bs.modal', function () {
                    $(this).remove();
                });
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });
    $('.everModalAutoTrigger').modal('show');
    // Sélectionner tous les éléments avec la classe "ever-slide"
    let sliders = $('.ever-slide');
    // Parcourir chaque élément slider
    sliders.each(function() {
        // Récupérer la valeur de data-duration en tant qu'attribut de l'élément
        let durationAttr = $(this).data('duration');
        // Convertir en entier en utilisant parseInt
        let intervalDuration = parseInt(durationAttr);
        // Initialiser le slider Bootstrap avec l'intervalle personnalisé
        $(this).carousel({
            interval: intervalDuration,
            wrap: true
        });
    });
    // Gallery modals
    $('.everblock-gallery img').on('click', function() {
        let imageSrc = $(this).attr('data-src');
        let imageAlt = $(this).attr('alt');
        let modalId = $(this).closest('.everblock-gallery').find('.modal').attr('id');
        $('#' + modalId + ' img').attr('src', imageSrc);
        $('#' + modalId + ' .modal-title').text(imageAlt); // Mets à jour le titre de la modal
    });
    $('.everblock-gallery .modal').modal({
        backdrop: true,
        show: false
    });
});