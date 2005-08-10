// $Id$

/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license GENERAL PUBLIC LICENSE (GPL)
 * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 *
 * @author Frederic Minne <zefredz@gmail.com>
 *
 * @package Wiki
 */

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