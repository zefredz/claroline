<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLICAL
 *
 * @author Claro Team <cvs@claroline.net>
 */


require_once dirname(__FILE__) . '/icalendar/class.iCal.inc.php';
require_once dirname(__FILE__) . '/fileManage.lib.php';


function buildICal($context)
{
    if (is_array($context) && count($context)>0)
    {
        $iCalRepositorySys =  get_conf('rootSys') . get_conf('iCalRepository','iCal/');
        if (file_exists($iCalRepositorySys) || claro_mkdir($iCalRepositorySys, CLARO_FILE_PERMISSIONS, true))
        {
            $iCal = (object) new iCal('', 0, $iCalRepositorySys ); // (ProgrammID, Method (1 = Publish | 0 = Request), Download Directory)

            $toolLabelList = array('CLCAL', 'CLANN', 'CLWRK');
            foreach ($toolLabelList as $toolLabel)
            {
                $icalToolLibPath = get_module_path($toolLabel) . '/lib/ical.write.lib.php';
                $icalToolFuncName =  $toolLabel . '_write_ical';
                if ( file_exists($icalToolLibPath)
                )
                {
                    include_once $icalToolLibPath;
                    if (function_exists($icalToolFuncName)) $iCal = call_user_func($icalToolFuncName, $iCal,$context );
                }
            }


            $iCalFilePath = $iCalRepositorySys . '/' ;
            if (array_key_exists('course',$context)) $iCalFilePath .= $context['course'] . '.';
            if (array_key_exists('group',$context)) $iCalFilePath .= 'g'.$context['group'] . '.';
            $iCalFilePath .= 'ics';
            $fpICal = fopen($iCalFilePath, 'w');
            fwrite($fpICal, $iCal->getOutput('ics'));
            return $iCalFilePath;

        }
    }
    return false;
}
?>