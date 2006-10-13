<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLANN
 * @subpackage CLRSS
 *
 * @author Claro Team <cvs@claroline.net>
 */

function CLANN_write_rss($context)
{
    if (is_array($context) && count($context)>0)
    {
        $courseId = (array_key_exists(CLARO_CONTEXT_COURSE,$context)) ? $context[CLARO_CONTEXT_COURSE] : $GLOBALS['_cid'];
    }

    require_once dirname(__FILE__) . '/../lib/announcement.lib.php';
    $courseData = claro_get_course_data($courseId);

    $toolNameList = claro_get_tool_name_list();
    $announcementList = announcement_get_item_list($context, 'DESC');

    $rssList = array();
    foreach ($announcementList as $announcementItem)
    {
        if('SHOW' == $announcementItem['visibility'])
        {
            $rssList[] = array( 'title'       => trim($announcementItem['title'])
            ,                   'category'    => trim($toolNameList['CLANN'])
            ,                   'guid'        => get_conf('rootWeb') .'claroline/' . 'announcements/announcements.php?cidReq=' . $courseId . '&l#ann'.$announcementItem['id']
            ,                   'link'        => get_conf('rootWeb') .'claroline/' . 'announcements/announcements.php?cidReq=' . $courseId . '&l#ann'.$announcementItem['id']
            ,                   'description' => trim(str_replace('<!-- content: html -->','',$announcementItem['content']))
            ,                   'pubDate'     => date('r', stripslashes(strtotime($announcementItem['time'])))
          //,                   'author'      => $_course['email']
            );
        }
    }
    return $rssList;
}



?>
