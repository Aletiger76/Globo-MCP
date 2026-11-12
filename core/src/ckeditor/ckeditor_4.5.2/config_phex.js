/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function(config) {
  config.toolbar_MyToolbar =
  [
    ['Source'],
    ['Maximize'],
    // ['Cut','Copy','Paste','PasteText','PasteFromWord'],
    ['Cut','Copy','Paste'],
    ['Bold','Italic','Underline'],
    ['SpecialChar'],
    ['Link','Unlink']
  ];

  config.toolbar = 'MyToolbar';
  config.removePlugins = 'elementspath';
  config.skin = 'kama';
  config.defaultLanguage = 'it';
  config.enterMode = CKEDITOR.ENTER_BR;
  config.shiftEnterMode = CKEDITOR.ENTER_BR;
  config.forcePasteAsPlainText = true;
};
