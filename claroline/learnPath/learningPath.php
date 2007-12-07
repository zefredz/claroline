<?php // $Id$
/**
 * @version  CLAROLINE version 1.6
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 */

/*======================================
       CLAROLINE MAIN
  ======================================*/

  $tlabelReq = 'CLLNP___';
  require '../inc/claro_init_global.inc.php';

   if (! $is_courseAllowed) claro_disp_auth_form();

  $interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);

  $nameTools = $langLearningPath;

  // tables names
  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

  //lib of this tool
  include($includePath."/lib/learnPath.lib.inc.php");
  
  //lib of document tool
  include($includePath."/lib/fileDisplay.lib.php");

  // $_SESSION
  if ( isset($_GET['path_id']) && $_GET['path_id'] > 0)
  {
        $_SESSION['path_id'] = (int) $_GET['path_id'];
  }
  elseif( (!isset($_SESSION['path_id']) || $_SESSION['path_id'] == "") )
  { 
    // if path id not set, redirect user to the home page of learning path
    header("Location: ".$clarolineRepositoryWeb."learnPath/learningPathList.php");
  }
  
   // use viewMode
   claro_set_display_mode_available(true);
   // permissions (only for the viewmode, there is nothing to edit here )
   if ( claro_is_allowed_to_edit() )
   {
     // if the fct return true it means that user is a course manager and than view mode is set to COURSE_ADMIN
   	 header("Location: ".$clarolineRepositoryWeb."learnPath/learningPathAdmin.php?path_id=".$_SESSION['path_id']);
   }

   // main page
   //####################################################################################\\
   //############################## MODULE TABLE LIST PREPARATION ###############################\\
   //####################################################################################\\
   if($_uid)
   {
       $uidCheckString = "AND UMP.`user_id` = ".$_uid;
   }
   else // anonymous
   {
       $uidCheckString = "AND UMP.`user_id` IS NULL ";
   }

   $sql = "SELECT LPM.* , 
                M.*, 
                UMP.`lesson_status`, UMP.`raw`, 
                UMP.`scoreMax`, UMP.`credit`,
                A.`path`
             FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                  `".$TABLEMODULE."` AS M
       LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
               ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
               ".$uidCheckString."
       LEFT JOIN `".$TABLEASSET."` AS A
              ON M.`startAsset_id` = A.`asset_id`
            WHERE LPM.`module_id` = M.`module_id`
              AND LPM.`learnPath_id` = ".$_SESSION['path_id']."
              AND LPM.`visibility` = 'SHOW'
              AND LPM.`module_id` = M.`module_id`
         GROUP BY LPM.`module_id`
         ORDER BY LPM.`rank`";

  $result = claro_sql_query($sql);
  
  $extendedList = array();
  while ($list = mysql_fetch_array($result, MYSQL_ASSOC))
  {
    $extendedList[] = $list;
  }
  
  // build the array of modules     
  // build_element_list return a multi-level array, where children is an array with all nested modules
  // build_display_element_list return an 1-level array where children is the deep of the module
  $flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));
   
  $is_blocked = false;
  $atleastOne = false;
  $moduleNb = 0;
   
  // look for maxDeep
  $maxDeep = 1; // used to compute colspan of <td> cells
  for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
  {
    if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
  }

/*================================================================
                      OUTPUT STARTS HERE
  ================================================================*/  
  
    //header
   include($includePath."/claro_init_header.inc.php");
   
   // display title
   claro_disp_tool_title($nameTools);
   
   //####################################################################################\\
   //##################################### TITLE ########################################\\
   //####################################################################################\\
   nameBox(LEARNINGPATH_, DISPLAY_);
   // and comment !
   commentBox(LEARNINGPATH_, DISPLAY_);
   
   //####################################################################################\\
   //############################## MODULE TABLE HEADER ######################################\\
   //####################################################################################\\
?>
     <br>
     <table class="claroTable" width="100%" border="0" cellspacing="2">
            <tr class="headerX" align="center" valign="top">
              <th colspan="<?php echo $maxDeep+1; ?>"><?php echo $langModule; ?></th>
<?php
        if($_uid)
        {
            echo "<th colspan=\"2\">".$langProgress."</th>";
        }
?>
             </tr>
             <tbody>
   <?php
   
  //####################################################################################\\
  //############################## MODULE TABLE LIST DISPLAY ###################################\\
  //####################################################################################\\
  foreach ($flatElementList as $module)
  {
          if( $module['scoreMax'] > 0 && $module['raw'] > 0 )
          {
               $progress = @round($module['raw']/$module['scoreMax']*100);
          }
          else
          {
                $progress = 0;
          }
          
          if ( $module['contentType'] == CTEXERCISE_ )
          {
             $passExercise = ($module['credit']=="CREDIT");
          }
          else
          {
             $passExercise = false;
          }
          
          if ( $module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0)
          {
             if ( $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
             {
                 $progress = 100;
                 $passExercise = true;
             }
             else
             {
                 $progress = 0;
                 $passExercise = false;
             }
          }

          // display the current module name (and link if allowed)
          
          $spacingString = "";
          for($i = 0; $i < $module['children']; $i++)
            $spacingString .= "<td width=\"5\">&nbsp;</td>";
          $colspan = $maxDeep - $module['children']+1;
          
          echo "<tr align=\"center\"".$style.">\n".$spacingString."<td colspan=\"".$colspan."\" align=\"left\">";
          //-- if chapter head
          if ( $module['contentType'] == CTLABEL_ )
          {
              echo "<b>".htmlspecialchars($module['name'])."</b>";
          }
          //-- if user can access module
          elseif ( !$is_blocked )
          {
              if($module['contentType'] == CTEXERCISE_ ) 
                $moduleImg = "quiz.gif";
              else
                $moduleImg = choose_image(basename($module['path']));
                
              $contentType_alt = selectAlt($module['contentType']);
              echo "<a href=\"module.php?module_id=".$module['module_id']."\">
            <img src=\"".$imgRepositoryWeb.$moduleImg."\" alt=\"".$contentType_alt."\" border=\"0\">"
                           .htmlspecialchars($module['name'])."</a>";
              // a module ALLOW access to the following modules if
              // document module : credit == CREDIT || lesson_status == 'completed'
              // exercise module : credit == CREDIT || lesson_status == 'passed'
              // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

              if( $module['lock'] == 'CLOSE' && $module['credit'] != 'CREDIT' && $module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED' && !$passExercise )
              {
                    if($_uid)
                    {
                        $is_blocked = true; // following modules will be unlinked
                    }
                    else // anonymous : don't display the modules that are unreachable
                    {
                        $atleastOne = true; // trick to avoid having the "no modules" msg to be displayed
                        break ;
                    }
              }
          }
          //-- user is blocked by previous module, don't display link
          else
          {
                if($module['contentType'] == CTEXERCISE_ ) 
                  $moduleImg = "quiz.gif";
                else
                  $moduleImg = choose_image(basename($module['path']));
                echo "<img src='".$imgRepositoryWeb.$moduleImg."' alt='".$contentType_alt."' border=\"0\">"
                            .htmlspecialchars($module['name']);

          }
          echo "</td>";

          if($_uid && ($module['contentType'] != CTLABEL_) )
          {
                // display the progress value for current module
                echo "<td align=\"right\">".claro_disp_progress_bar ($progress, 1)."</td>";
                echo "<td align=\"left\">
                       <small>&nbsp;".$progress."%</small>
                      </td>";
          }
          elseif($module['contentType'] == CTLABEL_)
          {
            echo "<td colspan=\"2\">&nbsp;</td>";
          }
          
          if ($progress > 0)
          {
            $globalProg =  $globalProg+$progress;
          }
          
          if($module['contentType'] != CTLABEL_) 
              $moduleNb++; // increment number of modules used to compute global progression except if the module is a title
           
          echo "\n</tr>\n";
          $atleastOne = true;
  }
  echo "</tbody>\n<tfoot>\n";
  
  if ($atleastOne == false)
  {
          echo "<tr><td align=\"center\" colspan=\"3\">".$langNoModule."</td></tr>";
  }
  elseif($_uid && $moduleNb > 0)
  {
            // add a blank line between module progression and global progression
            echo "<tr><td colspan=\"".($maxDeep+3)."\">&nbsp;</td></tr>";
            // display progression
            echo "<tr>".
                "<td align=\"right\" colspan=\"".($maxDeep+1)."\">".$langGlobalProgress."</td>".
                "<td align=\"right\">".
                claro_disp_progress_bar(round($globalProg / ($moduleNb) ), 1 ).
		"</td>".
                "<td align=\"left\">
                    <small>&nbsp;".round($globalProg / ($moduleNb) ) ."%</small></td>
                  </td>";
  }
  echo "</tfoot>\n</table>";

   //####################################################################################\\
   //################################### FOOTER #########################################\\
   //####################################################################################\\
   include($includePath."/claro_init_footer.inc.php");
?>
