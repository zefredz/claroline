tinyMCEPopup.requireLangPack();

var TexFormulaDialog = {
	init : function() {
		tinyMCEPopup.resizeToInnerSize();
	   
		// get url value of the selected object
		var ed = tinyMCEPopup.editor;
		var fe = ed.selection.getNode();
		var formula = '';
		
		if(ed.dom.getAttrib(fe, 'class') == 'latexFormula' && ed.dom.getAttrib(fe, 'src') )
		{
			src = ed.dom.getAttrib(fe, 'src');
			pos = src.indexOf('.cgi', 1);
			formula = src.substr(pos + 5, src.length);
		}
		
		var f = document.forms[0];

		// Set the selected contents as text and place it in the input
		$("#formula").children().remove();
		$("#formula").append(formula);				
		
	},

	insert : function() {
		
		var formula = document.forms[0].formula.value;
		
		//var code = "[tex]" + formula + "[/tex]";
		var code = '<img src="' + texRendererURL +'?' + document.forms[0].formula.value + '" border="0" align="absmiddle" class="latexFormula" />';
		// Insert the contents from the input into the document
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, code);
		tinyMCEPopup.close();
	},
	
	preview : function() {
		//var code = '<img src="' + texRendererURL +'?' + document.forms[0].formula.value + '" border="0" align="absmiddle" class="latexFormula" />';
		$("#preview").children().remove();
		$.ajax({
			type: "POST",
			url: "dialog.php",
			data: "cmd=rqTex&formula=" + $('#formula').val(),
			success : function(response){
				$("#preview").append(response);   
			},
			dataType: 'html'
		      });		
	}
};

tinyMCEPopup.onInit.add(TexFormulaDialog.init, TexFormulaDialog);
