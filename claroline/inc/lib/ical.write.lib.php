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


/**
 * This lib use
 * * cache lite
 * * icalendar/class.iCal.inc.php
 *
 */

require_once dirname(__FILE__) . '/icalendar/class.iCal.inc.php';

function buildICal($context, $calType='ics')
{
    if (is_array($context) && count($context) > 0)
    {
        $iCalRepositorySys =  get_conf('rootSys') . get_conf('iCalRepository','iCal/');


        if (!file_exists($iCalRepositorySys))
        {
            require_once dirname(__FILE__) . '/fileManage.lib.php';
            claro_mkdir($iCalRepositorySys, CLARO_FILE_PERMISSIONS, true);
        }

        $iCal = (object) new iCal('', 0, $iCalRepositorySys ); // (ProgrammID, Method (1 = Publish | 0 = Request), Download Directory)

        $toolLabelList = ical_get_tool_compatible_list();

        foreach ($toolLabelList as $toolLabel)
        {
            $icalToolLibPath = get_module_path($toolLabel) . '/connector/ical.write.cnr.php';
            $icalToolFuncName =  $toolLabel . '_write_ical';
            if ( file_exists($icalToolLibPath)
            )
            {
                include_once $icalToolLibPath;
                if (function_exists($icalToolFuncName)) $iCal = call_user_func($icalToolFuncName, $iCal, $context );
            }
        }


        $iCalFilePath = $iCalRepositorySys . '/' ;
        if (array_key_exists(CLARO_CONTEXT_COURSE,$context)) $iCalFilePath .= $context[CLARO_CONTEXT_COURSE] . '.';
        if (array_key_exists(CLARO_CONTEXT_GROUP,$context)) $iCalFilePath .= 'g'.$context[CLARO_CONTEXT_GROUP] . '.';


        if (get_conf('iCalGenStandard', true))
        {
            $stdICalFilePath = $iCalFilePath . 'ics';
            $fpICal = fopen($stdICalFilePath, 'w');
            fwrite($fpICal, $iCal->getOutput('ics'));
        }

        if (get_conf('iCalGenXml', true))
        {
            $xmlICalFilePath = $iCalFilePath . 'xml';
            $fpICal = fopen($xmlICalFilePath, 'w');
            fwrite($fpICal, $iCal->getOutput('xcs'));
        }

        if (get_conf('iCalGenRdf', false))
        {
            $rdfICalFilePath = $iCalFilePath . 'rdf';
            $fpICal = fopen($rdfICalFilePath, 'w');
            fwrite($fpICal, $iCal->getOutput('rdf'));
        }


        switch ($calType)
        {
            case 'xcs' :
                $iCalFilePath .= 'xml';
                return $iCalFilePath;
                break;
            case 'rdf' :
                $iCalFilePath .= 'rss';
                return $iCalFilePath;
                break;
            default :
                $iCalFilePath .= 'ics';
                return $iCalFilePath;
                break;
        }


    }
    return false;
}


/**
 * Build the list of claro label of tool having a iCal creator.
 *
 * @return array of claro_label
 *
 * This function use 2 level of cache.
 * - memory Cache to compute only one time the list by script execution
 * - if enabled : use cache lite
 */
function ical_get_tool_compatible_list()
{
    static $iCalToolList = null;
    if (is_null($iCalToolList))
    {
        $iCalToolList = array();
        if(get_conf('icalUseCache',true))
        {
            include_once dirname(__FILE__) . '/pear/Lite.php';

            // Cache_lite setting & init
            $cache_options = array(
            'cacheDir' => get_conf('rootSys') . 'cache/ical/',
            'lifeTime' => get_conf('cache_lifeTime', get_conf('iCalCacheLifeTime'), 600000), // 600.000 little less than a week
            'automaticCleaningFactor' => 500,
            );
            if (get_conf('CLARO_DEBUG_MODE',false) )
            {
                $cache_options ['pearErrorMode'] = CACHE_LITE_ERROR_DIE;
                $cache_options ['lifeTime'] = 60;
                $cache_options ['automaticCleaningFactor'] = 1;
            }

            if (! file_exists($cache_options['cacheDir']) )
            {
                include_once dirname(__FILE__) . '/fileManage.lib.php';
                claro_mkdir($cache_options['cacheDir'],CLARO_FILE_PERMISSIONS,true);
            }

            $iCalToolListCache = new Cache_Lite($cache_options);

            if (false === $iCalToolListSerialized = $iCalToolListCache->get('iCal'))
            {
                $toolList = $GLOBALS['_courseToolList'];
                foreach ($toolList as $tool)
                {
                    $toolLabel = trim($tool['label'],'_');
                    $icalToolLibPath = get_module_path($toolLabel) . '/connector/ical.write.cnr.php';
                    $icalToolFuncName =  $toolLabel . '_write_ical';
                    if ( file_exists($icalToolLibPath)
                    )
                    {
                        include_once $icalToolLibPath;
                        if (function_exists($icalToolFuncName))
                        {
                            $iCalToolList[] = $toolLabel;
                        }
                    }
                }
                $iCalToolListSerialized = serialize($iCalToolList);
                $iCalToolListCache->save($iCalToolListSerialized, 'iCal');
            }
            else
            $iCalToolList = unserialize($iCalToolListSerialized);

        }
        else
        $iCalToolList = array();

    } // if is_null $iCalToolList -> if not use static

    return $iCalToolList;
}
?>