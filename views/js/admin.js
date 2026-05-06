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

  if (cssTextarea && typeof CodeMirror !== 'undefined') {
    CodeMirror.fromTextArea(cssTextarea, {
      mode: 'text/css',
      theme: 'dracula',
      lineNumbers: true
    });
  }

  if (jsTextarea && typeof CodeMirror !== 'undefined') {
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

  $('.everblock-enhanced-multiselect, .everblock-bo-symfony-form select[multiple], .everblock-configuration-form select[multiple]').each(function () {
    enhanceEverblockMultiselect(this);
  });

  $('.everblock-enhanced-select, .everblock-bo-symfony-form select:not([multiple]), .everblock-configuration-form select:not([multiple])').each(function () {
    enhanceEverblockSelect(this);
  });

  initEverblockDateTimeFields();

  $(document).on('click', '[data-everblock-preview-open]', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $btn = $(this);
    const previewUrl = $btn.attr('data-everblock-preview-url') || $btn.data('everblockPreviewUrl');
    const $modal = $('#everblock-preview-modal');
    const $iframe = $('#everblock-preview-iframe');
    const $loader = $modal.find('[data-everblock-preview-loader]');
    const $openTab = $modal.find('#everblock-preview-open-tab');

    if (!previewUrl || !$modal.length || !$iframe.length) return;

    // Retire le focus du bouton pour éviter le warning ARIA
    $btn.blur();

    // Reset iframe et loader
    $iframe.attr('src', 'about:blank').css('opacity', 0);
    $loader.removeClass('d-none');
    if ($openTab.length) {
      $openTab.attr('href', previewUrl);
    }

    // Ouvre la modale
    if (typeof $modal.modal === 'function') {
      $modal.modal('show');
    } else {
      $modal.addClass('show').css('display', 'block');
    }

    // Charge la preview après l'ouverture complète (transition Bootstrap)
    setTimeout(function () {
      $iframe.attr('src', previewUrl);
    }, 200);

    $iframe.off('load.everblockPreview').on('load.everblockPreview', function () {
      $loader.addClass('d-none');
      $iframe.css('opacity', 1);
    });
  });

  $(document).on('hidden.bs.modal', '#everblock-preview-modal', function () {
    const $iframe = $('#everblock-preview-iframe');
    $iframe.attr('src', 'about:blank');
  });

  $(document).on('click', '[data-everblock-row-href]', function (event) {
    if (event.defaultPrevented) {
      return;
    }
    const $target = $(event.target);
    if ($target.closest('[data-everblock-row-no-click]').length) {
      return;
    }
    if ($target.closest('a, button, input, select, textarea, label').length) {
      return;
    }

    const href = $(this).attr('data-everblock-row-href');
    if (!href) {
      return;
    }

    if (event.metaKey || event.ctrlKey || event.button === 1) {
      window.open(href, '_blank');
      return;
    }

    window.location.href = href;
  });

  $(document).on('keydown', '[data-everblock-row-href]', function (event) {
    if (event.key !== 'Enter') {
      return;
    }
    if ($(event.target).closest('[data-everblock-row-no-click]').length) {
      return;
    }
    if ($(event.target).closest('a, button, input, select, textarea, label').length) {
      return;
    }
    const href = $(this).attr('data-everblock-row-href');
    if (href) {
      event.preventDefault();
      window.location.href = href;
    }
  });

  initEverblockFriendlyUrlAutoFill();

  $(document).on('click', '[data-everblock-copy]', function () {
    const button = this;
    const text = button.getAttribute('data-everblock-copy') || '';
    const originalHtml = button.innerHTML;
    const copiedLabel = button.getAttribute('data-everblock-copied-label') || 'Copied';

    function markCopied() {
      if (button.classList.contains('everblock-icon-btn')) {
        button.innerHTML = '<i class="material-icons">check</i><span class="sr-only">' + copiedLabel + '</span>';
      } else {
        button.innerHTML = '<i class="material-icons">check</i> ' + copiedLabel;
      }
      setTimeout(function () {
        button.innerHTML = originalHtml;
      }, 1400);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(markCopied, function () {});
      return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', 'readonly');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    try {
      document.execCommand('copy');
      markCopied();
    } catch (error) {
    }
    document.body.removeChild(textarea);
  });

  const $shortcodeDocSearch = $('[data-everblock-doc-search]');
  if ($shortcodeDocSearch.length) {
    const $emptyState = $('[data-everblock-doc-empty]');

    function filterShortcodeDocumentation() {
      const query = $.trim($shortcodeDocSearch.val()).toLowerCase();
      let visibleTotal = 0;

      $('[data-everblock-doc-group]').each(function () {
        const $group = $(this);
        let groupVisible = 0;

        $group.find('[data-everblock-doc-entry]').each(function () {
          const $entry = $(this);
          const matches = !query || $entry.text().toLowerCase().indexOf(query) !== -1;

          $entry.toggle(matches);
          if (matches) {
            groupVisible += 1;
            visibleTotal += 1;
          }
        });

        $group.toggle(groupVisible > 0);
        $group.find('[data-everblock-doc-visible-count]').text(groupVisible);
      });

      $emptyState.toggleClass('d-none', visibleTotal > 0);
    }

    $shortcodeDocSearch.on('input', filterShortcodeDocumentation);
    $('[data-everblock-doc-clear]').on('click', function () {
      $shortcodeDocSearch.val('').trigger('input').trigger('focus');
    });
  }

  $(document).on('click', function (event) {
    if (!$(event.target).closest('.everblock-multiselect').length) {
      $('.everblock-multiselect.is-open')
        .removeClass('is-open')
        .find('.everblock-multiselect__control')
        .attr('aria-expanded', 'false');
    }
  });

  $(document).on('change', '[data-everblock-check-all]', function () {
    const checked = this.checked;
    $('[data-everblock-row-check]').prop('checked', checked);
  });

  $(document).on('change', '[data-everblock-row-check]', function () {
    const $rows = $('[data-everblock-row-check]');
    const $checkedRows = $rows.filter(':checked');
    $('[data-everblock-check-all]')
      .prop('checked', $rows.length > 0 && $checkedRows.length === $rows.length)
      .prop('indeterminate', $checkedRows.length > 0 && $checkedRows.length < $rows.length);
  });
});

function everblockSlugify(value) {
  if (!value) {
    return '';
  }

  let slug = String(value).toString();
  if (typeof slug.normalize === 'function') {
    slug = slug.normalize('NFD').replace(/[̀-ͯ]/g, '');
  }
  slug = slug.toLowerCase();
  slug = slug.replace(/[^a-z0-9]+/g, '-');
  slug = slug.replace(/^-+|-+$/g, '');
  slug = slug.replace(/-{2,}/g, '-');

  return slug;
}

function initEverblockFriendlyUrlAutoFill() {
  const slugFields = document.querySelectorAll('input[name*="[link_rewrite_"], input[id*="link_rewrite_"]');
  if (!slugFields.length) {
    return;
  }

  slugFields.forEach(function (slugField) {
    if (slugField.dataset.everblockSlugInit === '1') {
      return;
    }
    slugField.dataset.everblockSlugInit = '1';
    slugField.dataset.everblockManuallyEdited = slugField.value && slugField.value.trim() !== '' ? '1' : '0';

    slugField.addEventListener('input', function () {
      slugField.dataset.everblockManuallyEdited = slugField.value && slugField.value.trim() !== '' ? '1' : '0';
    });

    slugField.addEventListener('blur', function () {
      if (slugField.value && slugField.value.trim() !== '') {
        slugField.value = everblockSlugify(slugField.value);
      }
    });

    let langId = '';
    const idMatch = (slugField.id || '').match(/link_rewrite_(\d+)$/);
    const nameMatch = (slugField.name || '').match(/\[link_rewrite_(\d+)\]/);
    if (idMatch) {
      langId = idMatch[1];
    } else if (nameMatch) {
      langId = nameMatch[1];
    }
    if (!langId) {
      return;
    }

    let nameField = null;
    if (slugField.id) {
      const candidateId = slugField.id.replace(/link_rewrite_(\d+)$/, 'name_$1');
      nameField = document.getElementById(candidateId);
    }
    if (!nameField) {
      const allCandidates = document.querySelectorAll('input[name*="[name_' + langId + ']"], input[id$="name_' + langId + '"]');
      if (allCandidates.length) {
        nameField = allCandidates[0];
      }
    }
    if (!nameField) {
      return;
    }

    function syncFromName() {
      if (slugField.dataset.everblockManuallyEdited === '1' && slugField.value.trim() !== '') {
        return;
      }
      slugField.value = everblockSlugify(nameField.value);
    }

    nameField.addEventListener('input', syncFromName);
    nameField.addEventListener('blur', syncFromName);
  });
}

function enhanceEverblockMultiselect(select) {
  const $select = $(select);

  if ($select.data('everblockEnhanced')) {
    return;
  }

  $select.data('everblockEnhanced', true);
  $select.addClass('everblock-native-multiselect');

  const placeholder = $select.data('everblock-placeholder') || 'Rechercher';
  const id = $select.attr('id') || ('everblock-multiselect-' + Math.random().toString(36).slice(2));
  const $wrapper = $('<div>', {
    class: 'everblock-multiselect',
    'data-target': id
  });
  const $control = $('<button>', {
    type: 'button',
    class: 'everblock-multiselect__control',
    'aria-expanded': 'false'
  });
  const $summary = $('<span>', { class: 'everblock-multiselect__summary' });
  const $chevron = $('<i>', { class: 'material-icons everblock-multiselect__chevron', text: 'expand_more' });
  const $panel = $('<div>', { class: 'everblock-multiselect__panel' });
  const $search = $('<input>', {
    type: 'search',
    class: 'everblock-multiselect__search',
    placeholder: placeholder
  });
  const $toolbar = $('<div>', { class: 'everblock-multiselect__toolbar' });
  const $selectVisible = $('<button>', { type: 'button', text: 'Tout sélectionner' });
  const $clear = $('<button>', { type: 'button', text: 'Effacer' });
  const $options = $('<div>', { class: 'everblock-multiselect__options' });
  const $chips = $('<div>', { class: 'everblock-multiselect__chips' });

  $control.append($summary, $chevron);
  $toolbar.append($selectVisible, $clear);
  $panel.append($search, $toolbar, $options);
  $wrapper.append($control, $panel, $chips);
  $select.after($wrapper);

  function optionLabel(option) {
    return $.trim($(option).text());
  }

  function optionValue(option) {
    return $(option).attr('value');
  }

  function renderOptions() {
    $options.empty();

    if (!select.options.length) {
      $options.append($('<div>', { class: 'everblock-multiselect__empty', text: 'Aucune option disponible' }));
      return;
    }

    Array.prototype.forEach.call(select.options, function (option) {
      const value = optionValue(option);
      const label = optionLabel(option);
      const checkboxId = id + '-' + value;
      const $checkbox = $('<input>', {
        type: 'checkbox',
        id: checkboxId,
        checked: option.selected,
        'data-value': value
      });
      const $label = $('<label>', {
        class: 'everblock-multiselect__option',
        for: checkboxId,
        role: 'option',
        'data-search': label.toLowerCase()
      });

      $label.append($checkbox, $('<span>', { text: label }));
      $options.append($label);
    });
  }

  function selectedOptions() {
    return Array.prototype.filter.call(select.options, function (option) {
      return option.selected;
    });
  }

  function sync() {
    const selected = selectedOptions();
    const count = selected.length;

    $summary
      .toggleClass('has-selection', count > 0)
      .text(count > 0 ? count + ' sélectionné' + (count > 1 ? 's' : '') : 'Aucune restriction');

    $chips.empty();
    selected.slice(0, 12).forEach(function (option) {
      const value = optionValue(option);
      const $chip = $('<span>', { class: 'everblock-multiselect__chip' });
      const $remove = $('<button>', {
        type: 'button',
        'aria-label': 'Retirer',
        html: '&times;'
      });

      $remove.on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        setSelected(value, false);
      });

      $chip.append($('<span>', { text: optionLabel(option) }), $remove);
      $chips.append($chip);
    });

    if (selected.length > 12) {
      $chips.append($('<span>', {
        class: 'everblock-multiselect__chip',
        text: '+' + (selected.length - 12)
      }));
    }

    $options.find('input[type="checkbox"]').each(function () {
      const option = Array.prototype.find.call(select.options, function (item) {
        return optionValue(item) === $(this).data('value').toString();
      }, this);
      this.checked = option ? option.selected : false;
    });
  }

  function setSelected(value, selected) {
    Array.prototype.forEach.call(select.options, function (option) {
      if (optionValue(option) === value.toString()) {
        option.selected = selected;
      }
    });
    $select.trigger('change');
    sync();
  }

  function filterOptions(query) {
    const normalized = query.toLowerCase();
    let visibleCount = 0;

    $options.find('.everblock-multiselect__option').each(function () {
      const matches = $(this).data('search').indexOf(normalized) !== -1;
      $(this).toggle(matches);
      if (matches) {
        visibleCount += 1;
      }
    });

    $options.find('.everblock-multiselect__empty').remove();
    if (visibleCount === 0) {
      $options.append($('<div>', { class: 'everblock-multiselect__empty', text: 'Aucun résultat' }));
    }
  }

  $control.on('click', function () {
    $('.everblock-multiselect.is-open').not($wrapper).removeClass('is-open').find('.everblock-multiselect__control').attr('aria-expanded', 'false');
    $wrapper.toggleClass('is-open');
    $control.attr('aria-expanded', $wrapper.hasClass('is-open') ? 'true' : 'false');
    if ($wrapper.hasClass('is-open')) {
      setTimeout(function () {
        $search.trigger('focus');
      }, 0);
    }
  });

  $search.on('input', function () {
    filterOptions(this.value);
  });

  $options.on('change', 'input[type="checkbox"]', function () {
    setSelected($(this).data('value'), this.checked);
  });

  $selectVisible.on('click', function () {
    $options.find('.everblock-multiselect__option:visible input[type="checkbox"]').each(function () {
      setSelected($(this).data('value'), true);
    });
  });

  $clear.on('click', function () {
    Array.prototype.forEach.call(select.options, function (option) {
      option.selected = false;
    });
    $select.trigger('change');
    sync();
  });

  renderOptions();
  sync();
}

function enhanceEverblockSelect(select) {
  const $select = $(select);

  if ($select.data('everblockEnhanced')) {
    return;
  }

  $select.data('everblockEnhanced', true);
  $select.addClass('everblock-native-select');

  const placeholder = $select.data('everblock-placeholder') || 'Rechercher';
  const id = $select.attr('id') || ('everblock-select-' + Math.random().toString(36).slice(2));
  const $wrapper = $('<div>', {
    class: 'everblock-multiselect everblock-multiselect--single',
    'data-target': id
  });
  const $control = $('<button>', {
    type: 'button',
    class: 'everblock-multiselect__control',
    'aria-expanded': 'false'
  });
  const $summary = $('<span>', { class: 'everblock-multiselect__summary' });
  const $chevron = $('<i>', { class: 'material-icons everblock-multiselect__chevron', text: 'expand_more' });
  const $panel = $('<div>', { class: 'everblock-multiselect__panel' });
  const $search = $('<input>', {
    type: 'search',
    class: 'everblock-multiselect__search',
    placeholder: placeholder
  });
  const $options = $('<div>', { class: 'everblock-multiselect__options', role: 'listbox' });

  $control.append($summary, $chevron);
  $panel.append($search, $options);
  $wrapper.append($control, $panel);
  $select.after($wrapper);

  function optionLabel(option) {
    return $.trim($(option).text());
  }

  function optionValue(option) {
    return $(option).attr('value');
  }

  function selectedOption() {
    return select.options[select.selectedIndex] || null;
  }

  function sync() {
    const selected = selectedOption();
    const selectedLabel = selected ? optionLabel(selected) : '';

    $summary
      .toggleClass('has-selection', selectedLabel !== '')
      .text(selectedLabel || 'Sélectionner');

    $options.find('.everblock-multiselect__option').each(function () {
      $(this).toggleClass('is-selected', String($(this).data('value')) === String($select.val()));
    });
  }

  function renderOptions() {
    $options.empty();

    if (!select.options.length) {
      $options.append($('<div>', { class: 'everblock-multiselect__empty', text: 'Aucune option disponible' }));
      return;
    }

    Array.prototype.forEach.call(select.options, function (option) {
      const value = optionValue(option);
      const label = optionLabel(option);
      const $option = $('<button>', {
        type: 'button',
        class: 'everblock-multiselect__option everblock-multiselect__option--button',
        'data-value': value,
        'data-search': label.toLowerCase(),
        role: 'option',
        text: label
      });

      $option.on('click', function () {
        $select.val(value).trigger('change');
        $wrapper.removeClass('is-open');
        $control.attr('aria-expanded', 'false');
        sync();
      });

      $options.append($option);
    });
  }

  function filterOptions(query) {
    const normalized = query.toLowerCase();
    let visibleCount = 0;

    $options.find('.everblock-multiselect__option').each(function () {
      const matches = $(this).data('search').indexOf(normalized) !== -1;
      $(this).toggle(matches);
      if (matches) {
        visibleCount += 1;
      }
    });

    $options.find('.everblock-multiselect__empty').remove();
    if (visibleCount === 0) {
      $options.append($('<div>', { class: 'everblock-multiselect__empty', text: 'Aucun résultat' }));
    }
  }

  $control.on('click', function () {
    $('.everblock-multiselect.is-open').not($wrapper).removeClass('is-open').find('.everblock-multiselect__control').attr('aria-expanded', 'false');
    $wrapper.toggleClass('is-open');
    $control.attr('aria-expanded', $wrapper.hasClass('is-open') ? 'true' : 'false');
    if ($wrapper.hasClass('is-open')) {
      setTimeout(function () {
        $search.trigger('focus');
      }, 0);
    }
  });

  $search.on('input', function () {
    filterOptions(this.value);
  });

  $select.on('change', sync);

  renderOptions();
  sync();
}

function initEverblockDateTimeFields() {
  $('.everblock-datetime-field').each(function () {
    const input = this;
    const normalized = sqlToDatetimeLocal(input.value);

    try {
      input.type = 'datetime-local';
    } catch (error) {
      input.type = 'text';
    }

    if (normalized) {
      input.value = input.type === 'datetime-local' ? normalized : datetimeLocalToSql(normalized);
    }
  });

  $(document).on('submit', '.everblock-bo-symfony-form', function () {
    $(this).find('.everblock-datetime-field').each(function () {
      this.value = datetimeLocalToSql(this.value);
    });
  });
}

function sqlToDatetimeLocal(value) {
  if (!value || value === '0000-00-00 00:00:00') {
    return '';
  }

  return value.trim().replace(' ', 'T').slice(0, 16);
}

function datetimeLocalToSql(value) {
  if (!value) {
    return '';
  }

  const normalized = value.trim().replace('T', ' ');
  if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(normalized)) {
    return normalized + ':00';
  }

  return normalized;
}
