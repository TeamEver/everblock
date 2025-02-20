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
  let scssTextarea = document.getElementById("EVERPSSASS");
  let scssEditor = CodeMirror.fromTextArea(scssTextarea, {
    mode: "text/css",
    theme: "dracula",
    lineNumbers: true
  });
  let cssTextarea = document.getElementById("EVERPSCSS");
  let cssEditor = CodeMirror.fromTextArea(cssTextarea, {
    mode: "text/css",
    theme: "dracula",
    lineNumbers: true
  });
  let jsTextarea = document.getElementById("EVERPSJS");
  let jsEditor = CodeMirror.fromTextArea(jsTextarea, {
    mode: "text/javascript",
    theme: "dracula",
    lineNumbers: true
  });
});