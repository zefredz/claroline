/*
    $Id$
 */

var CLDOC = {};

CLDOC.confirmation = function (name)
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

CLDOC.zoomIn = function()
{
    var oImage = document.getElementById('mainImage');
    
    oImage.width = nOriginalWidth;
    oImage.height = nOriginalHeight;
    
    oImage.onclick = function(){CLDOC.zoomOut();};
    oImage.setAttribute( 'title', Claroline.getLang('Click to zoom out') );
}

CLDOC.zoomOut = function()
{
    var oImage = document.getElementById('mainImage');
    
    nOriginalHeight = oImage.height;
    nOriginalWidth = oImage.width;
    
    var nNewWidth = CLDOC.getWindowWidth() - Math.floor(CLDOC.getWindowWidth() / 10);
    
    if ( nNewWidth < nOriginalWidth )
    {
        var nNewHeight = CLDOC.computeHeight ( nNewWidth );
        	
        oImage.width = nNewWidth;
        oImage.height = nNewHeight;
        	
        oImage.onclick = function(){CLDOC.zoomIn();};
        oImage.setAttribute( 'title', Claroline.getLang('Click to zoom in') );
    }
}

CLDOC.computeHeight = function ( nWidth )
{
    var nScaleFactor = nWidth / nOriginalWidth;
    var nNewHeight = nOriginalHeight * nScaleFactor;
    return Math.floor( nNewHeight );
}

CLDOC.getWindowWidth = function()
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
