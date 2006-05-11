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
 * @package CLCAL
 * @package CLRSS
 *
 * @author Claro Team <cvs@claroline.net>
 */

function CLCAL_write_rss($context)
{

    if (is_array($context) && count($context)>0)
    {
        $courseId = (array_key_exists(CLARO_CONTEXT_COURSE,$context)) ? $context[CLARO_CONTEXT_COURSE] : $GLOBALS['_cid'];
    }

    $courseData = claro_get_course_data($courseId);

    require_once dirname(__FILE__) . '/../lib/agenda.lib.php';
    $eventList    = agenda_get_item_list($context, 'ASC');
    $toolNameList = claro_get_tool_name_list();
    $eventRssList = array();
    foreach ($eventList as $id => $eventItem)
    {
        if($eventItem['visibility'] == 'SHOW')
        {
            if (empty($eventItem['title'])) $eventItem['title'] = $id;
            $eventRssList[] = array( 'title'       => $eventItem['title']
            ,                        'category'    => trim($toolNameList[str_pad('CLCAL',8,'_')])
            ,                        'guid'        => get_conf('clarolineRepositoryWeb') . 'calendar/agenda.php?cidReq=' . $courseId . '&amp;l#event' . $eventItem['id']
            ,                        'link'        => get_conf('clarolineRepositoryWeb') . 'calendar/agenda.php?cidReq=' . $courseId . '&amp;l#event' . $eventItem['id']
            ,                        'description' => trim(str_replace('<!-- content: html -->','',$eventItem['content']))
            ,                        'pubDate'     => date('r', stripslashes(strtotime($eventItem['day'] . ' ' . $eventItem['hour'] )))
        //, 'author' => $_course['email']
            );
        }
    }

    return $eventRssList;
}

?>