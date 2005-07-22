// $Id$

/**
 * Wiki help functions
 * @author Frederic Minne <zefredz@gmail.com>
 */

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