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
 * @author Team Ever <https://www.team-ever.com/>
 * @copyright 2019-2025 Team Ever
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

/**
 * PrestaShop n'utilise pas la police d'icônes de TinyMCE dans le back-office :
 * js/admin/tinymce.inc.js remplace après init chaque <i class="mce-i-*"> par un
 * <i class="material-icons">, via le callback changeToMaterial(). Le mapping CSS
 * de secours (themes/*\/scss/**\/_tinymce.scss) n'est utilisable que dans le thème
 * legacy, qui seul importe FontAwesome ; sur les pages Symfony (new-theme) les
 * codepoints \fXXX sortent vides.
 *
 * Comme on initialise TinyMCE nous-mêmes (tinySetup() référence baseAdminDir,
 * iso_user et lang_is_rtl, absents des pages Symfony), il faut appliquer la
 * substitution nous-mêmes, et compléter la table de PrestaShop qui ne couvre que
 * les 18 boutons de sa propre barre d'outils.
 */
var everblockMceMaterialIcons = {
  // Complète la table de changeToMaterial() de PrestaShop.
  'mce-i-newdocument': 'note_add',
  'mce-i-print': 'print',
  'mce-i-superscript': 'superscript',
  'mce-i-subscript': 'subscript',
  'mce-i-forecolor': 'format_color_text',
  'mce-i-backcolor': 'format_color_fill',
  'mce-i-outdent': 'format_indent_decrease',
  'mce-i-indent': 'format_indent_increase',
  'mce-i-cut': 'content_cut',
  'mce-i-copy': 'content_copy',
  'mce-i-paste': 'content_paste',
  'mce-i-pastetext': 'short_text',
  'mce-i-searchreplace': 'find_replace',
  'mce-i-undo': 'undo',
  'mce-i-redo': 'redo',
  'mce-i-unlink': 'link_off',
  'mce-i-anchor': 'anchor',
  'mce-i-emoticons': 'insert_emoticon',
  'mce-i-inserttime': 'access_time',
  'mce-i-preview': 'visibility',
  'mce-i-visualblocks': 'view_module',
  'mce-i-charmap': 'font_download',
  'mce-i-hr': 'horizontal_rule',
  'mce-i-pagebreak': 'space_bar',
  'mce-i-removeformat': 'format_clear',
  'mce-i-selectall': 'select_all',
  'mce-i-fullscreen': 'fullscreen',
  'mce-i-help': 'help_outline'
};

/**
 * Injecte les règles de taille appliquées par skins/prestashop/skin.min.css aux
 * <i> Material, au cas où la feuille de style du skin ne soit pas chargée.
 */
function everblockInjectMceIconStyles() {
  if (document.getElementById('everblock-mce-icons')) {
    return;
  }

  var style = document.createElement('style');
  style.id = 'everblock-mce-icons';
  style.textContent = [
    '.mce-widget button i.material-icons { font-size: 20px; line-height: 20px; color: #6c868e; }',
    '.mce-widget button:hover i.material-icons, .mce-widget.mce-active button i.material-icons { color: #25b9d7; }',
    '.mce-menu-item i.material-icons { font-size: 17px; line-height: 17px; color: #6c868e; }',
    '.mce-btn i.material-icons { vertical-align: middle; }'
  ].join('\n');
  document.head.appendChild(style);
}

/**
 * Appelée par TinyMCE (init_instance_callback) puis à chaque ouverture de menu
 * ou de popup, les icônes de ces conteneurs étant rendues à la demande.
 */
function everblockChangeToMaterial() {
  if (typeof changeToMaterial === 'function') {
    // Boutons couverts nativement par PrestaShop : bold, italic, link, table…
    changeToMaterial();
  }

  $.each(everblockMceMaterialIcons, function (mceClass, ligature) {
    $('i.' + mceClass).replaceWith('<i class="material-icons">' + ligature + '</i>');
  });
}

window.everblockChangeToMaterial = everblockChangeToMaterial;

function initCustomTinyMCE() {
  if (typeof tinymce === "undefined") {
    setTimeout(initCustomTinyMCE, 50);
    return;
  }

  if (!document.querySelector('textarea.evertranslatable')) {
    return;
  }

  // Détruire TinyMCE s'il est déjà initialisé.
  // Ne pas itérer sur tinymce.editors : remove() mute le tableau en cours de
  // parcours et laisse un éditeur sur deux en place.
  if (tinymce.editors.length > 0) {
    tinymce.remove();
  }

  everblockInjectMceIconStyles();

  // TinyMCE est chargé, ajoutez vos configurations

  const filemanagerBase = typeof baseAdminDir !== 'undefined' ? baseAdminDir : (typeof ad !== 'undefined' ? ad : (window.ad || ''));
  const languageIso = typeof iso_user !== 'undefined' ? iso_user : (typeof iso !== 'undefined' ? iso : (window.iso || 'en'));

  window.defaultTinyMceConfig = {
    selector: 'textarea.evertranslatable',
    menubar: true,
    plugins: "visualblocks, preview searchreplace print insertdatetime, hr charmap colorpicker anchor code link image paste pagebreak table contextmenu table code media autoresize textcolor emoticons",
    toolbar1: "styleselect,|,formatselect,|,fontselect,|,fontsizeselect",
    toolbar2: "newdocument,print,|,bold,italic,underline,|,strikethrough,superscript,subscript,|,forecolor,colorpicker,backcolor,|,bullist,numlist,outdent,indent",
    toolbar3: "code,|,table,|,cut,copy,paste,searchreplace,|,blockquote,|,undo,redo,|,link,unlink,anchor,|,image,emoticons,media,|,inserttime,|,preview",
    toolbar4: "visualblocks,|,charmap,|,hr",
    language: languageIso,
    skin: "prestashop",
    statusbar: false,
    relative_urls: false,
    convert_urls: false,
    extended_valid_elements: "em[class|name|id]",
    init_instance_callback: 'everblockChangeToMaterial',
    menu: {
      edit: { title: 'Edit', items: 'undo redo | cut copy paste | selectall' },
      insert: { title: 'Insert', items: 'media image link | pagebreak' },
      view: { title: 'View', items: 'visualaid' },
      table: { title: 'Table', items: 'inserttable tableprops deletetable | cell row column' },
      tools: { title: 'Tools', items: 'code' }
    }
  };

  if (filemanagerBase) {
    window.defaultTinyMceConfig.plugins += " filemanager";
    window.defaultTinyMceConfig.external_filemanager_path = filemanagerBase + "/filemanager/";
    window.defaultTinyMceConfig.filemanager_title = "File manager";
    window.defaultTinyMceConfig.external_plugins = { "filemanager": filemanagerBase + "/filemanager/plugin.min.js" };
  }

  tinymce.init(window.defaultTinyMceConfig);
}

$(document).ready(function () {
  // Les menus et fenêtres modales sont rendus à la demande : leurs icônes
  // doivent être substituées à l'ouverture, comme le fait tinySetup().
  $('body').on('click', '.mce-btn, .mce-open, .mce-menu-item', function () {
    everblockChangeToMaterial();
  });

  initCustomTinyMCE();
});
