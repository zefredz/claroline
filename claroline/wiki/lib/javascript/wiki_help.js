// $Id$

/**
 * Wiki help functions
 * @author Frederic Minne <zefredz@gmail.com>
 */
     
function showHelp()
{
    var oHelpdiv = document.getElementById('help');
    oHelpdiv.style.display = 'block';
    enableButton( 'hidehelp' );
}

function hideHelp()
{
    var oHelpdiv = document.getElementById('help');
    oHelpdiv.style.display = 'none';
    enableButton( 'showhelp' );
}

function enableButton( sAction )
{
    document.getElementById('helpbtn').innerHTML = '';
    
    if ( sAction == 'hidehelp' )
    {
        document.getElementById('helpbtn').innerHTML = '<a href="#helpStart" onclick="hideHelp();"><img src="'+sImgPath+'minus.gif" style="border:0;" />&nbsp;'+sLangWikiHideHelp+'</a>';
    }
    else if ( sAction == 'showhelp' )
    {
        document.getElementById('helpbtn').innerHTML = '<a href="#helpStart" onclick="showHelp();"><img src="'+sImgPath+'plus.gif" style="border:0;" />&nbsp;'+sLangWikiShowHelp+'</a>';
    }
    else
    {
        alert( 'error: button ' + sAction + ' not found' );
    }
}

function example( sDemoText, sDivId )
{
   document.getElementById( sDivId ).value = sDemoText;
}

function addExample( sDemoText, sDivId )
{
    if( confirm( sLangWikiExampleWarning ) )
    {
        example( sDemoText, sDivId );
    }
}