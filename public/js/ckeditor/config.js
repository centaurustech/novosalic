/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/
CKEDITOR.editorConfig = function( config )
{
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
    
    /* Adicionado por Tarcisio Angelo */
    config.toolbar = [
//        ['Cut', 'Copy', 'Paste', 'PasteText', '-', 'Print'],
//        ['Bold','Italic','Underline','Strike','-','Subscript'],
//        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
//        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
//        ['Link','Unlink','Anchor'],
//        ['Font','FontSize'],
//        ['TextColor','BGColor']
    ];
};
CKEDITOR.config.scayt_autoStartup = false; //desabilita corretor ortografico
CKEDITOR.config.fontSize_sizes = '12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;'; //limita tamanho de fontes possiveis
CKEDITOR.config.forcePasteAsPlainText = true; //Elimina todo css em um texto copiado e colado dentro do CKEDITOR.
