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
    const sliderStates = new Map();
    const mobileBreakpoint = 768;
    const tabletBreakpoint = 992;

    function parseNumber(value, fallback) {
        const parsed = parseInt(value, 10);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
    }

    function getItemsPerView(slider) {
        const itemsDesktop = parseNumber(slider.dataset.items, 1);
        const itemsTablet = parseNumber(slider.dataset.itemsTablet, itemsDesktop);
        const itemsMobile = parseNumber(slider.dataset.itemsMobile, 1);
        const width = window.innerWidth;
        if (width < mobileBreakpoint) {
            return itemsMobile;
        }
        if (width < tabletBreakpoint) {
            return itemsTablet;
        }
        return itemsDesktop;
    }

    function getGapValue(track) {
        const styles = window.getComputedStyle(track);
        const gapValue = styles.columnGap || styles.gap || '0';
        return parseFloat(gapValue) || 0;
    }

    function updateButtons(state) {
        if (!state.prevButton || !state.nextButton) {
            return;
        }
        state.prevButton.hidden = false;
        state.nextButton.hidden = false;
        if (state.infinite) {
            state.prevButton.disabled = false;
            state.nextButton.disabled = false;
            return;
        }
        if (state.disabled) {
            state.prevButton.disabled = true;
            state.nextButton.disabled = true;
            return;
        }
        state.prevButton.disabled = state.index <= 0;
        state.nextButton.disabled = state.index >= state.maxIndex;
    }

    function updateTrackPosition(state) {
        if (!state.track) {
            return;
        }
        const containerWidth = state.containerWidth || state.slider.clientWidth || 0;
        const offset = (containerWidth / 2) - (state.itemWidth / 2) - (state.index * state.itemWidth);
        state.track.style.transform = `translateX(${offset}px)`;
    }

    function updateItemStates(state) {
        state.items.forEach((item, itemIndex) => {
            const isActive = itemIndex === state.index;
            const isAdjacent = itemIndex === state.index - 1 || itemIndex === state.index + 1;
            item.classList.toggle('is-active', isActive);
            item.classList.toggle('is-adjacent', !isActive && isAdjacent);
            item.classList.toggle('is-inactive', !isActive && !isAdjacent);
        });
    }

    function updateState(state) {
        const totalItems = state.items.length;
        state.itemsPerView = getItemsPerView(state.slider);
        state.itemsPerView = Math.max(1, state.itemsPerView);
        state.gap = getGapValue(state.track);
        const containerWidth = state.slider.clientWidth || 0;
        state.containerWidth = containerWidth;
        const totalGap = state.gap * Math.max(0, state.itemsPerView - 1);
        state.itemWidth = state.itemsPerView > 0 ? (containerWidth - totalGap) / state.itemsPerView : 0;
        state.slider.style.setProperty('--ever-slider-active-width', `${state.itemWidth * 1.15}px`);
        state.items.forEach((item) => {
            item.style.width = `${state.itemWidth}px`;
        });
        state.maxIndex = Math.max(0, totalItems - 1);
        state.disabled = totalItems <= 1;
        if (state.index > state.maxIndex) {
            state.index = state.maxIndex;
        }
        state.pageCount = state.disabled ? 1 : totalItems;
        state.pageIndex = Math.min(state.pageCount - 1, state.index);
        updateTrackPosition(state);
        updateItemStates(state);
        updateButtons(state);
    }

    function clearAutoplay(state) {
        if (state.timer) {
            window.clearInterval(state.timer);
            state.timer = null;
        }
    }

    function startAutoplay(state) {
        clearAutoplay(state);
        if (!state.autoplay || state.pageCount <= 1 || state.disabled) {
            return;
        }
        state.timer = window.setInterval(() => {
            goToIndex(state, state.index + 1, false);
        }, state.autoplayDelay);
    }

    function goToIndex(state, index, resetAutoplay) {
        let targetIndex = index;
        if (state.infinite) {
            if (targetIndex > state.maxIndex) {
                targetIndex = 0;
            }
            if (targetIndex < 0) {
                targetIndex = state.maxIndex;
            }
        } else {
            targetIndex = Math.max(0, Math.min(state.maxIndex, targetIndex));
        }
        state.index = targetIndex;
        state.pageIndex = Math.min(state.pageCount - 1, state.index);
        updateTrackPosition(state);
        updateItemStates(state);
        updateButtons(state);
        if (resetAutoplay) {
            startAutoplay(state);
        }
    }

    function bindControls(state) {
        if (state.prevButton) {
            state.prevButton.addEventListener('click', () => {
                goToIndex(state, state.index - 1, true);
            });
        }
        if (state.nextButton) {
            state.nextButton.addEventListener('click', () => {
                goToIndex(state, state.index + 1, true);
            });
        }
    }

    function setupSlider(slider) {
        const track = slider.querySelector('.ever-slider-track');
        if (!track) {
            return;
        }
        const items = Array.from(track.querySelectorAll('.ever-slider-item'));
        const prevButton = slider.querySelector('.ever-slider-prev');
        const nextButton = slider.querySelector('.ever-slider-next');
        const autoplay = parseNumber(slider.dataset.autoplay, 0) === 1;
        const autoplayDelay = parseNumber(slider.dataset.autoplayDelay, 5000);
        const infinite = parseNumber(slider.dataset.infinite, 0) === 1;

        const state = {
            slider,
            track,
            items,
            prevButton,
            nextButton,
            autoplay,
            autoplayDelay,
            infinite,
            index: 0,
            itemsPerView: 1,
            itemWidth: 0,
            gap: 0,
            maxIndex: 0,
            pageCount: 1,
            pageIndex: 0,
            disabled: false,
            timer: null
        };

        sliderStates.set(slider, state);
        bindControls(state);
        updateState(state);
        startAutoplay(state);
    }

    function initEverblockSliders() {
        const sliders = document.querySelectorAll('.ever-slider');
        sliders.forEach((slider) => {
            if (sliderStates.has(slider)) {
                const state = sliderStates.get(slider);
                if (state) {
                    updateState(state);
                    startAutoplay(state);
                }
                return;
            }
            setupSlider(slider);
        });
    }

    let resizeTimer = null;
    function handleResize() {
        if (resizeTimer) {
            window.clearTimeout(resizeTimer);
        }
        resizeTimer = window.setTimeout(() => {
            sliderStates.forEach((state) => {
                updateState(state);
                startAutoplay(state);
            });
        }, 150);
    }

    document.addEventListener('DOMContentLoaded', () => {
        initEverblockSliders();
        window.addEventListener('resize', handleResize);
    });
})();
