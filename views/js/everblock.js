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
        $('.ever-slick-carousel:not(.slick-initialised)').each(function(){
            var $carousel = $(this);
            var slides = parseInt($carousel.data('items')) || 4;
            $carousel.slick({
                infinite: true,
                arrows: false,
                dots: true,
                slidesToShow: slides,
                slidesToScroll: 1,
                responsive: [{
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: Math.min(slides,3),
                        slidesToScroll: 1,
                        infinite: true,
                        dots: true
                    }
                }, {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: Math.min(slides,2),
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
            $carousel.on('setPosition', function(event, slick) {
                $(slick.$slider).find('.slick-track').addClass('row');
            });
            $carousel.addClass('slick-initialised');
        });
        $('.ever-cover-carousel:not(.slick-initialised)').each(function(){
            var $carousel = $(this);
            var slides = parseInt($carousel.data('items')) || 3;
            $carousel.on('init', function(event, slick){
                var $center = $(slick.$slides[slick.currentSlide]);
                $(slick.$prevArrow).appendTo($center);
                $(slick.$nextArrow).appendTo($center);
            });
            $carousel.on('afterChange', function(event, slick, currentSlide){
                var $center = $(slick.$slides[currentSlide]);
                $(slick.$prevArrow).appendTo($center);
                $(slick.$nextArrow).appendTo($center);
            });
            $carousel.slick({
                slidesToShow: slides,
                centerMode: true,
                arrows: true,
                dots: false,
                autoplay: false,
                responsive: [{
                    breakpoint: 768,
                    settings: {
                        slidesToShow: Math.min(slides, 1)
                    }
                }]
            });
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
            data: { id_everblock: blockId, token: everblock_token, everblock_origin_url: window.location.href },
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
        let productModalId = $(this).data('evermodal');
        if (!blockId && !cmsId && !productModalId) {
            return;
        }
        let data = { token: everblock_token, force: 1, everblock_origin_url: window.location.href };
        if (blockId) {
            data.id_everblock = blockId;
        }
        if (cmsId) {
            data.id_cms = cmsId;
        }
        if (productModalId) {
            data.id_everblock_modal = productModalId;
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

    // Masonry gallery modal generation
    $(document).on('click', '.everblock-masonry-gallery img', function () {
        let $img = $(this);
        let $gallery = $img.closest('.everblock-masonry-gallery');
        let galleryId = $gallery.data('gallery-id');
        let modalId = 'ever-masonry-modal-' + galleryId;

        if ($('#' + modalId).length === 0) {
            let modalHtml = '<div class="modal fade" id="' + modalId + '" tabindex="-1" aria-hidden="true">' +
                '<div class="modal-dialog modal-dialog-centered modal-lg">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                '<div id="' + modalId + '-carousel" class="carousel slide" data-bs-ride="carousel">' +
                '<div class="carousel-inner"></div>' +
                '<button class="carousel-control-prev" type="button" data-bs-target="#' + modalId + '-carousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>' +
                '<button class="carousel-control-next" type="button" data-bs-target="#' + modalId + '-carousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            $('body').append(modalHtml);
        }

        let $carouselInner = $('#' + modalId + '-carousel .carousel-inner');
        $carouselInner.empty();
        let currentIndex = $img.data('index');
        $gallery.find('img').each(function (idx) {
            let src = $(this).data('src');
            let alt = $(this).attr('alt');
            let active = (idx === currentIndex) ? ' active' : '';
            $carouselInner.append('<div class="carousel-item' + active + '"><img src="' + src + '" class="d-block w-100" alt="' + alt + '"></div>');
        });

        $('#' + modalId).modal('show');
        $('#' + modalId).on('hidden.bs.modal', function () {
            $(this).remove();
        });
    });

    // Video gallery modal
    $(document).on('click', '.everblock-video-gallery img', function () {
        let $img = $(this);
        let blockId = $img.data('block');
        let modal = $('#videoModal-' + blockId);
        modal.find('iframe').attr('src', $img.data('video-url'));
        modal.find('.modal-title').text($img.attr('title'));
        modal.find('.video-description').text($img.data('description'));
        modal.modal('show');
        modal.on('hidden.bs.modal', function () {
            modal.find('iframe').attr('src', '');
        });
    });

    // Video products modal
    $(document).on('click', '.everblock-video-products img', function () {
        let $img = $(this);
        let blockId = $img.data('block');
        let productIds = $img.data('product-ids');
        let $wrapper = $('#video-products-' + blockId);
        let fetchUrl = $wrapper.data('fetch-url');
        let productsLabel = $wrapper.data('products-label');

        $.ajax({
            url: fetchUrl,
            type: 'POST',
            data: {
                token: prestashop.static_token,
                product_ids: productIds
            },
            success: function (html) {
                let modalId = 'productVideoModal-' + blockId;
                let modal = $('<div>', {
                    'class': 'modal fade everblock-video-product-modal',
                    'id': modalId,
                    'tabindex': -1,
                    'aria-hidden': 'true'
                }).append(
                    '<div class="modal-dialog modal-dialog-centered modal-lg">' +
                    '<div class="modal-content">' +
                    '<div class="modal-header">' +
                    '<span class="modal-title h5"></span>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<div class="ratio ratio-16x9 mb-3">' +
                    '<iframe id="productVideoIframe-' + blockId + '" src="" allowfullscreen loading="lazy"></iframe>' +
                    '</div>' +
                    '<p class="h5">' + productsLabel + '</p>' +
                    '<div class="products-container"></div>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                );
                modal.find('.modal-title').text($img.attr('title'));
                modal.find('.products-container').html(html);
                modal.find('iframe').attr('src', $img.data('video-url'));
                $('body').append(modal);
                modal.modal('show');
                modal.on('hidden.bs.modal', function () {
                    modal.remove();
                });
            }
        });
    });

    // Flash deals countdown
    $('.flash-deals-wrapper').each(function() {
        var $wrapper = $(this);
        var dealsData = $wrapper.attr('data-deals');
        if (!dealsData) {
            return;
        }
        var deals;
        try {
            deals = typeof dealsData === 'string' ? JSON.parse(dealsData) : dealsData;
        } catch (e) {
            return;
        }
        $.each(deals, function(_, deal) {
            var $productEl = $wrapper.find('[data-id-product="' + deal.id_product + '"]');
            if (!$productEl.length) {
                return;
            }
            $productEl.css('position', 'relative');
            var $timer = $('<div>', { 'class': 'flash-deal-countdown badge bg-danger position-absolute' })
                .css({ top: '0.5rem', left: '0.5rem' });
            $productEl.append($timer);
            function updateTimer() {
                var distance = new Date(deal.end_date).getTime() - new Date().getTime();
                if (distance <= 0) {
                    $timer.text('');
                    return;
                }
                var hours = Math.floor(distance / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                $timer.text(hours + 'h ' + minutes + 'm ' + seconds + 's');
            }
            updateTimer();
            setInterval(updateTimer, 1000);
        });
    });

    // Disable downloads in preview mode
    $(document).on('click', '.everblock-downloads a', function (e) {
        if ($('body').hasClass('prettyblocks-preview')) {
            e.preventDefault();
        }
    });

    // Play video on scroll
    var $everVideos = $('.everblock-scroll-video');
    if ($everVideos.length) {
        function playVideosOnScroll() {
            var windowHeight = $(window).height();
            $everVideos.each(function() {
                var $container = $(this);
                if ($container.data('played')) {
                    return;
                }
                var rect = this.getBoundingClientRect();
                var visibleHeight = Math.min(rect.bottom, windowHeight) - Math.max(rect.top, 0);
                if (visibleHeight > rect.height / 2) {
                    var $img = $container.find('.everblock-video-thumb');
                    var $video = $container.find('video');
                    if ($video.length && $img.length) {
                        $img.addClass('d-none');
                        $video.removeClass('d-none')[0].play();
                        $container.data('played', true);
                    }
                }
            });
        }
        $(window).on('scroll resize load', playVideosOnScroll);
        playVideosOnScroll();
    }

    // Animated counters
    $('.everblock-counter').each(function() {
        var $counter = $(this).find('.everblock-counter-value');
        var target = parseInt($(this).data('value')) || 0;
        var speed = parseInt($(this).data('speed')) || 2000;
        $({countNum: 0}).animate({countNum: target}, {
            duration: speed,
            easing: 'swing',
            step: function() {
                $counter.text(Math.floor(this.countNum));
            },
            complete: function() {
                $counter.text(this.countNum);
            }
        });
    });

    // Podcasts player
    $('.everblock-podcasts audio').on('play', function () {
        $('.everblock-podcasts audio').not(this).each(function () {
            this.pause();
        });
    });

    // Lookbook modal triggers
    $('[id^="block-"][data-lookbook-url]').each(function() {
        var $block = $(this);
        var ajaxUrl = $block.data('lookbook-url');
        var blockId = this.id.replace('block-', '');
        var $modal = $('#lookbook-modal-' + blockId);
        if (!$modal.length) {
            return;
        }
        var $modalBody = $modal.find('.modal-body');
        $block.find('.lookbook-marker').on('click', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            $.get(ajaxUrl + '&id_product=' + productId, function(html) {
                $modalBody.html(html);
                $modal.modal('show');
            });
        });
    });

    // Exit intent modal
    var exitIntentShown = false;
    $(document).on('mouseout', function(e) {
        if (e.clientY <= 0 && !exitIntentShown) {
            var $modal = $('.ever-exit-intent-modal').first();
            if ($modal.length) {
                $modal.modal('show');
                exitIntentShown = true;
            }
        }
    });

});
