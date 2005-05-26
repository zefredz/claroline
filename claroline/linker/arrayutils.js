//<script type='text/javascript'>

    function array_delete( tbl, item )
    {
        var temp = new Array();

        for( i = 0; i < tbl.length; i++)
        {
            if( tbl[i] != item )
            {
                    temp.push( tbl[i] );
            }
        }

        return temp;
    }

    function in_array( tbl, item )
    {
        for( i = 0; i < tbl.length; i++)
        {
            if( tbl[i] == item )
            {
                return true;
            }
        }

        return false;
    }
    
    function array_dump(tbl)
    {
       	for(i=0;i<tbl.length;i++)
       	{
       		print('debug',tbl[i]+"<br>\n");	
       	}
   }               
//</script>