<?php // $Id$
/**
 * Claroline
 *
 * This  tool compute the disk Usage of each course.
 * @version 1.8 $Revision$
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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

$nameTools = get_lang('Disk Usage');
$byteUnits = get_locale('byteUnits');

$interbredcrump[]= array ( 'url' => get_path('rootAdminWeb'), 'name' => get_lang('Administration'));
$interbredcrump[]= array ( 'url' => 'index.php'  , 'name' => get_lang('Technical Tools'));

$disp_form = true;

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

if (isset( $_REQUEST['coursesToCheck'])) $coursesToCheck =  $_REQUEST['coursesToCheck'];
else                                     $coursesToCheck =  false;

if ($disp_form)
{

    $sqlListCoursesSel = "SELECT fake_code officialCode, code sysCode FROM `" . $tbl_course . "` order by trim(fake_code) ASC";
    $course_list = claro_sql_query_fetch_all($sqlListCoursesSel);

    if (is_array($course_list))
    {
        $coursesToCheck_list['** ' . get_lang('All') . ' ** !!! ' . get_lang('high resources')]= ' all ';
        foreach ($course_list as $courseSel)
        {
            $coursesToCheck_list[$courseSel['officialCode']] = $courseSel['sysCode'] ;
        }
    }
}


//OUTPUT
include get_path('incRepositorySys') . '/claro_init_header.inc.php' ;

echo claro_html_tool_title($nameTools);

echo get_lang('Course Repository') . ' : ' . get_path('coursesRepositorySys') . '<br />' . get_lang('Mysql Repository') . ' : ' . (get_conf('mysqlRepositorySys',false) ? get_conf('mysqlRepositorySys') : '!!! ' . get_lang('Missing')) . '<br />';



if ($disp_form)
{
    echo '<ul>';
if ($disp_claro )
    echo '<li>'
    .    'Claroline : '
    .    sprintf('%01.2f', disk_usage(get_path('clarolineRepositorySys'),'','m')) . ' ' . $byteUnits[2]
    .    '</li>'
    ;

if ($disp_allcrs)
{
    $diskUsage = sprintf('%01.2f', disk_usage(get_path('coursesRepositorySys'), get_path('mysqlRepositorySys'), 'm')) . ' ' . $byteUnits[2];
    echo '<li>'
    .    get_lang('Courses : %disk_usage (perhaps with others directory)',
         array ( '%disk_usage' => $diskUsage ) ) . '</li>' ;
}
?>
<li>
<hr />
<form  method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="checkbox" id="disp_claro" name="disp_claro" value="true"  />
<label for="disp_claro"><?php echo ' ' . get_lang('size of claroline scripts') ?></label>
<br />
<input type="checkbox" id="disp_allcrs" name="disp_allcrs" value="true"  />
<label for="disp_allcrs"><?php echo get_lang('!!!! size of course repository (include claroline and garbage in old systems)') ?></label>
<br />

<input type="checkbox" name="disp_selCrs" id="disp_selCrs" value="true"  />
<label for="disp_selCrs"><?php echo get_lang('size of selected courses') ?></label><br />

<?php
echo claro_html_form_select( 'coursesToCheck[]'
                           , $coursesToCheck_list
                           , ''
                           , array( 'multiple'=>'multiple'
                                  , 'size'=>'' ))
                           ; ?>
<input type="submit" />
</form>
<hr />
</li>
<?php
}


if ($disp_selCrs && $coursesToCheck)
{
    echo '<li><ol>';
    $sqlListCourses = "SELECT fake_code code,
                      directory dir,
                      dbName db,
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
            $duBase  = get_db_size($course["db"],'k');

            $duTotal = disk_usage(get_path('coursesRepositorySys') . $course['dir'] . '/', get_path('coursesRepositorySys') . $course['db'] . '/' , 'm');
            echo '<p>' . get_path('coursesRepositorySys') . $course['dir'] . '/'
            .    ' = '
            .    '<pre>'
            .    var_export( get_path('coursesRepositorySys') . $course['dir'] . '/',1)
            .    '</pre>'
            ;

            $quota   = $course['diskQuota'] * 1;
            echo '<li>'
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

?>
        </ol>
    </li>
<?php
}
?>
</ul>

<?php

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';



function disk_usage( $dirFiles = '', $dirBase='', $precision='m')
{
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