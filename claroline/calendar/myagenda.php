<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.5.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: Eric Remy <eremy@rmwc.edu>
//			Toon Van Hoecke <Toon.VanHoecke@UGent.be>
//----------------------------------------------------------------------

/*
 *	This file generates a general agenda of all items of the courses
 *	the user is registered for.
 *
 *	Based on the master-calendar code of Eric Remy (6 Oct 2003)
 *	adapted by Toon Van Hoecke (Dec 2003) and Hugues Peeters (March 2004)
 */


$langFile='calendar';

$cidReset = true;

require '../inc/claro_init_global.inc.php';

include($includePath."/lib/text.lib.php");

$nameTools = $langMyAgenda;

if(!empty($_REQUEST['coursePath']))
{
	$interbredcrump[]=array('url' => $rootWeb.$_REQUEST['coursePath'].'/index.php',
                            'name' => $_REQUEST['courseCode']);
}

$tbl_mdb_names       = claro_sql_get_main_tbl();

$tbl_course          = $tbl_mdb_names['course'];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];


$htmlHeadXtra[] =
"<style type=text/css>
<!--
.workingWeek {}
.weekEnd {background-color: #E3E3E3;}
.today {background-color: #FFCCCC;}
-->
</style>";

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($nameTools);

if (isset($_uid))
{
    $sql = "SELECT cours.code sysCode, cours.fake_code officialCode,
                   cours.intitule title, cours.titulaires t, 
                   cours.dbName db, cours.directory dir
	        FROM    `".$tbl_course."`     cours,
				    `".$tbl_rel_course_user."` cours_user
	        WHERE cours.code         = cours_user.code_cours
	        AND   cours_user.user_id = '".$_uid."'";

    $userCourseList = claro_sql_query_fetch_all($sql);

	$year  = $_REQUEST['year' ];
	$month = $_REQUEST['month'];

	if ( ($year == NULL) && ($month == NULL) )
	{
		$today = getdate();
		$year  = $today['year'];
		$month = $today['mon' ];
	}

	$agendaItemList = get_agenda_items($userCourseList, $month, $year);

	$monthName   = $langMonthNames['long'][$month-1];

	disp_monthly_calendar($agendaItemList, $month, $year, $langDay_of_weekNames['long'], $monthName, $langToday);
}

include($includePath."/claro_init_footer.inc.php");

//////////////////////////////////////////////////////////////////////////////



function get_agenda_items($userCourseList, $month, $year)
{
	global $courseTablePrefix, $dbGlu;

	$items = array();

	// get agenda-items for every course

    foreach( $userCourseList as $thisCourse)
    {
//	    $courseAgendaTable = $courseTablePrefix. $thisCourse['db'].$dbGlu."calendar_event";
		$tbl_cdb_names = claro_sql_get_course_tbl($courseTablePrefix. $thisCourse['db'].$dbGlu);
		$courseAgendaTable          = $tbl_cdb_names['calendar_event'];

        $sql = "SELECT id, titre title, day, hour, lasting 
                FROM `".$courseAgendaTable."`
                WHERE month(day) = '".$month."' 
                AND   year(day)  ='".$year."'" ;

	    $courseEventList = claro_sql_query_fetch_all($sql);

        foreach($courseEventList as $thisEvent )
 		if (!(trim(strip_tags($thisEvent["title"]))==""))
		{
            $eventDate = explode('-', $thisEvent['day']);
            $day       = intval($eventDate[2]);
            $eventTime = explode(':', $thisEvent['hour']);
            $time      = $eventTime[0].':'.$eventTime[1];
            $url       = 'agenda.php?cidReq='.$thisCourse['sysCode'];

            $items[$day][$thisEvent['hour']] .= 
            "<br><small><i>".$time." : </small><br></i> "
            .$thisEvent['title']
            ." - <small><a href=\"".$url."\">".$thisCourse['officialCode']."</a></small>\n";
        }
    }

	// sorting by hour for every day
	$agendaItemList = array();

	while ( list($agendaday, $tmpitems) = each($items))
	{
		sort($tmpitems);

		while ( list($key,$val) = each($tmpitems))
		{
			$agendaItemList[$agendaday].=$val;
		}
	}

	return $agendaItemList;
}

function disp_monthly_calendar($agendaItemList, $month, $year, $weekdaynames, $monthName, $langToday)
{
	global $PHP_SELF;

	//Handle leap year
	$numberofdays = array(0,31,28,31,30,31,30,31,31,30,31,30,31);

	if ( ($year%400 == 0) || ( $year%4 == 0 && $year%100 != 0 ) )
    {
        $numberofdays[2] = 29;
    }

	//Get the first day of the month
	$dayone = getdate(mktime(0,0,0,$month,1,$year));

  	//Start the week on monday
	$startdayofweek = $dayone['wday']<>0 ? ($dayone['wday']-1) : 6;

	$backwardsURL = $PHP_SELF."?coursePath=".$_REQUEST['coursePath']
                   ."&courseCode=".$_REQUEST['courseCode']
                   ."&month=".($month==1 ? 12 : $month-1)
                   ."&year=".($month==1 ? $year-1 : $year);

	$forewardsURL = $PHP_SELF."?coursePath=".$_REQUEST['coursePath']
                   ."&courseCode=".$_REQUEST['courseCode']
                   ."&month=".($month==12 ? 1 : $month+1)
                   ."&year=".($month==12 ? $year+1 : $year);

	echo "<table class=\"claroTable\" width=95%>\n"

  	    ."<tr class=\"superHeader\">\n"
	    ."<th width=13%><center>"
        ."<a href=".$backwardsURL.">&lt;&lt;</a></center>"
        ."</th>\n"
	    ."<th width=65% colspan=\"5\">"
        ."<center>".$monthName.' '.$year."</center>"
        ."</th>\n"
	    ."<th width=13%><center>"
        ."<a href=".$forewardsURL.">&gt;&gt;</center></a>"
        ."</th>\n"
	    ."</tr>\n";

	echo "<tr class=\"headerX\">\n";

	for ( $iterator = 1; $iterator<8; $iterator++)
	{
    	echo  "<th width=13%>".$weekdaynames[$iterator%7]."</th>\n";
    }
	
    echo "</tr>\n";

	$curday = -1;
	
    $today = getdate();

	while ($curday <= $numberofdays[$month])
  	{
  		echo "<tr>\n";

      	for ($iterator = 0; $iterator <7 ; $iterator++)
	  	{
	  		if ( ($curday == -1) && ($iterator == $startdayofweek) )
			{
	    		$curday = 1;
			}

			if ( ($curday > 0) && ($curday <= $numberofdays[$month]) )
			{
		  		if (   ($curday == $today['mday']) 
                    && ($year   == $today['year']) 
                    && ($month  == $today['mon' ]) )
				{
		  			$weekdayType = 'today';
				}
                elseif ( $iterator < 5 )
                {
                	$weekdayType = 'workingWeek';
                }
                else
                {
                    $weekdayType = 'weekEnd';
                }

				$dayheader = "<small>".$curday."</small>";


	      		echo "<td height=\"40\" width=\"12%\" valign=\"top\" class=\"".$weekdayType."\">"
                    .$dayheader
				    .$agendaItemList[$curday]
                    ."</td>\n";

	      		$curday++;
	    	}
	  		else
	    	{
	    		echo "<td width=12%>&nbsp;</td>\n";
	    	}
		}
    	echo "</tr>\n";
    }
  	echo  "</table>";
}
?>