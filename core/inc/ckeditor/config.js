/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
config.language = 'ru';
	// config.uiColor = '#AADC6E';
config.toolbar = 'Editbar';
config.resize_enabled = false;
config.height = '300px';
config.toolbar_Editbar =
[
	['Undo','Maximize','Source'],
    ['Cut','Copy','Paste','PasteFromWord','RemoveFormat'],
    ['Bold','Italic','Underline'],
    ['NumberedList','BulletedList','Image','Table','Outdent','Indent'],
    ['Format','Font','FontSize','TextColor'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Link','Unlink','Anchor','SpecialChar']
];

};
