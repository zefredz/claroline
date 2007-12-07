<?php // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.5.*                                              |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors: Piraux Sébastien <pir@cerdecam.be>                          |
  |          Lederer Guillaume <led@cerdecam.be>                         |
  +----------------------------------------------------------------------+

  DESCRIPTION:
  ****

*/

/*======================================
       CLAROLINE MAIN
  ======================================*/

  $langFile = "learnPath";
  $tlabelReq = 'CLLNP___';
  require '../inc/claro_init_global.inc.php';
  
  // if there is an auth information missing redirect to the first page of lp tool 
  // this page will do the necessary to auth the user, 
  // when leaving a course all the LP sessions infos are cleared so we use this trick to avoid other errors
  if ( ! $_cid) header("Location:./learningPathList.php");
  if ( ! $is_courseAllowed) header("Location:./learningPathList.php");
  
  $htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\" $langAreYouSureToDelete \"+ name + \" ?\"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

  $interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);

  $nameTools = $langLearningPath;
  //header
  include($includePath."/claro_init_header.inc.php");


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
  if ( isset($_GET['path_id']) && $_GET['path_id'] != "")
  {
        $_SESSION['path_id'] = $_GET['path_id'];
  }
  elseif( (!isset($_SESSION['path_id']) || $_SESSION['path_id'] == "") )
  { 
    // if path id not set, redirect user to the home page of learning path
    header("Location: ".$clarolineRepositoryWeb."learnPath/learningPathList.php");
  }
  

  // display title
  claro_disp_tool_title($nameTools);
  
   // main page

   if ($is_courseAdmin )
   {
        // the user is a teacher that access this in student mode, so
        // set asStudent to true so he'll be consider like a student in the following steps
        $_SESSION['asStudent'] = 1;
   }
   //####################################################################################\\
   //##################################### TITLE ########################################\\
   //####################################################################################\\
   nameBox(LEARNINGPATH_, DISPLAY_);
   // and comment !
   commentBox(LEARNINGPATH_, DISPLAY_);

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
  $flatElementList = build_display_element_list(build_element_list($extendedList));
   
  $is_blocked = false;
  $atleastOne = false;
  $moduleNb = 0;
   
  // look for maxDeep
  $maxDeep = 1; // used to compute colspan of <td> cells
  for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
  {
    if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
  }
  
  
   //####################################################################################\\
   //############################## MODULE TABLE HEADER ######################################\\
   //####################################################################################\\
?>
     <br>
     <table class="claroTable" width="100%" border="0" cellspacing="2">
            <tr class="headerX" align="center" valign="top">
              <th colspan="<?= $maxDeep+1; ?>"><?= $langModule; ?></th>
<?php
        if($_uid)
        {
            echo "<th colspan=\"2\">".$langProgress."</th>";
        }
?>
             </tr>
             <tbody>
   <?PHP
   
  //####################################################################################\\
  //############################## MODULE TABLE LIST DISPLAY ###################################\\
  //####################################################################################\\
  foreach ($flatElementList as $module)
  {
          if( $module['scoreMax'] > 0 )
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
              echo "<b>".$module['name']."</b>";
          }
          //-- if user can access module
          elseif ( !$is_blocked )
          {
              if($module['contentType'] == CTEXERCISE_ ) 
                $moduleImg = "quiz.gif";
              else
                $moduleImg = choose_image(basename($module['path']));
                
              $contentType_alt = selectAlt($module['contentType']);
              echo "<a href=\"module.php?module_id=".$module['module_id']."&asStudent=1\">
            <img src=\"".$clarolineRepositoryWeb."img/".$moduleImg."\" alt=\"".$contentType_alt."\" border=\"0\">"
                           .$module['name']."</a>";
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
                echo "<img src='".$clarolineRepositoryWeb."img/".$moduleImg."' alt='".$contentType_alt."' border=\"0\">"
                            .$module['name'];

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
