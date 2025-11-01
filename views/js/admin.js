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

  const $previewModal = $('#everblock-preview-modal');
  if ($previewModal.length) {
    const $previewRoot = $previewModal.find('[data-everblock-preview-root]');
    const $previewButton = $('[data-everblock-preview-open]');

    if ($previewRoot.length) {
      let previewData = $previewRoot.data('everblockPreviewContexts');

      if (typeof previewData === 'string' && previewData.length) {
        try {
          previewData = JSON.parse(previewData);
        } catch (error) {
          previewData = null;
        }
      }

      const previewUrl = $previewRoot.data('everblockPreviewUrl') || '';
      const texts = {
        select: $previewRoot.data('everblockPreviewTextSelect') || '',
        loading: $previewRoot.data('everblockPreviewTextLoading') || '',
        error: $previewRoot.data('everblockPreviewTextError') || '',
        empty: $previewRoot.data('everblockPreviewTextEmpty') || ''
      };
      const previewAvailable = !!$previewRoot.data('everblockPreviewAvailable');

      if (!previewAvailable || !previewData || !previewUrl) {
        if ($previewButton.length) {
          $previewButton.prop('disabled', true).addClass('disabled');
        }
        return;
      }

      const $languageSelect = $previewModal.find('[data-everblock-preview-language]');
      const $shopSelect = $previewModal.find('[data-everblock-preview-shop]');
      const $contextSelect = $previewModal.find('[data-everblock-preview-context]');
      const $fieldsContainer = $previewModal.find('[data-everblock-preview-fields]');
      const $iframe = $previewModal.find('[data-everblock-preview-frame]');
      const $placeholder = $previewModal.find('[data-everblock-preview-placeholder]');
      const $errorAlert = $previewModal.find('[data-everblock-preview-error]');
      const $runButton = $previewModal.find('[data-everblock-preview-run]');

      const controllers = Array.isArray(previewData.controllers) ? previewData.controllers : [];
      const defaults = typeof previewData.defaults === 'object' && previewData.defaults !== null
        ? previewData.defaults
        : {};

      function populateSelect($select, items, selectedValue) {
        if (!$select || !$select.length) {
          return;
        }

        $select.empty();

        if (!Array.isArray(items) || !items.length) {
          return;
        }

        items.forEach(function(item) {
          const id = item.id !== undefined
            ? item.id
            : item.value !== undefined
              ? item.value
              : item.key;
          const label = item.label !== undefined ? item.label : item.name;
          if (id === undefined || label === undefined) {
            return;
          }

          const $option = $('<option>', {
            value: id
          }).text(label);

          if (selectedValue !== undefined && selectedValue !== null && String(id) === String(selectedValue)) {
            $option.prop('selected', true);
          }

          $select.append($option);
        });
      }

      function getContextConfig(key) {
        for (let index = 0; index < controllers.length; index += 1) {
          if (controllers[index].key === key) {
            return controllers[index];
          }
        }
        return null;
      }

      function showPlaceholder(message) {
        if ($placeholder.length) {
          $placeholder.text(message || '').removeClass('hide');
        }
      }

      function hidePlaceholder() {
        if ($placeholder.length) {
          $placeholder.addClass('hide');
        }
      }

      function showError(message) {
        if ($errorAlert.length) {
          $errorAlert.text(message || texts.error).removeClass('hide');
        }
        hidePlaceholder();
      }

      function hideError() {
        if ($errorAlert.length) {
          $errorAlert.addClass('hide').text('');
        }
      }

      function renderFields(contextConfig) {
        $fieldsContainer.empty();

        if (!contextConfig || !Array.isArray(contextConfig.fields)) {
          return;
        }

        contextConfig.fields.forEach(function(field) {
          const fieldName = field.name || '';
          if (!fieldName) {
            return;
          }

          const fieldId = 'everblock-preview-field-' + fieldName;
          const $group = $('<div>', { class: 'form-group' });
          $group.append(
            $('<label>', {
              class: 'control-label',
              for: fieldId
            }).text(field.label || fieldName)
          );

          let $input;
          if (field.type === 'select') {
            $input = $('<select>', {
              class: 'form-control',
              id: fieldId,
              'data-everblock-preview-field': fieldName
            });

            if (Array.isArray(field.options)) {
              field.options.forEach(function(option) {
                const optionValue = option.id !== undefined ? option.id : option.value;
                const optionLabel = option.label !== undefined ? option.label : option.name;
                if (optionValue === undefined || optionLabel === undefined) {
                  return;
                }

                const $option = $('<option>', { value: optionValue }).text(optionLabel);
                if (field.value !== undefined && field.value !== null && String(optionValue) === String(field.value)) {
                  $option.prop('selected', true);
                }
                $input.append($option);
              });
            }
          } else {
            $input = $('<input>', {
              type: field.type || 'text',
              class: 'form-control',
              id: fieldId,
              value: field.value !== undefined && field.value !== null ? field.value : '',
              'data-everblock-preview-field': fieldName
            });

            if (field.placeholder) {
              $input.attr('placeholder', field.placeholder);
            }

            if (field.type === 'number') {
              $input.attr('step', '1');
              if (field.min !== undefined) {
                $input.attr('min', field.min);
              }
            }
          }

          $group.append($input);

          if (field.help) {
            $group.append(
              $('<p>', { class: 'help-block' }).text(field.help)
            );
          }

          $fieldsContainer.append($group);
        });
      }

      function buildPreviewUrl(contextConfig) {
        try {
          const url = new URL(previewUrl, window.location.href);

          if (contextConfig && contextConfig.controller) {
            url.searchParams.set('controller', contextConfig.controller);
          }

          if (contextConfig && contextConfig.page_name) {
            url.searchParams.set('page_name', contextConfig.page_name);
          }

          if ($languageSelect.length && $languageSelect.val()) {
            url.searchParams.set('id_lang', $languageSelect.val());
          }

          if ($shopSelect.length && $shopSelect.val()) {
            url.searchParams.set('id_shop', $shopSelect.val());
          }

          if (defaults.id_currency) {
            url.searchParams.set('id_currency', defaults.id_currency);
          }

          if (defaults.position !== undefined && defaults.position !== null) {
            url.searchParams.set('position', defaults.position);
          }

          $fieldsContainer.find('[data-everblock-preview-field]').each(function() {
            const $input = $(this);
            const name = $input.data('everblockPreviewField');
            const value = $input.val();

            if (name && value !== null && value !== undefined && value !== '') {
              url.searchParams.set(name, value);
            }
          });

          return url.toString();
        } catch (error) {
          return null;
        }
      }

      function resetState(message) {
        hideError();
        showPlaceholder(message || texts.empty);
        if ($iframe.length) {
          $iframe.off('load.everblockPreview');
          $iframe.attr('src', 'about:blank');
        }
      }

      function initializeModal() {
        populateSelect($languageSelect, previewData.languages || [], defaults.id_lang);
        populateSelect($shopSelect, previewData.shops || [], defaults.id_shop);
        populateSelect($contextSelect, controllers, defaults.context);

        const initialContext = getContextConfig($contextSelect.val());
        renderFields(initialContext);
        resetState(texts.select || texts.empty);
      }

      function updatePreview() {
        hideError();

        const contextConfig = getContextConfig($contextSelect.val());
        if (!contextConfig) {
          showError(texts.error || '');
          return;
        }

        const url = buildPreviewUrl(contextConfig);
        if (!url) {
          showError(texts.error || '');
          return;
        }

        showPlaceholder(texts.loading || '');

        if ($iframe.length) {
          $iframe.off('load.everblockPreview').on('load.everblockPreview', function() {
            hidePlaceholder();
          });
          $iframe.attr('src', url);
        }
      }

      $contextSelect.on('change', function() {
        const contextConfig = getContextConfig($(this).val());
        renderFields(contextConfig);
        resetState(texts.select || texts.empty);
      });

      $runButton.on('click', function(event) {
        event.preventDefault();
        updatePreview();
      });

      $previewButton.on('click', function(event) {
        event.preventDefault();
        $previewModal.modal('show');
      });

      $previewModal.on('show.bs.modal', function() {
        initializeModal();
      });
    }
  }
});