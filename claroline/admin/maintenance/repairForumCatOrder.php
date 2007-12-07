<?php //$Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// Job of this script  repair order of forums

// Lang files needed :
$cidReq="";$gidReq="";
$langFile = "admin";
$hideCourseOk = TRUE;

// initialisation of global variables and used libraries

require '../../inc/claro_init_global.inc.php';
include($includePath."/lib/admin.lib.inc.php");

//SECURITY CHECK
if (!$is_platformAdmin) treatNotAuthorized();
$is_allowedToAdmin     = $is_platformAdmin;

$tbl_mdb_names 			= claro_sql_get_main_tbl();
$tbl_claro_courses      = $tbl_mdb_names['cours'           ];


$countCoursNotOk =0;
$sqlGetCourses = 'SELECT * from `'.$tbl_claro_courses.'` ';
if ($cmd=='patch' && $_REQUEST['course']=='all' )
$sqlGetCourses  .=' LIMIT 25 ';
$logWork = "Bulk Repair task detected, limit to 25 courses. Relaunch it as need";
$result = claro_sql_query($sqlGetCourses);
$course_list = claro_sql_query_fetch_all($sqlGetCourses);
foreach($course_list as $course_data)
{
	$logWorkThisCourse = '<div><strong>'.$course_data['fake_code'].'</strong> '.$course_data['intitule'].'<br>';
	$logWorkThisCourse .= 'Code : '.$course_data['code'].' Db:'.$course_data['dbName'].'<br>';
	$logWorkThisCourse .= 'Start Job<br>';
	$sqlJob = ' SELECT  count( cat_id ) nbCat_with_this_order_place, 
						   	  cat_order
				FROM `'.$course_data['dbName'].$dbGlu.'bb_categories`  
				GROUP BY cat_order
				HAVING nbCat_with_this_order_place >1; ';
	$logWorkThisCourse .= $sqlJob;
	$res = claro_sql_query($sqlJob);
	if (mysql_num_rows($res)) 
	{
		while ($badforums = mysql_fetch_array($res)) 
		{
			if (is_array($badforums))
			{
				if  (	$_REQUEST['cmd']=='patch' 
						&&  (	$_REQUEST['course']=='all'
								||
								$_REQUEST['course']==$course_data['code']
							)
					)
				{

					$sqlRepair = 'UPDATE `'.$course_data['dbName'].$dbGlu.'bb_categories` 
									SET `cat_order`=`cat_id` where `cat_order` = "'.$badforums['cat_order'].'" ';
					$logWorkThisCourse .= '<br><strong>Run repair</strong>';
					claro_sql_query($sqlRepair);
					if ($badforums['cat_order']=="")
					{
						$sqlRepair = 'UPDATE `'.$course_data['dbName'].$dbGlu.'bb_categories` 
										SET `cat_order`=`cat_id` where `cat_order` is NULL ';
						$logWorkThisCourse .= '<br><strong>Run repair Null</strong>';
						claro_sql_query($sqlRepair);
					}

					$countCoursNotOk--;

				}
				$logWorkThisCourse .= '<br>Qty :'.$badforums['nbCat_with_this_order_place'];
				$logWorkThisCourse .= ' pos :'.$badforums['forum_order'];
				$logWorkThisCourse .= ' <a href="'.$_SERVER['PHPSELF'].'?cmd=patch&course='.$course_data['code'].'">Repair</a>';
				$thisCourseOk =  FALSE;
				$countCoursNotOk++;
			}
			else
			{
				$logWorkThisCourse .= '<strong>OK</strong>';
				$thisCourseOk =  TRUE;
				echo "pwet";
			}
			
		}

	}
	else
	{
		$thisCourseOk =  TRUE;
	}
	mysql_free_result($res);
	$logWorkThisCourse .= '</div><HR>';
	if (!$hideCourseOk||!$thisCourseOk)
	{
		$logWork .= $logWorkThisCourse;
	}
}

$linkToRunPatch = '
<a href="'.$_SERVER['PHPSELF'].'?cmd=patch&course=all">Patch All</a>';
$nameTools ="repair forums (order of categories)";
$noQUERY_STRING=true;
include($includePath."/claro_init_header.inc.php");
echo $linkToRunPatch;

if ($countCoursNotOk>0)
echo '<br>
<h1>'.$countCoursNotOk.' courses to repair</h1><br>
';
echo $logWork;
echo $linkToRunPatch;
include($includePath."/claro_init_footer.inc.php");
?>
