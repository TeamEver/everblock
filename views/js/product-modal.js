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

  function initEverblockModalUploader() {
    var container = document.querySelector('.everblock-modal-panel[data-everblock-modal]');
    if (!container) {
      return;
    }

    var ajaxUrl = container.getAttribute('data-ever-ajax-url');
    var productId = container.getAttribute('data-ever-product-id');
    var noFileText = container.getAttribute('data-ever-no-file-text') || '';
    var defaultErrorMessage = container.getAttribute('data-ever-error-text') || '';
    var defaultDropText = container.getAttribute('data-ever-drop-text') || '';
    var uploadingText = container.getAttribute('data-ever-uploading-text') || '';

    if (!ajaxUrl || !productId) {
      return;
    }

    initLanguageTabs(container);

    function initUploader(options) {
      var fileInput = container.querySelector(options.fileInputSelector);
      var deleteCheckbox = container.querySelector(options.deleteCheckboxSelector);
      var payloadInput = container.querySelector(options.payloadInputSelector);
      var nameInput = container.querySelector(options.nameInputSelector);
      var feedback = container.querySelector(options.feedbackSelector);
      var fileWrapper = container.querySelector(options.fileWrapperSelector);
      var deleteWrapper = container.querySelector(options.deleteWrapperSelector);
      var previewContainer = container.querySelector(options.previewContainerSelector);
      var previewWrapper = container.querySelector(options.previewWrapperSelector);
      var previewImage = container.querySelector(options.previewImageSelector);
      var previewEmpty = container.querySelector(options.previewEmptySelector);
      var dropzone = fileInput ? fileInput.closest('.everblock-modal-dropzone') : null;
      var dropzoneTitle = dropzone ? dropzone.querySelector('.everblock-modal-dropzone__title') : null;
      var initialDropTitle = dropzoneTitle ? dropzoneTitle.textContent : defaultDropText;
      var previewEmptyText = container.getAttribute('data-ever-preview-empty-text')
        || (previewContainer && previewContainer.getAttribute('data-ever-preview-empty-text'))
        || '';
      var feedbackTimeout = null;
      var isProcessing = false;

      if (!fileInput) {
        return;
      }

      function resetPayload() {
        if (payloadInput) {
          payloadInput.value = '';
        }
        if (nameInput) {
          nameInput.value = '';
        }
      }

      function setProcessing(state) {
        isProcessing = state;
        fileInput.disabled = state;
        if (deleteCheckbox) {
          deleteCheckbox.disabled = state;
        }
        if (dropzone) {
          dropzone.classList.toggle('is-uploading', state);
        }
        if (dropzoneTitle) {
          dropzoneTitle.textContent = state && uploadingText ? uploadingText : initialDropTitle;
        }
      }

      function showFeedback(type, message) {
        if (!feedback) {
          return;
        }

        if (feedbackTimeout) {
          clearTimeout(feedbackTimeout);
          feedbackTimeout = null;
        }

        feedback.classList.remove('alert-success', 'alert-danger', 'd-none');

        if (!message) {
          feedback.classList.add('d-none');
          feedback.textContent = '';
          return;
        }

        feedback.textContent = message;

        if (type === 'success') {
          feedback.classList.add('alert-success');
          feedback.classList.remove('alert-danger');
          feedbackTimeout = setTimeout(function () {
            feedback.classList.add('d-none');
            feedback.textContent = '';
          }, 4000);
        } else {
          feedback.classList.add('alert-danger');
          feedback.classList.remove('alert-success');
        }
      }

      function buildPreviewUrl(url, timestamp) {
        if (!url) {
          return '';
        }

        var separator = url.indexOf('?') === -1 ? '?' : '&';
        var safeTimestamp = typeof timestamp === 'number' && !isNaN(timestamp) ? timestamp : Date.now();

        return url + separator + 't=' + safeTimestamp;
      }

      function updateFileDisplay(url, name, updateOptions) {
        updateOptions = updateOptions || {};
        if (!fileWrapper) {
          return;
        }

        var current = fileWrapper.querySelector('.everblock-modal-current-file');
        if (!current) {
          current = document.createElement('p');
          current.className = 'everblock-modal-current-file';
          fileWrapper.appendChild(current);
        }

        current.innerHTML = '';

        var icon = document.createElement('i');
        icon.className = 'material-icons';
        icon.setAttribute('aria-hidden', 'true');

        if (url) {
          current.classList.remove('text-muted');
          icon.textContent = 'link';
          current.appendChild(icon);
          var link = document.createElement('a');
          link.href = url;
          link.target = '_blank';
          link.rel = 'noopener';
          link.className = 'everblock-modal-file-link';
          var linkLabel = name || url;
          link.textContent = linkLabel;
          current.appendChild(link);
        } else {
          current.classList.add('text-muted');
          icon.textContent = 'cloud_off';
          current.appendChild(icon);
          var emptyText = document.createTextNode(' ' + noFileText);
          current.appendChild(emptyText);
        }

        if (previewContainer) {
          if (url) {
            previewContainer.classList.remove('d-none');
          } else {
            previewContainer.classList.add('d-none');
          }
        }

        var hasPreview = !!(url && updateOptions.isImage);
        var previewUrl = updateOptions.previewUrl || buildPreviewUrl(url, updateOptions.timestamp);

        if (previewWrapper) {
          if (hasPreview) {
            previewWrapper.classList.remove('d-none');
            if (previewImage) {
              previewImage.style.display = '';
              previewImage.src = previewUrl || url;
              previewImage.alt = name || '';
            }
          } else {
            previewWrapper.classList.add('d-none');
            if (previewImage) {
              previewImage.removeAttribute('src');
              previewImage.alt = '';
            }
          }
        }

        if (previewEmpty) {
          if (!url) {
            previewEmpty.classList.add('d-none');
          } else if (hasPreview) {
            previewEmpty.classList.add('d-none');
          } else {
            previewEmpty.classList.remove('d-none');
            var emptyLabel = previewEmpty.querySelector('span');
            if (emptyLabel && previewEmptyText) {
              emptyLabel.textContent = previewEmptyText;
            }
          }
        }
      }

      function toggleDeleteWrapper(visible) {
        if (!deleteWrapper) {
          return;
        }

        deleteWrapper.style.display = visible ? '' : 'none';

        if (deleteCheckbox) {
          deleteCheckbox.checked = false;
        }
      }

      function buildFormData() {
        var formData = new FormData();
        formData.append('ajax', '1');
        formData.append('action', 'EverblockProductModalFile');
        formData.append('configure', 'everblock');
        formData.append('id_product', productId);
        if (options.target) {
          formData.append('target', options.target);
        }
        return formData;
      }

      function handleResponse(data, context) {
        setProcessing(false);

        var success = !!(data && data.success);
        var message = data && data.message ? data.message : (defaultErrorMessage || '');

        showFeedback(success ? 'success' : 'error', message);

        if (success) {
          var hasFile = data && data.file_url ? data.file_url.length > 0 : false;
          var displayName = data && (data.file_display_name || data.file_name) ? (data.file_display_name || data.file_name) : '';
          updateFileDisplay(
            hasFile ? data.file_url : '',
            displayName,
            {
              isImage: !!(data && data.is_image),
              previewUrl: data && data.file_preview_url ? data.file_preview_url : '',
              timestamp: data && typeof data.file_timestamp !== 'undefined' ? data.file_timestamp : null,
            }
          );
          toggleDeleteWrapper(hasFile);
          resetPayload();
        } else if (context === 'delete' && deleteCheckbox) {
          deleteCheckbox.checked = false;
        }
      }

      function sendRequest(formData, context) {
        if (isProcessing) {
          return;
        }

        setProcessing(true);
        showFeedback(null, '');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onreadystatechange = function () {
          if (xhr.readyState !== 4) {
            return;
          }

          var data = null;

          if (xhr.status >= 200 && xhr.status < 300) {
            try {
              data = JSON.parse(xhr.responseText);
            } catch (error) {
              data = null;
            }
          }

          handleResponse(data, context);
        };

        xhr.onerror = function () {
          handleResponse(null, context);
        };

        xhr.send(formData);
      }

      function uploadFile(file) {
        if (!file) {
          return;
        }

        var formData = buildFormData();
        formData.append(options.fileField, file);

        resetPayload();
        sendRequest(formData, 'upload');
      }

      fileInput.addEventListener('change', function () {
        if (!fileInput.files || fileInput.files.length === 0) {
          resetPayload();
          return;
        }

        uploadFile(fileInput.files[0]);
        // Reset the native input so picking the same file again still triggers change.
        fileInput.value = '';
      });

      if (deleteCheckbox) {
        deleteCheckbox.addEventListener('change', function () {
          if (!deleteCheckbox.checked) {
            return;
          }

          var formData = buildFormData();
          formData.append('delete', '1');
          sendRequest(formData, 'delete');
        });
      }

      // ----- Drag & drop on the dropzone -----
      if (dropzone) {
        var dragEnterCount = 0;

        var preventDefaults = function (e) {
          e.preventDefault();
          e.stopPropagation();
        };

        ['dragenter', 'dragover'].forEach(function (eventName) {
          dropzone.addEventListener(eventName, function (e) {
            preventDefaults(e);
            if (eventName === 'dragenter') {
              dragEnterCount++;
            }
            dropzone.classList.add('is-dragover');
          });
        });

        ['dragleave', 'dragend'].forEach(function (eventName) {
          dropzone.addEventListener(eventName, function (e) {
            preventDefaults(e);
            dragEnterCount = Math.max(0, dragEnterCount - 1);
            if (dragEnterCount === 0) {
              dropzone.classList.remove('is-dragover');
            }
          });
        });

        dropzone.addEventListener('drop', function (e) {
          preventDefaults(e);
          dragEnterCount = 0;
          dropzone.classList.remove('is-dragover');

          if (isProcessing) {
            return;
          }

          var dt = e.dataTransfer;
          if (!dt || !dt.files || !dt.files.length) {
            return;
          }

          uploadFile(dt.files[0]);
        });
      }
    }

    initUploader({
      target: 'modal',
      fileField: 'everblock_modal_file',
      fileInputSelector: '#everblock_modal_file',
      deleteCheckboxSelector: 'input[name="everblock_modal_file_delete"]',
      payloadInputSelector: '#everblock_modal_file_payload',
      nameInputSelector: '#everblock_modal_file_name',
      feedbackSelector: '.everblock-modal-feedback',
      fileWrapperSelector: '.everblock-modal-file-wrapper',
      deleteWrapperSelector: '.everblock-modal-delete-wrapper',
      previewContainerSelector: '.everblock-modal-preview-container',
      previewWrapperSelector: '.everblock-modal-preview-wrapper',
      previewImageSelector: '.everblock-modal-preview-image',
      previewEmptySelector: '.everblock-modal-preview-empty',
    });

    initUploader({
      target: 'button',
      fileField: 'everblock_modal_button_file',
      fileInputSelector: '#everblock_modal_button_file',
      deleteCheckboxSelector: 'input[name="everblock_modal_button_file_delete"]',
      payloadInputSelector: '#everblock_modal_button_file_payload',
      nameInputSelector: '#everblock_modal_button_file_name',
      feedbackSelector: '.everblock-modal-button-feedback',
      fileWrapperSelector: '.everblock-modal-button-file-wrapper',
      deleteWrapperSelector: '.everblock-modal-button-delete-wrapper',
      previewContainerSelector: '.everblock-modal-button-preview-container',
      previewWrapperSelector: '.everblock-modal-button-preview-wrapper',
      previewImageSelector: '.everblock-modal-button-preview-image',
      previewEmptySelector: '.everblock-modal-button-preview-empty',
    });
  }

  function initLanguageTabs(container) {
    var tabs = container.querySelectorAll('.everblock-modal-lang-tab');
    if (!tabs.length) {
      return;
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function (event) {
        var targetSelector = tab.getAttribute('href');
        if (!targetSelector || targetSelector.charAt(0) !== '#') {
          return;
        }

        var target = document.querySelector(targetSelector);
        if (!target) {
          return;
        }

        // Avoid relying on a specific Bootstrap version: handle the toggle ourselves.
        event.preventDefault();
        event.stopPropagation();

        var siblingsContent = target.parentElement
          ? target.parentElement.querySelectorAll('.tab-pane')
          : [];

        Array.prototype.forEach.call(siblingsContent, function (pane) {
          pane.classList.remove('show', 'active', 'in');
        });

        target.classList.add('show', 'active', 'in');

        Array.prototype.forEach.call(tabs, function (other) {
          other.classList.remove('active');
          other.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEverblockModalUploader);
  } else {
    initEverblockModalUploader();
  }
})();
