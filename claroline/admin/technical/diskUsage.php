<?php // $Id$
/**
 * Claroline
 *
 * This  tool compute the disk Usage of each course.
 * @version 1.9 $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author  Christophe Gesché <moosh@claroline.net>
 * @package maintenance
 *
 */

require_once '../../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

require_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
require_once get_path('incRepositorySys') . '/lib/form.lib.php';

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_course = $tbl_mdb_names['course'];

$nameTools = get_lang('Disk usage');
$byteUnits = get_locale('byteUnits');

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Technical Tools'), get_path('rootAdminWeb').'technical/index.php' );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$disp_form = true;
if (get_conf('singleDbEnabled') == TRUE ) $msg['warning'][] = get_lang('Cannot compute db size of a course in singleDBMode');

if (isset( $_REQUEST['disp_claro'])) $disp_claro = $_REQUEST['disp_claro'];
else                                 $disp_claro =  false;

if (isset( $_REQUEST['disp_selCrs'])) $disp_selCrs = $_REQUEST['disp_selCrs'];
else                                  $disp_selCrs =  false;

if (isset( $_REQUEST['disp_allcrs'])) $disp_allcrs = $_REQUEST['disp_allcrs'];
else                                  $disp_allcrs =  false;




if (isset( $_REQUEST['disp_garbage']))
{
    $disp_garbage =  $_REQUEST['disp_garbage'];
    $garbagedisk_usage = disk_usage(get_path('garbageRepositorySys'),'','m');
}
else
{
    $disp_garbage =  false;
}

$coursesToCheck=array();
if (isset( $_REQUEST['coursesToCheck']))
{
        $course_list = fetchtCourseList();

        foreach($_REQUEST['coursesToCheck'] as $chkCourse)
        {
            reset($course_list);
            foreach($course_list as $existingcourse)
            if ($chkCourse == $existingcourse['sysCode']) $coursesToCheck[]= $chkCourse;
        }
        if (count($coursesToCheck)<1) $coursesToCheck =  false;
}
else
{
    $coursesToCheck =  false;
}

if ($disp_form)
{
    $course_list = fetchtCourseList();


    if (is_array($course_list))
    {
        $coursesToCheck_list['** ' . get_lang('All') . ' ** !!! ' . get_lang('high resources')]= ' all ';
        foreach ($course_list as $courseSel)
        {
            $coursesToCheck_list[$courseSel['officialCode']] = $courseSel['sysCode'] ;
        }
    }
}


$msg['info'][] = get_lang('Course Repository') . ' : ' . get_path('coursesRepositorySys');
$msg['info'][] = get_lang('Mysql Repository') . ' : ' . (get_conf('mysqlRepositorySys',false) ? get_conf('mysqlRepositorySys') : '!!! ' . get_lang('Missing'));


//OUTPUT
$out = '';

$out .= claro_html_tool_title($nameTools)
.    claro_html_msg_list($msg)
;


if ($disp_form)
{
    $out .= '<ul>';
    if ($disp_claro )
        $out .= '<li>'
        .    'Claroline : '
        .    sprintf('%01.2f', disk_usage(get_path('clarolineRepositorySys'),'','m')) . ' ' . $byteUnits[2]
        .    '</li>'
        ;

    if ($disp_allcrs)
    {
        $diskUsage = sprintf('%01.2f', disk_usage(get_path('coursesRepositorySys'), get_path('mysqlRepositorySys'), 'm')) . ' ' . $byteUnits[2];
        $out .= '<li>'
        .    get_lang('Courses : %disk_usage (perhaps with other directories)',
             array ( '%disk_usage' => $diskUsage ) ) . '</li>' ;
    }

    $out .= '</ul>
    <hr />
    <form  method="post" action="' . $_SERVER['PHP_SELF'] .'">
    <input type="checkbox" id="disp_claro" name="disp_claro" value="true"  />
    <label for="disp_claro"> ' . get_lang('size of claroline scripts') . ' </label>
    <br />
    <input type="checkbox" id="disp_allcrs" name="disp_allcrs" value="true"  />
    <label for="disp_allcrs">' . get_lang('!!!! size of course repository (include claroline and garbage in old systems)') . '</label>
    <br />
    
    <input type="checkbox" name="disp_selCrs" id="disp_selCrs" value="true"  />
    <label for="disp_selCrs">' . get_lang('size of selected courses') . '</label><br />'
    ;
    
    $out .= claro_html_form_select( 'coursesToCheck[]'
                               , $coursesToCheck_list
                               , ''
                               , array( 'multiple'=>'multiple'
                                      , 'size'=>'' ))
                               ;
    $out .= '<input type="submit" />
    </form>
    <hr />'
    ;

}


if ($disp_selCrs && $coursesToCheck)
{
    $out .= '<ol>';
    $sqlListCourses = "
                       SELECT administrativeNumber AS code,
                              directory            AS dir,
                              dbName               AS db,
                              diskQuota
                         FROM `" . $tbl_course . "` ";
    if($coursesToCheck[0]==" all ")    $sqlListCourses .= " order by dbName";
    elseif (is_array($coursesToCheck)) $sqlListCourses .= " where code in ('".implode( "','", $coursesToCheck )."') order by dbName";
    else unset($sqlListCourses);

    if (isset($sqlListCourses))
    {
        $resCourses= claro_sql_query($sqlListCourses);
        while (($course = mysql_fetch_array($resCourses,MYSQL_ASSOC)))
        {


            $duFiles = disk_usage(get_path('coursesRepositorySys') . $course['dir'] . '/','','k');
            if (get_conf('singleDbEnabled') == TRUE ) $duBase=null;
            else                                      $duBase  = get_db_size($course['db'],'k');

            $duTotal = disk_usage(get_path('coursesRepositorySys') . $course['dir'] . '/', get_path('coursesRepositorySys') . $course['db'] . '/' , 'm');
            $out .= '<p>' . get_path('coursesRepositorySys') . $course['dir'] . '/'
            .    ' = '
            .    '<pre>'
            .    var_export( get_path('coursesRepositorySys') . $course['dir'] . '/',1)
            .    '</pre>'
            ;

            $quota   = $course['diskQuota'] * 1;
            $out .= '<li>'
            .    $course['code'] . ' : '
            .    (is_null($course['diskQuota']) ? ' ' . get_lang('No quota') . ' '
                                                : get_lang('Quota') . ' : ' . $course['diskQuota']
                 )
            .    ' ' . $byteUnits[2] . ' | '
            .    sprintf("%01.2f", $duFiles ) . ' ' . $byteUnits[1]
            .    ' + '
            .    sprintf('%01.2f', $duBase  ) . ' ' . $byteUnits[1] . ' = <strong>'
            .    sprintf('%01.2f', $duTotal ) . ' ' . $byteUnits[2] . '</strong>'
            .    (is_null($course['diskQuota']) || ($quota > (int) $duTotal)
                 ? ' ok '
                 : ' <font color="#FF0000">!!!!!!!! '. get_lang('OVER QUOTA') .' !!!!!!</font>'
                 )
            .   '</li>'
            ;
        }
    }

    $out .= '</ol>';
    
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();


function fetchtCourseList()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

    $sqlListCoursesSel = "
        SELECT administrativeNumber AS officialCode,
               code                 AS sysCode
          FROM `" .  $tbl_mdb_names['course'] . "`
      ORDER BY trim(administrativeNumber) ASC
      ";
return claro_sql_query_fetch_all($sqlListCoursesSel);

}

function disk_usage( $dirFiles = '', $dirBase='', $precision='m')
{
    $dirFiles = escapeshellarg( $dirFiles );
    $dirBase = escapeshellarg( $dirBase );
    $precision = escapeshellarg( $precision );
    
    // $precision  -> b Bytes, k Kilobyte, m Megabyte
    switch (PHP_OS)
    {
        case 'Linux' :
            $usedspace = (int)`du -sc$precision $dirFiles`;
            $usedspace += (int)`du -sc$precision $dirBase`;
//            $usedspace += (int) get_db_size($course["db"],k);

            break;
        //case "WIN32" : // no  optimazing found  for  WIN32, use  long version
        //case "WINNT" : // no  optimazing found  for  WINNT, use  long version
        default :
            $usedspace  = claro_get_file_size($dirFiles);
            $usedspace += claro_get_file_size($dirBase);
            switch ($precision)
            {
                case 'm' : $usedspace /= 1024;
                case 'k' : $usedspace /= 1024;
            }

            break;
    }

    return $usedspace;
}

function get_db_size($tdb)
{
    $db = mysql_connect(get_conf('dbHost'), get_conf('dbLogin'), get_conf('dbPass')) or die ("Error connecting to MySQL Server!\n");
    mysql_select_db($tdb, $db);

    $sql_result = "SHOW TABLE STATUS FROM " .$tdb;
    $result = claro_sql_query($sql_result);
    mysql_close($db);

    if($result)
    {
        $size = 0;
        while (($data = mysql_fetch_array($result)))
        {
            $size = $size + $data['Data_length'] + $data['Index_length'];
        }
        return $size;
    }
    else
    {
        return false;
    }
}

?>