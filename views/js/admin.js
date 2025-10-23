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

$(document).ready(function() {
  const cssTextarea = document.getElementById('EVERPSCSS');
  const jsTextarea = document.getElementById('EVERPSJS');
  let currentFaqTag = null;

  if (cssTextarea) {
    CodeMirror.fromTextArea(cssTextarea, {
      mode: 'text/css',
      theme: 'dracula',
      lineNumbers: true
    });
  }

  if (jsTextarea) {
    CodeMirror.fromTextArea(jsTextarea, {
      mode: 'text/javascript',
      theme: 'dracula',
      lineNumbers: true
    });
  }

  // Ensure documentation cards are displayed at the bottom of each tab in the
  // module configuration form. HelperForm renders documentation inputs as
  // ``type => html`` fields wrapped in a ``form-group`` element. We move that
  // wrapper to the end of the tab content so that tips are displayed after all
  // configuration fields, which matches the expected layout.
  $('.tab-content .tab-pane').each(function() {
    const $wrapper = $(this).find('.form-wrapper');

    if (!$wrapper.length) {
      return;
    }

    $wrapper
      .find('.form-group')
      .filter(function() {
        return $(this).find('.everblock-doc').length > 0;
      })
      .appendTo($wrapper);
  });

  // Transform legacy documentation cards into accessible accordions that match
  // the refreshed admin layout.
  $('.everblock-config__card--form .everblock-doc').each(function() {
    const $card = $(this);
    const $body = $card.find('.card-body');
    const $group = $card.closest('.form-group');

    if (!$body.length) {
      return;
    }

    const $title = $body.find('.card-title').first();
    const summaryHtml = $title.length ? $title.html() : '';

    if ($title.length) {
      $title.remove();
    }

    const $details = $('<details>', {
      class: 'everblock-doc-accordion',
      open: true
    });

    const $summary = $('<summary>', {
      class: 'everblock-doc-accordion__summary'
    }).html(summaryHtml || $card.data('title') || 'Documentation');

    const $content = $('<div>', {
      class: 'everblock-doc-accordion__content'
    }).append($body.contents());

    $details.append($summary, $content);
    $card.replaceWith($details);

    if ($group.length) {
      $group.addClass('everblock-form-group--doc');
    }
  });

  // Add a subtle pulse feedback on tab switch to give the interface more life.
  $('#module_form .nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
    const $target = $(e.target);
    const $pulse = $('<span>', { class: 'everblock-tab-pulse' });

    $target.append($pulse);

    setTimeout(function() {
      $pulse.remove();
    }, 600);
  });

  const $faqTable = $('[data-everblock-faq-table]');

  function isFaqPositionRequest(settings) {
    return (
      settings &&
      typeof settings.data === 'string' &&
      settings.data.indexOf('controller=AdminEverBlockFaq') !== -1 &&
      settings.data.indexOf('action=updatePositions') !== -1
    );
  }

  function updateFaqRowPositions() {
    if (!$faqTable.length) {
      return;
    }

    const tagCounters = {};

    $faqTable.find('tr.js-everblock-faq-row:visible').each(function() {
      const $row = $(this);
      const rowTag = ($row.data('tag') || '').toString();

      if (!Object.prototype.hasOwnProperty.call(tagCounters, rowTag)) {
        tagCounters[rowTag] = 0;
      }

      $row.attr('data-position', tagCounters[rowTag]);
      tagCounters[rowTag] += 1;
    });
  }

  function setCurrentFaqTagFromRow($row) {
    if (!$row || !$row.length) {
      return;
    }

    const tag = ($row.data('tag') || '').toString();

    if (tag) {
      currentFaqTag = tag;
    }
  }

  if ($faqTable.length) {
    const $faqFilter = $('.js-everblock-faq-filter');

    if ($faqFilter.length) {
      $faqFilter.on('change', function() {
        const selectedTag = $(this).val();
        const $rows = $faqTable.find('tr.js-everblock-faq-row');

        if (!selectedTag) {
          $rows.show();
          updateFaqRowPositions();
          return;
        }

        $rows.each(function() {
          const $row = $(this);
          const rowTag = ($row.data('tag') || '').toString();

          if (rowTag === selectedTag) {
            $row.show();
          } else {
            $row.hide();
          }
        });

        updateFaqRowPositions();
      });
    }

    $faqTable.on('mousedown touchstart', '.dragHandle', function() {
      const $row = $(this).closest('tr');
      setCurrentFaqTagFromRow($row);
    });

    updateFaqRowPositions();
  }

  $(document).ajaxSend(function(event, jqXHR, settings) {
    if (!isFaqPositionRequest(settings)) {
      return;
    }

    if (!currentFaqTag) {
      const idMatch = settings.data.match(/everblock_faq%5B%5D=(\d+)/);

      if (idMatch && idMatch[1]) {
        const $row = $('#tr_everblock_faq_' + idMatch[1]);
        setCurrentFaqTagFromRow($row);
      }
    }

    if (currentFaqTag && settings.data.indexOf('tag_name=') === -1) {
      settings.data += '&tag_name=' + encodeURIComponent(currentFaqTag);
    }
  });

  $(document).ajaxSuccess(function(event, jqXHR, settings) {
    if (!isFaqPositionRequest(settings)) {
      return;
    }

    currentFaqTag = null;
    updateFaqRowPositions();
  });

  $(document).ajaxError(function(event, jqXHR, settings) {
    if (isFaqPositionRequest(settings)) {
      currentFaqTag = null;
    }
  });
});
