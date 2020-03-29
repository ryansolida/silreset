/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.toolbar = 'Full';

	config.toolbar_Full =
	[
		['Format','Bold','Italic','Underline','Strike','-'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-'],
		['Image','-','Table','-','Templates'],
		['Link','Unlink','Anchor'],
		['NumberedList','BulletedList','-','HorizontalRule'],
	    ['SpellChecker'],
	    ['Undo','Redo','-','Source']
	];
};
