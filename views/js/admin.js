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


  $(document).on('click', '[data-everblock-preview-open]', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const url = $btn.data('everblockPreviewUrl');
    const $modal = $('#everblock-preview-modal');
    const $iframe = $modal.find('iframe');

    if (!url) {
      console.warn('Aucune URL de preview trouvée.');
      return;
    }
    if (!$modal.length) {
      console.warn('La modale de preview Everblock est introuvable dans le DOM.');
      return;
    }

    // reset l’iframe avant d’afficher la modale
    $iframe.attr('src', 'about:blank');

    // afficher la modale
    $modal.modal('show');

    // spinner optionnel
    const $spinner = $('<div class="everblock-preview-spinner d-flex align-items-center justify-content-center w-100 h-100 position-absolute top-0 start-0 bg-white" style="z-index:2;"><i class="icon-spinner icon-spin" style="font-size:2rem;"></i></div>');
    $modal.find('.modal-body').append($spinner);
    $spinner.fadeIn(150);

    // charger le contenu dans l’iframe
    $iframe.off('load.everblock').on('load.everblock', function () {
      $spinner.fadeOut(150, function () { $(this).remove(); });
    });
    $iframe.attr('src', url);
  });
});