tinyMCE.init({
    mode : "exact",
    elements: "intro_content",
    theme : "advanced",
    browsers : "msie,gecko,opera",
    plugins : "media,paste,table",
    theme_advanced_buttons1 : "fontselect,fontsizeselect,formatselect,bold,italic,underline,strikethrough,separator,sub,sup,separator,undo,redo",
    theme_advanced_buttons2 : "cut,copy,paste,pasteword,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent,separator,forecolor,backcolor,separator,hr,link,unlink,image,media,code",
    theme_advanced_buttons3 : "tablecontrols,separator,help",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_path : true,
    theme_advanced_path_location : "bottom",
    apply_source_formatting : true,
    convert_urls : false,
    relative_urls : false,
    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
});