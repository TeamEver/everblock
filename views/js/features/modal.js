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
(function () {
    'use strict';

    var $ = window.jQuery || window.$;
    var delegatedHandlersBound = false;
    var exitIntentBound = false;
    var exitIntentShown = false;

    window.everblockModalFeatureLoaded = true;

    if (!$) {
        return;
    }

    function getModalInstance($modal, options) {
        if (!$modal || !$modal.length) {
            return null;
        }

        if (typeof window.bootstrap === 'undefined' || typeof window.bootstrap.Modal === 'undefined') {
            return null;
        }

        var modalElement = $modal.get(0);
        if (typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
            return window.bootstrap.Modal.getOrCreateInstance(modalElement, options || {});
        }

        var modalInstance = null;
        if (typeof window.bootstrap.Modal.getInstance === 'function') {
            modalInstance = window.bootstrap.Modal.getInstance(modalElement);
        }

        return modalInstance || new window.bootstrap.Modal(modalElement, options || {});
    }

    function showModal($modal, options) {
        var modalInstance = getModalInstance($modal, options);
        if (modalInstance && typeof modalInstance.show === 'function') {
            modalInstance.show();
            return;
        }

        if ($modal && typeof $modal.modal === 'function') {
            $modal.modal('show');
        }
    }

    function findInContext(context, selector) {
        var $context = context && context.jquery ? context : $(context || document);
        var $matches = $context.find(selector);

        if ($context.is && $context.is(selector)) {
            $matches = $matches.add($context);
        }

        return $matches;
    }

    function getDecodedLink(value) {
        if (typeof value !== 'string' || !value.length) {
            return '';
        }

        try {
            return window.atob(value);
        } catch (e) {
            return '';
        }
    }

    function initAutoModals(context) {
        findInContext(context, 'div[data-evermodal]').each(function () {
            var $trigger = $(this);
            var triggerId = $trigger.attr('id') || '';
            var blockId = triggerId.indexOf('everblock-') === 0
                ? triggerId.replace('everblock-', '')
                : $trigger.data('evermodal');
            var timeout = parseInt($trigger.data('evertimeout'), 10);
            var modalUrl = getDecodedLink(window.evermodal_link);

            blockId = parseInt(blockId, 10);
            if (!blockId || !modalUrl || typeof window.everblock_token === 'undefined') {
                return;
            }

            if ($trigger.data('everblockModalInitialized')) {
                return;
            }
            $trigger.data('everblockModalInitialized', true);

            if (isNaN(timeout) || timeout < 0) {
                timeout = 0;
            }

            $.ajax({
                url: modalUrl,
                type: 'POST',
                data: {
                    id_everblock: blockId,
                    token: window.everblock_token,
                    everblock_origin_url: window.location.href
                },
                success: function (modal) {
                    if (!modal || !$.trim(modal)) {
                        return;
                    }

                    $('#everblockModal').remove();
                    $('body').append(modal);

                    var $modal = $('#everblockModal');
                    if (!$modal.length) {
                        return;
                    }

                    window.setTimeout(function () {
                        showModal($modal);
                    }, timeout);

                    $modal.on('shown.bs.modal', function () {
                        var $currentModal = $(this);
                        var windowHeight = $(window).height();
                        var modalHeaderHeight = $currentModal.find('.modal-header').outerHeight() || 0;
                        var modalFooterHeight = $currentModal.find('.modal-footer').outerHeight() || 0;
                        var modalBodyPadding = (parseInt($currentModal.find('.modal-body').css('padding-top'), 10) || 0)
                            + (parseInt($currentModal.find('.modal-body').css('padding-bottom'), 10) || 0);
                        var maxModalBodyHeight = windowHeight - modalHeaderHeight - modalFooterHeight - modalBodyPadding - 20;

                        $currentModal.find('.modal-body').css({
                            'max-height': maxModalBodyHeight + 'px',
                            'overflow-x': 'hidden',
                            'overflow-y': 'auto'
                        });
                    });

                    $modal.on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                },
                error: function (xhr) {
                    if (window.console && typeof window.console.log === 'function') {
                        window.console.log(xhr.responseText);
                    }
                }
            });
        });
    }

    function bindDelegatedHandlers() {
        if (delegatedHandlersBound) {
            return;
        }

        delegatedHandlersBound = true;

        $(document).on('submit.everblockModalFeature', '.evercontactform', function (event) {
            event.preventDefault();

            var contactUrl = getDecodedLink(window.evercontact_link);
            if (!contactUrl) {
                return;
            }

            $.ajax({
                url: contactUrl,
                type: 'POST',
                data: new window.FormData(this),
                contentType: false,
                processData: false,
                success: function (modal) {
                    $('#everblockModal').remove();
                    $('body').append(modal);
                    showModal($('#evercontactModal'));
                    $('#evercontactModal').on('hidden.bs.modal', function () {
                        $(this).remove();
                        $('.modal-backdrop').remove();
                    });
                },
                error: function (xhr) {
                    if (window.console && typeof window.console.log === 'function') {
                        window.console.log(xhr.responseText);
                    }
                }
            });
        });

        $(document).on('click.everblockModalFeature', '.everblock-modal-button, [data-everclickmodal]', function (event) {
            event.preventDefault();

            var blockId = $(this).data('everclickmodal');
            var cmsId = $(this).data('evercms');
            var productModalId = $(this).data('evermodal');
            var modalUrl = getDecodedLink(window.evermodal_link);

            if ((!blockId && !cmsId && !productModalId) || !modalUrl || typeof window.everblock_token === 'undefined') {
                return;
            }

            var data = {
                token: window.everblock_token,
                force: 1,
                everblock_origin_url: window.location.href
            };

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
                url: modalUrl,
                type: 'POST',
                data: data,
                success: function (modal) {
                    if (!modal || !$.trim(modal)) {
                        return;
                    }

                    $('#everblockModal').remove();
                    $('body').append(modal);

                    var $modal = $('#everblockModal');
                    if (!$modal.length) {
                        return;
                    }

                    showModal($modal);
                    $modal.on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                },
                error: function (xhr) {
                    if (window.console && typeof window.console.log === 'function') {
                        window.console.log(xhr.responseText);
                    }
                }
            });
        });

        $(document).on('click.everblockImageModal', '.everblock-page__content img', function (event) {
            var $imageModal = $('#everblockImageModal');
            var $clickedImage = $(this);
            var imageSrc = $clickedImage.attr('src');

            if (!$imageModal.length || !imageSrc) {
                return;
            }

            event.preventDefault();

            var imageAlt = $clickedImage.attr('alt') || '';
            var imageTitle = $clickedImage.attr('title') || imageAlt;
            var $modalImage = $imageModal.find('.everblock-image-modal__img');
            var $caption = $imageModal.find('.everblock-image-modal__caption');

            $modalImage.attr('src', imageSrc).attr('alt', imageAlt);
            if (imageTitle) {
                $caption.text(imageTitle).removeClass('d-none');
            } else {
                $caption.addClass('d-none').text('');
            }

            showModal($imageModal);
        });
    }

    function initAutoTriggers(context) {
        findInContext(context, '.everModalAutoTrigger').each(function () {
            var $modal = $(this);

            if ($modal.data('everblockModalAutoShown')) {
                return;
            }

            $modal.data('everblockModalAutoShown', true);
            showModal($modal);
        });
    }

    function bindExitIntent(context) {
        if (exitIntentBound || !findInContext(context, '.ever-exit-intent-modal').length) {
            return;
        }

        exitIntentBound = true;
        $(document).on('mouseout.everblockExitIntent', function (event) {
            if (event.clientY > 0 || exitIntentShown) {
                return;
            }

            var $modal = $('.ever-exit-intent-modal').first();
            if ($modal.length) {
                showModal($modal);
                exitIntentShown = true;
            }
        });
    }

    function init(context) {
        var initContext = context || document;

        bindDelegatedHandlers();
        initAutoModals(initContext);
        initAutoTriggers(initContext);
        bindExitIntent(initContext);
    }

    window.everblockShowModal = window.everblockShowModal || function (modal, options) {
        showModal(modal && modal.jquery ? modal : $(modal), options);
    };
    window.everblockInitModals = init;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            init(document);
        });
    } else {
        init(document);
    }

    document.addEventListener('everblock:refresh', function (event) {
        var detail = event.detail || {};
        init(detail.context || document);
    });
})();
