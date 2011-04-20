function confirmation(name)
{
    if (confirm(Claroline.getLang('Are you sure to delete '+ name +' ?')))
    {
        return true;
    }
    else
    {
        return false;
    }
}

var nOriginalHeight;
var nOriginalWidth;

function zoomIn()
{
    var oImage = document.getElementById('mainImage');
    
    oImage.width = nOriginalWidth;
    oImage.height = nOriginalHeight;
    
    oImage.onclick = function(){zoomOut();};
    oImage.setAttribute( 'title', Claroline.getLang('Click to zoom out') );
}

function zoomOut()
{
    var oImage = document.getElementById('mainImage');
    
    nOriginalHeight = oImage.height;
    nOriginalWidth = oImage.width;
    
    var nNewWidth = getWindowWidth() - Math.floor(getWindowWidth() / 10);
    
    if ( nNewWidth < nOriginalWidth )
    {
        var nNewHeight = computeHeight ( nNewWidth );
        	
        oImage.width = nNewWidth;
        oImage.height = nNewHeight;
        	
        oImage.onclick = function(){zoomIn();};
        oImage.setAttribute( 'title', Claroline.getLang('Click to zoom in') );
    }
}

function computeHeight( nWidth )
{
    var nScaleFactor = nWidth / nOriginalWidth;
    var nNewHeight = nOriginalHeight * nScaleFactor;
    return Math.floor( nNewHeight );
}

function getWindowWidth()
{
    var ww = 0;
    
    if ( typeof window.innerWidth != 'undefined' )
    {
        ww = window.innerWidth;  // NN and Opera version
    }
    else
    {
        if ( document.documentElement
            && typeof document.documentElement.clientWidth!='undefined'
            && document.documentElement.clientWidth != 0 )
        {
            ww = document.documentElement.clientWidth;
        }
        else
        {
            if ( document.body
                && typeof document.body.clientWidth != 'undefined' )
            {
                ww = document.body.clientWidth;
            }
        }
   }
   return ww;
}