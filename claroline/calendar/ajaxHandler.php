<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
* CLAROLINE
*
* User desktop : MyCalendar portlet ajax backend
* FIXME : move to calendar module
*
* @version      1.9 $Revision$
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/

//$tlabelReq = 'CLCAL';
require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';

require_once dirname(__FILE__) . '/lib/desktopcalendar.lib.php';

$cal = new UserDesktopCalendar;

if ( isset($_REQUEST['year']) )
{
    $cal->setYear( (int) $_REQUEST['year'] );
}

if ( isset($_REQUEST['month']) )
{
    $cal->setMonth( (int) $_REQUEST['month'] );
}

echo claro_utf8_encode( $cal->render(), get_conf('charset') );

?>