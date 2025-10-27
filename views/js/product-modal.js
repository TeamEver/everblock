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

(function() {
  'use strict';

  function initEverblockModalUploader() {
    var fileInput = document.getElementById('everblock_modal_file');
    var payloadInput = document.getElementById('everblock_modal_file_payload');
    var nameInput = document.getElementById('everblock_modal_file_name');

    if (!fileInput || !payloadInput || !nameInput) {
      return;
    }

    var resetPayload = function() {
      payloadInput.value = '';
      nameInput.value = '';
    };

    fileInput.addEventListener('change', function() {
      if (!fileInput.files || fileInput.files.length === 0) {
        resetPayload();
        return;
      }

      var file = fileInput.files[0];
      var reader = new FileReader();

      reader.onload = function(event) {
        var result = event && event.target ? event.target.result : null;

        if (typeof result !== 'string') {
          resetPayload();
          return;
        }

        var commaIndex = result.indexOf(',');
        var base64Content = commaIndex !== -1 ? result.substring(commaIndex + 1) : result;

        payloadInput.value = base64Content;
        nameInput.value = file.name || '';
      };

      reader.onerror = function() {
        resetPayload();
      };

      reader.readAsDataURL(file);
    });

    var deleteCheckbox = document.querySelector('input[name="everblock_modal_file_delete"]');

    if (deleteCheckbox) {
      deleteCheckbox.addEventListener('change', function() {
        if (this.checked) {
          fileInput.value = '';
          resetPayload();
        }
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEverblockModalUploader);
  } else {
    initEverblockModalUploader();
  }
})();
