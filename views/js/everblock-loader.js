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

    var loaded = {};
    var loading = {};
    var prestashopEventsBound = false;

    var features = {
        legacy: {
            file: 'everblock.js',
            selectors: [
                'select[name$="[id_categories][]"], select[name$="[id_categories]"]',
                '.prettyblocks-image-slider',
                '.ever-bootstrap-carousel',
                '[data-ever-infinite-carousel="1"], [data-ever-mobile-carousel="1"]',
                '.ever_instagram img',
                '#everImageModal',
                '.ever-slide',
                '.everblock-gallery',
                '.everblock-masonry-gallery',
                '.everblock-video-gallery',
                '.everblock-video-products',
                '.everblock-guided-step',
                '.flash-deals-wrapper',
                '.everblock-downloads a',
                '.everblock-scroll-video',
                '.everblock-counter',
                '.everblock-countdown',
                '.everblock-podcasts audio',
                '[id^="block-"][data-lookbook-url]',
                '.ever-wheel-of-fortune',
                '.ever-scratch-card-block',
                '.ever-mystery-boxes',
                '.ever-advent-calendar',
                '.ever-slot-machine',
                '.prettyblock-category-tabs',
                '.pb-toc-summary'
            ]
        },
        slider: {
            file: 'everblock-slider.js',
            selectors: [
                '.ever-slider'
            ]
        },
        modal: {
            file: 'features/modal.js',
            selectors: [
                '[data-evermodal]',
                '.everblock-modal-button',
                '[data-everclickmodal]',
                '.evercontactform',
                '.everModalAutoTrigger',
                '#everblockImageModal',
                '.ever-exit-intent-modal'
            ]
        }
    };

    function getFallbackBaseUrl() {
        var script = document.currentScript;
        if (!script || !script.src) {
            return '';
        }

        return script.src.replace(/everblock-loader\.js(?:\?.*)?$/, '');
    }

    function getBaseUrl() {
        var baseUrl = window.everblock_js_base_url || getFallbackBaseUrl();
        if (!baseUrl) {
            return '';
        }

        return baseUrl.charAt(baseUrl.length - 1) === '/' ? baseUrl : baseUrl + '/';
    }

    function addVersion(url) {
        var version = window.everblock_js_version || '';
        if (!version) {
            return url;
        }

        return url + (url.indexOf('?') === -1 ? '?' : '&') + 'v=' + encodeURIComponent(version);
    }

    function getContextNode(context) {
        if (context && context.jquery && context.length) {
            return context[0];
        }

        if (context && context.nodeType) {
            return context;
        }

        return document;
    }

    function contextHasSelector(context, selector) {
        var root = getContextNode(context);

        try {
            if (root.nodeType === 1 && root.matches && root.matches(selector)) {
                return true;
            }

            return !!(root.querySelector && root.querySelector(selector));
        } catch (e) {
            return false;
        }
    }

    function hasFeature(featureName, context) {
        var feature = features[featureName];
        if (!feature) {
            return false;
        }

        return feature.selectors.some(function (selector) {
            return contextHasSelector(context, selector);
        });
    }

    function runFeatureInit(featureName, context) {
        if (featureName === 'slider' && typeof window.everblockInitSliders === 'function') {
            window.everblockInitSliders();
        }

        if (featureName === 'modal' && typeof window.everblockInitModals === 'function') {
            window.everblockInitModals(context || document);
        }
    }

    function loadFeature(featureName, context) {
        var feature = features[featureName];
        var baseUrl = getBaseUrl();

        if (!feature || !baseUrl) {
            return Promise.resolve();
        }

        if (loaded[featureName]) {
            runFeatureInit(featureName, context);
            return Promise.resolve();
        }

        if (loading[featureName]) {
            return loading[featureName].then(function () {
                runFeatureInit(featureName, context);
            });
        }

        loading[featureName] = new Promise(function (resolve, reject) {
            var scriptId = 'everblock-feature-' + featureName;
            var existingScript = document.getElementById(scriptId);
            var script = existingScript || document.createElement('script');

            if (existingScript) {
                loaded[featureName] = true;
                loading[featureName] = null;
                runFeatureInit(featureName, context);
                resolve();
                return;
            }

            script.id = scriptId;
            script.async = false;
            script.onload = function () {
                loaded[featureName] = true;
                loading[featureName] = null;
                runFeatureInit(featureName, context);
                resolve();
            };
            script.onerror = function () {
                loading[featureName] = null;
                reject(new Error('Unable to load Everblock feature: ' + featureName));
            };

            script.src = addVersion(baseUrl + feature.file);
            document.head.appendChild(script);
        });

        return loading[featureName];
    }

    function detectFeatures(context) {
        var detected = [];
        var legacyNeeded = hasFeature('legacy', context);

        if (legacyNeeded) {
            detected.push('legacy');
        }

        if (hasFeature('slider', context)) {
            detected.push('slider');
        }

        if (!legacyNeeded && hasFeature('modal', context)) {
            detected.push('modal');
        }

        return detected;
    }

    function scan(context) {
        var detected = detectFeatures(context || document);

        detected.forEach(function (featureName) {
            loadFeature(featureName, context || document).catch(function (error) {
                if (window.console && typeof window.console.error === 'function') {
                    window.console.error(error);
                }
            });
        });

        return detected;
    }

    function scheduleScan(context) {
        window.setTimeout(function () {
            scan(context || document);
        }, 0);
    }

    function bindPrestashopEvents() {
        if (prestashopEventsBound || typeof window.prestashop === 'undefined' || typeof window.prestashop.on !== 'function') {
            return;
        }

        prestashopEventsBound = true;
        [
            'updatedProduct',
            'updateProduct',
            'updateCart',
            'updatedCart',
            'changedCheckoutStep'
        ].forEach(function (eventName) {
            window.prestashop.on(eventName, function () {
                scheduleScan(document);
            });
        });
    }

    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    document.addEventListener('everblock:refresh', function (event) {
        var detail = event.detail || {};
        scheduleScan(detail.context || document);
    });

    window.EverblockLoader = {
        scan: scan,
        load: loadFeature,
        isLoaded: function (featureName) {
            return !!loaded[featureName];
        }
    };

    onReady(function () {
        scan(document);
        bindPrestashopEvents();
    });

    window.addEventListener('load', function () {
        scan(document);
        bindPrestashopEvents();
    });
})();
