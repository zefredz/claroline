<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.1 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
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
 
$langFile = "tracking";
require '../inc/claro_init_global.inc.php';
/*
$interbredcrump[]= array ("url"=>"../group/group.php", "name"=> $langBredCrumpGroups);
$interbredcrump[]= array ("url"=>"../group/group_space.php?gidReq=$_gid", "name"=> $langBredCrumpGroupSpace);
*/
$interbredcrump[]= array ("url"=>"../user/userInfo.php?uInfo=".$_GET['uInfo'], "name"=> $langBredCrumpUsers);

$nameTools = $langToolName;

$htmlHeadXtra[] = "<style type='text/css'>
<!--
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
-->
</style>
<STYLE media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";

// regroup table names for maintenance purpose
$TABLECOURSUSER	        = $mainDbName."`.`cours_user";
$TABLEUSER	        = $mainDbName."`.`user";

$TABLETRACK_LOGIN       = $statsDbName."`.`track_e_login";

$TABLETRACK_ACCESS      = $_course['dbNameGlu']."track_e_access";
$TABLETRACK_LINKS       = $_course['dbNameGlu']."track_e_links";
$TABLETRACK_DOWNLOADS   = $_course['dbNameGlu']."track_e_downloads";
$TABLETRACK_UPLOADS     = $_course['dbNameGlu']."track_e_uploads";
$TABLETRACK_EXERCISES   = $_course['dbNameGlu']."track_e_exercices";

$TABLECOURSE_LINKS      = $_course['dbNameGlu']."link";
$TABLECOURSE_WORK       = $_course['dbNameGlu']."assignment_doc";
$TABLECOURSE_DOCUMENTS  = $_course['dbNameGlu']."document";
$TABLECOURSE_GROUPS     = $_course['dbNameGlu']."group_team";
$TABLECOURSE_GROUPSPROP = $_course['dbNameGlu']."group_property";
$TABLECOURSE_GROUPSUSER = $_course['dbNameGlu']."group_rel_team_user";
$TABLECOURSE_EXERCICES = $_course['dbNameGlu']."quiz_test";

// for learning paths section
$TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
$TABLEMODULE            = $_course['dbNameGlu']."lp_module";
$TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
$TABLEASSET             = $_course['dbNameGlu']."lp_asset";
$TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";


@include($includePath."/claro_init_header.inc.php");
@include($includePath."/lib/statsUtils.lib.inc.php");


$is_allowedToTrack = $is_groupTutor; // allowed to track only user of one group
if (isset($uInfo) && isset($_uid)) $is_allowedToTrack = $is_allowedToTrack || ($uInfo == $_uid); //added by RH to allow user to see its own course stats 
$is_allowedToTrackEverybodyInCourse = $is_courseAdmin; // allowed to track all student in course
?>
<h3>
    <?php echo $nameTools ?>
</h3>
<h4>
    <?php echo $langStatsOfUser ?>
</h4>
<table width="100%" cellpadding="2" cellspacing="3" border="0">
<?
// check if uid is tutor of this group

if( ( $is_allowedToTrack || $is_allowedToTrackEverybodyInCourse ) && $is_trackingEnabled )
{
    if(!$uInfo && !isset($uInfo) )
    {
        /***************************************************************************
         *              
         *		Display list of user of this group
         *
         ***************************************************************************/
        echo "<h4>$langListStudents</h4>";
        if( $is_allowedToTrackEverybodyInCourse )
        {
            // if user can track everybody : list user of course
            $sql = "SELECT count(user_id)
                        FROM `$TABLECOURSUSER` 
                        WHERE `code_cours` = '$_cid'";
        }
        else
        {
            // if user can only track one group : list users of this group
            $sql = "SELECT count(user)
                        FROM `$TABLECOURSE_GROUPSUSER`
                        WHERE `team` = '$_gid'";
        }
        $userGroupNb = getOneResult($sql);
        $step = 25; // number of student per page
        if ($userGroupNb > $step)
        {
            if(!isset($offset))
            {
                    $offset=0;
            }
    
            $next     = $offset + $step;
            $previous = $offset - $step;
    
            $navLink = "<table width='100%' border='0'>\n"
                      ."<tr>\n"
                              ."<th align='left'>";
    
            if ($previous >= 0)
            {
                    $navLink .= "<small><a href='$PHP_SELF?offset=$previous'>&lt;&lt; $langPreviousPage</a></small>";
            }
    
            $navLink .= "</td>\n"
                       ."<td align='right'>";
    
            if ($next < $userGroupNb)
            {
                    $navLink .= "<small><a href='$PHP_SELF?offset=$next'>$langNextPage &gt;&gt;</a></small>";
            }
    
            $navLink .= "</td>\n"
                       ."</tr>\n"
                       ."</table>\n";
        }
        else
        {
            $offset = 0;
        }
        
        echo $navLink;
        
        if( $is_allowedToTrackEverybodyInCourse )
        {
            // list of users in this course
            $sql = "SELECT `u`.`user_id`, `u`.`prenom`,`u`.`nom`
                        FROM `$TABLECOURSUSER` cu , `$TABLEUSER` u 
                        WHERE `cu`.`user_id` = `u`.`user_id`
                            AND `cu`.`code_cours` = '$_cid'
                        LIMIT $offset,$step";
        }
        else
        {
            // list of users of this group
            $sql = "SELECT `u`.`user_id`, `u`.`prenom`,`u`.`nom`
                        FROM `$TABLECOURSE_GROUPSUSER` gu , `$TABLEUSER` u 
                        WHERE `gu`.`user` = `u`.`user_id`
                            AND `gu`.`team` = '$_gid'
                        LIMIT $offset,$step";
        }
        $list_users = getManyResults3Col($sql);
        echo 	"<table class=\"claroTable\" width='100%' cellpadding='2' cellspacing='1' border='0'>\n"
                    ."<tr class=\"headerX\" align='center' valign='top'>\n"
                    ."<th align='left'>",$langUserName,"</th>\n"
                    ."</tr>\n";
        for($i = 0 ; $i < sizeof($list_users) ; $i++)
        {
            echo    "<tr valign='top' align='center'>\n"
                    ."<td align='left'>"
                    ."<a href='$PHP_SELF?uInfo=",$list_users[$i][0],"'>"
                    .$list_users[$i][1]," ",$list_users[$i][2]
                    ."</a>".
                    "</td>\n";
        }
        echo        "</table>\n";
    
        echo $navLink;
    }
    else // if uInfo is set
    {
        /***************************************************************************
         *              
         *		Informations about student uInfo
         *
         ***************************************************************************/
        // these checks exists for security reasons, neither a prof nor a tutor can see statistics of an user from 
        // another course, or group
        //if( $is_allowedToTrackEverybodyInCourse ) 
        if( $is_allowedToTrackEverybodyInCourse || ($uInfo == $_uid) )
        {
            // check if user is in this course
            $sql = "SELECT `u`.`prenom`,`u`.`nom`, `u`.`email`
                        FROM `$TABLECOURSUSER` cu , `$TABLEUSER` u
                        WHERE `cu`.`user_id` = `u`.`user_id`
                            AND `cu`.`code_cours` = '$_cid'
                            AND `u`.`user_id` = '$uInfo'";
        }
        else
        {
            // check if user is in the group of this tutor
            $sql = "SELECT `u`.`prenom`,`u`.`nom`, `u`.`email`
                        FROM `$TABLECOURSE_GROUPSUSER` gu , `$TABLEUSER` u 
                        WHERE `gu`.`user` = `u`.`user_id`
                            AND `gu`.`team` = '$_gid'
                            AND `u`.`user_id` = '$uInfo'";
        }
        $query = @mysql_query($sql);
        $res = @mysql_fetch_array($query);
        if(is_array($res))
        {
            $res[2] == "" ? $res2 = $langNoEmail : $res2 = $res[2];
                
            echo "<tr><td>";
            echo $informationsAbout." : <br>";
            echo "<ul>\n"
                    ."<li>".$langFirstName." : ".$res[1]."</li>\n"
                    ."<li>".$langLastName." : ".$res[0]."</li>\n"
                    ."<li>".$langEmail." : ".$res2."</li>\n"
                    ."</ul>";
            echo "</td></tr>";
            
            // show all : number of 1 is equal to or bigger than number of categories
            // show none : number of 0 is equal to or bigger than number of categories
            echo "<tr>
                    <td>
                    <small>
                    [<a href='$PHP_SELF?uInfo=$uInfo&view=1111111'>".$langShowAll."</a>] 
                    [<a href='$PHP_SELF?uInfo=$uInfo&view=0000000'>".$langShowNone."</a>]".
                    //"||[<a href='$PHP_SELF'>".$langBackToList."</a>]".
                    
                    "</small>
                    </td>
                </tr>
            ";
                    
            if(!isset($view)) $view ="0000000";
            $viewLevel = -1; //  position of the flag of the view in the $view array/string
            /***************************************************************************
             *              
             *		Logins
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';
                echo "
                    <tr>
                            <td valign='top'>
                            -&nbsp;&nbsp;&nbsp;<b>".$langLoginsAndAccessTools."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."'>".$langClose."</a>]</small>
                            </td>
                    </tr>
                ";
                echo "<tr><td style='padding-left : 40px;' valign='top'>$langLoginsDetails<br>"; 
                
                $sql = "SELECT UNIX_TIMESTAMP(`login_date`), count(`login_date`)
                            FROM `$TABLETRACK_LOGIN`
                            WHERE `login_user_id` = '$uInfo'
                            GROUP BY MONTH(`login_date`)
                            ORDER BY `login_date` ASC";
                echo "<tr><td style='padding-left : 40px;padding-right : 40px;'>";  
                $results = getManyResults2Col($sql);
                echo "<table class=\"claroTable\" cellpadding='2' cellspacing='1' border='0' align=center>";
                echo "<tr class=\"headerX\">
                        <th>
                        $langLoginsTitleMonthColumn
                        </th>
                        <th>
                        $langLoginsTitleCountColumn
                        </th>
                    </tr>
                    <tbody>";
                $total = 0;
                if (is_array($results))
                { 
                    for($j = 0 ; $j < count($results) ; $j++)
                    { 
                        echo "<tr>"; 
                        echo "<td><a href='logins_details.php?uInfo=$uInfo&reqdate=".$results[$j][0]."'>".$langMonthNames['long'][date("n", $results[$j][0])-1]." ".date("Y", $results[$j][0])."</a></td>";
                        echo "<td valign='top' align='right'>".$results[$j][1]."</td>";
                        echo"</tr>";
                        $total = $total + $results[$j][1];
                    }
                    echo "</tbody><tfoot><tr>"; 
                    echo "<td>".$langTotal."</td>";
                    echo "<td align='right'>".$total."</td>";
                    echo"</tr></tfoot>";
                }
                else
                {
                    echo "<tfoot><tr>"; 
                    echo "<td colspan='2'><center>".$langNoResult."</center></td>";
                    echo"</tr></tfoot>";
                }
                echo "</table>";
                echo "</td></tr>";   
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo "
                    <tr>
                            <td valign='top'>
                            +&nbsp;&nbsp;<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."' class='specialLink'>$langLoginsAndAccessTools</a>
                            </td>
                    </tr>
                ";
            }
            
            /***************************************************************************
             *              
             *		Exercices
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';
                echo "
                    <tr>
                            <td valign='top'>
                            -&nbsp;&nbsp;&nbsp;<b>".$langExercicesResults."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."'>".$langClose."</a>]</small>
                            </td>
                    </tr>
                ";
                //-- scores stats for each exercise : min / max / avg scores
                echo "<tr><td style='padding-left : 40px;' valign='top'>$langExercicesDetails<br>";
                $sql = "SELECT `E`.`titre`, `E`.`id`,
                        MIN(`TEX`.`exe_result`) AS minimum,
                        MAX(`TEX`.`exe_result`) AS maximum,
                        AVG(`TEX`.`exe_result`) AS average,
                        MAX(`TEX`.`exe_weighting`) AS weighting,
                        COUNT(`TEX`.`exe_user_id`) AS attempts,
                        MAX(`TEX`.`exe_date`) AS lastAttempt 
                    FROM `$TABLECOURSE_EXERCICES` AS E , `$TABLETRACK_EXERCISES` AS TEX
                    WHERE `TEX`.`exe_user_id` = '".$_GET['uInfo']."'
                        AND `TEX`.`exe_exo_id` = `E`.`id`
                    GROUP BY `TEX`.`exe_exo_id`
                    ORDER BY `E`.`titre` ASC";
            
                echo "<tr><td style='padding-left : 40px;padding-right : 40px;'>";  
                $result = claro_sql_query($sql);
                
                echo "<table class=\"claroTable\" cellpadding='2' cellspacing='1' border='0' align='center'>";
                echo "<tr class=\"headerX\">
                        <th>
                        $langExercicesTitleExerciceColumn
                        </th>
                        <th>
                        $langScoreMin
                        </th>
                        <th>
                        $langScoreMax
                        </th>
                        <th>
                        $langScoreAvg
                        </th>
                        <th>
                        $langAttempts
                        </th>
                        <th>
                        $langLastAttempt
                        </th>
                    </tr>";
                if( mysql_num_rows($result) == 0)
                {
                    echo "<tfoot><tr>"; 
                    echo "<td colspan='6'><center>".$langNoResult."</center></td>";
                    echo"</tr></tfoot>";
                }
                else
                {
                      echo "<tbody>";
                      while( $exo_details = mysql_fetch_array($result) )
                      { 
                              echo "<tr>"; 
                              echo "<td><a href=\"$PHP_SELF?uInfo=".$_GET['uInfo']."&view=".$view."&exoDet=".$exo_details['id']."\">".$exo_details['titre']."</td>";
                              echo "<td>".$exo_details['minimum']."</td>";
                              echo "<td>".$exo_details['maximum']."</td>";
                              echo "<td>".(round($exo_details['average']*10)/10)."</td>";
                              echo "<td>".$exo_details['attempts']."</td>";
                              echo "<td>".$exo_details['lastAttempt']."</td>";
                              echo"</tr>";
                              
                              // display details of the exercise, all attempts
                              if ($_GET['exoDet'] == $exo_details['id'])
                              {
                                $sql = "SELECT `exe_date`, `exe_result`, `exe_weighting`
                                FROM `".$TABLETRACK_EXERCISES."`
                                WHERE `exe_exo_id` = ".$exo_details['id']."
                                AND `exe_user_id` = ".$_GET['uInfo']."
                                ORDER BY `exe_date` ASC";
                                $resListAttempts = claro_sql_query($sql);
                                
                                echo "<tr>";
                                echo "<td class=\"noHover\">&nbsp;</td>";
                                echo "<td colspan=\"5\" class=\"noHover\">";
                                echo "<table class=\"claroTable\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\" width=\"100%\">\n
                                <tr class=\"headerX\">
                                  <th><small>$langDate</small></th>\n
                                  <th><small>$langScore</small></th>\n
                                </tr>
                                <tbody>";
                                
                                while ( $exo_attempt = mysql_fetch_array($resListAttempts) )
                                {
                                    echo "<tr>";
                                    echo "<td><small>".$exo_attempt['exe_date']."</small></td>";
                                    echo "<td><small>".$exo_attempt['exe_result']."/".$exo_attempt['exe_weighting']."</small></td>";
                                    echo "</tr>";
                                }
                                echo  "</tbody></table>";
                                echo "</td>";
                                echo "</tr>";
                                
                              }
                      
                      }
                      echo "</tbody>";
                }
                echo "</table>";
                echo "</td></tr>";

            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo "
                    <tr>
                            <td valign='top'>
                            +&nbsp;&nbsp;<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."' class='specialLink'>$langExercicesResults</a>
                            </td>
                    </tr>
                ";
            }
            
            /***************************************************************************
             *              
             *		Learning paths // doesn't use the tracking table but the lp_user_module_progress learnPath table
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';
                echo "
                    <tr>
                            <td valign='top'>
                            -&nbsp;&nbsp;&nbsp;<b>".$langLearningPath."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."'>".$langClose."</a>]</small>
                            </td>
                    </tr>
                ";
                echo "<tr><td style='padding-left : 40px;' valign='top'>$langLearnPathDetails<br>";
                
                // get list of learning paths of this course
                // list available learning paths
                $sql = "SELECT LP.`name`, LP.`learnPath_id`
                       FROM `".$TABLELEARNPATH."` AS LP
                  ORDER BY LP.`rank`";
              
                $lpList = claro_sql_query_fetch_all($sql);

                // table header
                echo "<table class=\"claroTable\" cellpadding='2' cellspacing='1' border='0' align='center'>
                <tr class=\"headerX\">
                        <th>
                        $langLearningPath
                        </th>
                        <th colspan=\"2\">
                        $langProgress
                        </th>
                    </tr>";
                if(sizeof($lpList) == 0)
                {
                    echo "<tfoot><tr> 
                    <td colspan='3'><center>".$langNoLearnPath."</center></td>
                    </tr></tfoot>";
                }
                else
                {
                  // we need the library of learning paths, include it only if needed
                  include($includePath."/lib/learnPath.lib.inc.php");
                  
                  // display each learning path with the corresponding progression of the user
                  foreach($lpList as $lpDetails)
                  {
                      
                      $lpProgress = get_learnPath_progress($lpDetails['learnPath_id'],$_GET['uInfo']);
                      echo "<tr>
                    <td><a href=\"lp_modules_details.php?uInfo=".$_GET['uInfo']."&path_id=".$lpDetails['learnPath_id']."\">".$lpDetails['name']."</a></td>
                    <td align=\"right\">".
                    claro_disp_progress_bar($lpProgress, 1).
                    "</td>
                    <td align=\"left\"><small>".$lpProgress."%</small></td>
                   </tr>";
                  }
                }
                echo "</table>
              </td></tr>";
                
                
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo "
                    <tr>
                            <td valign='top'>
                            +&nbsp;&nbsp;<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."' class='specialLink'>$langLearningPath</a>
                            </td>
                    </tr>
                ";
            }
            /***************************************************************************
             *              
             *		Work upload
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';
                echo "
                    <tr>
                            <td valign='top'>
                            -&nbsp;&nbsp;&nbsp;<b>".$langWorkUploads."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."'>".$langClose."</a>]</small>
                            </td>
                    </tr>
                ";
                echo "<tr><td style='padding-left : 40px;' valign='top'>$langWorksDetails<br>";
                $sql = "SELECT `u`.`upload_date`, `w`.`titre`, `w`.`auteurs`,`w`.`url`
                                    FROM `$TABLETRACK_UPLOADS` `u` , `$TABLECOURSE_WORK` `w`
                                    WHERE `u`.`upload_work_id` = `w`.`id`
                                        AND `u`.`upload_user_id` = '$uInfo'
                                    ORDER BY `u`.`upload_date` DESC";
                echo "<tr><td style='padding-left : 40px;padding-right : 40px;'>";  
                $results = getManyResultsXCol($sql,4);
                echo "<table class=\"claroTable\" cellpadding='2' cellspacing='1' border='0' align=center>";
                echo "<tr class=\"headerX\">
                        <th width='40%'>
                        $langWorkTitle
                        </th>
                        <th width='30%'>
                        $langWorkAuthors
                        </th>
                        <th width='30%'>
                        $langDate
                        </th>
                    </tr>";
                if (is_array($results))
                { 
                    echo "<tbody>";
                    for($j = 0 ; $j < count($results) ; $j++)
                    { 
                        $pathToFile = $coursesRepositoryWeb.$_course['path']."/".$results[$j][3];
                        $timestamp = strtotime($results[$j][0]);
                        $beautifulDate = dateLocalizer($dateTimeFormatLong,$timestamp);
                        echo "<tr>";
                        echo "<td>"
                                ."<a href ='".$pathToFile."'>".$results[$j][1]."</a>"
                                ."</td>";
                        echo "<td>".$results[$j][2]."</td>";
                        echo "<td><small>".$beautifulDate."</small></td>";
                        echo"</tr>";
                    }
                    echo "</tbody>";
                
                }
                else
                {
                    echo "<tfoot><tr>"; 
                    echo "<td colspan='3'><center>".$langNoResult."</center></td>";
                    echo"</tr></tfoot>";
                }
                echo "</table>";
                echo "</td></tr>";
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo "
                    <tr>
                            <td valign='top'>
                            +&nbsp;&nbsp;<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."' class='specialLink'>$langWorkUploads</a>
                            </td>
                    </tr>
                ";
            }
            
           /***************************************************************************
             *              
             *		Links usage
             *
             ***************************************************************************/
             /*
            $tempView = $view;
            $viewLevel++;
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';
                echo "
                    <tr>
                            <td valign='top'>
                            -&nbsp;&nbsp;&nbsp;<b>".$langLinksAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."'>".$langClose."</a>]</small>
                            </td>
                    </tr>
                ";
                echo "<tr><td style='padding-left : 40px;' valign='top'>$langLinksDetails<br>";
                $sql = "SELECT `cl`.`titre`, `cl`.`url`
                            FROM `$TABLETRACK_LINKS` AS sl, `$TABLECOURSE_LINKS` AS cl
                            WHERE `sl`.`links_link_id` = `cl`.`id`
                                AND `sl`.`links_user_id` = '$uInfo'
                            GROUP BY `cl`.`titre`, `cl`.`url`";
                echo "<tr><td style='padding-left : 40px;padding-right : 40px;'>";  
                $results = getManyResults2Col($sql);
                echo "<table cellpadding='2' cellspacing='1' border='0' align=center>";
                echo "<tr>
                        <td class='secLine'>
                        $langLinksTitleLinkColumn
                        </td>
                    </tr>";
                if (is_array($results))
                { 
                    for($j = 0 ; $j < count($results) ; $j++)
                    { 
                            echo "<tr>"; 
                            echo "<td class='content'><a href='".$results[$j][1]."'>".$results[$j][0]."</a></td>";
                            echo"</tr>";
                    }
                
                }
                else
                {
                    echo "<tr>"; 
                    echo "<td ><center>".$langNoResult."</center></td>";
                    echo"</tr>";
                }
                echo "</table>";
                echo "</td></tr>";   
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo "
                    <tr>
                            <td valign='top'>
                            +&nbsp;&nbsp;<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."' class='specialLink'>$langLinksAccess</a>
                            </td>
                    </tr>
                ";
            }
            */
            /***************************************************************************
             *              
             *		Access to documents
             *
             ***************************************************************************/
            $tempView = $view;
            $viewLevel++;
            if($view[$viewLevel] == '1')
            {
                $tempView[$viewLevel] = '0';
                echo "
                    <tr>
                            <td valign='top'>
                            -&nbsp;&nbsp;&nbsp;<b>".$langDocumentsAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."'>".$langClose."</a>]</small>
                            </td>
                    </tr>
                ";
                echo "<tr><td style='padding-left : 40px;' valign='top'>$langDocumentsDetails<br>";
                
                $sql = "SELECT `down_doc_path`
                            FROM `$TABLETRACK_DOWNLOADS`
                            WHERE `down_user_id` = '$uInfo'
                            GROUP BY `down_doc_path`";
            
                echo "<tr><td style='padding-left : 40px;padding-right : 40px;'>";  
                $results = getManyResults1Col($sql);
                echo "<table class=\"claroTable\" cellpadding='2' cellspacing='1' border='0' align='center'>";
                echo "<tr class=\"headerX\">
                        <th>
                        $langDocumentsTitleDocumentColumn
                        </th>
                    </tr>";
                if (is_array($results))
                { 
                    echo "<tbody>"; 
                    for($j = 0 ; $j < count($results) ; $j++)
                    { 
                            echo "<tr>"; 
                            echo "<td>".$results[$j]."</td>";
                            echo"</tr>";
                    }
                    echo "</tbody>";
                
                }
                else
                {
                    echo "<tfoot><tr>"; 
                    echo "<td><center>".$langNoResult."</center></td>";
                    echo"</tr></tfoot>";
                }
                echo "</table>";
                echo "</td></tr>";
            }
            else
            {
                $tempView[$viewLevel] = '1';
                echo "
                    <tr>
                            <td valign='top'>
                            +&nbsp;&nbsp;<a href='$PHP_SELF?uInfo=$uInfo&view=".$tempView."' class='specialLink'>$langDocumentsAccess</a>
                            </td>
                    </tr>
                ";
            }
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
?>

</table>

<?
@include($includePath."/claro_init_footer.inc.php");
?>
