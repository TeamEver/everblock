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
    function isEverblockElementVisible($element) {
        if (!$element || !$element.length) {
            return false;
        }
        if (!$element.is(':visible')) {
            return false;
        }
        var node = $element.get(0);
        return !!(node && node.offsetParent !== null);
    }

    function everblockGetModalInstance($modal, options) {
        if (!$modal || !$modal.length) {
            return null;
        }
        if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
            return null;
        }
        var modalElement = $modal.get(0);
        if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
            return bootstrap.Modal.getOrCreateInstance(modalElement, options || {});
        }
        var modalInstance = null;
        if (typeof bootstrap.Modal.getInstance === 'function') {
            modalInstance = bootstrap.Modal.getInstance(modalElement);
        }
        return modalInstance || new bootstrap.Modal(modalElement, options || {});
    }

    function everblockShowModal($modal, options) {
        var modalInstance = everblockGetModalInstance($modal, options);
        if (modalInstance && typeof modalInstance.show === 'function') {
            modalInstance.show();
            return;
        }
        if ($modal && typeof $modal.modal === 'function') {
            $modal.modal('show');
        }
    }

    var everblockCarouselIndex = 0;

    function getEverblockItemsPerSlide($carousel) {
        var slidesDesktop = parseInt($carousel.data('itemsDesktop'), 10);
        var slidesMobile = parseInt($carousel.data('itemsMobile'), 10);
        var slides = parseInt($carousel.data('items'), 10);
        if (isNaN(slidesDesktop) || slidesDesktop <= 0) {
            slidesDesktop = !isNaN(slides) && slides > 0 ? slides : 1;
        }
        var viewportWidth = window.innerWidth || $(window).width();
        if (!isNaN(slidesMobile) && slidesMobile > 0 && viewportWidth < 768) {
            return slidesMobile;
        }
        return slidesDesktop;
    }

    function buildEverblockCarousel($carousel) {
        var itemsPerSlide = getEverblockItemsPerSlide($carousel);
        var storedItems = $carousel.data('everblockItems');
        if (!storedItems) {
            storedItems = $carousel.children().detach();
            $carousel.data('everblockItems', storedItems);
        } else {
            storedItems = storedItems.detach();
        }

        var layout = $carousel.data('layout') || 'grid';
        var rowClass = $carousel.data('rowClass') || 'row';
        var controlsEnabled = $carousel.data('controls');
        var indicatorsEnabled = $carousel.data('indicators');
        var autoplay = parseInt($carousel.data('autoplay'), 10) === 1;
        var autoplayDelay = parseInt($carousel.data('autoplayDelay'), 10);
        var infinite = parseInt($carousel.data('infinite'), 10);
        var pauseOnHover = parseInt($carousel.data('pauseOnHover'), 10);
        if (isNaN(autoplayDelay) || autoplayDelay <= 0) {
            autoplayDelay = 5000;
        }
        if (isNaN(infinite)) {
            infinite = 1;
        }
        if (controlsEnabled === undefined) {
            controlsEnabled = true;
        }
        if (indicatorsEnabled === undefined) {
            indicatorsEnabled = true;
        }

        var itemsArray = storedItems.toArray();
        var hasMultipleSlides = itemsArray.length > itemsPerSlide;

        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Carousel !== 'undefined') {
            var instance = bootstrap.Carousel.getInstance($carousel[0]);
            if (instance) {
                instance.dispose();
            }
        } else if (typeof $carousel.carousel === 'function') {
            $carousel.carousel('dispose');
        }

        $carousel.removeClass('carousel slide').removeAttr('data-bs-ride');
        $carousel.empty();

        if (!hasMultipleSlides) {
            if (layout === 'grid') {
                var $row = $('<div />').addClass(rowClass);
                $row.append(storedItems);
                $carousel.append($row);
            } else {
                $carousel.append(storedItems);
            }
            $carousel.data('everblockItemsPerSlide', itemsPerSlide);
            return;
        }

        var carouselId = $carousel.attr('id');
        if (!carouselId) {
            everblockCarouselIndex += 1;
            carouselId = 'everblock-carousel-' + everblockCarouselIndex;
            $carousel.attr('id', carouselId);
        }

        var paddedItems = itemsArray;
        if (infinite && itemsArray.length && itemsArray.length % itemsPerSlide !== 0) {
            paddedItems = itemsArray.slice();
            var missingItems = itemsPerSlide - (itemsArray.length % itemsPerSlide);
            for (var padIndex = 0; padIndex < missingItems; padIndex += 1) {
                var sourceItem = itemsArray[padIndex % itemsArray.length];
                var clonedItem = $(sourceItem).clone(true, true)[0];
                paddedItems.push(clonedItem);
            }
        }

        var $inner = $('<div class="carousel-inner"></div>');
        var slideCount = Math.ceil(paddedItems.length / itemsPerSlide);
        for (var i = 0; i < slideCount; i += 1) {
            var $slide = $('<div class="carousel-item"></div>');
            if (i === 0) {
                $slide.addClass('active');
            }
            var chunkStart = i * itemsPerSlide;
            var chunkItems = paddedItems.slice(chunkStart, chunkStart + itemsPerSlide);
            if (layout === 'grid') {
                var $chunkRow = $('<div />').addClass(rowClass);
                $chunkRow.append(chunkItems);
                $slide.append($chunkRow);
            } else {
                $slide.append(chunkItems);
            }
            $inner.append($slide);
        }

        $carousel.addClass('carousel slide');
        if (autoplay) {
            $carousel.attr('data-bs-ride', 'carousel');
        }

        if (indicatorsEnabled) {
            var $indicators = $('<div class="carousel-indicators"></div>');
            for (var indicatorIndex = 0; indicatorIndex < slideCount; indicatorIndex += 1) {
                var $indicator = $('<button type="button"></button>')
                    .attr('data-bs-target', '#' + carouselId)
                    .attr('data-bs-slide-to', indicatorIndex);
                if (indicatorIndex === 0) {
                    $indicator.addClass('active').attr('aria-current', 'true');
                }
                $indicator.attr('aria-label', 'Slide ' + (indicatorIndex + 1));
                $indicators.append($indicator);
            }
            $carousel.append($indicators);
        }

        $carousel.append($inner);

        if (controlsEnabled) {
            var $prev = $('<button class="carousel-control-prev" type="button"></button>')
                .attr('data-bs-target', '#' + carouselId)
                .attr('data-bs-slide', 'prev')
                .append('<span class="carousel-control-prev-icon" aria-hidden="true"></span>')
                .append('<span class="visually-hidden">Previous</span>');
            var $next = $('<button class="carousel-control-next" type="button"></button>')
                .attr('data-bs-target', '#' + carouselId)
                .attr('data-bs-slide', 'next')
                .append('<span class="carousel-control-next-icon" aria-hidden="true"></span>')
                .append('<span class="visually-hidden">Next</span>');
            $carousel.append($prev, $next);
        }

        var pauseSetting = pauseOnHover === 0 ? false : 'hover';
        var config = {
            interval: autoplay ? autoplayDelay : false,
            pause: pauseSetting,
            ride: autoplay ? 'carousel' : false,
            wrap: !!infinite,
            keyboard: true,
            touch: true
        };

        try {
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Carousel !== 'undefined') {
                var newInstance = new bootstrap.Carousel($carousel[0], config);
                if (!autoplay && newInstance && typeof newInstance.pause === 'function') {
                    newInstance.pause();
                }
            } else if (typeof $carousel.carousel === 'function') {
                $carousel.carousel(config);
                if (!autoplay) {
                    $carousel.carousel('pause');
                }
            }
        } catch (e) {
            console.error('Everblock carousel initialization failed', e);
        }

        $carousel.data('everblockItemsPerSlide', itemsPerSlide);
    }

    function initEverblockCarousels($context, options) {
        var $scope = $context && $context.length ? $context : $(document);
        var forceInit = options && options.force === true;
        $scope.find('.ever-bootstrap-carousel').each(function () {
            var $carousel = $(this);
            if (!isEverblockElementVisible($carousel)) {
                return;
            }
            var itemsPerSlide = getEverblockItemsPerSlide($carousel);
            var previousItemsPerSlide = $carousel.data('everblockItemsPerSlide');
            if (!forceInit && previousItemsPerSlide === itemsPerSlide && $carousel.find('.carousel-inner').length) {
                return;
            }
            buildEverblockCarousel($carousel);
        });
    }
    initEverblockCarousels();
    var everblockCarouselResizeTimeout = null;
    $(window).on('resize', function () {
        if (everblockCarouselResizeTimeout) {
            clearTimeout(everblockCarouselResizeTimeout);
        }
        everblockCarouselResizeTimeout = setTimeout(function () {
            initEverblockCarousels();
        }, 200);
    });
    function padEverblockCarouselSlides($carousel) {
        var $inner = $carousel.find('.carousel-inner');
        if (!$inner.length) {
            return;
        }
        var $slides = $inner.children('.carousel-item');
        if ($slides.length <= 1) {
            return;
        }
        var itemsPerSlide = 0;
        $slides.each(function () {
            var $slide = $(this);
            var $row = $slide.children('.row');
            var count = $row.length ? $row.first().children().length : $slide.children().length;
            if (count > itemsPerSlide) {
                itemsPerSlide = count;
            }
        });
        if (itemsPerSlide <= 0) {
            return;
        }
        var $allItems = $slides.map(function () {
            var $slide = $(this);
            var $row = $slide.children('.row');
            return $row.length ? $row.first().children() : $slide.children();
        }).get();
        if (!$allItems.length) {
            return;
        }
        var itemIndex = 0;
        $slides.each(function () {
            var $slide = $(this);
            var $row = $slide.children('.row');
            var $container = $row.length ? $row.first() : $slide;
            var currentCount = $container.children().length;
            var missing = itemsPerSlide - currentCount;
            for (var i = 0; i < missing; i += 1) {
                var sourceItem = $allItems[itemIndex % $allItems.length];
                itemIndex += 1;
                $container.append($(sourceItem).clone(true, true));
            }
        });
    }

    $('[data-ever-infinite-carousel="1"], [data-ever-mobile-carousel="1"]').each(function(){
        var $carousel = $(this);
        var $inner = $carousel.find('.carousel-inner');
        if (!$inner.length || $inner.children('.carousel-item').length <= 1) {
            return;
        }
        padEverblockCarouselSlides($carousel);
        var refreshInstanceItems = function() {
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Carousel !== 'undefined') {
                var instance = bootstrap.Carousel.getInstance($carousel[0]);
                if (instance) {
                    instance._items = [].slice.call($inner.children('.carousel-item'));
                    instance._activeElement = $inner.children('.carousel-item.active')[0] || null;
                }
            } else if (typeof $carousel.data === 'function') {
                var legacyInstance = $carousel.data('bs.carousel') || $carousel.data('carousel');
                if (legacyInstance) {
                    legacyInstance._items = [].slice.call($inner.children('.carousel-item'));
                    legacyInstance._activeElement = $inner.children('.carousel-item.active')[0] || null;
                }
            }
        };
        $carousel.on('slide.bs.carousel', function(event){
            $carousel.data('everInfiniteDirection', event.direction);
        });
        $carousel.on('slid.bs.carousel', function(){
            var direction = $carousel.data('everInfiniteDirection');
            if (!direction) {
                return;
            }
            var $items = $inner.children('.carousel-item');
            if (direction === 'left') {
                $inner.append($items.first());
            } else if (direction === 'right') {
                $inner.prepend($items.last());
            }
            $items = $inner.children('.carousel-item');
            $items.removeClass('active');
            $items.first().addClass('active');
            refreshInstanceItems();
            $carousel.removeData('everInfiniteDirection');
        });
    });
    $('.ever_instagram img').on('click', function() {
        // Mettre à jour le src de l'image dans la modal
        var imageSrc = $(this).attr('src');
        $('#everModalImage').attr('src', imageSrc);

        // Ouvrir la modal
        everblockShowModal($('#everImageModal'));
    });
    if (!window.everblockModalFeatureLoaded) {
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
                everblockShowModal($('#evercontactModal'));
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
        let $trigger = $(this);
        let triggerId = $trigger.attr('id') || '';
        let blockId = triggerId.indexOf('everblock-') === 0
            ? triggerId.replace('everblock-', '')
            : $trigger.data('evermodal');
        let timeout = parseInt($trigger.data('evertimeout'), 10);
        blockId = parseInt(blockId, 10);
        if (!blockId || typeof evermodal_link === 'undefined' || typeof everblock_token === 'undefined') {
            return;
        }
        if (isNaN(timeout) || timeout < 0) {
            timeout = 0;
        }
        $.ajax({
            url: atob(evermodal_link),
            type: 'POST',
            data: { id_everblock: blockId, token: everblock_token, everblock_origin_url: window.location.href },
            success: function(modal) {
                if (!modal || !$.trim(modal)) {
                    return;
                }
                $('#everblockModal').remove();
                $('body').append(modal);
                let $modal = $('#everblockModal');
                if (!$modal.length) {
                    return;
                }
                setTimeout(function() {
                    everblockShowModal($modal);
                }, timeout);
                $modal.on('shown.bs.modal', function () {
                    let windowHeight = $(window).height();
                    let modalHeaderHeight = $(this).find('.modal-header').outerHeight() || 0; // S'il y a un en-tête
                    let modalFooterHeight = $(this).find('.modal-footer').outerHeight() || 0; // S'il y a un pied de page
                    let modalBodyPadding = (parseInt($(this).find('.modal-body').css('padding-top'), 10) || 0)
                        + (parseInt($(this).find('.modal-body').css('padding-bottom'), 10) || 0);
                    
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

    $(document).on('click', '.everblock-modal-button, [data-everclickmodal]', function(e) {
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
                if (!modal || !$.trim(modal)) {
                    return;
                }
                $('#everblockModal').remove();
                $('body').append(modal);
                let $modal = $('#everblockModal');
                if (!$modal.length) {
                    return;
                }
                everblockShowModal($modal);
                $modal.on('hidden.bs.modal', function () {
                    $(this).remove();
                });
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });
    everblockShowModal($('.everModalAutoTrigger'));
    }
    if (!window.everblockModalFeatureLoaded) {
    var $everblockImageModal = $('#everblockImageModal');
    if ($everblockImageModal.length) {
        $(document).on('click', '.everblock-page__content img', function (event) {
            var $clickedImage = $(this);
            var imageSrc = $clickedImage.attr('src');
            if (!imageSrc) {
                return;
            }
            event.preventDefault();
            var imageAlt = $clickedImage.attr('alt') || '';
            var imageTitle = $clickedImage.attr('title') || imageAlt;
            var $modalImage = $everblockImageModal.find('.everblock-image-modal__img');
            $modalImage.attr('src', imageSrc).attr('alt', imageAlt);
            var $caption = $everblockImageModal.find('.everblock-image-modal__caption');
            if (imageTitle) {
                $caption.text(imageTitle).removeClass('d-none');
            } else {
                $caption.addClass('d-none').text('');
            }
            everblockShowModal($everblockImageModal);
        });
    }

    // Exit intent modal
    var exitIntentShown = false;
    $(document).on('mouseout', function(e) {
        if (e.clientY <= 0 && !exitIntentShown) {
            var $modal = $('.ever-exit-intent-modal').first();
            if ($modal.length) {
                everblockShowModal($modal);
                exitIntentShown = true;
            }
        }
    });
    }

});
