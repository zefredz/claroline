tinyMCE.init({

	//-- general
    mode : "textareas",
    editor_selector : "simpleMCE",
    // plugins must be the same as in tinyMCE_GZ.init
    plugins : "paste,safari,texformula,spoiler",
    theme : "advanced",
    browsers : "safari,msie,gecko,opera",
	directionality : text_dir,
	gecko_spellcheck : true,
	
	//-- url
    convert_urls : false,
    relative_urls : false,
    
    //-- advanced theme
    theme_advanced_buttons1 : "cut,copy,paste,pasteword,bold,italic,underline,strikethrough,separator,bullist,numlist,undo,redo,link,unlink,texformula,spoiler",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_path : true,
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_resizing : true,
    theme_advanced_resize_horizontal : false,
    theme_advanced_resizing_use_cookie : false,
    
    //-- cleanup/output
    forced_root_block : false,
    apply_source_formatting : true,
	cleanup_on_startup : true,
    entity_encoding : "raw",
    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
});
