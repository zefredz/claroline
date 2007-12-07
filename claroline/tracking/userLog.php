<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Sebastien Piraux  <piraux_seb@hotmail.com>
      +----------------------------------------------------------------------+
 */
 
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"../user/user.php", "name"=> $langUsers);

if( !empty($_REQUEST['uInfo']) )
	$interbredcrump[]= array ("url"=>"../user/userInfo.php?uInfo=".$_REQUEST['uInfo'], "name"=> $langUser);

$nameTools = $langStatistics;

/*
 * DB tables definition
 */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_track_e_login           = $tbl_mdb_names['track_e_login'    ];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'    ];
$tbl_group_team              = $tbl_cdb_names['group_team'             ];
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];
$tbl_quiz_test               = $tbl_cdb_names['quiz_test'              ];
$tbl_wrk_assignment          = $tbl_cdb_names['wrk_assignment'         ];
$tbl_wrk_submission          = $tbl_cdb_names['wrk_submission'         ];    
$tbl_track_e_downloads       = $tbl_cdb_names['track_e_downloads'      ];
$tbl_track_e_exercises       = $tbl_cdb_names['track_e_exercices'      ];
$tbl_track_e_uploads         = $tbl_cdb_names['track_e_uploads'        ];
$tbl_bb_topics               = $tbl_cdb_names['bb_topics'                ];
$tbl_bb_posts                = $tbl_cdb_names['bb_posts'                ];


// for learning paths section 
// those vars need to be name like this $TABLE* be cause they are used 
// in get_learnPath_progress function
$TABLELEARNPATH         = $tbl_lp_learnPath;
$TABLEMODULE            = $tbl_lp_module;
$TABLELEARNPATHMODULE   = $tbl_lp_rel_learnPath_module;
$TABLEASSET             = $tbl_lp_asset;
$TABLEUSERMODULEPROGRESS= $tbl_lp_user_module_progress;


include($includePath."/lib/statsUtils.lib.inc.php");
include($includePath."/lib/pager.lib.php");


$is_allowedToTrack = $is_groupTutor; // allowed to track only user of one group
if (isset($_REQUEST['uInfo']) && isset($_uid)) $is_allowedToTrack = $is_allowedToTrack || ($_REQUEST['uInfo'] == $_uid);
$is_allowedToTrackEverybodyInCourse = $is_courseAdmin; // allowed to track all student in course


include($includePath."/claro_init_header.inc.php");

$toolTitle['mainTitle'] = $nameTools;
$toolTitle['subTitle'] = $langStatsOfUser;
echo claro_disp_tool_title($toolTitle);

if( ( $is_allowedToTrack || $is_allowedToTrackEverybodyInCourse ) && $is_trackingEnabled )
{
    if( empty($_REQUEST['uInfo']) )
    {
        /***************************************************************************
         *        Display list of user of this group
         ***************************************************************************/
        echo '<h4>'.$langListStudents.'</h4>'."\n";

        $userPerPage = 50; // number of student per page


        if( $is_allowedToTrackEverybodyInCourse )
        {
            // list of users in this course
            $sql = "SELECT `u`.`user_id`, `u`.`prenom`,`u`.`nom`
                        FROM `".$tbl_rel_course_user."` cu , `".$tbl_user."` u 
                        WHERE `cu`.`user_id` = `u`.`user_id`
                            AND `cu`.`code_cours` = '". addslashes($_cid)."'";
        }
        else
        {
            // list of users of this group
            $sql = "SELECT `u`.`user_id`, `u`.`prenom`,`u`.`nom`
                        FROM `".$tbl_group_rel_team_user."` gu , `".$tbl_user."` u 
                        WHERE `gu`.`user` = `u`.`user_id`
                            AND `gu`.`team` = '". (int)$_gid."'";
        }

        /*----------------------------------------------------------------------
		   Pager
		  ----------------------------------------------------------------------*/
		if ( empty($_REQUEST['offset']) )	$offset = "0";
		else 								$offset = $_REQUEST['offset'];
		
		$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
		$userList = $myPager->get_result_list();

		$myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

        /*----------------------------------------------------------------------
		   Display user list
		  ----------------------------------------------------------------------*/
		echo '<table class="claroTable" width="100%" cellpadding="2" cellspacing="1" border="0">'."\n"
            .'<tr class="headerX" align="center" valign="top">'."\n"
            .'<th align="left">'.$langUserName.'</th>'."\n"
            .'</tr>'."\n";

        foreach( $userList as $thisUser )
        {
            echo '<tr valign="top" align="center">'."\n"
				.'<td align="left">'
				.'<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$thisUser['user_id'].'">'
				.$thisUser['nom'].' '.$thisUser['prenom']
				.'</a>'
				.'</td>'."\n";
        }
        echo '</table>'."\n";

    }
    else // if $_REQUEST['uInfo'] is set
    {
        if( isset($_REQUEST['view']))   $view = $_REQUEST['view'];
		else							$view = "0000000";
        /***************************************************************************
         *              
         *        Informations about student uInfo
         *
         ***************************************************************************/
        // these checks exists for security reasons, neither a prof nor a tutor can see statistics of an user from 
        // another course, or group
        //if( $is_allowedToTrackEverybodyInCourse ) 
        if( $is_allowedToTrackEverybodyInCourse || ($_REQUEST['uInfo'] == $_uid) )
        {
            // check if user is in this course
            $sql = "SELECT `u`.`nom` AS `lastname`,`u`.`prenom` AS `firstname`, `u`.`email`
                        FROM `".$tbl_rel_course_user."` as `cu` , `".$tbl_user."` as `u`
                        WHERE `cu`.`user_id` = `u`.`user_id`
                            AND `cu`.`code_cours` = '". addslashes($_cid) ."'
                            AND `u`.`user_id` = '". (int)$_REQUEST['uInfo']."'";
        }
        else
        {
            // check if user is in the group of this tutor
            $sql = "SELECT `u`.`nom` AS `lastname`,`u`.`prenom` AS `firstname`, `u`.`email`
                        FROM `".$tbl_group_rel_team_user."` as `gu` , `".$tbl_user."` as `u`
                        WHERE `gu`.`user` = `u`.`user_id`
                            AND `gu`.`team` = '". (int)$_gid."'
                            AND `u`.`user_id` = '". (int)$_REQUEST['uInfo']."'";
        }
        $results = claro_sql_query_fetch_all($sql);
        if( !empty($results) && is_array($results) )
        {
            $trackedUser = $results[0];

            echo '<p>'."\n"
                .'<ul>'."\n"
                .'<li>'.$langLastName.' : '.$trackedUser['lastname'].'</li>'."\n"
                .'<li>'.$langFirstName.' : '.$trackedUser['firstname'].'</li>'."\n"
                .'<li>'.$langEmail.' : ';
			if( empty($trackedUser['email']) )	echo $langNoEmail;
			else 								echo $trackedUser['email'];

			echo '</li>'."\n"
                .'</ul>'."\n"
                .'</p>'."\n";
            
            // in $view, a 1 in X posof the $view string means that the 'category' number X
            // will be show, 0 means don't show
            echo "\n".'<small>'."\n"
                .'[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&amp;view=1111111">'.$langShowAll.'</a>]&nbsp;'."\n"
                .'[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&amp;view=0000000">'.$langShowNone.'</a>]'."\n"
                .'</small>'."\n\n";

            $viewLevel = -1; //  position of the flag of the view in the $view array/string
            
            
            /***************************************************************************
             *              
             *        Logins
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            echo '<p>'."\n";
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';

              echo '-&nbsp;&nbsp;<b>'.$langLoginsAndAccessTools.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&view='.$tempView.'">'.$langClose.'</a>]</small>'
                        .'<br />'."\n".'&nbsp;&nbsp;&nbsp;'.$langLoginsDetails.'<br />'."\n";
                
                $sql = "SELECT UNIX_TIMESTAMP(`login_date`) AS `unix_date`, count(`login_date`) AS `nbr_login`
                            FROM `".$tbl_track_e_login."`
                            WHERE `login_user_id` = '". (int)$_REQUEST['uInfo']."'
                            GROUP BY MONTH(`login_date`), YEAR(`login_date`)
                            ORDER BY `login_date` ASC";
                $results = claro_sql_query_fetch_all($sql);

                echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
                	.'<tr class="headerX">'."\n"
                    .'<th>'.$langLoginsTitleMonthColumn.'</th>'."\n"
                    .'<th>'.$langLoginsTitleCountColumn.'</th>'."\n"
                    .'</tr>'."\n"
                    .'<tbody>'."\n";
                        
                $total = 0;
                if( !empty($results) && is_array($results) )
                { 
					foreach( $results as $result )
	                {
						echo '<tr>'."\n"
							.'<td><a href="logins_details.php?uInfo='.$_REQUEST['uInfo'].'&reqdate='.$result['unix_date'].'">'.$langMonthNames['long'][date('n', $result['unix_date'])-1].' '.date('Y', $result['unix_date']).'</a></td>'."\n"
							.'<td valign="top" align="right">'.$result['nbr_login'].'</td>'."\n"
							.'</tr>'."\n";
	                    $total = $total + $result['nbr_login'];
	                }
	                echo '</tbody>'."\n".'<tfoot>'."\n".'<tr>'."\n"
	                    .'<td>'.$langTotal.'</td>'."\n"
	                    .'<td align="right">'.$total.'</td>'."\n"
	                    .'</tr>'."\n".'</tfoot>'."\n";
                }
                else
                {
                    echo '<tfoot>'."\n".'<tr>'."\n"
                        .'<td colspan="2"><center>'.$langNoResult.'</center></td>'."\n"
                        .'</tr>'."\n".'</tfoot>'."\n";
                }
                echo '</table>'."\n"
					.'</td></tr>'."\n";
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&view='.$tempView.'">'.$langLoginsAndAccessTools.'</a>'."\n";
            }
            echo '<br />'."\n".'</p>'."\n\n";
            /***************************************************************************
             *              
             *        Exercises
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            echo '<p>'."\n";
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';

                echo '-&nbsp;&nbsp;<b>'.$langExercisesResults.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&view='.$tempView.'">'.$langClose.'</a>]</small>'."\n"
                        .'<br />&nbsp;&nbsp;&nbsp;'.$langExercisesDetails.'<br />'."\n";
                        
                $sql = "SELECT `E`.`titre`, `E`.`id`,
                        MIN(`TEX`.`exe_result`) AS `minimum`,
                        MAX(`TEX`.`exe_result`) AS `maximum`,
                        AVG(`TEX`.`exe_result`) AS `average`,
                        MAX(`TEX`.`exe_weighting`) AS `weighting`,
                        COUNT(`TEX`.`exe_user_id`) AS `attempts`,
                        MAX(`TEX`.`exe_date`) AS `lastAttempt`,
                        AVG(`TEX`.`exe_time`) AS `avgTime`
                    FROM `$tbl_quiz_test` AS `E` , `$tbl_track_e_exercises` AS `TEX`
                    WHERE `TEX`.`exe_user_id` = '". (int)$_GET['uInfo']."'
                        AND `TEX`.`exe_exo_id` = `E`.`id`
                    GROUP BY `TEX`.`exe_exo_id`
                    ORDER BY `E`.`titre` ASC";

                $results = claro_sql_query_fetch_all($sql);
                
                echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
                	.'<tr class="headerX">'."\n"
                    .'<th>'.$langExercisesTitleExerciseColumn.'</th>'."\n"
                    .'<th>'.$langScoreMin.'</th>'."\n"
                    .'<th>'.$langScoreMax.'</th>'."\n"
                    .'<th>'.$langScoreAvg.'</th>'."\n"
                    .'<th>'.$langExeAvgTime.'</th>'."\n"
                    .'<th>'.$langAttempts.'</th>'."\n"
                    .'<th>'.$langLastAttempt.'</th>'."\n"
                    .'</tr>';
                    
                if( !empty($results) && is_array($results) )
                {
                	echo '<tbody>'."\n";
					foreach( $results as $exo_details )
					{
						echo '<tr>'."\n"
							.'<td><a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_GET['uInfo'].'&view='.$view.'&exoDet='.$exo_details['id'].'">'.$exo_details['titre'].'</td>'."\n"
							.'<td>'.$exo_details['minimum'].'</td>'."\n"
							.'<td>'.$exo_details['maximum'].'</td>'."\n"
							.'<td>'.(round($exo_details['average']*10)/10).'</td>'."\n"
							.'<td>'.claro_disp_duration(floor($exo_details['avgTime'])).'</td>'."\n"
							.'<td>'.$exo_details['attempts'].'</td>'."\n"
							.'<td>'.$exo_details['lastAttempt'].'</td>'."\n"
							.'</tr>'."\n";
                              
						// display details of the exercise, all attempts
						if ( isset($_GET['exoDet']) && $_GET['exoDet'] == $exo_details['id'])
						{
							$sql = "SELECT `exe_id`, `exe_date`, `exe_result`, `exe_weighting`, `exe_time`
									FROM `".$tbl_track_e_exercises."`
									WHERE `exe_exo_id` = ". (int)$exo_details['id']."
									AND `exe_user_id` = ". (int)$_GET['uInfo']."
									ORDER BY `exe_date` ASC";
							$resListAttempts = claro_sql_query_fetch_all($sql);

							echo '<tr>'
								.'<td class="noHover">&nbsp;</td>'."\n"
								.'<td colspan="6" class="noHover">'."\n"
								.'<table class="claroTable" cellspacing="1" cellpadding="2" border="0" width="100%">'."\n"
								.'<tr class="headerX">'."\n"
								.'<th><small>'.$langDate.'</small></th>'."\n"
								.'<th><small>'.$langScore.'</small></th>'."\n"
								.'<th><small>'.$langExeTime.'</small></th>'."\n"
								.'</tr>'."\n"
								.'<tbody>'."\n";

							foreach ( $resListAttempts as $exo_attempt )
							{
								echo '<tr>'."\n"
								.'<td><small><a href="user_exercise_details.php?track_id='.$exo_attempt['exe_id'].'">'.$exo_attempt['exe_date'].'</a></small></td>'."\n"
								.'<td><small>'.$exo_attempt['exe_result'].'/'.$exo_attempt['exe_weighting'].'</small></td>'."\n"
								.'<td><small>'.claro_disp_duration($exo_attempt['exe_time']).'</small></td>'."\n"
								.'</tr>'."\n";
							}
							echo '</tbody>'."\n".'</table>'."\n\n"
								.'</td>'."\n"
								.'</tr>'."\n";

						}
                      
					}
					echo '</tbody>'."\n";
                }
				else
                {
                    echo '<tfoot>'."\n".'<tr>'."\n"
                          .'<td colspan="7" align="center">'.$langNoResult.'</td>'."\n"
                          .'</tr>'."\n".'</tfoot>'."\n";
                }
                echo '</table>'."\n\n"
                	.'</td>'."\n".'</tr>'."\n";

            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&view='.$tempView.'">'.$langExercisesResults.'</a>';
            }
            echo '<br />'."\n".'</p>'."\n\n";
            /***************************************************************************
             *              
             *        Learning paths // doesn't use the tracking table but the lp_user_module_progress learnPath table
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            echo '<p>'."\n";
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';

                echo '-&nbsp;&nbsp;<b>'.$langLearningPath.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&view='.$tempView.'">'.$langClose.'</a>]</small>'
                        .'<br />'."\n".'&nbsp;&nbsp;&nbsp;'.$langLearnPathDetails.'<br />'."\n";
                
                // get list of learning paths of this course
                // list available learning paths
                $sql = "SELECT LP.`name`, LP.`learnPath_id`
						 FROM `".$TABLELEARNPATH."` AS LP
						 ORDER BY LP.`rank`";
              
                $lpList = claro_sql_query_fetch_all($sql);

                // table header
                echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
                    .'<tr class="headerX">'."\n"
                    .'<th>'.$langLearningPath.'</th>'."\n"
                    .'<th colspan="2">'.$langProgress.'</th>'."\n"
                    .'</tr>';
                if(sizeof($lpList) == 0)
                {
                    echo '<tfoot>'."\n".'<tr>'."\n"
						.'<td colspan="3" align="center">'.$langNoLearningPath.'</td>'."\n"
						.'</tr>'."\n".'</tfoot>'."\n";
                }
                else
                {
                  // we need the library of learning paths, include it only if needed
                  include($includePath."/lib/learnPath.lib.inc.php");
                  
                  // display each learning path with the corresponding progression of the user
                  foreach($lpList as $lpDetails)
                  {
                      
                      $lpProgress = get_learnPath_progress($lpDetails['learnPath_id'],$_GET['uInfo']);
                      echo "\n".'<tr>'."\n"
	                      .'<td><a href="lp_modules_details.php?uInfo='.$_GET['uInfo'].'&path_id='.$lpDetails['learnPath_id'].'">'.htmlspecialchars($lpDetails['name']).'</a></td>'."\n"
	                      .'<td align="right">'."\n"
	                      .claro_disp_progress_bar($lpProgress, 1)
	                      .'</td>'."\n"
	                      .'<td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
	                      .'</tr>'."\n";
                  }
                }
                echo '</table>'."\n"
                	.'</td>'."\n".'</tr>'."\n";
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&view='.$tempView.'">'.$langLearningPath.'</a>';
            }
            echo '<br />'."\n".'</p>'."\n\n";
            /***************************************************************************
             *              
             *        Works
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            echo '<p>'."\n";
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';

                echo '-&nbsp;&nbsp;<b>'.$langWorkUploads.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&view='.$tempView.'">'.$langClose.'</a>]</small>'
                        .'<br />'."\n".'&nbsp;&nbsp;&nbsp;'.$langWorksDetails.'<br />'."\n";
                        
                $sql = "SELECT `A`.`title` as `a_title`, `A`.`assignment_type`,
                                `S`.`id`, `S`.`title` as `s_title`,
                                `S`.`group_id`, `S`.`last_edit_date`, `S`.`authors`,
                                `S`.`score`, `S`.`parent_id`, `G`.`name` as `g_name`
                            FROM `".$tbl_wrk_assignment."` as `A` ,
                                `".$tbl_wrk_submission."` as `S`
                            LEFT JOIN `".$tbl_group_team."` as `G`
                                ON `G`.`id` = `S`.`group_id`
                            WHERE `A`.`id` = `S`.`assignment_id`
                                AND ( `S`.`user_id` = ". (int)$_REQUEST['uInfo']."
                                        OR ( `S`.`parent_id` IS NOT NULL AND `S`.`parent_id` ) )
                                AND `A`.`visibility` = 'VISIBLE'
                            ORDER BY `A`.`title` ASC, `S`.`last_edit_date` ASC";

                $results = claro_sql_query_fetch_all($sql);
                
                // first pass to create a array of submission id
                // do not record the correction id
                $submissions = array();
                foreach( $results as $work )
                {
                        if( !isset($work['parent_id']) || empty($work['parent_id']) )
                        {
                                $submissions[$work['id']] = 0;
                        }
                }

                // second pass to add the score in the submission array
                // and to delete correction that have a parent_id that is not in the first pass array
                $i = 0;
                foreach( $results as $work )
                {
                        // correction and correction of one of the submissions to display
                        if( isset($work['parent_id']) && !empty($work['parent_id']) && array_key_exists($work['parent_id'],$submissions) )
                        {
                                if( $work['score'] > $submissions[$work['parent_id']] )
                                {
                                        $submissions[$work['parent_id']] = $work['score'];
                                }
                        }
                        
                        if( isset($work['parent_id']) && !empty($work['parent_id']) )
                        {
                                // unset correction 
                                unset($results[$i]);
                        }
                        $i++;
                }
                
                echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
                        .'<tr class="headerX">'."\n"
                        .'<th>'.$langAssignment.'</th>'."\n"
                        .'<th>'.$langWorkTitle.'</th>'."\n"
                        .'<th>'.$langWorkAuthors.'</th>'."\n"
                        .'<th>'.$langScore.'</th>'."\n"
                        .'<th>'.$langDate.'</th>'."\n"
                        .'</tr>'."\n";
                // third pass to finally display
                if( !empty($results) && is_array($results) )
                { 
                    echo '<tbody>'."\n";
                    $prevATitle = "";
                    foreach($results as $work)
                    { 
                        $timestamp = strtotime($work['last_edit_date']);
                        $beautifulDate = claro_disp_localised_date($dateTimeFormatLong,$timestamp);
                        
                        if( $work['a_title'] == $prevATitle )
                        {
                                $displayedATitle = "";
                        }
                        else
                        {
                                $displayedATitle = $work['a_title'];
                                $prevATitle = $work['a_title'];
                        }
                        if( $submissions[$work['id']] != 0 )
                        {
                                $displayedScore = $submissions[$work['id']]." %";
                        }
                        else
                        {
                                $displayedScore  = $langNoScore;
                        }
                        
                        if( isset($work['g_name']) )
                        {
                                $displayedAuthors = $work['authors']."( ".$work['g_name']." )";
                        }
                        else
                        {
                                $displayedAuthors = $work['authors'];
                        }
                        
                        echo '<tr>'."\n"
                            .'<td>'.$displayedATitle.'</td>'."\n"
                            .'<td>'.$work['s_title'].'</td>'."\n"
                            .'<td>'.$displayedAuthors.'</td>'."\n"
                            .'<td>'.$displayedScore.'</td>'."\n"
                            .'<td><small>'.$beautifulDate.'</small></td>'."\n"
                            .'</tr>'."\n";
                    }
                    echo '</tbody>'."\n";
                
                }
                else
                {
                    echo '<tfoot><tr>'."\n"
                        .'<td colspan="5" align="center">'.$langNoResult.'</td>'."\n"
                        .'</tr></tfoot>'."\n";
                }
                echo '</table>'."\n"
                    .'</td></tr>'."\n";
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&amp;view='.$tempView.'">'.$langWorkUploads.'</a>';
            }
            echo '<br />'."\n".'</p>'."\n\n";
            /***************************************************************************
             *        Access to documents
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            echo '<p>'."\n";
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';

                echo '-&nbsp;&nbsp;<b>'.$langDocumentsAccess.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&amp;view='.$tempView.'">'.$langClose.'</a>]</small>'
                    .'<br />'."\n".'&nbsp;&nbsp;&nbsp;'.$langDocumentsDetails.'<br />'."\n";
                        
                $sql = "SELECT `down_doc_path`
                            FROM `".$tbl_track_e_downloads."`
                            WHERE `down_user_id` = '". (int)$_REQUEST['uInfo']."'
                            GROUP BY `down_doc_path`";
                $results = claro_sql_query_fetch_all($sql);
                
                echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
                	.'<tr class="headerX">'."\n"
					.'<th>'.$langDocumentsTitleDocumentColumn.'</th>'."\n"
					.'</tr>'."\n";
                if( !empty($results) && is_array($results) )
                { 
                    echo '<tbody>'."\n";
                    foreach( $results as $result )
                    { 
                            echo '<tr>'."\n"
                                    .'<td>'.$result['down_doc_path'].'</td>'."\n"
                                    .'</tr>'."\n";
                    }
                    echo '</tbody>'."\n";
                
                }
                else
                {
                    echo '<tfoot>'."\n".'<tr>'."\n"
                            .'<td align="center">'.$langNoResult.'</td>'."\n"
                            .'</tr>'."\n".'</tfoot>'."\n";
                }
                echo '</table>'."\n"
                    .'</td>'."\n"
                    .'</tr>';
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&amp;view='.$tempView.'">'.$langDocumentsAccess.'</a>';
            }
            echo '<br />'."\n".'</p>'."\n\n";
            /***************************************************************************
             *
             *        Forum posts
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            echo '<p>'."\n";
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';

                echo '-&nbsp;&nbsp;<b>'.$langTrackForumUsage.'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&view='.$tempView.'">'.$langClose.'</a>]</small>'
                        .'<br />'."\n";
                // total number of messages posted by user
                $sql = "SELECT count(`post_id`)
                            FROM `".$tbl_bb_posts."`
                            WHERE `poster_id` = '". (int)$_REQUEST['uInfo']."'
                            ";
                $totalPosts = claro_sql_query_get_single_value($sql);
                
                // total number of threads started by user
                $sql = "SELECT count(`topic_title`)
                            FROM `".$tbl_bb_topics."`
                            WHERE `topic_poster` = '". (int)$_REQUEST['uInfo']."'
                            ";
                $totalTopics = claro_sql_query_get_single_value($sql);

                echo '<ul>'."\n"
                    .'<li>'.$langTrackTotalPosts.' : '.$totalPosts.'</li>'
                    .'<li>'.$langTrackTotalTopics.' : '.$totalTopics.'</li>'
                    .'<li>'.$langLastMsgs."\n";
                // last 10 distinct messages posted
                $sql = "SELECT `bb_t`.`topic_id`,
                                `bb_t`.`topic_title`, 
                                max(`bb_t`.`topic_time`) as `last_message`
                            FROM `".$tbl_bb_posts."` as `bb_p`, `".$tbl_bb_topics."` as `bb_t`
                            WHERE `bb_p`.`poster_id` = '". (int)$_REQUEST['uInfo']."'
                            AND `bb_t`.`topic_id` = `bb_p`.`topic_id`
                            GROUP BY `bb_t`.`topic_title`
                            ORDER BY `bb_p`.`post_time` DESC
                            LIMIT 10";

                $results = claro_sql_query_fetch_all($sql);

                echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
					.'<tr class="headerX">'."\n"
					.'<th>'.$l_topic.'</th>'."\n"
					.'<th>'.$langLastMsg.'</th>'."\n"
					.'</tr>'."\n";
                if( !empty($results) && is_array($results) )
                {
                    echo '<tbody>'."\n";
                    foreach( $results as $result )
                    {
						echo '<tr>'."\n"
							.'<td><a href="../phpbb/viewtopic.php?topic='.$result['topic_id'].'">'.$result['topic_title'].'</a></td>'."\n"
							.'<td>'.$result['last_message'].'</td>'."\n"
							.'</tr>'."\n";
                    }
                    echo '</tbody>'."\n";

                }
                else
                {
                    echo '<tfoot>'."\n".'<tr>'."\n"
	                    .'<td align="center">'.$langNoResult.'</td>'."\n"
	                    .'</tr>'."\n".'</tfoot>'."\n";
                }
                echo '</table>'."\n"
                    .'</li>'."\n".'</ul>';
                
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?uInfo='.$_REQUEST['uInfo'].'&amp;view='.$tempView.'">'.$langTrackForumUsage.'</a>';
            }
            echo '<br />'."\n".'</p>'."\n\n";
        }
        else
        {
            echo $langErrorUserNotInGroup;
        }
        
    }
}
// not allowed
else
{
    if(!$is_trackingEnabled)
    {
        echo $langTrackingDisabled;
    }
    else
    {
        echo $langNotAllowed;
    }
}
include($includePath."/claro_init_footer.inc.php");
?>
