<?php 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*			                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$             |
      +----------------------------------------------------------------------+
      |  Authors : see CREDITS.txt					     |
      +----------------------------------------------------------------------+
 */
 
$langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$nameTools = $langToolName;

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
-->
</style>
<STYLE media=\"print\" type=\"text/css\">
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";


// regroup table names for maintenance purpose
$TABLETRACK_ACCESS      = $_course['dbNameGlu']."track_e_access";
$TABLETRACK_LINKS       = $_course['dbNameGlu']."track_e_links";
$TABLETRACK_DOWNLOADS   = $_course['dbNameGlu']."track_e_downloads";

$TABLETRACK_EXERCISES = $_course['dbNameGlu']."track_e_exercices";
$TABLE_QUIZ_TEST = $_course['dbNameGlu']."quiz_test";

$TABLECOURSUSER	        = $mainDbName."`.`cours_user";
$TABLEUSER = $mainDbName."`.`user";

$TABLECOURSE_LINKS      = $_course['dbNameGlu']."link";
$TABLECOURSE_DOCUMENTS  = $_course['dbNameGlu']."document";


@include($includePath."/claro_init_header.inc.php");
@include($includePath."/lib/statsUtils.lib.inc.php");

$is_allowedToTrack = $is_courseAdmin;

// display title
$titleTab['mainTitle'] = $nameTools;
$titleTab['subTitle'] = $langStatsOfCourse." : ".$_course['officialCode'];
claro_disp_tool_title($titleTab);

?>

<table width="100%" cellpadding="2" cellspacing="3" border="0">
<?php
// check if uid is prof of this group

if($is_allowedToTrack && $is_trackingEnabled)
{
    // show all : view must be equal to the sum of all view values (1024+512+...+64)
    // show none : less than the tiniest value
    echo "<tr>
            <td>
            <small>
            [<a href=\"$PHP_SELF?view=1111111\">$langShowAll</a>] 
            [<a href=\"$PHP_SELF?view=0000000\">$langShowNone</a>]
            </small>
            </td>
        </tr>
    ";
    
    if(!isset($view)) $view ="0000000";
    
    /***************************************************************************
     *              
     *		Main
     *
     ***************************************************************************/
    
    $tempView = $view;
    if($view[0] == '1')
    {
        $tempView[0] = '0';
        echo "
            <tr>
                    <td valign=\"top\">
                    -&nbsp;&nbsp;&nbsp;<b>".$langUsers."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small>
                    </td>
            </tr>
        ";
        //-- total number of user in the course
        $sql = "SELECT count(*)
                    FROM `$TABLECOURSUSER`
                    WHERE code_cours = '".$_cid."'";
        $count = getOneResult($sql);
        echo "
            <tr>
                <td style=\"padding-left : 40px;\" valign=\"top\">
                ".$langCountUsers." : ".$count."
                </td>
            </tr>
        ";
        
        //--  student never connected
        $sql = "SELECT  U.`user_id`, U.`nom`, U.`prenom`
            FROM `$TABLEUSER` AS U, `$TABLECOURSUSER` AS CU
            LEFT JOIN `$TABLETRACK_ACCESS` AS A
            ON A.`access_user_id` = CU.`user_id`
            WHERE U.`user_id` = CU.`user_id`
            AND CU.`code_cours` = '".$_cid."'
            AND A.`access_user_id` IS NULL
            "; 
        echo "
            <tr>
                <td style=\"padding-left : 40px;\" valign=\"top\">
                ".$langNeverConnectedStudents;
    
        $results = getManyResults3Col($sql);
        if (is_array($results))
        { 
            echo "<ul>";
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                    echo "<li>"; 
                    echo "<a href=\"../user/userInfo.php?uInfo=".$results[$j][0]."\">".$results[$j][2]." ".$results[$j][1]."</a>";
                    echo"</li>";
            }
            echo "</ul>";
        }
        else
        {
            echo "<small>".$langNoResult."</small>";
        }
         echo            "</td>
            </tr>
        ";
        //-- student not connected for 1 month
        $sql = "SELECT U.`user_id`, U.`nom`, U.`prenom`, MAX(A.`access_date`)
            FROM `$TABLEUSER` AS U, `$TABLECOURSUSER` AS CU, `$TABLETRACK_ACCESS` AS A
            WHERE U.`user_id` = CU.`user_id`
            AND CU.`code_cours` = '".$_cid."'
            AND A.`access_date` < ( NOW() - INTERVAL 15 DAY ) 
            GROUP BY A.`access_user_id`
            ORDER BY A.`access_date` ASC
            ";
        echo "
            <tr>
                <td style=\"padding-left : 40px;\" valign=\"top\">
                ".$langNotRecentlyConnectedStudents;
    
        $results = getManyResultsXCol($sql,4);
        if (is_array($results))
        { 
            echo "<ul>";
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                    echo "<li>"; 
                    echo "<a href=\"../user/userInfo.php?uInfo=".$results[$j][0]."\">".$results[$j][2]." ".$results[$j][1]."</a> ( ".$langLastAccess." : ".$results[$j][3]." )";
                    echo"</li>";
            }
            echo "</ul>";
        }
        else
        {
            echo "<small>".$langNoResult."</small>";
        }
         echo            "</td>
            </tr>
        ";
        
        
    }
    else
    {
        $tempView[0] = '1';
        echo "
            <tr>
                    <td valign=\"top\">
                    +&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\" class=\"specialLink\">$langUsers</a>
                    </td>
            </tr>
        ";
    }
    
    /***************************************************************************
     *              
     *		Access to this course
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[1] == '1')
    {
        $tempView[1] = '0';
        echo "
            <tr>
                    <td valign=\"top\">
                    -&nbsp;&nbsp;&nbsp;<b>".$langCourseAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small>
                    </td>
            </tr>
        ";
        $sql = "SELECT count(*)
                    FROM `$TABLETRACK_ACCESS`
                    WHERE access_tool IS NULL";
        $count = getOneResult($sql);
        echo "
            <tr>
                <td style=\"padding-left : 40px;\" valign=\"top\">"
                .$langCountToolAccess." : ".$count."
                </td>
            </tr>
        ";
        // last 31 days
        $sql = "SELECT count(*) 
                    FROM `$TABLETRACK_ACCESS` 
                    WHERE (access_date > DATE_ADD(CURDATE(), INTERVAL -31 DAY))
                        AND access_tool IS NULL";
        $count = getOneResult($sql);
        echo "
            <tr>
                <td style=\"padding-left : 40px;\" valign=\"top\">
                ".$langLast31days." : ".$count."
                </td>
            </tr>
        ";
        // last 7 days
        $sql = "SELECT count(*) 
                    FROM `$TABLETRACK_ACCESS` 
                    WHERE (access_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY))
                        AND access_tool IS NULL";
        $count = getOneResult($sql);
        echo "
            <tr>
                <td style=\"padding-left : 40px;\" valign=\"top\">
                ".$langLast7days." : ".$count."
                </td>
            </tr>
        ";
        // today
        $sql = "SELECT count(*) 
                    FROM `$TABLETRACK_ACCESS` 
                    WHERE ( access_date > CURDATE() )
                        AND access_tool IS NULL";
        $count = getOneResult($sql);
        echo "
            <tr>
                <td style=\"padding-left : 40px;\" valign=\"top\">
                ".$langThisday." : ".$count."
                </td>
            </tr>
        ";
        //-- view details of traffic
        echo "
            <tr>
                <td style=\"padding-left : 40px;\" valign=\"top\">
                <a href=\"course_access_details.php\">".$langTrafficDetails."</a>
                </td>
            </tr>
        ";
    
    }
    else
    {
        $tempView[1] = '1';
        echo "
            <tr>
                    <td valign=\"top\">
                    +&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\" class=\"specialLink\">$langCourseAccess</a>
                    </td>
            </tr>
        ";
        
    }
    
    /***************************************************************************
     *              
     *		Tools
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[2] == '1')
    {
        $tempView[2] = '0';
        echo "
            <tr>
                    <td valign=\"top\">
                    -&nbsp;&nbsp;&nbsp;<b>".$langToolsAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small>
                    </td>
            </tr>
        ";
        
        
        $sql = "SELECT `access_tool`, COUNT(DISTINCT `access_user_id`),count( `access_tool` )
                    FROM `$TABLETRACK_ACCESS`
                    WHERE `access_tool` IS NOT NULL
                      AND `access_tool` <> ''
                    GROUP BY `access_tool`";
        
        echo "<tr><td style=\"padding-left : 40px;padding-right : 40px;\">";  
        $results = getManyResults3Col($sql);
        echo "<table class=\"claroTable\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\" align=\"center\">";
        echo "<tr class=\"headerX\">
                <th>
                &nbsp;$langToolTitleToolnameColumn&nbsp;
                </th>
                <th>
                &nbsp;$langToolTitleUsersColumn&nbsp;
                </th>
                <th>
                &nbsp;$langToolTitleCountColumn&nbsp;
                </th>
            </tr><tbody>";
        if (is_array($results))
        { 
            for($j = 0 ; $j < count($results) ; $j++)
            {                 
                $encodedTool = urlencode($results[$j][0]);
		echo "<tr>"; 
                echo "<td><a href=\"toolaccess_details.php?tool=".$encodedTool."\">".$results[$j][0]."</a></td>";
                //echo "<td align=\"right\">".$results[$j][1]."</td>";
		echo "<td align=\"right\"><a href=\"user_access_details.php?cmd=tool&data=".$encodedTool."\">".$results[$j][1]."</a></td>";
                echo "<td align=\"right\">".$results[$j][2]."</td>";
                echo"</tr>";
            }
        
        }
        else
        {
            echo "<tr>"; 
            echo "<td colspan=\"3\"><center>".$langNoResult."</center></td>";
            echo"</tr>";
        }
        echo "</tbody></table>";
        echo "</td></tr>";
    }
    else
    {
        $tempView[2] = '1';
        echo "
            <tr>
                    <td valign=\"top\">
                    +&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\" class=\"specialLink\">$langToolsAccess</a>
                    </td>
            </tr>
        ";
    }
    
    /***************************************************************************
     *              
     *		Links
     *
     ***************************************************************************/
     /*
    $tempView = $view;
    if($view[3] == '1')
    {
        $tempView[3] = '0';
        echo "
            <tr>
                    <td valign=\"top\">
                    -&nbsp;&nbsp;&nbsp;<b>".$langLinksAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small>
                    </td>
            </tr>
        ";
        
        $sql = "SELECT `cl`.`titre`, `cl`.`url`,count(DISTINCT `sl`.`links_user_id`), count(`cl`.`titre`)
                    FROM `$TABLETRACK_LINKS` AS sl, `$TABLECOURSE_LINKS` AS cl
                    WHERE `sl`.`links_link_id` = `cl`.`id`
                    GROUP BY `cl`.`titre`, `cl`.`url`";
                    
        echo "<tr><td style=\"padding-left : 40px;padding-right : 40px;\">";  
        $results = getManyResultsXCol($sql,4);
        echo "<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\" align=\"center\">";
        echo "<tr class=\"headerX\">
                <td class=\"secLine\">
                &nbsp;$langLinksTitleLinkColumn&nbsp;
                </td>
                <td class=\"secLine\">
                &nbsp;$langLinksTitleUsersColumn&nbsp;
                </td>
                <td class=\"secLine\">
                &nbsp;$langLinksTitleCountColumn&nbsp;
                </td>
            </tr>";
        if (is_array($results))
        { 
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                    echo "<tr>"; 
                    echo "<td class=\"content\"><a href=\"".$results[$j][1]."\">".$results[$j][0]."</a></td>";
                    echo "<td align=\"right\" class=\"content\">".$results[$j][2]."</td>";
                    echo "<td align=\"right\" class=\"content\">".$results[$j][3]."</td>";
                    echo"</tr>";
            }
        
        }
        else
        {
            echo "<tr>"; 
            echo "<td colspan=\"3\"><center>".$langNoResult."</center></td>";
            echo"</tr>";
        }
        echo "</table>";
        echo "</td></tr>";
    }
    else
    {
        $tempView[3] = '1';
        echo "
            <tr>
                    <td valign=\"top\">
                    +&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\" class=\"specialLink\">$langLinksAccess</a>
                    </td>
            </tr>
        ";
    }
    */
    /***************************************************************************
     *              
     *		Documents
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[4] == '1')
    {
        $tempView[4] = '0';
        echo "
            <tr>
                    <td valign=\"top\">
                    -&nbsp;&nbsp;&nbsp;<b>".$langDocumentsAccess."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small>
                    </td>
            </tr>
        ";
        
        $sql = "SELECT `down_doc_path`, COUNT(DISTINCT `down_user_id`), COUNT(`down_doc_path`)
                    FROM `$TABLETRACK_DOWNLOADS`
                    GROUP BY `down_doc_path`";
    
        echo "<tr><td style=\"padding-left : 40px;padding-right : 40px;\">";  
        $results = getManyResults3Col($sql);
        echo "<table class=\"claroTable\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\" align=\"center\">";
        echo "<tr class=\"headerX\">
                <th>
                &nbsp;$langDocumentsTitleDocumentColumn&nbsp;
                </th>
                <th>
                &nbsp;$langDocumentsTitleUsersColumn&nbsp;
                </th>
                <th>
                &nbsp;$langDocumentsTitleCountColumn&nbsp;
                </th>
            </tr>
            <tbody>";
        if (is_array($results))
        { 
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                    echo "<tr>"; 
                    echo "<td>".$results[$j][0]."</td>";
                    //echo "<td align=\"right\">".$results[$j][1]."</td>";
	            echo "<td align=\"right\"><a href=\"user_access_details.php?cmd=doc&data=".urlencode($results[$j][0])."\">".$results[$j][1]."</a></td>";
                    echo "<td align=\"right\">".$results[$j][2]."</td>";
                    echo"</tr>";
            }
        
        }
        else
        {
            echo "<tr>"; 
            echo "<td colspan=\"3\"><center>".$langNoResult."</center></td>";
            echo"</tr>";
        }
        echo "</tbody></table>";
        echo "</td></tr>";
    }
    else
    {
        $tempView[4] = '1';
        echo "
            <tr>
                    <td valign=\"top\">
                    +&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\" class=\"specialLink\">$langDocumentsAccess</a>
                    </td>
            </tr>
        ";
    }
    
    
    /***************************************************************************
     *              
     *		Exercises
     *
     ***************************************************************************/
    $tempView = $view;
    if($view[5] == '1')
    {
        $tempView[5] = '0';
        echo "
            <tr>
                    <td valign=\"top\">
                    -&nbsp;&nbsp;&nbsp;<b>".$langExercises."</b>&nbsp;&nbsp;&nbsp;<small>[<a href=\"$PHP_SELF?view=".$tempView."\">".$langClose."</a>]</small>
                    </td>
            </tr>
        ";
        
        $sql = "SELECT TEX.`exe_exo_id`, COUNT(DISTINCT TEX.`exe_user_id`), COUNT(TEX.`exe_exo_id`), EX.`titre`
                    FROM `$TABLETRACK_EXERCISES` AS TEX, `$TABLE_QUIZ_TEST` AS EX
                    WHERE TEX.`exe_exo_id` = EX.`id`
                    GROUP BY TEX.`exe_exo_id`";
    
        echo "<tr><td style=\"padding-left : 40px;padding-right : 40px;\">";  
        $results = getManyResultsXCol($sql,4);
        echo "<table class=\"claroTable\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\" align=\"center\">";
        echo "<tr class=\"headerX\">
                <th>
                &nbsp;$langExercicesTitleExerciceColumn&nbsp;
                </th>
                <th>
                &nbsp;$langExerciseUsersAttempts&nbsp;
                </th>
                <th>
                &nbsp;$langExerciseTotalAttempts&nbsp;
                </th>
            </tr>
            <tbody>";
        if (is_array($results))
        { 
            for($j = 0 ; $j < count($results) ; $j++)
            { 
                    echo "<tr>"; 
                    echo "<td><a href=\"exercises_details.php?exo_id=".$results[$j][0]."\">".$results[$j][3]."</a></td>";
                    echo "<td align=\"right\">".$results[$j][1]."</td>";
                    echo "<td align=\"right\">".$results[$j][2]."</td>";
                    echo"</tr>";
            }
        
        }
        else
        {
            echo "<tr>"; 
            echo "<td colspan=\"3\"><center>".$langNoResult."</center></td>";
            echo"</tr>";
        }
        echo "</tbody></table>";
        echo "</td></tr>";
    }
    else
    {
        $tempView[5] = '1';
        echo "
            <tr>
                    <td valign=\"top\">
                    +&nbsp;&nbsp;<a href=\"$PHP_SELF?view=".$tempView."\" class=\"specialLink\">$langExercises</a>
                    </td>
            </tr>
        ";
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
