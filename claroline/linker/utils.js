//<script type="text/javascript">

    /** @package net.claroline.wiki*/

    function escapeHTML( str )
    {
 	 	encodedHtml = escape( str );
  		encodedHtml = encodedHtml.replace( /\//g,"%2F" );
  		encodedHtml = encodedHtml.replace( /\?/g,"%3F" );
  		encodedHtml = encodedHtml.replace( /=/g,"%3D" );
  		encodedHtml = encodedHtml.replace( /&/g,"%26" );
  		encodedHtml = encodedHtml.replace( /@/g,"%40" );
  		return encodedHtml;
   	}
   	
   	function in_array( needle, haystack )
   	{
        for( var i = 0; i < haystack.length; i++ )
        {
            if( hastack[i] == needle )
            {
                return true;
            }
        }
        
        return false;
   	}
   	
   	function array_delete( element, table )
   	{
        var temp = new Array();
        
        for( var i = 0; i < table.length; i++ )
        {
            if( table[i] == element )
            {
                continue;
            }
            else
            {
                temp.push( element );
            }
        }

        return temp;
   	}
   	
    function array_search( needle, haystack )
   	{
        for( var i = 0; i < haystack.length; i++ )
        {
            if( hastack[i] == needle )
            {
                return i;
            }
        }

        return null;
   	}
   	
   	function delay( waitTime )
   	{
        startDate = new Date();
        while (1)
        {
            currentDate = new Date();
            diff = currentDate - startDate;
            if( diff > waitTime )
            {
                break;
            }
        }
   	}
   	
    function print( divid, str )
    {
        document.getElementById( divid ).innerHTML += str;
    }
    
    function println( divid, str )
    {
        print( divid, str+"<br />\n" );
    }
    
    function clearDiv( divid )
    {
        print( divid, '' );
    }
    
    function overwrite( divid, str )
    {
        clearDiv( divid );
        print( divid, str );
    }
    
    function showDiv( divid )
    {
        var thediv = document.getElementById( divid );
        thediv.style.display = 'block';
    }
    
    function hideDiv( divid )
    {
        var thediv = document.getElementById( divid );
        thediv.style.display = 'none';
    }

//</script>
