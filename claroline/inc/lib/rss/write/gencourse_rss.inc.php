<?php // $Id$
/**
 * CLAROLINE 
 * 
 * Build an rss file containing agenda and announcement item of a cours
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
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
if ((bool) stristr($_SERVER['PHP_SELF'],'course_rss.pear.inc.php'))
die("---");

define('RSS_FILE_EXT', 'xml');

require_once 'XML/Serializer.php';

include_once $includePath . '/conf/rss.conf.inc.php'; 
require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/CLANN.lib.inc.php';
require_once $includePath . '/lib/CLCAL.lib.inc.php';
if (!isset($rssRepository))
{
    $rssRepositorySys = $rootSys . 'rss/';
    echo '<Hr><H1>Message for Devel </H1>goto <a href="'.$clarolineRepositoryWeb.'/admin/tool/config_edit.php?config_code=CLRSS">config</a> to build RSS conf file.<HR>'; 
}
else 
{
    $rssRepositorySys = $rootSys . $rssRepository;
}

claro_mkdir($rssRepositorySys);


$toolNameList = claro_get_tool_name_list();

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
            'title'          => '['.$siteName.'] '.$_course['officialCode'],
            'description'    => $_course['name'],
            'link'           => $coursesRepositoryWeb.$_course['path'],
            'generator'      => 'Claroline-PEARSerializer',
            'webMaster'      => $administrator_email,
            'managingEditor' => $_course['email'],
            'language'       => $iso639_1_code,
            'docs'           => 'http://blogs.law.harvard.edu/tech/rss',
            'pubDate'        => date("r")
    );

$eventRssList = agenda_get_rss_item_list();
$announcementRssList = announcement_get_rss_item_list();
$data['channel'] = array_merge($data['channel'], $eventRssList, $announcementRssList );

$serializer = new XML_Serializer($options);

if ($serializer->serialize($data)) 
{
    $fprss = fopen($rssRepositorySys . $_cid . '.xml','w');
    fwrite($fprss,$serializer->getSerializedData());
    fclose($fprss);
}

function agenda_get_rss_item_list( $course_id=NULL)
{
    GLOBAL $platform_id, $clarolineRepositoryWeb, $_cid, $_course;
    $eventList        = agenda_get_item_list('ASC', $course_id);
    foreach ($eventList as $eventItem) 
    {   
        $eventRssList[] = array( 'title' => $eventItem['title']
                          , 'category' => $toolNameList[str_pad('CLCAL',8,'_')]
                          , 'guid' => $clarolineRepositoryWeb . 'calendar/agenda.php?cidReq=' . $_cid.'&amp;l#event' . $eventItem['id']
                          , 'link' => $clarolineRepositoryWeb . 'calendar/agenda.php?cidReq=' . $_cid.'&amp;l#event' . $eventItem['id']
                          , 'description' => str_replace('<!-- content: html -->','',$eventItem['content'])
                          , 'pubDate' => date('r', stripslashes(strtotime($eventItem['day'].' '.$eventItem['hour'] )))
                          //, 'author' => $_course['email']
                          );
    }
    return $eventRssList;
}

/**
 *
 */
function announcement_get_rss_item_list( $course_id=NULL)
{
    GLOBAL $platform_id, $clarolineRepositoryWeb, $_cid, $_course;
    $announcementList = announcement_get_item_list('DESC', $course_id);
    foreach ($announcementList as $announcementItem) 
    {   
        $rssList[] = array( 'title' => $announcementItem['title']
                      , 'category' => $item->category = $toolNameList[str_pad('CLANN',8,'_')]
                      , 'guid' => $clarolineRepositoryWeb.'announcements/announcements.php?cidReq='.$_cid.'&l#ann'.$announcementItem['id']
                      , 'link' => $clarolineRepositoryWeb.'announcements/announcements.php?cidReq='.$_cid.'&l#ann'.$announcementItem['id']
                      , 'description' => str_replace('<!-- content: html -->','',$announcementItem['content'])
                      , 'pubDate' => date("r", stripslashes(strtotime($announcementItem['time'])))
                      //, 'author' => $_course['email']
                        );
    }
    return $rssList;
}

?>