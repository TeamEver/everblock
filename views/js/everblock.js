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
    var $wheelCatSelects = $('select[name$="[id_categories][]"], select[name$="[id_categories]"]');
    if ($wheelCatSelects.length) {
        if ($.fn.select2) {
            $wheelCatSelects.select2();
        } else if ($.fn.chosen) {
            $wheelCatSelects.chosen();
        }
    }
    function everblockShowGameModal(message, code, details) {
        var safeMessage = typeof message === 'string' ? message : '';
        var couponCode = typeof code === 'string' ? code : '';
        var infoDetails = [];
        if (Array.isArray(details)) {
            infoDetails = details.filter(function (item) {
                return typeof item === 'string' && item.trim().length;
            });
        } else if (typeof details === 'string' && details.trim().length) {
            infoDetails = [details];
        }
        var codeHtml = '';
        if (couponCode) {
            codeHtml = '<div class="ever-wheel-code-wrapper">'
                + '<span class="ever-wheel-code">' + couponCode + '</span>'
                + '<button type="button" class="btn btn-secondary btn-sm ms-2 ever-wheel-copy">Copier</button>'
                + '<span class="ever-wheel-copy-feedback ms-2 text-success" style="display:none;"></span>'
                + '</div>';
        }
        var detailsHtml = '';
        if (infoDetails.length) {
            detailsHtml = infoDetails.map(function (item) {
                return '<p class="ever-wheel-detail">' + item + '</p>';
            }).join('');
        }
        $('#everWheelModal').remove();
        var modal = '<div class="modal fade" id="everWheelModal" tabindex="-1" role="dialog">'
            + '<div class="modal-dialog" role="document">'
            + '<div class="modal-content">'
            + '<div class="modal-header border-0">'
            + '<button type="button" class="close btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">'
            + '<span aria-hidden="true">&times;</span>'
            + '</button>'
            + '</div>'
            + '<div class="modal-body text-center">'
            + '<p>' + safeMessage + '</p>' + codeHtml + detailsHtml
            + '<button type="button" class="btn btn-primary mt-3" data-dismiss="modal" data-bs-dismiss="modal">OK</button>'
            + '</div></div></div></div>';
        $('body').append(modal);
        var $modal = $('#everWheelModal');
        $modal.modal('show');
        if (couponCode) {
            $modal.find('.ever-wheel-copy').on('click', function () {
                var $feedback = $modal.find('.ever-wheel-copy-feedback');
                function showFeedback(text, isSuccess) {
                    $feedback.text(text).toggleClass('text-success', !!isSuccess).toggleClass('text-danger', !isSuccess).show();
                    setTimeout(function () {
                        $feedback.fadeOut();
                    }, 2000);
                }
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    navigator.clipboard.writeText(couponCode).then(function () {
                        showFeedback('Code copied!', true);
                    }).catch(function () {
                        showFeedback('Unable to copy the code', false);
                    });
                } else {
                    var $temp = $('<input type="text" class="d-none" />');
                    $('body').append($temp);
                    $temp.val(couponCode).trigger('focus').select();
                    var copied = false;
                    try {
                        copied = document.execCommand('copy');
                    } catch (err) {
                        copied = false;
                    }
                    $temp.remove();
                    if (copied) {
                        showFeedback('Code copied!', true);
                    } else {
                        showFeedback('Unable to copy the code', false);
                    }
                }
            });
        }
        $modal.on('hidden.bs.modal', function () {
            $(this).remove();
        });
    }
    function makeLoopUrl(url) {
        if (!url) {
            return url;
        }
        let separator = url.indexOf('?') === -1 ? '?' : '&';
        if (url.indexOf('youtube.com/embed/') !== -1) {
            let match = url.match(/embed\/([^?]+)/);
            if (match && match[1]) {
                return url + separator + 'autoplay=1&loop=1&playlist=' + match[1];
            }
        }
        return url + separator + 'autoplay=1&loop=1';
    }
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
    // Wheel login modal handlers
    $(document).on('click', '.ever-wheel-login-btn', function(e){
        e.preventDefault();
        $('#everWheelLoginModal').modal('show');
    });
    $(document).on('hidden.bs.modal', '#everWheelLoginModal', function(){
        var form = $(this).find('form')[0];
        if(form){
            form.reset();
        }
    });
    $(document).on('click', '.ever-scratch-login-btn', function(e){
        e.preventDefault();
        $('#everScratchLoginModal').modal('show');
    });
    $(document).on('hidden.bs.modal', '#everScratchLoginModal', function(){
        var form = $(this).find('form')[0];
        if(form){
            form.reset();
        }
    });
    $(document).on('click', '.ever-mystery-login-btn', function(e){
        e.preventDefault();
        var target = $(this).data('target');
        if (typeof target === 'string' && target.length) {
            $(target).modal('show');
        }
    });
    $(document).on('hidden.bs.modal', '.ever-mystery-login-modal', function(){
        var form = $(this).find('form')[0];
        if(form){
            form.reset();
        }
    });
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
        modal.find('iframe').attr('src', makeLoopUrl($img.data('video-url')));
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
        let token = $wrapper.data('token') || (typeof prestashop !== 'undefined' ? prestashop.static_token : '');

        $.ajax({
            url: fetchUrl,
            type: 'POST',
            data: {
                token: token,
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
                    '<div class="modal-header justify-content-center position-relative">' +
                    '<span class="modal-title h5 w-100 text-center"></span>' +
                    '<button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<div class="ratio ratio-16x9 mb-3">' +
                    '<iframe id="productVideoIframe-' + blockId + '" src="" allowfullscreen loading="lazy"></iframe>' +
                    '</div>' +
                    '<p class="h5 text-center">' + productsLabel + '</p>' +
                    '<div class="products-container d-flex flex-wrap justify-content-center"></div>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                );
                modal.find('.modal-title').text($img.attr('title'));
                modal.find('.products-container').html(html);
                modal.find('.products-container .products').addClass('justify-content-center');
                modal.find('iframe').attr('src', makeLoopUrl($img.data('video-url')));
                $('body').append(modal);
                modal.modal('show');
                modal.on('hidden.bs.modal', function () {
                    modal.remove();
                });
            }
        });
    });

    // Guided product selector
    var guidedSelections = {};
    var stepHistory = [];
    var $guidedSteps = $('.everblock-guided-step');
    var totalSteps = $guidedSteps.length;
    var currentStep = 0;

    function updateProgress(current) {
        if (!totalSteps) {
            $('.everblock-guided-progress').addClass('d-none');
            return;
        }
        var pct = (current / totalSteps) * 100;
        $('.everblock-guided-progress .progress-bar').css('width', pct + '%');
        $('.everblock-guided-progress .progress-counter').text(current + '/' + totalSteps);
    }

    function showStep(index) {
        if (index >= totalSteps) {
            showFallback();
            return;
        }
        var $target = $guidedSteps.eq(index);
        var hasAnswers = $target.find('.guided-answer').length > 0;
        if (!hasAnswers) {
            showFallback();
            return;
        }
        $guidedSteps.addClass('d-none');
        $target.removeClass('d-none');
        $target.find('.guided-back').toggleClass('d-none', index === 0);
        $('.everblock-guided-fallback').addClass('d-none');
        currentStep = index;
        updateProgress(index + 1);
    }

    function showFallback() {
        $guidedSteps.addClass('d-none');
        $('.everblock-guided-fallback').removeClass('d-none');
        updateProgress(totalSteps);
    }

    if (totalSteps) {
        showStep(0);
    } else {
        showFallback();
    }

    $(document).on('click', '.everblock-guided-step .guided-answer', function () {
        var $btn = $(this);
        var $step = $btn.closest('.everblock-guided-step');
        var key = $step.data('question');
        var value = $btn.data('value');
        if (key && value) {
            guidedSelections[key] = value;
        }
        var url = $btn.data('url');
        if (url) {
            var query = $.param(guidedSelections);
            var separator = url.indexOf('?') === -1 ? '?' : '&';
            window.location.href = url + (query ? separator + query : '');
            return;
        }
        var nextIndex = $guidedSteps.index($step) + 1;
        if (nextIndex < totalSteps) {
            stepHistory.push($guidedSteps.index($step));
            showStep(nextIndex);
        } else {
            showFallback();
        }
    });

    $(document).on('click', '.guided-back', function () {
        if (!stepHistory.length) {
            return;
        }
        var prevIndex = stepHistory.pop();
        var currentKey = $guidedSteps.eq(currentStep).data('question');
        if (currentKey) {
            delete guidedSelections[currentKey];
        }
        showStep(prevIndex);
    });

    $(document).on('click', '.guided-restart', function () {
        guidedSelections = {};
        stepHistory = [];
        showStep(0);
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

    // Countdown block
    $('.everblock-countdown').each(function() {
        var $block = $(this);
        var target = $block.data('target');
        if (!target) {
            return;
        }
        var targetDate = new Date(target);
        if (isNaN(targetDate.getTime())) {
            return;
        }
        var $wrapper = $block.closest('.everblock-countdown-wrapper');
        var $message = $wrapper.length ? $wrapper.find('.everblock-countdown-finished-message') : $();
        var timerId = null;
        var completionShown = false;

        function showCompletion() {
            if (completionShown) {
                return;
            }
            completionShown = true;
            if ($message.length) {
                $block.hide();
                $message.removeClass('d-none').show();
            }
        }

        function hideCompletion() {
            if ($message.length) {
                $block.show();
                $message.addClass('d-none').hide();
            }
            completionShown = false;
        }

        function updateCountdown() {
            var now = new Date();
            var distance = targetDate.getTime() - now.getTime();
            if (distance <= 0) {
                $block.find('[data-type="days"]').text('00');
                $block.find('[data-type="hours"]').text('00');
                $block.find('[data-type="minutes"]').text('00');
                $block.find('[data-type="seconds"]').text('00');
                if (timerId !== null) {
                    clearInterval(timerId);
                    timerId = null;
                }
                showCompletion();
                return false;
            }

            hideCompletion();

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            $block.find('[data-type="days"]').text(('0' + days).slice(-2));
            $block.find('[data-type="hours"]').text(('0' + hours).slice(-2));
            $block.find('[data-type="minutes"]').text(('0' + minutes).slice(-2));
            $block.find('[data-type="seconds"]').text(('0' + seconds).slice(-2));

            return true;
        }

        var shouldContinue = updateCountdown();
        if (shouldContinue !== false) {
            timerId = setInterval(updateCountdown, 1000);
        }
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

    // Wheel of fortune
    $('.ever-wheel-of-fortune').each(function () {
        var $container = $(this);
        var configB64 = $container.data('config');
        var config = {};
        if (typeof configB64 === 'string') {
            try {
                config = JSON.parse(atob(configB64));
            } catch (e) {
                config = {};
            }
        }
        var segmentsData = config.segments || [];
        var segments = Array.isArray(segmentsData) ? segmentsData : Object.values(segmentsData);
        var spinUrl = config.spinUrl || '';
        var token = config.token || '';
        var blockId = $container.data('block-id') || 0;
        var isLogged = typeof prestashop !== 'undefined' && prestashop.customer && prestashop.customer.is_logged;
        var startDate = parseWheelDate(config.startDate);
        var endDate = parseWheelDate(config.endDate);
        var preStartMessage = typeof config.preStartMessage === 'string' ? config.preStartMessage : '';
        var postEndMessage = typeof config.postEndMessage === 'string' ? config.postEndMessage : '';
        var defaultPreStartMessage = typeof config.defaultPreStartMessage === 'string' ? config.defaultPreStartMessage : '';
        var defaultPostEndMessage = typeof config.defaultPostEndMessage === 'string' ? config.defaultPostEndMessage : '';
        var countdownLabel = typeof config.countdownLabel === 'string' ? config.countdownLabel : '';
        var isEmployee = !!config.isEmployee;
        if (!isEmployee && typeof everblock_is_employee !== 'undefined') {
            isEmployee = !!everblock_is_employee;
        }
        var $statusMessage = $container.find('.ever-wheel-status-message');
        var $statusText = $statusMessage.find('.ever-wheel-status-text');
        var $countdownWrapper = $statusMessage.find('.ever-wheel-countdown');
        var $countdownLabel = $countdownWrapper.find('.ever-wheel-countdown-label');
        var $countdownValue = $countdownWrapper.find('.ever-wheel-countdown-value');
        var $content = $container.find('.ever-wheel-content');
        if (countdownLabel) {
            $countdownLabel.text(countdownLabel);
        }
        var countdownTimer = null;
        var countdownTargetDate = null;
        var wheelCheckRequested = false;
        var serverOverrideReason = null;
        var serverOverrideMessage = '';
        var serverStartDate = null;
        var wheelInitialized = false;

        function parseWheelDate(dateStr) {
            if (typeof dateStr !== 'string') {
                return null;
            }
            var trimmed = dateStr.trim();
            if (!trimmed) {
                return null;
            }
            var normalized = trimmed.replace('T', ' ');
            var parts = normalized.split(/\s+/);
            var dateParts = parts[0] ? parts[0].split('-') : [];
            if (dateParts.length < 3) {
                return null;
            }
            var year = parseInt(dateParts[0], 10);
            var month = parseInt(dateParts[1], 10) - 1;
            var day = parseInt(dateParts[2], 10);
            var timeParts = parts[1] ? parts[1].split(':') : [];
            var hour = parseInt(timeParts[0] || 0, 10);
            var minute = parseInt(timeParts[1] || 0, 10);
            var second = parseInt(timeParts[2] || 0, 10);
            if ([year, month, day, hour, minute, second].some(function (value) { return isNaN(value); })) {
                return null;
            }
            return new Date(year, month, day, hour, minute, second);
        }

        function clearCountdown() {
            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }
            countdownTargetDate = null;
            $countdownValue.text('');
            $countdownWrapper.hide();
        }

        function startCountdown(targetDate) {
            if (!(targetDate instanceof Date)) {
                clearCountdown();
                return;
            }
            if (countdownTargetDate && countdownTargetDate.getTime() === targetDate.getTime()) {
                return;
            }
            clearCountdown();
            countdownTargetDate = targetDate;
            if (countdownLabel) {
                $countdownLabel.text(countdownLabel);
            }
            $countdownWrapper.show();

            function updateCountdown() {
                var now = new Date();
                var diff = targetDate.getTime() - now.getTime();
                if (diff <= 0) {
                    clearCountdown();
                    if (serverOverrideReason === 'before_start') {
                        serverOverrideReason = null;
                        serverOverrideMessage = '';
                        serverStartDate = null;
                        wheelCheckRequested = false;
                    }
                    setTimeout(function () {
                        updateStatus();
                    }, 100);
                    return;
                }
                var totalSeconds = Math.floor(diff / 1000);
                var days = Math.floor(totalSeconds / 86400);
                totalSeconds -= days * 86400;
                var hours = Math.floor(totalSeconds / 3600);
                totalSeconds -= hours * 3600;
                var minutes = Math.floor(totalSeconds / 60);
                var seconds = totalSeconds - minutes * 60;
                var parts = [];
                if (days > 0) {
                    parts.push(days + 'd');
                }
                parts.push(('0' + hours).slice(-2) + 'h');
                parts.push(('0' + minutes).slice(-2) + 'm');
                parts.push(('0' + seconds).slice(-2) + 's');
                $countdownValue.text(parts.join(' '));
            }

            updateCountdown();
            countdownTimer = setInterval(updateCountdown, 1000);
        }

        function evaluateStatus() {
            var now = new Date();
            var effectiveStart = serverStartDate || startDate;
            var beforeStart = false;
            var afterEnd = false;
            var message = '';
            var countdownTarget = null;

            if (serverOverrideReason === 'after_end') {
                afterEnd = true;
                message = serverOverrideMessage || postEndMessage || defaultPostEndMessage;
            } else {
                if (effectiveStart instanceof Date) {
                    if (serverOverrideReason === 'before_start') {
                        beforeStart = true;
                        countdownTarget = effectiveStart;
                        message = serverOverrideMessage || preStartMessage || defaultPreStartMessage;
                    } else if (now < effectiveStart) {
                        beforeStart = true;
                        countdownTarget = effectiveStart;
                        message = preStartMessage || defaultPreStartMessage;
                    }
                }
                if (!beforeStart && endDate instanceof Date && now > endDate) {
                    afterEnd = true;
                    message = postEndMessage || defaultPostEndMessage;
                }
            }

            if (!message && beforeStart) {
                message = preStartMessage || defaultPreStartMessage;
            }
            if (!message && afterEnd) {
                message = postEndMessage || defaultPostEndMessage;
            }

            return {
                beforeStart: beforeStart,
                afterEnd: afterEnd,
                active: !beforeStart && !afterEnd,
                message: message,
                countdownTarget: countdownTarget
            };
        }

        function maybeStartWheel(force) {
            if (wheelCheckRequested && !force) {
                return;
            }
            if (!force) {
                var status = evaluateStatus();
                if (!status.active && !isEmployee) {
                    return;
                }
            }
            wheelCheckRequested = true;
            if (spinUrl) {
                $.ajax({
                    url: spinUrl,
                    type: 'POST',
                    data: {
                        id_block: blockId,
                        token: token,
                        check: 1
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.played) {
                            var playedMsg = res.message || '';
                            $container.html('<p class="ever-wheel-already-played">' + playedMsg + '</p>');
                            return;
                        }
                        if (res && res.playable === false && !isEmployee) {
                            if (res.reason === 'before_start' || res.reason === 'after_end') {
                                wheelCheckRequested = false;
                                serverOverrideReason = res.reason;
                                serverOverrideMessage = res.message || '';
                                if (res.reason === 'before_start') {
                                    if (typeof res.start_timestamp === 'number') {
                                        serverStartDate = new Date(res.start_timestamp * 1000);
                                    } else if (!serverStartDate) {
                                        serverStartDate = startDate;
                                    }
                                } else {
                                    serverStartDate = null;
                                }
                                updateStatus();
                            } else {
                                wheelCheckRequested = true;
                                serverOverrideReason = null;
                                serverOverrideMessage = '';
                                if (res && res.message) {
                                    $statusText.html(res.message);
                                    $statusMessage.show();
                                } else {
                                    $statusMessage.hide();
                                }
                                $content.hide();
                            }
                            return;
                        }
                        serverOverrideReason = null;
                        serverOverrideMessage = '';
                        serverStartDate = null;
                        initWheel();
                    },
                    error: function () {
                        initWheel();
                    }
                });
            } else {
                initWheel();
            }
        }

        function updateStatus() {
            var status = evaluateStatus();
            if (status.beforeStart) {
                if (status.message) {
                    $statusText.html(status.message);
                    $statusMessage.show();
                } else {
                    $statusMessage.hide();
                }
                if (status.countdownTarget instanceof Date) {
                    startCountdown(status.countdownTarget);
                } else {
                    clearCountdown();
                }
                if (!isEmployee) {
                    $content.hide();
                } else {
                    $content.show();
                    maybeStartWheel();
                }
                return;
            }
            if (status.afterEnd) {
                clearCountdown();
                if (status.message) {
                    $statusText.html(status.message);
                    $statusMessage.show();
                } else {
                    $statusMessage.hide();
                }
                if (!isEmployee) {
                    $content.hide();
                } else {
                    $content.show();
                    maybeStartWheel();
                }
                return;
            }
            clearCountdown();
            $statusMessage.hide();
            $content.show();
            maybeStartWheel();
        }

        function initWheel() {
            var $canvas = $container.find('.ever-wheel-canvas');
            if (!$canvas.length) {
                return;
            }
            if (wheelInitialized) {
                return;
            }
            wheelInitialized = true;
            var currentRotation = 0;
            var POINTER_ANGLE = 270;
            var pointerAngleAttr = $container.attr('data-pointer-angle');
            var allowedPointerAngles = [0, 90, 180, 270];
            var pointerAngleResolved = false;
            if (typeof pointerAngleAttr !== 'undefined' && pointerAngleAttr !== null && pointerAngleAttr !== '') {
                var parsedPointerAngle = parseInt(pointerAngleAttr, 10);
                if (!isNaN(parsedPointerAngle) && allowedPointerAngles.indexOf(parsedPointerAngle) !== -1) {
                    POINTER_ANGLE = parsedPointerAngle;
                    pointerAngleResolved = true;
                }
            }
            if (!pointerAngleResolved) {
                var $pointer = $container.find('.ever-wheel-arrow');
                if ($pointer.hasClass('arrow-top')) {
                    POINTER_ANGLE = 270;
                } else if ($pointer.hasClass('arrow-right')) {
                    POINTER_ANGLE = 0;
                } else if ($pointer.hasClass('arrow-bottom')) {
                    POINTER_ANGLE = 90;
                } else if ($pointer.hasClass('arrow-left')) {
                    POINTER_ANGLE = 180;
                }
            }
            console.log('[Wheel Debug] Using pointer angle:', POINTER_ANGLE);
            var segmentCenters = [];

            function normalizeSegmentForComparison(value) {
                if (value === null || typeof value === 'undefined') {
                    return '';
                }
                if (Array.isArray(value)) {
                    return '[' + value.map(normalizeSegmentForComparison).join('|') + ']';
                }
                if (typeof value === 'object') {
                    var keys = Object.keys(value).sort();
                    return '{' + keys.map(function (key) {
                        return key + ':' + normalizeSegmentForComparison(value[key]);
                    }).join('|') + '}';
                }
                if (typeof value === 'boolean') {
                    return value ? '1' : '0';
                }

                return String(value);
            }

            var normalizedSegments = segments.map(normalizeSegmentForComparison);

            function clampChannel(value) {
                return Math.max(0, Math.min(255, value));
            }

            function parseColor(color) {
                if (typeof color !== 'string') {
                    return null;
                }
                var str = color.trim();
                if (!str) {
                    return null;
                }
                var hexMatch = str.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
                if (hexMatch) {
                    var hex = hexMatch[1];
                    if (hex.length === 3) {
                        hex = hex.split('').map(function (char) {
                            return char + char;
                        }).join('');
                    }
                    var intValue = parseInt(hex, 16);
                    if (isNaN(intValue)) {
                        return null;
                    }
                    return {
                        r: (intValue >> 16) & 255,
                        g: (intValue >> 8) & 255,
                        b: intValue & 255,
                        a: 1
                    };
                }
                var rgbMatch = str.match(/^rgba?\((\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*(\d*\.?\d+))?\)$/i);
                if (rgbMatch) {
                    var r = parseInt(rgbMatch[1], 10);
                    var g = parseInt(rgbMatch[2], 10);
                    var b = parseInt(rgbMatch[3], 10);
                    if (isNaN(r) || isNaN(g) || isNaN(b)) {
                        return null;
                    }
                    var a = typeof rgbMatch[4] !== 'undefined' ? parseFloat(rgbMatch[4]) : 1;
                    if (isNaN(a)) {
                        a = 1;
                    }
                    return {
                        r: clampChannel(r),
                        g: clampChannel(g),
                        b: clampChannel(b),
                        a: Math.max(0, Math.min(1, a))
                    };
                }
                return null;
            }

            function lightenColor(color, factor) {
                var parsed = parseColor(color);
                if (!parsed) {
                    return null;
                }
                var safeFactor = Math.max(0, Math.min(1, factor || 0));
                var r = Math.round(parsed.r + (255 - parsed.r) * safeFactor);
                var g = Math.round(parsed.g + (255 - parsed.g) * safeFactor);
                var b = Math.round(parsed.b + (255 - parsed.b) * safeFactor);
                if (parsed.a < 1) {
                    return 'rgba(' + clampChannel(r) + ', ' + clampChannel(g) + ', ' + clampChannel(b) + ', ' + parsed.a + ')';
                }
                return 'rgb(' + clampChannel(r) + ', ' + clampChannel(g) + ', ' + clampChannel(b) + ')';
            }

            function drawWheel() {
                var dimension = Math.min($container.width(), $(window).height());
                $canvas.attr('width', dimension).attr('height', dimension);
                var ctx = $canvas[0].getContext('2d');
                ctx.clearRect(0, 0, dimension, dimension);
                var size = dimension / 2;
                var start = 0;
                function wrapWheelText(text, maxWidth, fontSize) {
                    if (!text) {
                        return [''];
                    }
                    ctx.font = fontSize + 'px sans-serif';
                    var words = text.split(/\s+/).filter(function (word) {
                        return word.length;
                    });
                    if (!words.length) {
                        return [''];
                    }
                    var lines = [];
                    var currentLine = words.shift();
                    words.forEach(function (word) {
                        var testLine = currentLine ? currentLine + ' ' + word : word;
                        if (ctx.measureText(testLine).width <= maxWidth) {
                            currentLine = testLine;
                        } else {
                            lines.push(currentLine);
                            currentLine = word;
                        }
                    });
                    if (currentLine) {
                        lines.push(currentLine);
                    }
                    var adjustedLines = [];
                    lines.forEach(function (line) {
                        if (ctx.measureText(line).width <= maxWidth || line.length <= 1) {
                            adjustedLines.push(line);
                            return;
                        }
                        var buffer = '';
                        line.split('').forEach(function (char) {
                            var testBuffer = buffer + char;
                            if (!buffer || ctx.measureText(testBuffer).width <= maxWidth) {
                                buffer = testBuffer;
                            } else {
                                adjustedLines.push(buffer);
                                buffer = char;
                            }
                        });
                        if (buffer) {
                            adjustedLines.push(buffer);
                        }
                    });
                    return adjustedLines.length ? adjustedLines : [''];
                }
                var segmentCount = segments.length || 1;
                var stepAngle = 2 * Math.PI / segmentCount;
                segmentCenters = [];
                segments.forEach(function (seg, i) {
                    var end = start + stepAngle;
                    ctx.beginPath();
                    ctx.moveTo(size, size);
                    var baseColor = seg.color || '#' + Math.floor(Math.random() * 16777215).toString(16);
                    var gradientColor = lightenColor(baseColor, 0.2);
                    if (gradientColor) {
                        var gradient = ctx.createRadialGradient(size, size, 0, size, size, size);
                        gradient.addColorStop(0, gradientColor);
                        gradient.addColorStop(1, baseColor);
                        ctx.fillStyle = gradient;
                    } else {
                        ctx.fillStyle = baseColor;
                    }
                    ctx.arc(size, size, size, start, end);
                    ctx.lineTo(size, size);
                    ctx.fill();
                    var textAngle = start + stepAngle / 2;
                    var textRadius = size * 0.65;
                    var textX = size + Math.cos(textAngle) * textRadius;
                    var textY = size + Math.sin(textAngle) * textRadius;
                    ctx.save();
                    ctx.fillStyle = seg.text_color || '#ffffff';
                    var baseFontSize = Math.max(12, size / 12);
                    var label = seg.label;
                    if (typeof label === 'object') {
                        label = Object.values(label)[0] || '';
                    }
                    label = (label || '').toString();
                    var maxTextWidth = size * 0.7;
                    var lines = wrapWheelText(label, maxTextWidth, baseFontSize);
                    var fontSize = baseFontSize;
                    var attempt = 0;
                    while (attempt < 10) {
                        ctx.font = fontSize + 'px sans-serif';
                        var tooWide = lines.some(function (line) {
                            return ctx.measureText(line).width > maxTextWidth;
                        });
                        if (!tooWide && lines.length <= 3) {
                            break;
                        }
                        fontSize = Math.max(10, fontSize - 1);
                        lines = wrapWheelText(label, maxTextWidth, fontSize);
                        attempt += 1;
                    }
                    ctx.font = fontSize + 'px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.translate(textX, textY);
                    var rotation = textAngle - Math.PI / 2;
                    var fullCircle = 2 * Math.PI;
                    rotation = ((rotation + Math.PI) % fullCircle + fullCircle) % fullCircle - Math.PI;
                    if (rotation > Math.PI / 2) {
                        rotation -= Math.PI;
                    } else if (rotation < -Math.PI / 2) {
                        rotation += Math.PI;
                    }
                    ctx.rotate(rotation);
                    var lineHeight = fontSize * 1.1;
                    var offset = -((lines.length - 1) * lineHeight) / 2;
                    lines.forEach(function (line, idx) {
                        ctx.fillText(line, 0, offset + idx * lineHeight);
                    });
                    ctx.restore();
                    segmentCenters[i] = textAngle * 180 / Math.PI;
                    start = end;
                });
            }

            drawWheel();
            $(window).on('resize', drawWheel);

            if (!isLogged) {
                return;
            }

            $container.find('.ever-wheel-spin').on('click', function () {
                var $btn = $(this);
                $btn.prop('disabled', true);
                $.ajax({
                    url: spinUrl,
                    type: 'POST',
                    data: {
                        id_block: blockId,
                        token: token
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.reason && (res.reason === 'before_start' || res.reason === 'after_end') && !isEmployee) {
                            serverOverrideReason = res.reason;
                            serverOverrideMessage = res.message || '';
                            if (res.reason === 'before_start' && typeof res.start_timestamp === 'number') {
                                serverStartDate = new Date(res.start_timestamp * 1000);
                            } else if (res.reason === 'after_end') {
                                serverStartDate = null;
                            }
                            wheelCheckRequested = false;
                            updateStatus();
                            $btn.prop('disabled', false);
                            return;
                        }
                        var msg = res.message || '';
                        if (res && res.status && res.result) {
                            var normalizedResult = normalizeSegmentForComparison(res.result);
                            var idx = -1;
                            if (typeof res.index !== 'undefined') {
                                var parsedIndex = parseInt(res.index, 10);
                                if (!isNaN(parsedIndex)) {
                                    idx = parsedIndex;
                                }
                            }
                            if (idx === -1 && normalizedResult) {
                                idx = normalizedSegments.indexOf(normalizedResult);
                            }
                            if (idx === -1) {
                                idx = 0;
                            }
                            console.log('[Wheel Debug] PHP index:', res.index, 'JS idx:', idx, 'Segments total:', segments.length);
                            var center = segmentCenters[idx];
                            if (typeof center !== 'number' && segments.length) {
                                var fallbackStep = 360 / segments.length;
                                center = fallbackStep * idx + fallbackStep / 2;
                            }
                            if (typeof center !== 'number') {
                                center = 0;
                            }
                            var normalizedRotation = ((currentRotation % 360) + 360) % 360;
                            var desiredAlignment = (POINTER_ANGLE - center + 90 + 360) % 360;
                            console.log('[Wheel Debug] Desired alignment:', desiredAlignment, 'Pointer angle:', POINTER_ANGLE);
                            var rotationDelta = (desiredAlignment - normalizedRotation + 360) % 360;
                            currentRotation += 360 * 5 + rotationDelta;
                            console.log('[Wheel Debug] Segment center:', segmentCenters[idx]);
                            console.log('[Wheel Debug] Final rotation:', currentRotation);
                            $canvas.css('transform', 'rotate(' + currentRotation + 'deg)');
                            $canvas.one('transitionend', function () {
                                var isWinning = res.result && (res.result.isWinning || res.result.is_winning);
                                var code = isWinning ? res.code : null;
                                var details = [];
                                if (isWinning) {
                                    if (res.categories_message) {
                                        details.push(res.categories_message);
                                    }
                                    if (res.minimum_purchase_message) {
                                        details.push(res.minimum_purchase_message);
                                    }
                                }
                                everblockShowGameModal(msg, code, details);
                                $btn.prop('disabled', false);
                            });
                        } else {
                            var isWinning = res.result && (res.result.isWinning || res.result.is_winning);
                            var code = isWinning ? res.code : null;
                            var details = [];
                            if (isWinning) {
                                if (res.categories_message) {
                                    details.push(res.categories_message);
                                }
                                if (res.minimum_purchase_message) {
                                    details.push(res.minimum_purchase_message);
                                }
                            }
                            everblockShowGameModal(msg, code, details);
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function () {
                        $btn.prop('disabled', false);
                    }
                });
            });
        }

        updateStatus();
    });

    $('.ever-scratch-card-block').each(function () {
        var $container = $(this);
        var configB64 = $container.data('config');
        var config = {};
        if (typeof configB64 === 'string') {
            try {
                config = JSON.parse(atob(configB64));
            } catch (e) {
                config = {};
            }
        }
        var segmentsData = config.segments || [];
        var segments = Array.isArray(segmentsData) ? segmentsData : Object.values(segmentsData);
        var spinUrl = config.spinUrl || '';
        var token = config.token || '';
        var blockId = parseInt($container.data('block-id'), 10) || 0;
        var isLogged = typeof prestashop !== 'undefined' && prestashop.customer && prestashop.customer.is_logged;
        var isEmployee = !!config.isEmployee;
        if (!isEmployee && typeof everblock_is_employee !== 'undefined') {
            isEmployee = !!everblock_is_employee;
        }
        if (!isLogged && !isEmployee) {
            return;
        }
        var $statusMessage = $container.find('.ever-scratch-status-message');
        var $statusText = $statusMessage.find('.ever-scratch-status-text');
        var $content = $container.find('.ever-scratch-content');
        var $card = $container.find('.ever-scratch-card').first();
        var $canvas = $card.find('.ever-scratch-overlay');
        var $resultLabel = $card.find('.ever-scratch-result-label');
        var $resultImageWrapper = $card.find('.ever-scratch-result-image');
        var $resultImage = $resultImageWrapper.find('img');
        if (!$canvas.length || !$card.length) {
            return;
        }
        var canvas = $canvas[0];
        var ctx = canvas.getContext ? canvas.getContext('2d') : null;
        if (!ctx) {
            return;
        }
        var scratchEnabled = false;
        var revealTriggered = false;
        var ajaxTriggered = false;
        var isPointerDown = false;
        var revealThreshold = 0.5;
        var lastLoggedRatio = 0;
        var revealCheckScheduled = false;
        var defaultBackground = $card.css('background');
        var defaultTextColor = $resultLabel.css('color');

        function showStatus(message, visible) {
            if (visible && typeof message === 'string' && message.trim().length) {
                $statusText.html(message);
                $statusMessage.show();
            } else {
                $statusMessage.hide();
                $statusText.empty();
            }
        }

        function disableScratch(message) {
            scratchEnabled = false;
            if (!$card.hasClass('ever-scratch-card-disabled')) {
                $card.addClass('ever-scratch-card-disabled');
            }
            $canvas.css('pointer-events', 'none');
            showStatus(message || '', !!message);
        }

        function resetResultDisplay() {
            revealTriggered = false;
            ajaxTriggered = false;
            isPointerDown = false;
            lastLoggedRatio = 0;
            if ($card.hasClass('ever-scratch-card--revealed')) {
                $card.removeClass('ever-scratch-card--revealed');
            }
            if (typeof defaultBackground === 'string') {
                $card.css('background', defaultBackground);
            }
            if (typeof defaultTextColor === 'string') {
                $resultLabel.css('color', defaultTextColor);
            }
            $card.find('.ever-scratch-result').attr('aria-hidden', 'true');
            $resultLabel.text('');
            $resultImageWrapper.hide();
            $resultImage.attr('src', '').attr('alt', '');
        }

        function enableScratch() {
            scratchEnabled = true;
            $card.removeClass('ever-scratch-card-disabled');
            $canvas.css('pointer-events', '');
            showStatus('', false);
            resetResultDisplay();
            window.requestAnimationFrame(prepareCanvas);
        }

        function resolveImageUrl(imageData) {
            if (!imageData) {
                return '';
            }
            if (typeof imageData === 'string') {
                return imageData;
            }
            if (Array.isArray(imageData)) {
                for (var i = 0; i < imageData.length; i++) {
                    var nested = resolveImageUrl(imageData[i]);
                    if (nested) {
                        return nested;
                    }
                }
            }
            if (typeof imageData === 'object') {
                if (typeof imageData.url === 'string' && imageData.url.length) {
                    return imageData.url;
                }
                if (typeof imageData.src === 'string' && imageData.src.length) {
                    return imageData.src;
                }
            }

            return '';
        }

        function applyResult(result) {
            if (!result || typeof result !== 'object') {
                return;
            }
            var labelValue = result.label;
            if (labelValue && typeof labelValue === 'object') {
                if (typeof labelValue.value !== 'undefined') {
                    labelValue = labelValue.value;
                } else {
                    var labelCandidates = Array.isArray(labelValue) ? labelValue : Object.values(labelValue);
                    labelValue = '';
                    for (var i = 0; i < labelCandidates.length; i++) {
                        var candidate = labelCandidates[i];
                        if (typeof candidate === 'string' && candidate.trim().length) {
                            labelValue = candidate;
                            break;
                        }
                    }
                }
            }
            if (typeof labelValue !== 'string') {
                labelValue = '';
            }
            var backgroundColorValue = result.color;
            if (backgroundColorValue && typeof backgroundColorValue === 'object' && typeof backgroundColorValue.value !== 'undefined') {
                backgroundColorValue = backgroundColorValue.value;
            }
            var textColorValue = result.text_color;
            if (textColorValue && typeof textColorValue === 'object' && typeof textColorValue.value !== 'undefined') {
                textColorValue = textColorValue.value;
            }
            if (!textColorValue && result.textColor && typeof result.textColor === 'object' && typeof result.textColor.value !== 'undefined') {
                textColorValue = result.textColor.value;
            } else if (!textColorValue && typeof result.textColor === 'string') {
                textColorValue = result.textColor;
            }
            var label = labelValue;
            var backgroundColor = typeof backgroundColorValue === 'string' ? backgroundColorValue : '';
            var textColor = typeof textColorValue === 'string' ? textColorValue : '';
            if (backgroundColor) {
                $card.css('background', backgroundColor);
            }
            if (textColor) {
                $resultLabel.css('color', textColor);
            } else if (typeof defaultTextColor === 'string') {
                $resultLabel.css('color', defaultTextColor);
            }
            $resultLabel.text(label);
            $card.find('.ever-scratch-result').attr('aria-hidden', 'false');
            var imageUrl = resolveImageUrl(result.image || result.picture);
            if (imageUrl) {
                $resultImage.attr('src', imageUrl);
                $resultImage.attr('alt', label);
                $resultImageWrapper.show();
            } else {
                $resultImageWrapper.hide();
                $resultImage.attr('src', '').attr('alt', '');
            }
        }

        function resizeCanvas() {
            if (!canvas) {
                return;
            }
            var width = Math.round($card.innerWidth());
            if (width <= 0) {
                return;
            }
            var height = Math.round($card.innerHeight());
            if (height <= 0) {
                height = Math.round(width / 1.5);
            }
            if (canvas.width !== width || canvas.height !== height) {
                canvas.width = width;
                canvas.height = height;
            }
        }

        function drawOverlay() {
            if (!ctx) {
                return;
            }
            ctx.save();
            ctx.globalCompositeOperation = 'source-over';
            var gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
            gradient.addColorStop(0, '#f7f7f7');
            gradient.addColorStop(0.5, '#c8c8c8');
            gradient.addColorStop(1, '#9f9f9f');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.lineWidth = 1;
            ctx.strokeStyle = 'rgba(255,255,255,0.2)';
            for (var offset = -canvas.height; offset < canvas.width; offset += 16) {
                ctx.beginPath();
                ctx.moveTo(offset, 0);
                ctx.lineTo(offset + canvas.height, canvas.height);
                ctx.stroke();
            }
            ctx.restore();
        }

        function prepareCanvas() {
            if (!scratchEnabled || revealTriggered) {
                return;
            }
            resizeCanvas();
            drawOverlay();
        }

        function getPointerPosition(event) {
            var original = event.originalEvent || event;
            var clientX;
            var clientY;
            if (original.touches && original.touches.length) {
                clientX = original.touches[0].clientX;
                clientY = original.touches[0].clientY;
            } else if (original.changedTouches && original.changedTouches.length) {
                clientX = original.changedTouches[0].clientX;
                clientY = original.changedTouches[0].clientY;
            } else {
                clientX = original.clientX;
                clientY = original.clientY;
            }
            var rect = canvas.getBoundingClientRect();
            var x = ((clientX - rect.left) / rect.width) * canvas.width;
            var y = ((clientY - rect.top) / rect.height) * canvas.height;
            return {
                x: x,
                y: y
            };
        }

        function scratchAt(x, y) {
            if (!ctx || !scratchEnabled || revealTriggered) {
                return;
            }
            ctx.save();
            ctx.globalCompositeOperation = 'destination-out';
            var brushSize = Math.max(canvas.width, canvas.height) * 0.08;
            ctx.beginPath();
            ctx.arc(x, y, brushSize, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
            scheduleRevealCheck();
        }

        function scheduleRevealCheck() {
            if (revealCheckScheduled) {
                return;
            }
            revealCheckScheduled = true;
            window.requestAnimationFrame(function () {
                revealCheckScheduled = false;
                checkRevealProgress();
            });
        }

        function checkRevealProgress() {
            if (!ctx || revealTriggered) {
                return;
            }
            try {
                var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                var data = imageData.data;
                var transparentPixels = 0;
                var totalPixels = data.length / 4;
                for (var i = 3; i < data.length; i += 4) {
                    if (data[i] < 128) {
                        transparentPixels++;
                    }
                }
                if (totalPixels === 0) {
                    return;
                }
                var ratio = transparentPixels / totalPixels;
                if (Math.abs(ratio - lastLoggedRatio) >= 0.05) {
                    console.log('[Scratch Debug] Reveal ratio:', ratio.toFixed(2));
                    lastLoggedRatio = ratio;
                }
                if (ratio >= revealThreshold) {
                    console.log('[Scratch Debug] Threshold reached, clearing overlay.');
                    finalizeReveal();
                }
            } catch (err) {
                console.warn('[Scratch Debug] Unable to evaluate reveal ratio', err);
            }
        }

        function finalizeReveal() {
            if (revealTriggered) {
                return;
            }
            revealTriggered = true;
            scratchEnabled = false;
            ctx.save();
            ctx.globalCompositeOperation = 'destination-out';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.restore();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            $card.addClass('ever-scratch-card--revealed');
            triggerResultRequest();
        }

        function triggerResultRequest() {
            if (ajaxTriggered) {
                return;
            }
            ajaxTriggered = true;
            if (!spinUrl) {
                applyResult(segments.length ? segments[0] : null);
                everblockShowGameModal('', '', []);
                return;
            }
            $.ajax({
                url: spinUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id_block: blockId,
                    token: token
                },
                success: function (res) {
                    handleResultResponse(res);
                },
                error: function () {
                    everblockShowGameModal('An error occurred.', '', []);
                }
            });
        }

        function handleResultResponse(res) {
            if (res && res.result) {
                applyResult(res.result);
            }
            var details = [];
            var isWinning = res && res.result && (res.result.isWinning || res.result.is_winning);
            if (res && res.categories_message) {
                details.push(res.categories_message);
            }
            if (res && res.minimum_purchase_message) {
                details.push(res.minimum_purchase_message);
            }
            var code = isWinning && res ? (res.code || '') : '';
            var message = res && typeof res.message === 'string' ? res.message : '';
            everblockShowGameModal(message, code, details);
            if (res && res.status === false && res.message) {
                showStatus(res.message, true);
            }
        }

        var supportsPointerEvents = window.PointerEvent && typeof window.PointerEvent === 'function';

        function handleScratchStart(event) {
            if (!scratchEnabled || revealTriggered) {
                return;
            }
            if (event.type === 'pointerdown') {
                if (event.pointerType === 'mouse' && event.button !== 0) {
                    return;
                }
                if (canvas.setPointerCapture && typeof event.pointerId !== 'undefined') {
                    try {
                        canvas.setPointerCapture(event.pointerId);
                    } catch (captureErr) {
                        console.warn('[Scratch Debug] Unable to capture pointer', captureErr);
                    }
                }
            } else if (event.type === 'mousedown' && event.which !== 1) {
                return;
            }
            event.preventDefault();
            isPointerDown = true;
            var pos = getPointerPosition(event);
            scratchAt(pos.x, pos.y);
        }

        function handleScratchMove(event) {
            if (!scratchEnabled || revealTriggered) {
                return;
            }
            if (event.type === 'pointermove') {
                if (event.pointerType === 'mouse' && (!isPointerDown || (typeof event.buttons !== 'undefined' && event.buttons === 0))) {
                    return;
                }
            } else if (event.type === 'mousemove' && !isPointerDown) {
                return;
            }
            event.preventDefault();
            var pos = getPointerPosition(event);
            scratchAt(pos.x, pos.y);
        }

        function handleScratchEnd(event) {
            if (event && event.type === 'pointerup' && canvas.releasePointerCapture && typeof event.pointerId !== 'undefined') {
                try {
                    canvas.releasePointerCapture(event.pointerId);
                } catch (releaseErr) {
                    console.warn('[Scratch Debug] Unable to release pointer capture', releaseErr);
                }
            }
            isPointerDown = false;
        }

        if (supportsPointerEvents) {
            $canvas.on('pointerdown', handleScratchStart);
            $canvas.on('pointermove', handleScratchMove);
            $canvas.on('pointerup pointerleave pointercancel', handleScratchEnd);
        } else {
            $canvas.on('mousedown touchstart', handleScratchStart);
            $canvas.on('mousemove touchmove', handleScratchMove);
            $canvas.on('mouseup mouseleave touchend touchcancel', handleScratchEnd);
        }

        $(window).on('resize', function () {
            if (!scratchEnabled || revealTriggered) {
                return;
            }
            window.requestAnimationFrame(prepareCanvas);
        });

        function requestInitialStatus() {
            if (!spinUrl) {
                enableScratch();
                return;
            }
            $.ajax({
                url: spinUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id_block: blockId,
                    token: token,
                    check: 1
                },
                success: function (res) {
                    if (res && res.played) {
                        disableScratch(res.message || '');
                        return;
                    }
                    if (res && res.playable === false && !isEmployee) {
                        if (res.reason === 'before_start' || res.reason === 'after_end') {
                            disableScratch(res.message || '');
                        } else {
                            disableScratch(res ? res.message : '');
                        }
                        return;
                    }
                    enableScratch();
                },
                error: function () {
                    enableScratch();
                }
            });
        }

        if (!segments.length) {
            disableScratch('No segments available.');
            return;
        }

        requestInitialStatus();
    });

    $('.ever-mystery-boxes').each(function () {
        var $container = $(this);
        var configB64 = $container.data('config');
        var config = {};
        if (typeof configB64 === 'string') {
            try {
                config = JSON.parse(atob(configB64));
            } catch (e) {
                config = {};
            }
        }
        var boxesData = config.boxes || config.segments || [];
        var boxes = Array.isArray(boxesData) ? boxesData : Object.values(boxesData);
        var playUrl = config.playUrl || config.spinUrl || '';
        var token = config.token || '';
        var blockId = parseInt($container.data('block-id'), 10) || 0;
        var isLogged = typeof prestashop !== 'undefined' && prestashop.customer && prestashop.customer.is_logged;
        var isEmployee = !!config.isEmployee;
        if (!isEmployee && typeof everblock_is_employee !== 'undefined') {
            isEmployee = !!everblock_is_employee;
        }
        if (!isLogged && !isEmployee) {
            return;
        }
        var langId = parseInt(config.langId, 10);
        if (!langId && typeof prestashop !== 'undefined' && prestashop.language && prestashop.language.id) {
            langId = parseInt(prestashop.language.id, 10) || 0;
        }
        var $statusMessage = $container.find('.ever-mystery-status-message');
        var $statusText = $statusMessage.find('.ever-mystery-status-text');
        var $content = $container.find('.ever-mystery-content');
        var $grid = $container.find('.ever-mystery-grid');
        var $boxes = $grid.find('.ever-mystery-box');
        var $revealMessage = $container.find('.ever-mystery-reveal-message');
        var emptyStatusMessage = $container.data('empty-status');
        if (typeof emptyStatusMessage !== 'string') {
            emptyStatusMessage = '';
        }
        if (!$grid.length || !$boxes.length || !boxes.length) {
            if (!boxes.length) {
                var message = emptyStatusMessage || 'No boxes available.';
                $statusText.html(message);
                $statusMessage.show();
            }
            return;
        }
        var closedLabel = typeof config.closedLabel === 'string' && config.closedLabel.length
            ? config.closedLabel
            : ($grid.data('closed-label') || '?');
        var requestInProgress = false;
        var gameLocked = false;

        function extractValue(value) {
            if (value === null || typeof value === 'undefined') {
                return '';
            }
            if (typeof value === 'string') {
                return value;
            }
            if (typeof value === 'number' || typeof value === 'boolean') {
                return value.toString();
            }
            if (Array.isArray(value)) {
                for (var i = 0; i < value.length; i++) {
                    var nested = extractValue(value[i]);
                    if (nested) {
                        return nested;
                    }
                }
                return '';
            }
            if (typeof value === 'object') {
                if (langId && Object.prototype.hasOwnProperty.call(value, langId)) {
                    var langVal = extractValue(value[langId]);
                    if (langVal) {
                        return langVal;
                    }
                }
                if (typeof prestashop !== 'undefined' && prestashop.language && prestashop.language.id) {
                    var psLangId = parseInt(prestashop.language.id, 10) || 0;
                    if (psLangId && Object.prototype.hasOwnProperty.call(value, psLangId)) {
                        var psLangVal = extractValue(value[psLangId]);
                        if (psLangVal) {
                            return psLangVal;
                        }
                    }
                }
                for (var key in value) {
                    if (!Object.prototype.hasOwnProperty.call(value, key)) {
                        continue;
                    }
                    var nestedVal = extractValue(value[key]);
                    if (nestedVal) {
                        return nestedVal;
                    }
                }
            }
            return '';
        }

        function resolveImage(imageData) {
            if (!imageData) {
                return '';
            }
            if (typeof imageData === 'string') {
                return imageData;
            }
            if (Array.isArray(imageData)) {
                for (var i = 0; i < imageData.length; i++) {
                    var nested = resolveImage(imageData[i]);
                    if (nested) {
                        return nested;
                    }
                }
                return '';
            }
            if (typeof imageData === 'object') {
                if (typeof imageData.url === 'string' && imageData.url.length) {
                    return imageData.url;
                }
                if (typeof imageData.src === 'string' && imageData.src.length) {
                    return imageData.src;
                }
                for (var key in imageData) {
                    if (!Object.prototype.hasOwnProperty.call(imageData, key)) {
                        continue;
                    }
                    var nestedValue = resolveImage(imageData[key]);
                    if (nestedValue) {
                        return nestedValue;
                    }
                }
            }
            return '';
        }

        function showStatus(message, visible) {
            if (visible && typeof message === 'string' && message.trim().length) {
                $statusText.html(message);
                $statusMessage.show();
            } else {
                $statusText.empty();
                $statusMessage.hide();
            }
        }

        function setBoxesDisabled(disabled) {
            $boxes.prop('disabled', !!disabled);
            $boxes.toggleClass('ever-mystery-box--disabled', !!disabled);
        }

        function disableBoxes(message, permanent) {
            if (permanent) {
                gameLocked = true;
            }
            setBoxesDisabled(true);
            var text = message;
            if (typeof text !== 'string' || !text.trim().length) {
                text = emptyStatusMessage || '';
            }
            showStatus(text, !!text);
        }

        function enableBoxes() {
            if (gameLocked) {
                return;
            }
            setBoxesDisabled(false);
            showStatus('', false);
        }

        function applyResult($box, result, response) {
            if (!$box || !$box.length) {
                return;
            }
            gameLocked = true;
            setBoxesDisabled(true);
            $box.removeClass('ever-mystery-box--disabled');
            $box.addClass('ever-mystery-box--revealed');
            var labelText = extractValue(result && result.label);
            var messageHtml = extractValue(result && result.message);
            var $label = $box.find('.ever-mystery-box-result-label');
            var $message = $box.find('.ever-mystery-box-result-message');
            var $imageWrapper = $box.find('.ever-mystery-box-result-image');
            var $image = $imageWrapper.find('img');
            $label.text(labelText || '');
            if (messageHtml) {
                $message.html(messageHtml);
            } else {
                $message.empty();
            }
            var textColor = result && result.text_color ? result.text_color : '';
            var backgroundColor = result && result.color ? result.color : '';
            if (backgroundColor) {
                $box.css('background-color', backgroundColor);
            }
            if (textColor) {
                $box.find('.ever-mystery-box-back').css('color', textColor);
            }
            var imageUrl = resolveImage(result && result.image);
            if (imageUrl) {
                $image.attr('src', imageUrl);
                $image.attr('alt', labelText || '');
                $imageWrapper.show();
            } else {
                $image.attr('src', '').attr('alt', '');
                $imageWrapper.hide();
            }
            $box.attr('aria-live', 'polite');
            var modalDetails = [];
            if (response && response.categories_message) {
                modalDetails.push(response.categories_message);
            }
            if (response && response.minimum_purchase_message) {
                modalDetails.push(response.minimum_purchase_message);
            }
            var isWinning = response && response.result && (response.result.isWinning || response.result.is_winning);
            var code = isWinning && response ? (response.code || '') : '';
            var modalMessage = response && typeof response.message === 'string' ? response.message : labelText;
            if ($revealMessage.length) {
                if (modalMessage) {
                    $revealMessage.html(modalMessage).show();
                } else {
                    $revealMessage.empty().hide();
                }
            }
            everblockShowGameModal(modalMessage || '', code, modalDetails);
        }

        function handleResultResponse($box, res) {
            if (res && res.played && !res.status) {
                disableBoxes(res.message || '', true);
                return;
            }
            if (res && res.playable === false && !isEmployee) {
                disableBoxes(res.message || '', true);
                return;
            }
            if (res && res.reason && (res.reason === 'before_start' || res.reason === 'after_end') && !isEmployee) {
                disableBoxes(res.message || '', true);
                return;
            }
            if (res && res.result) {
                applyResult($box, res.result, res);
                return;
            }
            if (res && res.message) {
                showStatus(res.message, true);
            } else {
                showStatus('', false);
            }
            if (!gameLocked) {
                enableBoxes();
            }
        }

        function sendPlayRequest($box) {
            if (!$box || !$box.length || requestInProgress || gameLocked) {
                return;
            }
            requestInProgress = true;
            setBoxesDisabled(true);
            if (!playUrl) {
                requestInProgress = false;
                applyResult($box, boxes[0] || null, {});
                return;
            }
            $.ajax({
                url: playUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id_block: blockId,
                    token: token,
                    box_index: parseInt($box.data('box-index'), 10) || 0
                },
                success: function (res) {
                    requestInProgress = false;
                    handleResultResponse($box, res || {});
                },
                error: function () {
                    requestInProgress = false;
                    gameLocked = false;
                    enableBoxes();
                    showStatus('An error occurred.', true);
                }
            });
        }

        function requestInitialStatus() {
            if (!playUrl) {
                enableBoxes();
                return;
            }
            setBoxesDisabled(true);
            $.ajax({
                url: playUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id_block: blockId,
                    token: token,
                    check: 1
                },
                success: function (res) {
                    if (res && res.played) {
                        disableBoxes(res.message || '', true);
                        return;
                    }
                    if (res && res.playable === false && !isEmployee) {
                        disableBoxes(res.message || '', true);
                        return;
                    }
                    if (res && res.reason && (res.reason === 'before_start' || res.reason === 'after_end') && !isEmployee) {
                        disableBoxes(res.message || '', true);
                        return;
                    }
                    gameLocked = false;
                    enableBoxes();
                },
                error: function () {
                    gameLocked = false;
                    enableBoxes();
                }
            });
        }

        $boxes.on('click', function (event) {
            event.preventDefault();
            sendPlayRequest($(this));
        });

        requestInitialStatus();
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
