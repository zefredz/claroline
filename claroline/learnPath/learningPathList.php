<?php  // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 *
 * DESCRIPTION:
 * ************
 * This file display the list of all learning paths availables for the
 * course.
 *
 *  Display :
 *  - Name of tool
 *  - Introduction text for learning paths
 *  - (admin of course) link to create new empty learning path
 *  - (admin of course) link to import (upload) a learning path
 *  - list of available learning paths
 *    - (student) only visible learning paths
 *    - (student) the % of progression into each learning path
 *    - (admin of course) all learning paths with
 *       - modify, delete, statistics, visibility and order, options
 */

/*======================================
       CLAROLINE MAIN
  ======================================*/

$tlabelReq = 'CLLNP';
require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];

$TABLELEARNPATH         = $tbl_lp_learnPath;
$TABLEMODULE            = $tbl_lp_module;
$TABLELEARNPATHMODULE   = $tbl_lp_rel_learnPath_module;
$TABLEASSET             = $tbl_lp_asset;
$TABLEUSERMODULEPROGRESS= $tbl_lp_user_module_progress;

//lib of this tool
include_once ($includePath . '/lib/learnPath.lib.inc.php');

//lib needed to delete packages
include_once ($includePath . '/lib/fileManage.lib.php');

// statistics
event_access_tool($_tid, $_courseTool['label']);

$htmlHeadXtra[] =
          '<script type="text/javascript">
          function confirmation (name)
          {
              if (confirm("'. clean_str_for_javascript(get_lang('Modules of this path will still be available in the pool of modules'))
							. '\n'
							. clean_str_for_javascript(get_lang('Are you sure to delete') . ' ?' )
							. '\n'
							. '" + name))
                  {return true;}
              else
                  {return false;}
          }
          </script>' . "\n";
$htmlHeadXtra[] =
         '<script type="text/javascript">
          function scormConfirmation (name)
          {
              if (confirm("'. clean_str_for_javascript(get_block('blockConfirmDeleteScorm')) .  '\n" + name ))
                  {return true;}
              else
                  {return false;}
          }
          </script>' . "\n";

$nameTools = get_lang('Learning path list');

$cmd = ( isset($_REQUEST['cmd']) )? $_REQUEST['cmd'] : '';

if ( $cmd == 'export' )
{
      include ('include/scormExport.inc.php');
      $scorm = new ScormExport($_REQUEST['path_id']);
      if ( !$scorm->export() )
      {
          $dialogBox = '<b>'.get_lang('Error exporting SCORM package').'</b><br />'."\n".'<ul>'."\n";
          foreach( $scorm->getError() as $error)
          {
              $dialogBox .= '<li>' . $error . '</li>'."\n";
          }
          $dialogBox .= '<ul>'."\n";
      }
} // endif $cmd == export

// use viewMode
claro_set_display_mode_available(true);



// main page
$is_AllowedToEdit = claro_is_allowed_to_edit();
$lpUid = $_uid;

// display introduction
$moduleId = $_tid; // Id of the Learning Path introduction Area
$helpAddIntroText = get_block('blockIntroLearningPath');

// execution of commands
switch ( $cmd )
{
    // DELETE COMMAND
    case "delete" :

            // delete learning path
            // have to delete also learningPath_module using this learningPath
            // The first multiple-table delete format is supported starting from MySQL 4.0.0. The second multiple-table delete format is supported starting from MySQL 4.0.2.
            /*  this query should work with mysql > 4
            $sql = "DELETE
                      FROM `".$TABLELEARNPATHMODULE."`,
                           `".$TABLEUSERMODULEPROGRESS."`,
                           `".$TABLELEARNPATH."`
                      WHERE `".$TABLELEARNPATHMODULE."`.`learnPath_module_id` = `".$TABLEUSERMODULEPROGRESS."`.`learnPath_module_id`
                        AND `".$TABLELEARNPATHMODULE."`.`learnPath_id` = `".$TABLELEARNPATH."`.`learnPath_id`
                        AND `".$TABLELEARNPATH."`.`learnPath_id` = ".$_GET['path_id'] ;
            */
            // so we use a multiple query method


            // in case of a learning path made by SCORM, we completely remove files and use in others path of the imported package
            // First we save the module_id of the SCORM modules in a table in case of a SCORM imported package

            if (is_dir($coursesRepositorySys.$_course['path']."/scormPackages/path_".$_GET['del_path_id']))
            {
                $findsql = "SELECT M.`module_id`
                            FROM  `".$TABLELEARNPATHMODULE."` AS LPM,
                                      `".$TABLEMODULE."` AS M
                            WHERE LPM.`learnPath_id` = ". (int)$_GET['del_path_id']."
                              AND
                                    ( M.`contentType` = '".CTSCORM_."'
                                      OR
                                      M.`contentType` = '".CTLABEL_."'
                                    )
                              AND LPM.`module_id` = M.`module_id`
                                ";
                $findResult =claro_sql_query($findsql);

                // Delete the startAssets

                $delAssetSql = "DELETE
                                FROM `".$TABLEASSET."`
                                WHERE 1=0
                               ";

                while ($delList = mysql_fetch_array($findResult))
                {
                    $delAssetSql .= " OR `module_id`=". (int)$delList['module_id'];
                }

                claro_sql_query($delAssetSql);

                //echo $delAssetSql."<br>";

                // DELETE the SCORM modules

                $delModuleSql = "DELETE
                                 FROM `".$TABLEMODULE."`
                                 WHERE (`contentType` = '".CTSCORM_."' OR `contentType` = '".CTLABEL_."')
                                 AND (1=0
                                 ";

                if (mysql_num_rows($findResult)>0)
                {
                    mysql_data_seek($findResult,0);
                }

                while ($delList = mysql_fetch_array($findResult))
                {
                    $delModuleSql .= " OR `module_id`=". (int)$delList['module_id'];
                }
                $delModuleSql .= ")";

                //echo $delModuleSql."<br>";

                claro_sql_query($delModuleSql);

                // DELETE the directory containing the package and all its content
                $real = realpath($coursesRepositorySys.$_course['path']."/scormPackages/path_".$_GET['del_path_id']);
                claro_delete_file($real);

            }   // end of dealing with the case of a scorm learning path.
            else
            {
                $findsql = "SELECT M.`module_id`
                                 FROM  `".$TABLELEARNPATHMODULE."` AS LPM,
                                      `".$TABLEMODULE."` AS M
                                 WHERE LPM.`learnPath_id` = ". (int)$_GET['del_path_id']."
                                 AND M.`contentType` = '".CTLABEL_."'
                                 AND LPM.`module_id` = M.`module_id`
                                 ";
                //echo $findsql;
                $findResult =claro_sql_query($findsql);
                // delete labels of non scorm learning path
                $delLabelModuleSql = "DELETE
                                     FROM `".$TABLEMODULE."`
                                     WHERE 1=0
                                  ";

                while ($delList = mysql_fetch_array($findResult))
                {
                    $delLabelModuleSql .= " OR `module_id`=". (int)$delList['module_id'];
                }
                //echo $delLabelModuleSql;
                $query = claro_sql_query($delLabelModuleSql);
            }

            // delete everything for this path (common to normal and scorm paths) concerning modules, progression and path

            // delete all user progression
            $sql1 = "DELETE
                       FROM `".$TABLEUSERMODULEPROGRESS."`
                       WHERE `learnPath_id` = ". (int)$_GET['del_path_id'];
            $query = claro_sql_query($sql1);

            // delete all relation between modules and the deleted learning path
            $sql2 = "DELETE
                       FROM `".$TABLELEARNPATHMODULE."`
                       WHERE `learnPath_id` = ". (int)$_GET['del_path_id'];
            $query = claro_sql_query($sql2);

            // delete the learning path
            $sql3 = "DELETE
                          FROM `".$TABLELEARNPATH."`
                          WHERE `learnPath_id` = ". (int)$_GET['del_path_id'] ;

            $query = claro_sql_query($sql3);

            // notify the event manager with the deletion
            $eventNotifier->notifyCourseEvent("learningpath_deleted",$_cid, $_tid, $_GET['del_path_id'], $_gid, "0");
            break;

      // ACCESSIBILITY COMMAND
      case "mkBlock" :
      case "mkUnblock" :
            $cmd == "mkBlock" ? $blocking = 'CLOSE' : $blocking = 'OPEN';
            $sql = "UPDATE `".$TABLELEARNPATH."`
                    SET `lock` = '$blocking'
                    WHERE `learnPath_id` = ". (int)$_GET['cmdid']."
                      AND `lock` != '$blocking'";
            $query = claro_sql_query ($sql);
            break;

      // VISIBILITY COMMAND
      case "mkVisibl" :
      case "mkInvisibl" :
            $cmd == "mkVisibl" ? $visibility = 'SHOW' : $visibility = 'HIDE';
            $sql = "UPDATE `".$TABLELEARNPATH."`
                       SET `visibility` = '$visibility'
                     WHERE `learnPath_id` = ". (int)$_GET['visibility_path_id']."
                       AND `visibility` != '$visibility'";
            $query = claro_sql_query ($sql);

            //notify the event manager with the event of new visibility

            if ($visibility == 'SHOW')
            {
                $eventNotifier->notifyCourseEvent("learningpath_visible",$_cid, $_tid, $_GET['visibility_path_id'], $_gid, "0");
            }
            else
            {
                $eventNotifier->notifyCourseEvent("learningpath_invisible",$_cid, $_tid, $_GET['visibility_path_id'], $_gid, "0");
            }

            break;

      // ORDER COMMAND
      case "moveUp" :
            $thisLearningPathId = $_GET['move_path_id'];
            $sortDirection = "DESC";
            break;

      case "moveDown" :
            $thisLearningPathId = $_GET['move_path_id'];
            $sortDirection = "ASC";
            break;

      case "changeOrder" :
            // $sortedTab = new Order => id learning path
            $sortedTab = setOrderTab( $_POST['id2sort'] );
            if ($sortedTab)
            {
               foreach ( $sortedTab as $order => $LP_id )
               {
                    // `order` is set to $order+1 only for display later
                    $sql = "UPDATE `".$TABLELEARNPATH."`
                               SET `rank` = ".($order+1)."
                             WHERE `learnPath_id` = ". (int)$LP_id;
                    claro_sql_query($sql);
               }
            }
            break;

      // CREATE COMMAND
      case "create" :
            // create form sent
            if( isset($_POST["newPathName"]) && $_POST["newPathName"] != "")
            {

                // check if name already exists
                $sql = "SELECT `name`
                         FROM `".$TABLELEARNPATH."`
                        WHERE `name` = '". addslashes($_POST['newPathName']) ."'";
                $query = claro_sql_query($sql);
                $num = mysql_numrows($query);
                if($num == 0 ) // "name" doesn't already exist
                {
                    // determine the default order of this Learning path
                    $result = claro_sql_query("SELECT MAX(`rank`)
                                               FROM `".$TABLELEARNPATH."`");

                    list($orderMax) = mysql_fetch_row($result);
                    $order = $orderMax + 1;

                    // create new learning path
                    $sql = "INSERT
                              INTO `".$TABLELEARNPATH."`
                                     (`name`, `comment`, `rank`)
                              VALUES ('". addslashes($_POST['newPathName']) ."','" . addslashes(trim($_POST['newComment']))."',".(int)$order.")";
                    //echo $sql;
                    $lp_id = claro_sql_query_insert_id($sql);

                    // notify the creation to eventmanager
                    $eventNotifier->notifyCourseEvent("learningpath_created",$_cid, $_tid, $lp_id, $_gid, "0");
                }
                else
                {
                    // display error message
                    $dialogBox = get_lang('Error : Name already exists in the learning path or in the module pool');
                }
            }
            else  // create form requested
            {
                $dialogBox = "\n\n"
                           . '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n"
                           . '<h4>' . get_lang('Create a new learning path') . '</h4>' . "\n"
                           . '<label for="newPathName">' . get_lang('Title') . ' : </label>' . "\n"
                           . '<br />' . "\n"
                           . '<input type="text" name="newPathName" id="newPathName" maxlength="255">' . "\n"
                           . '<br />' . "\n"
                           . '<br />' . "\n"
                           . '<label for="newComment">' . get_lang('Comment') . ' : </label>' . "\n"
                           . '<br />' . "\n"
                           . '<textarea id="newComment" name="newComment" rows="2" cols="50">'
                           . '</textarea>' . "\n"
                           . '<br /><br />' . "\n"
                           . '<input type="hidden" name="cmd" value="create">' . "\n"
                           . '<input type="submit" value="' . get_lang('Ok') . '">&nbsp;' . "\n"
                           . claro_html_button('learningPathList.php', get_lang('Cancel'))
                           . '</form>' . "\n"
                           ;
            }
            break;
}

// IF ORDER COMMAND RECEIVED
// CHANGE ORDER
if (isset($sortDirection) && $sortDirection)
{
    $sql = "SELECT `learnPath_id`, `rank`
            FROM `".$TABLELEARNPATH."`
            ORDER BY `rank` $sortDirection";
    $result = claro_sql_query($sql);

     // LP = learningPath
     while (list ($LPId, $LPOrder) = mysql_fetch_row($result))
     {
        // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
        //          COMMIT ORDER SWAP ON THE DB

        if (isset($thisLPOrderFound)&&$thisLPOrderFound == true)
        {
            $nextLPId = $LPId;
            $nextLPOrder = $LPOrder;

            // move 1 to a temporary rank
            $sql = "UPDATE `".$TABLELEARNPATH."`
                    SET `rank` = \"-1337\"
                    WHERE `learnPath_id` =  \"" . (int)$thisLearningPathId . "\"";
            claro_sql_query($sql);

             // move 2 to the previous rank of 1
             $sql = "UPDATE `".$TABLELEARNPATH."`
                     SET `rank` = \"" . (int)$thisLPOrder . "\"
                     WHERE `learnPath_id` =  \"" . (int)$nextLPId . "\"";
             claro_sql_query($sql);

             // move 1 to previous rank of 2
             $sql = "UPDATE `".$TABLELEARNPATH."`
                             SET `rank` = \"" . (int)$nextLPOrder . "\"
                           WHERE `learnPath_id` =  \"" . (int)$thisLearningPathId . "\"";
             claro_sql_query($sql);

             break;
         }

         // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT
         if ($LPId == $thisLearningPathId)
         {
             $thisLPOrder = $LPOrder;
             $thisLPOrderFound = true;
         }
     }
}

// DISPLAY

include $includePath . '/claro_init_header.inc.php';
echo claro_html_tool_title($nameTools);

if (isset($dialogBox))
{
  echo claro_html_message_box($dialogBox);
}

if($is_AllowedToEdit)
{
    // Display links to create and import a learning path
?>
      <p>
      <a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF'] ?>?cmd=create"><?php echo get_lang('Create a new learning path'); ?></a> |
      <a class="claroCmd" href="importLearningPath.php"><?php echo get_lang('Import a learning path'); ?></a> |
      <a class="claroCmd" href="modules_pool.php"><?php echo get_lang('Pool of modules') ?></a> |
      <a class="claroCmd" href="<?php echo $clarolineRepositoryWeb; ?>tracking/learnPath_detailsAllPath.php"><?php echo get_lang('Learning paths tracking'); ?></a>
      </p>
<?php
}

// Display list of available training paths

/*
 This is for dealing with the block in the sequence of learning path,  the idea is to make only one request to get the credit
 of last module of learning paths to know if the rest of the sequence mut be blocked or not, does NOT work yet ;) ...

 $sql="SELECT LPM.`learnPath_module_id` AS LPMID, LPM.`learnpath_id`, MAX(`rank`) AS M, UMP.`credit` AS UMPC
              FROM `".$TABLELEARNPATHMODULE."` AS LPM
              RIGHT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
              ON LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
              WHERE `user_id` = ".$lpUid."
              GROUP BY LPM.`learnpath_id`
              ";


 echo $sql."<br>";
 $resultB = claro_sql_query($sql);

 echo mysql_error();

 while ($listB = mysql_fetch_array($resultB))
        {
        echo "LPMID : ".$listB['LPMID']." rank : ".$listB['M']." LPID : ".$listB['learnpath_id']." credit : ".$listB['UMPC']."<br>";
        }

 $resultB = claro_sql_query($sql);
 */

if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid); // get date for notified "as new" paths

echo "<table class=\"claroTable emphaseLine\" width=\"100%\" border=\"0\" cellspacing=\"2\">
 <thead>
 <tr class=\"headerX\" align=\"center\" valign=\"top\">
  <th>".get_lang('Learning path')."</th>";

if($is_AllowedToEdit)
{
     // Titles for teachers
     echo "<th>".get_lang('Modify')."</th>"
            ."<th>".get_lang('Delete')."</th>"
            ."<th>".get_lang('Block')."</th>"
            ."<th>".get_lang('Visibility')."</th>"
            ."<th colspan=\"2\">".get_lang('Order')."</th>"
            ."<th>".get_lang('Export')."</th>"
            ."<th>".get_lang('Tracking')."</th>";
}
elseif($lpUid)
{
   // display progression only if user is not teacher && not anonymous
   echo "<th colspan=\"2\">".get_lang('Progress')."</th>";
}
// close title line
echo "</tr>\n</thead>\n<tbody>";

// display invisible learning paths only if user is courseAdmin
if ($is_AllowedToEdit)
{
    $visibility = "";
}
else
{
    $visibility = " AND LP.`visibility` = 'SHOW' ";
}
// check if user is anonymous
if($lpUid)
{
    $uidCheckString = "AND UMP.`user_id` = ". (int)$lpUid;
}
else // anonymous
{
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

// list available learning paths
$sql = "SELECT LP.* , MIN(UMP.`raw`) AS minRaw, LP.`lock`
           FROM `".$TABLELEARNPATH."` AS LP
     LEFT JOIN `".$TABLELEARNPATHMODULE."` AS LPM
            ON LPM.`learnPath_id` = LP.`learnPath_id`
     LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
            ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
            ".$uidCheckString."
         WHERE 1=1
             ".$visibility."
      GROUP BY LP.`learnPath_id`
      ORDER BY LP.`rank`";

$result = claro_sql_query($sql);

// used to know if the down array (for order) has to be displayed
$LPNumber = mysql_num_rows($result);
$iterator = 1;

$is_blocked = false;
while ( $list = mysql_fetch_array($result) ) // while ... learning path list
{
    //modify style if the file is recently added since last login

    if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $list['learnPath_id']))
    {
        $classItem=' hot';
    }
    else // otherwise just display its name normally
    {
        $classItem='';
    }


    if ( $list['visibility'] == 'HIDE' )
    {
        if ($is_AllowedToEdit)
        {
            $style=" class=\"invisible\"";
        }
        else
        {
            continue; // skip the display of this file
        }
    }
    else
    {
        $style="";
    }

    echo "<tr align=\"center\"".$style.">";

    //Display current learning path name

    if ( !$is_blocked )
    {
        echo "<td align=\"left\"><a class=\"item".$classItem."\" href=\"learningPath.php?path_id="
            .$list['learnPath_id']."\"><img src=\"".$imgRepositoryWeb."learnpath.gif\" alt=\"\"
            border=\"0\" />  ".htmlspecialchars($list['name'])."</a></td>";

        /*
        if( $list['lock'] == 'CLOSE' && ( $list['minRaw'] == -1 || $list['minRaw'] == "" ) )
        {
            if($lpUid)
            {
                if ( !$is_AllowedToEdit )
                {
                    $is_blocked = true;
                } // never blocked if allowed to edit
            }
            else // anonymous : don't display the modules that are unreachable
            {
                break ;
            }
        } */

        // --------------TEST IF FOLLOWING PATH MUST BE BLOCKED------------------
        // ---------------------(MUST BE OPTIMIZED)------------------------------

        // step 1. find last visible module of the current learning path in DB

        $blocksql = "SELECT `learnPath_module_id`
                     FROM `".$TABLELEARNPATHMODULE."`
                     WHERE `learnPath_id`=". (int)$list['learnPath_id']."
                     AND `visibility` = \"SHOW\"
                     ORDER BY `rank` DESC
                     LIMIT 1
                    ";

        //echo $blocksql;

        $resultblock = claro_sql_query($blocksql);

        // step 2. see if there is a user progression in db concerning this module of the current learning path

        $number = mysql_num_rows($resultblock);
        if ($number != 0)
        {
            $listblock = mysql_fetch_array($resultblock);
            $blocksql2 = "SELECT `credit`
                          FROM `".$TABLEUSERMODULEPROGRESS."`
                          WHERE `learnPath_module_id`=". (int)$listblock['learnPath_module_id']."
                          AND `user_id`='". (int)$lpUid."'
                         ";

            $resultblock2 = claro_sql_query($blocksql2);
            $moduleNumber = mysql_num_rows($resultblock2);
        }
        else
        {
            //echo "no module in this path!";
            $moduleNumber = 0;
        }

        //2.1 no progression found in DB

        if (($moduleNumber == 0)  && ($list['lock'] == 'CLOSE'))
        {
            //must block next path because last module of this path never tried!

            if($lpUid)
            {
                if ( !$is_AllowedToEdit )
                {
                    $is_blocked = true;
                } // never blocked if allowed to edit
            }
            else // anonymous : don't display the modules that are unreachable
            {
                $iterator++; // trick to avoid having the "no modules" msg to be displayed
                break;
            }
        }

        //2.2. deal with progression found in DB if at leats one module in this path

        if ($moduleNumber!=0)
        {
            $listblock2 = mysql_fetch_array($resultblock2);

            if (($listblock2['credit']=="NO-CREDIT") && ($list['lock'] == 'CLOSE'))
            {
                //must block next path because last module of this path not credited yet!
                if($lpUid)
                {
                    if ( !$is_AllowedToEdit )
                    {
                        $is_blocked = true;
                    } // never blocked if allowed to edit
                }
                else // anonymous : don't display the modules that are unreachable
                {
                    break ;
                }
            }
        }

        //----------------------------------------------------------------------


        /*   This is for dealing with the block in the sequence of learning path,  the idea is to make only one request to get the credit
             of last module of learning paths to know if the rest of the sequence mut be blocked or not, does NOT work yet ;) ...

        if (mysql_num_rows($resultB) != 0) {mysql_data_seek($resultB,0);}

        while ($listB = mysql_fetch_array($resultB))
        {
            echo  "lp_id listB: ".$listB['learnpath_id']." lp_id list: ".$list['learnPath_id']." creditUMP: ".$listB['UMPC']." Lplock: ".$list['lock']."<br>";

            if (($listB['learnpath_id']==$list['learnPath_id']) && ($listB['UMPC']=="NO-CREDIT") && ($list['lock'] == "CLOSE"))
            {
                echo "ok";
                if($lpUid)
                {
                    if ( !$is_AllowedToEdit )
                    {
                        echo "on va bloquer pour LPMID : ".$listB['LPMID'];
                        $is_blocked = true;
                    } // never blocked if allowed to edit
                }
                else // anonymous : don't display the modules that are unreachable
                {
                    break ;
                }
            }
        }

        //must also block if no usermoduleprogress exists in DB for this user.

        $LPMNumberB = mysql_num_rows($resultB);
        if (($LPMNumberB == 0) && ($list['lock'] == "CLOSE"))
        {
            echo "ok2";
            if($lpUid)
            {
                if ( !$is_AllowedToEdit )
                {
                    echo "on va bloquer pour LPMID : ".$listB['LPMID'];
                    $is_blocked = true;
                } // never blocked if allowed to edit
            }
            else // anonymous : don't display the modules that are unreachable
            {
                break ;
            }
        }

       */
       //------------------------------------------------------------------------

    }
    else   //else of !$is_blocked condition , we have already been blocked before, so we continue beeing blocked : we don't display any links to next paths any longer
    {
        echo "<td align=\"left\"> <img src=\"".$imgRepositoryWeb."learnpath.gif\" alt=\"\"
                    border=\"0\" /> ".$list['name'].$list['minRaw']."</td>\n";
    }

    // DISPLAY ADMIN LINK-----------------------------------------------------------

    if($is_AllowedToEdit)
    {
        // 5 administration columns

        // Modify command / go to other page
        echo "<td>\n",
             "<a href=\"learningPathAdmin.php?path_id=".$list['learnPath_id']."\">\n",
             "<img src=\"".$imgRepositoryWeb."edit.gif\" border=\"0\" alt=\"".get_lang('Modify')."\" />\n",
             "</a>\n",
             "</td>\n";

        // DELETE link
        $real = realpath($coursesRepositorySys.$_course['path']."/scormPackages/path_".$list['learnPath_id']);

        // check if the learning path is of a Scorm import package and add right popup:

        if (is_dir($real))
        {
            echo  "<td>\n",
                  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=delete&del_path_id=".$list['learnPath_id']."\" ",
                  "onClick=\"return scormConfirmation('",clean_str_for_javascript($list['name']),"');\">\n",
                  "<img src=\"".$imgRepositoryWeb."delete.gif\" border=\"0\" alt=\"".get_lang('Delete')."\" />\n",
                  "</a>\n",
                  "</td>\n";

        }
        else
        {
            echo  "<td>\n",
                  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=delete&del_path_id=".$list['learnPath_id']."\" ",
                  "onClick=\"return confirmation('",clean_str_for_javascript($list['name']),"');\">\n",
                  "<img src=\"".$imgRepositoryWeb."delete.gif\" border=\"0\" alt=\"".get_lang('Delete')."\" />\n",
                  "</a>\n",
                  "</td>\n";
        }

        // LOCK link

        echo "<td>";

        if ( $list['lock'] == 'OPEN')
        {
            echo  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=mkBlock&cmdid=".$list['learnPath_id']."\">\n",
                  "<img src=\"".$imgRepositoryWeb."unblock.gif\" alt=\"".get_lang('Block')."\" border=\"0\">\n",
                  "</a>\n";
        }
        else
        {
            echo  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=mkUnblock&cmdid=".$list['learnPath_id']."\">\n",
                  "<img src=\"".$imgRepositoryWeb."block.gif\" alt=\"" . get_lang('Unblock') . "\" border=\"0\">\n",
                  "</a>\n";
        }
        echo  "</td>\n";

        // VISIBILITY link

        echo  "<td>\n";

        if ( $list['visibility'] == 'HIDE')
        {
            echo  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=mkVisibl&visibility_path_id=".$list['learnPath_id']."\">\n",
                  "<img src=\"".$imgRepositoryWeb."invisible.gif\" alt=\"" . get_lang('Make visible') . "\" border=\"0\" />\n",
                  "</a>";
        }
        else
        {
            if ($list['lock']=='CLOSE')
            {
                $onclick = "onClick=\"return confirm('" . clean_str_for_javascript(get_block('blockConfirmBlockingPathMadeInvisible')) . "');\"";
            }
            else
            {
                $onclick = "";
            }

            echo "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=mkInvisibl&visibility_path_id=".$list['learnPath_id']."\" ",$onclick, " >\n",
                 "<img src=\"".$imgRepositoryWeb."visible.gif\" alt=\"".get_lang('Make invisible')."\" border=\"0\" />\n",
                 "</a>\n";
        }
        echo  "</td>\n";

        // ORDER links

        // DISPLAY MOVE UP COMMAND only if it is not the top learning path
        if ($iterator != 1)
        {
            echo  "<td>\n",
                  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=moveUp&move_path_id=".$list['learnPath_id']."\">\n",
                  "<img src=\"".$imgRepositoryWeb."up.gif\" alt=\"" . get_lang('Move up') . "\" border=\"0\" />\n",
                  "</a>\n",
                  "</td>\n";
        }
        else
        {
            echo "<td>&nbsp;</td>\n";
        }

        // DISPLAY MOVE DOWN COMMAND only if it is not the bottom learning path
        if($iterator < $LPNumber)
        {
            echo  "<td>\n",
                  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=moveDown&move_path_id=".$list['learnPath_id']."\">\n",
                  "<img src=\"".$imgRepositoryWeb."down.gif\" alt=\"".get_lang('Move down')."\" border=\"0\" />\n",
                  "</a>\n",
                  "</td>\n";
        }
        else
        {
            echo "<td>&nbsp;</td>\n";
        }

        // EXPORT links
        echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?cmd=export&amp;path_id=' . $list['learnPath_id'] . '" >'
            .'<img src="' . $clarolineRepositoryWeb . 'img/export.gif" alt="' . get_lang('Export') . '" border="0"></a></td>' . "\n";

        // statistics links
        echo "<td>\n
          <a href=\"".$clarolineRepositoryWeb."tracking/learnPath_details.php?path_id=".$list['learnPath_id']."\">
          <img src=\"".$imgRepositoryWeb."statistics.gif\" border=\"0\" alt=\"".get_lang('Tracking')."\" />
          </a>
          </td>\n";
    }
    elseif($lpUid)
    {
        // % progress
        $prog = get_learnPath_progress($list['learnPath_id'], $lpUid);
        if (!isset($globalprog)) $globalprog = 0;
        if ($prog >= 0)
        {
            $globalprog += $prog;
        }
        echo '<td align="right">'
        .    claro_html_progress_bar($prog, 1)
        .    '</td>' . "\n"
        .    '<td align="left">'
        .    '<small>' . $prog . '% </small>'
        .    '</td>'
        ;
    }
    echo "</tr>";
    $iterator++;

} // end while

echo "</tbody>\n<tfoot>";

if( $iterator == 1 )
{
      echo "<tr><td align=\"center\" colspan=\"8\">".get_lang('No learning path')."</td></tr>";
}
elseif (!$is_courseAdmin && $iterator != 1 && $lpUid)
{
    // add a blank line between module progression and global progression
    echo "<tr><td colspan=\"3\">&nbsp;</td></tr>";
    $total = round($globalprog/($iterator-1));
    echo "<tr>
          <td align =\"right\">
          ".get_lang('Course progression')." :
          </td>
          <td align=\"right\" >".
          claro_html_progress_bar($total, 1).
          "</td>
          <td align=\"left\">
          <small> ".$total."% </small>
          </td>
          </tr>
          ";
}
echo "</tfoot>\n";
echo "</table>\n";

// footer

include($includePath."/claro_init_footer.inc.php");

?>
