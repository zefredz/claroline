<?php // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.5.*
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
  ***********

  This file is available only if is course admin

  It allow course admin to :
  - change learning path name
  - change learning path comment
  - links to
    - create empty module
    - use document as module
    - use exercice as module
    - re-use a module of the same course
    - import (upload) a module
    - use a module from another course
  - remove modules from learning path (it doesn't delete it ! )
  - change locking , visibility, order
  - access to config page of modules in this learning path

*/

/*======================================
       CLAROLINE MAIN
  ======================================*/

  $langFile = "learnPath";

  $tlabelReq = 'CLLNP___';
  require '../inc/claro_init_global.inc.php';

  $htmlHeadXtra[] =
         "<style type=text/css>
         <!--
         .comment { margin-left: 30px}
         .invisible {color: #999999}
         .invisible a {color: #999999}
         .invisible a:visited {color: #999999}
         -->
         </style>";
  $htmlHeadXtra[] =
            "<script>
            function confirmation (txt)
            {
                if (confirm(txt))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

  $htmlHeadXtra[] =
           "<script>
            function scormConfirmation (name)
            {
                if (confirm(\" $langAreYouSureToDeleteScormModule \"+ name + \" ?\"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

  $interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);

  $nameTools = $langLearningPathAdmin;
  $QUERY_STRING=''; // used forthe breadcrumb 
                  // when one need to add a parameter after the filename
  //header
  include($includePath."/claro_init_header.inc.php");

  //lib of document tool
  include($includePath."/lib/fileDisplay.lib.php");

  // tables names
  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

  //lib of this tool
  include($includePath."/lib/learnPath.lib.inc.php");

  // $_SESSION
  if ( isset($_GET['path_id']) && $_GET['path_id'] != '' )
  {
        $_SESSION['path_id'] = $_GET['path_id'];
  }

  // display title
  claro_disp_tool_title($nameTools);
  
  // main page

   if (!$is_courseAdmin) die ("<center>Not allowed !</center>");

   $is_AllowedToEdit = $is_courseAdmin;

   //####################################################################################\\
   //################################# COMMANDES ########################################\\
   //####################################################################################\\

   switch($cmd)
   {
        // MODULE DELETE
        case "delModule" :
              //--- BUILD ARBORESCENCE OF MODULES IN LEARNING PATH
              $sql = "SELECT M.*, LPM.*
                       FROM `".$TABLEMODULE."` AS M, `".$TABLELEARNPATHMODULE."` AS LPM
                      WHERE M.`module_id` = LPM.`module_id`
                        AND LPM.`learnPath_id` = ".$_SESSION['path_id']."
                   ORDER BY LPM.`rank` ASC";
              $result = claro_sql_query($sql);
              
              $extendedList = array();
              while ($list = mysql_fetch_array($result, MYSQL_ASSOC))
              {
                $extendedList[] = $list;
              }
              
              //-- delete module cmdid and his children if it is a label
              // get the modules tree ( cmdid module and all its children)
              $temp[0] = get_module_tree( build_element_list($extendedList), $_GET['cmdid'] , 'learnPath_module_id');
              // delete the tree
              delete_module_tree($temp);


              break;

        // VISIBILITY COMMAND
        case "mkVisibl" :
        case "mkInvisibl" :
              $cmd == "mkVisibl" ? $visibility = 'SHOW' : $visibility = 'HIDE';
              //--- BUILD ARBORESCENCE OF MODULES IN LEARNING PATH
              $sql = "SELECT M.*, LPM.*
                       FROM `".$TABLEMODULE."` AS M, `".$TABLELEARNPATHMODULE."` AS LPM
                      WHERE M.`module_id` = LPM.`module_id`
                        AND LPM.`learnPath_id` = ".$_SESSION['path_id']."
                   ORDER BY LPM.`rank` ASC";
              $result = claro_sql_query($sql);
              
              $extendedList = array();
              while ($list = mysql_fetch_array($result, MYSQL_ASSOC))
              {
                $extendedList[] = $list;
              }

              //-- set the visibility for module cmdid and his children if it is a label
              // get the modules tree ( cmdid module and all its children)
              $temp[0] = get_module_tree( build_element_list($extendedList), $_GET['cmdid'] );
              // change the visibility according to the new father visibility
              set_module_tree_visibility( $temp, $visibility);
              
              break;
        // ACCESSIBILITY COMMAND
        case "mkBlock" :
        case "mkUnblock" :
              $cmd == "mkBlock" ? $blocking = 'CLOSE' : $blocking = 'OPEN';
              $sql = "UPDATE `".$TABLELEARNPATHMODULE."`
                              SET `lock` = '$blocking'
                              WHERE `learnPath_module_id` = ".$_GET['cmdid']."
                                AND `lock` != '$blocking'";
              $query = claro_sql_query ($sql);
              break;
        // ORDER COMMAND
        case "changePos" :               
              // changePos form sent
              if( isset($_POST["newPos"]) && $_POST["newPos"] != "")
              {
                  // get order of parent module            
                  $temp = claro_sql_query_fetch_all("SELECT * FROM `".$TABLELEARNPATHMODULE."` WHERE `learnPath_module_id` = ".$_POST['cmdid']);
                  $movedModule = $temp[0];
                  
                  // if origin and target are the same ... cancel operation
                  if ($movedModule['learnPath_module_id'] == $_POST['newPos'])
                  {
                    $dialogBox .= $langWrongOperation;
                  }
                  else
                  {
                      //--
                      // select max order 
                      // get the max rank of the children of the new parent of this module
                      $result = claro_sql_query("SELECT MAX(`rank`)
                                                FROM `".$TABLELEARNPATHMODULE."`
                                                WHERE `parent` = ".$_POST['newPos']);

                      list($orderMax) = mysql_fetch_row($result);
                      $order = $orderMax + 1;
                      
                      // change parent module reference in the moved module and set order to 999 (added to the end of target group)
                      $sql = "UPDATE `".$TABLELEARNPATHMODULE."`
                       SET `parent` = ".$_POST['newPos'].",
                            `rank` = $order
                       WHERE `learnPath_module_id` = ".$_POST['cmdid'];
                      $query = claro_sql_query($sql);  
                      $dialogBox .= $langModuleMoved;
                  }

              }
              else  // create form requested
              {
                  // create elementList
                  $sql = "SELECT M.*, LPM.*
                 FROM `".$TABLEMODULE."` AS M, `".$TABLELEARNPATHMODULE."` AS LPM
                WHERE M.`module_id` = LPM.`module_id`
                  AND LPM.`learnPath_id` = ".$_SESSION['path_id']."
                  AND M.`contentType` = \"".CTLABEL_."\"
             ORDER BY LPM.`rank` ASC";
                  $result = claro_sql_query($sql);
                  $i=0;
                  $extendedList = array();
                  while ($list = mysql_fetch_array($result))
                  {
                    // this array will display target for the "move" command
                    // so don't add the module itself build_element_list will ignore all childre so that
                    // children of the moved module won't be shown, a parent cannot be a child of its own children                    
                    if ( $list['learnPath_module_id'] != $_GET['cmdid'] ) $extendedList[] = $list;
                  }
                  
                  // build the array that will be used by the claro_build_nested_select_menu function
                  $elementList = array();
                  $elementList = build_element_list($extendedList);
                 
                  $topElement['name'] = $langRoot;
                  $topElement['value'] = 0;
                  if (!is_array($elementList)) $elementList = array();
                  array_unshift($elementList,$topElement);
                  
                  // get infos about the moved module
                  $sql = "SELECT M.`name`
                    FROM `".$TABLELEARNPATHMODULE."` AS LPM, 
                          `".$TABLEMODULE."` AS M
                    WHERE LPM.`module_id` = M.`module_id`
                      AND LPM.`learnPath_module_id` = ".$_GET['cmdid'].$_POST['cmdid'];
                  $temp = claro_sql_query_fetch_all($sql);
                  $moduleInfos = $temp[0];
                  
                  $displayChangePosForm = true; // the form code comes after name and comment boxes section
              }
              break;
        case "moveUp" :
              $thisLPMId = $_GET['cmdid'];
              $sortDirection = "DESC";
              break;
        case "moveDown" :
              $thisLPMId = $_GET['cmdid'];
              $sortDirection = "ASC";
              break;
        case "createLabel" :
              // create form sent
              if( isset($_POST["newLabel"]) && trim($_POST["newLabel"]) != "")
              {
                  // determine the default order of this Learning path ( a new label is a root child)
                  $result = claro_sql_query("SELECT MAX(`rank`)
                                            FROM `".$TABLELEARNPATHMODULE."`
                                            WHERE `parent` = 0");

                  list($orderMax) = mysql_fetch_row($result);
                  $order = $orderMax + 1;
                  // create new module
                  $sql = "INSERT INTO
                      `".$TABLEMODULE."`
                      (`name`, `comment`, `contentType`)
                      VALUES ('".claro_addslashes($_POST['newLabel'])."','', '".CTLABEL_."')";
                  $query = claro_sql_query($sql);

                  // request ID of the last inserted row (module_id in $TABLEMODULE) to add it in $TABLELEARNPATHMODULE
                  $thisInsertedModuleId = mysql_insert_id();

                  // create new learning path module
                  $sql = "INSERT INTO
                    `".$TABLELEARNPATHMODULE."`
                    (`learnPath_id`, `module_id`, `specificComment`, `rank`, `parent`)
                    VALUES ('".$_SESSION['path_id']."', '".$thisInsertedModuleId."','', $order, 0)";
                  $query = claro_sql_query($sql);
              }
              else  // create form requested
              {
                  $displayCreateLabelForm = true; // the form code comes after name and comment boxes section
              }
              break;
        default:
              break;

   }
   // IF ORDER COMMAND RECEIVED
   // CHANGE ORDER
   if ($sortDirection)
   {

        // get list of modules with same parent as the moved module

        $sql = "SELECT LPM.`learnPath_module_id`, LPM.`rank`
                                 FROM `".$TABLELEARNPATHMODULE."` AS LPM, `".$TABLELEARNPATH."` AS LP
                                LEFT JOIN `".$TABLELEARNPATHMODULE."` AS LPM2
                                  ON LPM2.`parent` = LPM.`parent`
                                  WHERE LPM2.`learnPath_module_id` = ".$thisLPMId."
                                  AND LPM.`learnPath_id` = LP.`learnPath_id`
                                  AND LP.`learnPath_id` = ".$_SESSION['path_id']."
                             ORDER BY LPM.`rank` $sortDirection";
                             
        $listModules  = claro_sql_query_fetch_all($sql);
        
        // LP = learningPath
        foreach( $listModules as $module)
        {
            // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
            //          COMMIT ORDER SWAP ON THE DB
            if (isset($thisLPMOrderFound)&& $thisLPMOrderFound == true)
            {

                $nextLPMId = $module['learnPath_module_id'];
                $nextLPMOrder =  $module['rank'];

                claro_sql_query("UPDATE `".$TABLELEARNPATHMODULE."`
                                SET `rank` = \"$nextLPMOrder\"
                              WHERE `learnPath_module_id` =  \"$thisLPMId\"");
                claro_sql_query("UPDATE `".$TABLELEARNPATHMODULE."`
                                SET `rank` = \"$thisLPMOrder\"
                              WHERE `learnPath_module_id` =  \"$nextLPMId\"");
                break;
            }

            // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT
            if ($module['learnPath_module_id'] == $thisLPMId)
            {
                $thisLPMOrder = $module['rank'];
                $thisLPMOrderFound = true;
            }
        }
   }
   	   // select details of learning path to display
   $sql = "SELECT *
             FROM `".$TABLELEARNPATH."`
            WHERE `learnPath_id` = ".$_SESSION['path_id'];
   $query = claro_sql_query($sql);
   $LPDetails = mysql_fetch_array($query);

   //####################################################################################\\
   //############################ LEARNING PATH NAME BOX ################################\\
   //####################################################################################\\
   if ( $cmd == "updateName" )
   {
        nameBox(LEARNINGPATH_, UPDATE_);
   }
   else
   {
        nameBox(LEARNINGPATH_, DISPLAY_);
   }
   //####################################################################################\\
   //############################ LEARNING PATH COMMENT BOX #############################\\
   //####################################################################################\\
   if ( $cmd == "updatecomment" )
   {
        commentBox(LEARNINGPATH_, UPDATE_);
   }
   elseif ($cmd == "delcomment" )
   {
        commentBox(LEARNINGPATH_, DELETE_);
   }
   else
   {
        commentBox(LEARNINGPATH_, DISPLAY_);
   }
   //####################################################################################\\
   //############################ create label && change pos forms  ##################################\\
   //####################################################################################\\
   if ($displayCreateLabelForm)
   {
            $dialogBox .= "<form action=\"$PHP_SELF\" method=\"post\">
                        <h4><label for=\"newLabel\">".$langNewLabel."</label></h4>
                        <input type=\"text\" name=\"newLabel\" id=\"newLabel\" maxlength=\"255\" />
                        <input type=\"hidden\" name=\"cmd\" value=\"createLabel\" />
                        <input type=\"submit\" value=\"".$langOk."\" />
                      </form>";
   }
    if ($displayChangePosForm)
    { 
            $dialogBox .= "<form action=\"$PHP_SELF\" method=\"post\">
                        <h4>".$langMove." ' ".$moduleInfos['name']." ' ".$langTo."</h4>";
            // build select input - $elementList has been declared in the previous big cmd case
            $dialogBox .= claro_build_nested_select_menu("newPos",$elementList);
            $dialogBox .= "<input type=\"hidden\" name=\"cmd\" value=\"changePos\" />
                        <input type=\"hidden\" name=\"cmdid\" value=\"".$_GET['cmdid']."\" />
                        <input type=\"submit\" value=\"".$langOk."\" />
                      </form>";
    }
   //####################################################################################\\
   //############################### DIALOG BOX SECTION #################################\\
   //####################################################################################\\
   if ($dialogBox)
   {
           claro_disp_message_box($dialogBox);
   }
   //####################################################################################\\
   //######################### LEARNING PATH COURSEADMIN LINKS ##########################\\
   //####################################################################################\\
   ?>
           <a href="insertMyDoc.php"><?php echo $langDocumentAsModule; ?></a> |
           <a href="insertMyExercise.php"><?php echo $langExerciseAsModule; ?></a> |
           <a href="insertMyModule.php"><?php echo $langModuleOfMyCourse; ?></a> |
           <a href="<?php echo $PHP_SELF ?>?cmd=createLabel"><?php echo $langCreateLabel; ?></a>

   <?PHP
   //####################################################################################\\
   //######################### LEARNING PATH LIST CONTENT ###############################\\
   //####################################################################################\\
   //
   //--- BUILD ARBORESCENCE OF MODULES IN LEARNING PATH
    $sql = "SELECT M.*, LPM.*, A.`path`
             FROM `".$TABLEMODULE."` AS M, 
                  `".$TABLELEARNPATHMODULE."` AS LPM
            LEFT JOIN `".$TABLEASSET."` AS A
              ON M.`startAsset_id` = A.`asset_id`
            WHERE M.`module_id` = LPM.`module_id`
              AND LPM.`learnPath_id` = ".$_SESSION['path_id']."
         ORDER BY LPM.`rank` ASC";
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

   
   $iterator = 1;
   $atleastOne = false;
   $i = 0;

  // look for maxDeep
  $maxDeep = 1; // used to compute colspan of <td> cells
  for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
  {
    if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
  }
   //####################################################################################\\
   //######################### LEARNING PATH LIST HEADER ################################\\
   //####################################################################################\\

   ?>
     <table class="claroTable" width="100%" border="0" cellspacing="2">
            <tr class="headerX" align="center" valign="top">
              <th colspan="<?php echo $maxDeep+1 ?>"><?php echo $langModule; ?></th>
              <th><?php echo $langModify; ?></th>
              <th><?php echo $langRemoveFromLPShort; ?></th>
              <th><?php echo $langBlock; ?></th>
              <th><?php echo $langVisible; ?></th>
              <th><?php echo $langMove; ?></th>
              <th colspan="2"><?php echo $langOrder; ?></th>
             </tr><tbody>
   <?PHP
   
   //####################################################################################\\
   //######################### LEARNING PATH LIST DISPLAY #######################################\\
   //####################################################################################\\
   foreach ($flatElementList as $module)
   {
            //-------------visibility-----------------------------
            if ( $module['visibility'] == 'HIDE' )
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
            
            $spacingString = "";
            for($i = 0; $i < $module['children']; $i++)
              $spacingString .= "<td width='5'>&nbsp;</td>";
            $colspan = $maxDeep - $module['children']+1;
            
            echo "<tr align=\"center\"".$style.">\n".$spacingString."<td colspan=\"".$colspan."\" align=\"left\">";
                         
            if ($module['contentType'] == CTLABEL_) // chapter head
            {
                echo "<b>".$module['name']."</b>\n";
            }
            else // module
            {
                if($module['contentType'] == CTEXERCISE_ ) 
                  $moduleImg = "quiz.gif";
                else
                  $moduleImg = choose_image(basename($module['path']));
                  
                $contentType_alt = selectAlt($module['contentType']);
                echo "<a href=\"module.php?module_id=".$module['module_id']."&asStudent=1\"><img src=\"".$clarolineRepositoryWeb."img/".$moduleImg."\" alt=\"".$contentType_alt."\" border=\"0\">"
                             .$module['name'].
                             "</a>";
            }
            echo "</td>"; // end of td of module name
            // Modify command / go to other page
            echo     "
			<td>
				<a href=\"module.php?asStudent=0&module_id=".$module['module_id']."\">".
               "<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=0 alt=\"".$langModify."\" />".
               "</a>
			</td>";
            // DELETE ROW

              //in case of SCORM module, the pop-up window to confirm must be different as the action will be different on the server
            echo    "
			<td>
            	<a href=\"".$PHP_SELF."?cmd=delModule&cmdid=".$module['learnPath_module_id']."\" ".
                        "onClick=\"return confirmation('".$langAreYouSureToRemove." ",addslashes($module['name'])," ? ";
            if ($module['contentType'] == CTSCORM_) 
              echo $langAreYouSureToRemoveSCORM ;
            elseif ( $module['contentType'] == CTLABEL_ )
              echo $langAreYouSureToRemoveLabel;
            else
              echo $langAreYouSureToRemoveStd ;
            echo   "');\"
			><img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=0 alt=\"".$langRemoveFromLPShort."\"></a>
   			</td>";

            // LOCK
            echo    "<td>";
            if ( $module['contentType'] == CTLABEL_)
            {
                echo "&nbsp;";
            }
            elseif ( $module['lock'] == 'OPEN')
            {
                echo    "<a href=\"",$PHP_SELF,"?cmd=mkBlock&cmdid=".$module['learnPath_module_id']."\">".
                        "<img src=\"".$clarolineRepositoryWeb."img/unblock.gif\" alt=\"$langAltMakeBlocking\" border=0>".
                        "</a>";
            }
            elseif( $module['lock'] == 'CLOSE')
            {
                echo    "<a href=\"",$PHP_SELF,"?cmd=mkUnblock&cmdid=".$module['learnPath_module_id']."\">".
                        "<img src=\"".$clarolineRepositoryWeb."img/block.gif\" alt=\"$langAltMakeNotBlocking\" border=0>".
                        "</a>";
            }
            echo    "</td>";

            // VISIBILITY
            echo    "<td>";

            if ( $module['visibility'] == 'HIDE')
            {
                echo    "<a href=\"",$PHP_SELF,"?cmd=mkVisibl&cmdid=".$module['module_id']."\">".
                        "<img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" alt=\"$langAltMakeVisible\" border=\"0\">".
                        "</a>";
            }
            else
            {
                if( $module['lock'] == 'CLOSE' )
                {
                        $onclick = "onClick=\"return confirmation('".$langAlertBlockingMakedInvisible."');\"";
                }
                else
                {
                        $onclick = "";
                }
                echo    "<a href=\"",$PHP_SELF,"?cmd=mkInvisibl&cmdid=".$module['module_id']."\" ",$onclick, " >".
                        "<img src=\"".$clarolineRepositoryWeb."img/visible.gif\" alt=\"$langAltMakeInvisible\" border=0>".
                        "</a>";
            }

            echo    "</td>";

            // ORDER COMMANDS
            // DISPLAY CATEGORY MOVE COMMAND 
            echo     "<td>".
                         "<a href=\"",$PHP_SELF,"?cmd=changePos&cmdid=".$module['learnPath_module_id']."\">".
                         "<img src=\"".$clarolineRepositoryWeb."img/deplacer.gif\" alt=\"$langAltMove\" border=0>".
                         "</a>".
                         "</td>";
            // DISPLAY MOVE UP COMMAND only if it is not the top learning path
            if ($module['up'])
            {
                echo     "<td>".
                         "<a href=\"",$PHP_SELF,"?cmd=moveUp&cmdid=".$module['learnPath_module_id']."\">".
                         "<img src=\"".$clarolineRepositoryWeb."img/up.gif\" alt=\"$langAltMoveUp\" border=0>".
                         "</a>".
                         "</td>";
            }
            else
            {
                echo "<td>&nbsp;</td>";
            }

            // DISPLAY MOVE DOWN COMMAND only if it is not the bottom learning path
            if($module['down'])
            {
                echo    "<td>".
                        "<a href=\"",$PHP_SELF,"?cmd=moveDown&cmdid=".$module['learnPath_module_id']."\">".
                        "<img src=\"".$clarolineRepositoryWeb."img/down.gif\" alt=\"$langAltMoveDown\" border=0>".
                        "</a>".
                         "</td>";
            }
            else
            {
                echo "<td>&nbsp;</td>";
            }

            echo "\n</tr>\n";
            $iterator++;
            $atleastOne = true;
   }

   echo "</tbody>";

   echo "<tfoot>";

    if ($atleastOne == false)
   {
          echo "<tr><td align=\"center\" colspan=\"7\">".$langNoModule."</td></tr>";
    }

   echo "</tfoot>";

   //display table footer
   echo "</table>";

   // footer
   include($includePath."/claro_init_footer.inc.php");
?>
