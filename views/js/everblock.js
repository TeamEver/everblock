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
        function updateCountdown() {
            var distance = new Date(target).getTime() - new Date().getTime();
            if (distance <= 0) {
                $block.find('.everblock-countdown-value').text('00');
                return;
            }
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            $block.find('[data-type="days"]').text(('0' + days).slice(-2));
            $block.find('[data-type="hours"]').text(('0' + hours).slice(-2));
            $block.find('[data-type="minutes"]').text(('0' + minutes).slice(-2));
            $block.find('[data-type="seconds"]').text(('0' + seconds).slice(-2));
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
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

        function initWheel() {
            var $canvas = $container.find('.ever-wheel-canvas');
            if (!$canvas.length) {
                return;
            }
            var currentRotation = 0;
            var segmentCenters = [];
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
                var totalProb = segments.reduce(function (sum, seg) {
                    var p = parseFloat(seg.probability);
                    return sum + (isNaN(p) ? 0 : p);
                }, 0);
                if (totalProb <= 0) {
                    totalProb = segments.length || 1;
                }
                segmentCenters = [];
                segments.forEach(function (seg, i) {
                    var prob = parseFloat(seg.probability);
                    prob = isNaN(prob) ? 0 : prob;
                    var step = 2 * Math.PI * prob / totalProb;
                    if (totalProb <= 0) {
                        step = 2 * Math.PI / (segments.length || 1);
                    }
                    ctx.beginPath();
                    ctx.moveTo(size, size);
                    ctx.fillStyle = seg.color || '#' + Math.floor(Math.random() * 16777215).toString(16);
                    ctx.arc(size, size, size, start, start + step);
                    ctx.lineTo(size, size);
                    ctx.fill();
                    var textAngle = start + step / 2;
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
                    start += step;
                });
            }

            drawWheel();
            $(window).on('resize', drawWheel);

            if (!isLogged) {
                return;
            }

            function showWheelModal(msg, code, categories) {
                var codeHtml = '';
                if (code) {
                    codeHtml = '<div class="ever-wheel-code-wrapper">'
                        + '<span class="ever-wheel-code">' + code + '</span>'
                        + '<button type="button" class="btn btn-secondary btn-sm ms-2 ever-wheel-copy">Copier</button>'
                        + '<span class="ever-wheel-copy-feedback ms-2 text-success" style="display:none;"></span>'
                        + '</div>';
                }
                var categoriesHtml = '';
                if (categories) {
                    categoriesHtml = '<p class="ever-wheel-categories">' + categories + '</p>';
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
                    + '<p>' + msg + '</p>' + codeHtml + categoriesHtml
                    + '<button type="button" class="btn btn-primary mt-3" data-dismiss="modal" data-bs-dismiss="modal">OK</button>'
                    + '</div></div></div></div>';
                $('body').append(modal);
                var $modal = $('#everWheelModal');
                $modal.modal('show');
                if (code) {
                    $modal.find('.ever-wheel-copy').on('click', function () {
                        navigator.clipboard.writeText(code).then(function () {
                            var $feedback = $modal.find('.ever-wheel-copy-feedback');
                            $feedback.text('Code copié !').show();
                            setTimeout(function () {
                                $feedback.fadeOut();
                            }, 2000);
                        });
                    });
                }
                $modal.on('hidden.bs.modal', function () {
                    $(this).remove();
                });
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
                        var msg = res.message || '';
                        if (res && res.status && res.result) {
                            var idx = typeof res.index !== 'undefined' ? res.index : -1;
                            if (idx === -1) {
                                idx = segments.findIndex(function (seg) {
                                    return JSON.stringify(seg) === JSON.stringify(res.result);
                                });
                                if (idx === -1) {
                                    idx = 0;
                                }
                            }
                            var center = segmentCenters[idx] || 0;
                            var targetAngle = -90 - center;
                            if (targetAngle < 0) {
                                targetAngle += 360;
                            }
                            currentRotation += 360 * 5 + targetAngle;
                            $canvas.css('transform', 'rotate(' + currentRotation + 'deg)');
                            $canvas.one('transitionend', function () {
                                var isWinning = res.result && (res.result.isWinning || res.result.is_winning);
                                var code = isWinning ? res.code : null;
                                var categories = isWinning ? res.categories_message : '';
                                showWheelModal(msg, code, categories);
                                $btn.prop('disabled', false);
                            });
                        } else {
                            var isWinning = res.result && (res.result.isWinning || res.result.is_winning);
                            var code = isWinning ? res.code : null;
                            var categories = isWinning ? res.categories_message : '';
                            showWheelModal(msg, code, categories);
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function () {
                        $btn.prop('disabled', false);
                    }
                });
            });
        }

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
                        var msg = res.message || '';
                        $container.html('<p class="ever-wheel-already-played">' + msg + '</p>');
                    } else {
                        initWheel();
                    }
                },
                error: initWheel
            });
        } else {
            initWheel();
        }
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
