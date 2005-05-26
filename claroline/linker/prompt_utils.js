//<script type='text/javascript'>

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
 
	function prompt_for_url()
    {
        var url = window.prompt( lang_linker_prompt_for_url, '' );
        var emptyurl = /^\s*$/;
        var urlwithscheme = /^\w+:\/\/\w+/;
        var urlmailto = /^mailto:\w+@\w+/
        var urlmailtocandidate = /^mailto:\w+/

        if( url == null )
        {
            return null;
        }
        else if( url.match( emptyurl ) )
        {
        	window.alert(lang_linker_prompt_invalid_url);
            return prompt_for_url();
        }
        else if( url.match( urlwithscheme ) )
        {
            return url;
        }
        else if( url.match( urlmailtocandidate ) )
        {
        	if( url.match( urlmailto ) )
        	{
        		return url;
        	} 
        	else
        	{
        		window.alert(lang_linker_prompt_invalid_email);
        		return prompt_for_url();
        	} 
        }
        else
        {
            return 'http://' + url;
        }
    }

    function prompt_for_title()
    {
        var title = window.prompt( "Enter Link Title", "" );
        var emptystr = /^\s*$/;

        if( title == null || title.match( emptystr ) )
        {
            return null;
        }
        else
        {
            return title;
        }
    }
    
    function prompt_popup_for_external_link(current_crl)
   	{
    	var url = prompt_for_url();
    	var crl = null;
    		
    	if( url != null )
    	{
 			crl = coursecrl+"/CLEXT___/"+url;
 			
 			var currentlocation = window.location.toString();
 			var newlocation = "";

 			if( currentlocation.indexOf( "?" ) == -1 )
 			{
 				newlocation = 	currentlocation + "?cmd=add&crl="+html_escape(crl)+"&current_crl="+current_crl;
 			}
 			else
 			{
 				newlocation = 	currentlocation + "&cmd=add&crl="+html_escape(crl)+"&current_crl="+current_crl;
 			}
 			
 			window.location = newlocation;
    	}		
   	} 
   	
   	/**
	*   
	* @param 
	* @return 
	*/
   	function html_escape(str) 
   	{
     	encodedHtml = escape(str);
     	encodedHtml = encodedHtml.replace(/\//g,"%2F");
     	encodedHtml = encodedHtml.replace(/\?/g,"%3F");
     	encodedHtml = encodedHtml.replace(/=/g,"%3D");
      	encodedHtml = encodedHtml.replace(/&/g,"%26");
      	encodedHtml = encodedHtml.replace(/@/g,"%40");
      	return encodedHtml;
   	}
//</script>  