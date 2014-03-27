<?php // $Id$

/**
 * CLAROLINE
 *
 * debug functions
 * All this  function output only  if  debugClaro is on
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel.utils
 * @author      Claro Team <cvs@claroline.net>
 * @author      Christophe Gesché <moosh@claroline.net>
 */

defined ( 'PRINT_DEBUG_INFO' ) || define ( 'PRINT_DEBUG_INFO', false ) ;

/**
 * Display access permissions from filesystem mode
 * @param int $mode
 * @return string
 */
function display_perms( $mode )
{
    /* Determine Type */
    if ($mode & 0x1000)
        $type = 'p' ; /* FIFO pipe */
    elseif ($mode & 0x2000)
        $type = 'c' ; /* Character special */
    elseif ($mode & 0x4000)
        $type = 'd' ; /* Directory */
    elseif ($mode & 0x6000)
        $type = 'b' ; /* Block special */
    elseif ($mode & 0x8000)
        $type = '-' ; /* Regular */
    elseif ($mode & 0xA000)
        $type = 'l' ; /* Symbolic Link */
    elseif ($mode & 0xC000)
        $type = 's' ; /* Socket */
    else
    $type='u'; /* UNKNOWN */
    
    /* Determine permissions */
    $owner['read'   ] = ($mode & 00400) ? 'r' : '-';
    $owner['write'  ] = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read'   ] = ($mode & 00040) ? 'r' : '-';
    $group['write'  ] = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read'   ] = ($mode & 00004) ? 'r' : '-';
    $world['write'  ] = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';
    
    /* Adjust for SUID, SGID and sticky bit */
    if( $mode & 0x800 )
    $owner['execute'] = ($owner[execute]=='x') ? 's' : 'S';
    if( $mode & 0x400 )
    $group['execute'] = ($group[execute]=='x') ? 's' : 'S';
    if( $mode & 0x200 )
    $world['execute'] = ($world[execute]=='x') ? 't' : 'T';

    $strPerms = '<strong>t</strong>:' . $type
    .           '<strong>o</strong>:' . $owner['read'] . $owner['write'] . $owner['execute']
    .           '<strong>g</strong>:' . $group['read'] . $group['write'] . $group['execute']
    .           '<strong>w</strong>:' . $world['read'] . $world['write'] . $world['execute']
    ;
    return $strPerms;
}

/**
 * Return an html list of function called until this.
 *
 * @return html stream
 */
function claro_html_debug_backtrace()
{
    $bt = debug_backtrace();
    $cbt = '<pre style="color:gray">' . "\n";
    $bt = array_reverse($bt);
    foreach ($bt as $btLevel)
    {
        if ($btLevel['function'] == __FUNCTION__) continue;
        
        $cbt .= 'L'.str_pad($btLevel['line'],5,' ',STR_PAD_LEFT) . ':'  ;
        $cbt .= '<a href="'.$btLevel['file'].'">#</a> ' ;
        $cbt .= str_pad(basename($btLevel['file']),30,' ', STR_PAD_BOTH) . '| ';
        $cbt .= '<b>' . $btLevel['function'] . '()</b>' . "\n";
    
    }
    return $cbt . '</pre>';
}

/**
 * Assertion handler for Claroline
 * @param string $file
 * @param int $line
 * @param string $code
 */
function claro_debug_assertion_handler($file, $line, $code)
{
    pushClaroMessage( claro_htmlspecialchars("Assertion failed in {$file} at lin {$line} : $code"), 'assert' );
}
