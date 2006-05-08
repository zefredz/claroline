<?php // $Id$
/**
 * CLAROLINE
 *
 * Build an rss file containing agenda and announcement item of a cours
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLRSS
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @see http://www.stervinou.com/projets/rss/
 * @see http://feedvalidator.org/
 * @see http://rss.scripting.com/
 *
 */
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');

define('RSS_FILE_EXT', 'xml');

function build_course_feed($forceBuild= false, $_cid=null)
{
    global $rootSys,
        $siteName ,
        $_course ,
        $coursesRepositoryWeb ,
        $clarolineRepositoryWeb ,
        $administrator_email ,
        $iso639_1_code,
        $charset ;

    require_once 'XML/Serializer.php';
    include dirname(__FILE__) . '/../../../conf/rss.conf.php';
    require_once dirname(__FILE__) . '/../../fileManage.lib.php';
    require_once dirname(__FILE__) . '/../../announcement.lib.php';
    require_once dirname(__FILE__) . '/../../agenda.lib.php';

    $rssRepositoryCache = get_conf('rssRepositoryCache');
    $rssRepositoryCacheSys = $rootSys . $rssRepositoryCache;
    if (!file_exists($rssRepositoryCacheSys) ) claro_mkdir($rssRepositoryCacheSys, CLARO_FILE_PERMISSIONS, true);

    $rssFilePath = $rssRepositoryCacheSys . $_cid . '.xml';

    if ($forceBuild || !file_exists($rssFilePath))
    {


        $options = array(
        'indent'    => '    ',
        'linebreak' => "\n",
        'typeHints' => FALSE,
        'addDecl'   => TRUE,
        'encoding'  => $charset,
        'rootName'  => 'rss',
        'defaultTagName' => 'item',
        'rootAttributes' => array('version' => '2.0')
        );

        $data['channel'] = array(
        'title'          => '[' . $siteName . '] '.$_course['officialCode'],
        'description'    => $_course['name'],
        'link'           => $coursesRepositoryWeb.$_course['path'],
        'generator'      => 'Claroline-PEARSerializer',
        'webMaster'      => $administrator_email,
        'managingEditor' => $_course['email'],
        'language'       => $iso639_1_code,
        'docs'           => 'http://blogs.law.harvard.edu/tech/rss',
        'pubDate'        => date("r",time())
        );

        $eventRssList = agenda_get_rss_item_list();
        $announcementRssList = announcement_get_rss_item_list();
        $data['channel'] = array_merge($data['channel'], $eventRssList, $announcementRssList );

        $serializer = new XML_Serializer($options);

        if ($serializer->serialize($data))
        {
            $fprss = fopen($rssFilePath, 'w');
            fwrite($fprss, $serializer->getSerializedData());
            fclose($fprss);
        }

    }
    return $rssFilePath;
}

function agenda_get_rss_item_list( $course_id=NULL)
{
    GLOBAL $clarolineRepositoryWeb, $_cid, $_course;
    $eventList    = agenda_get_item_list('ASC', $course_id);
    $toolNameList = claro_get_tool_name_list();
    $eventRssList = array();
    foreach ($eventList as $eventItem)
    {
        if($eventItem['visibility'] == 'SHOW')
        {
            $eventRssList[] = array( 'title'       => $eventItem['title']
            ,                        'category'    => trim($toolNameList[str_pad('CLCAL',8,'_')])
            ,                        'guid'        => $clarolineRepositoryWeb . 'calendar/agenda.php?cidReq=' . $_cid.'&amp;l#event' . $eventItem['id']
            ,                        'link'        => $clarolineRepositoryWeb . 'calendar/agenda.php?cidReq=' . $_cid.'&amp;l#event' . $eventItem['id']
            ,                        'description' => trim(str_replace('<!-- content: html -->','',$eventItem['content']))
            ,                        'pubDate'     => date('r', stripslashes(strtotime($eventItem['day'].' '.$eventItem['hour'] )))
        //, 'author' => $_course['email']
            );
        }
    }
    return $eventRssList;
}

/**
 *
 */
function announcement_get_rss_item_list( $course_id=NULL)
{
    GLOBAL $clarolineRepositoryWeb, $_cid, $_course;
    $announcementList = announcement_get_item_list('DESC', $course_id);
    $toolNameList = claro_get_tool_name_list();
    $rssList = array();
    foreach ($announcementList as $announcementItem)
    {
        if('SHOW' == $announcementItem['visibility'])
        {
            $rssList[] = array( 'title'       => trim($announcementItem['title'])
            ,                   'category'    => trim($toolNameList[str_pad('CLANN',8,'_')])
            ,                   'guid'        => $clarolineRepositoryWeb.'announcements/announcements.php?cidReq='.$_cid.'&l#ann'.$announcementItem['id']
            ,                   'link'        => $clarolineRepositoryWeb.'announcements/announcements.php?cidReq='.$_cid.'&l#ann'.$announcementItem['id']
            ,                   'description' => trim(str_replace('<!-- content: html -->','',$announcementItem['content']))
            ,                   'pubDate'     => date('r', stripslashes(strtotime($announcementItem['time'])))
          //,                   'author'      => $_course['email']
            );
        }
    }
    return $rssList;
}

?>
