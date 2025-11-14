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
(function ($) {
  'use strict';

  function formatFaqOption(option) {
    if (!option || typeof option !== 'object') {
      return '';
    }
    if (option.loading) {
      return option.text;
    }
    return option.text || '';
  }

  function initFaqSelector($select) {
    if (!$select || !$select.length) {
      return;
    }
    var ajaxUrl = $select.data('ajax-url');
    var placeholder = $select.data('placeholder') || '';

    if ($.fn.select2 && ajaxUrl) {
      $select.select2({
        width: '100%',
        allowClear: false,
        placeholder: placeholder,
        multiple: true,
        ajax: {
          url: ajaxUrl,
          dataType: 'json',
          delay: 250,
          cache: true,
          data: function (params) {
            params = params || {};
            return {
              ajax: 1,
              action: 'EverblockSearchFaq',
              configure: 'everblock',
              q: params.term || '',
              page: params.page || 1
            };
          },
          processResults: function (data, params) {
            params = params || {};
            params.page = params.page || 1;
            var results = Array.isArray(data.results) ? data.results : [];
            return {
              results: results,
              pagination: {
                more: data.pagination && data.pagination.more === true
              }
            };
          }
        },
        templateResult: formatFaqOption,
        templateSelection: function (option) {
          return formatFaqOption(option) || option.text || '';
        },
        escapeMarkup: function (markup) {
          return markup;
        }
      });
    } else if ($.fn.chosen) {
      $select.chosen({width: '100%'});
    }
  }

  $(document).ready(function () {
    $('.js-everblock-faq-selector').each(function () {
      initFaqSelector($(this));
    });
  });
})(window.jQuery);
