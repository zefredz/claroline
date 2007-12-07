<?php
    // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.3.2 $Revision$                            |
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
  
  $langAddModuleComment = $langAddComment; // include of document.inc.php will overwrite this var, so save it ...
  // also include the document languague file
  @include($includePath."/../lang/".$languageInterface."/document.inc.php");
  $langAddComment = $langAddModuleComment; // ...to restore it


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
  
  // $_SESSION
  // path_id
  if ( isset($_GET['path_id']) && $_GET['path_id'] != "" )
  {
        $_SESSION['path_id'] = (int) $_GET['path_id'];
  }
  // module_id
  if ( isset($_GET['module_id']) && $_GET['module_id'] != "")
  {
        $_SESSION['module_id'] = (int) $_GET['module_id'];
  }
  // asStudent
  if ( isset($_GET['asStudent']) )
  {
        $_SESSION['asStudent'] = $_GET['asStudent'];
  }
  else
  {
      //asStudent MUST be requested
      $_SESSION['asStudent'] = 0;
  }
  
  //-- interbredcrump
  $interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);
  if ( $is_courseAdmin && (!isset($_SESSION['asStudent']) || $_SESSION['asStudent'] == 0 ) )
  {
        $interbredcrump[]= array ("url"=>"../learnPath/learningPathAdmin.php", "name"=> $langLearningPathAdmin);
  }
  else
  {
        $interbredcrump[]= array ("url"=>"../learnPath/learningPath.php", "name"=> $langLearningPath);
  }


  $nameTools = $langModule;

  // tables names
  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";
  
  $TABLEQUIZTEST               = $_course['dbNameGlu']."quiz_test";

  $dbTable = $TABLEASSET; // for old functions of document tool

  //lib of this tool
  @include($includePath."/lib/learnPath.lib.inc.php");

  include($includePath."/lib/fileDisplay.lib.php");
  include($includePath."/lib/fileManage.lib.php");
  include($includePath."/lib/fileUpload.lib.php");


  // clean exercise session vars
  if(session_is_registered('objExercise'))        { session_unregister('objExercise');        }
  if(session_is_registered('objQuestion'))        { session_unregister('objQuestion');        }
  if(session_is_registered('objAnswer'))          { session_unregister('objAnswer');          }
  if(session_is_registered('questionList'))       { session_unregister('questionList');       }
  if(session_is_registered('exerciseResult'))     { session_unregister('exerciseResult');     }



     // main page
     if ( $_SESSION['asStudent'] )
     {
        $is_AllowedToEdit = false; // asStudent !
     }
     else
     {
        $is_AllowedToEdit = $is_courseAdmin;    // as teacher
     }

  // FIRST WE SEE IF USER MUST SKIP THE PRESENTATION PAGE OR NOT
  // triggers are : if there is no introdution text or no user module progression statistics yet and user is not admin,
  // then there is nothing to show and we must enter in the module without displaying this page.

   /*
    *  GET INFOS ABOUT MODULE and LEARNPATH_MODULE
    */

    // check in the DB if there is a comment set for this module in general

    $sql = "SELECT *
              FROM `".$TABLEMODULE."`
             WHERE `module_id` = ".$_SESSION['module_id'];

    $query = claro_sql_query($sql);
    $module = @mysql_fetch_array($query);

    if ($module['comment']=='' || $module['comment']==$langDefaultModuleComment) {
        $noModuleComment = true;
        }
    else
    {
        $noModuleComment = false;
    }
    
    if($module['startAsset_id'] == 0)
    {
      $noStartAsset = true;
    }
    else
    {
      $noStartAsset = false;
    }
    // check if there is a specific comment for this module in this path

    $sql =  "SELECT *
              FROM `".$TABLELEARNPATHMODULE."`
             WHERE `module_id` = ".$_SESSION['module_id'];
    $query = claro_sql_query($sql);
    $learnpath_module = @mysql_fetch_array($query);

     if ($learnpath_module['specificComment']=='' || $learnpath_module['specificComment']==$langDefaultModuleAddedComment) {
        $noModuleSpecificComment = true;
        }
    else
    {
        $noModuleSpecificComment = false;
    }
    // check in DB if user has already browsed this module

    $sql = "SELECT *
                FROM `".$TABLEUSERMODULEPROGRESS."` AS UMP, `".$TABLELEARNPATHMODULE."` AS LPM, `".$TABLEMODULE."` AS M
               WHERE UMP.`user_id` = '$_uid'
                 AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
                 AND LPM.`learnPath_id` = ".$_SESSION['path_id']."
                 AND LPM.`module_id` = ".$_SESSION['module_id']."
                 AND LPM.`module_id` = M.`module_id`
                 ";
    $resultBrowsed = claro_sql_query($sql);

    //REDIRECT USER IF NEEDED

    if ((!$is_AllowedToEdit) && (@mysql_num_rows($resultBrowsed)==0 ) && $noModuleComment && $noModuleSpecificComment && !$noStartAsset) {
          header("Location:./navigation/viewer.php");
          }

  //header

  @include($includePath."/claro_init_header.inc.php");

   //####################################################################################\\
   //################################## MODULE NAME BOX #################################\\
   //####################################################################################\\
   echo "<br />";
   if ( $cmd == "updateName" )
   {
        nameBox(MODULE_, UPDATE_);
   }
   else
   {
        nameBox(MODULE_, DISPLAY_);
   }

  if($module['contentType'] != CTLABEL_ )
  {
       //####################################################################################\\
       //############################### MODULE COMMENT BOX #################################\\
       //####################################################################################\\
       //#### COMMENT #### courseAdmin cannot modify this if this is a imported module ####\\
       // this the comment of the module in ALL learning paths
       if ( $cmd == "updatecomment" )
       {
            commentBox(MODULE_, UPDATE_);
       }
       elseif ($cmd == "delcomment" )
       {
            commentBox(MODULE_, DELETE_);
       }
       else
       {
            commentBox(MODULE_, DISPLAY_);
       }
       //#### ADDED COMMENT #### courseAdmin can always modify this ####\\
       // this is a comment for THIS module in THIS learning path
       echo "<small>";
       if ( $cmd == "updatespecificComment" )
       {
            commentBox(LEARNINGPATHMODULE_, UPDATE_);
       }
       elseif ($cmd == "delspecificComment" )
       {
            commentBox(LEARNINGPATHMODULE_, DELETE_);
       }
       else
       {
            commentBox(LEARNINGPATHMODULE_, DISPLAY_);
       }
       echo "</small>";
  } //  if($module['contentType'] != CTLABEL_ )
   //back button

   if ($is_AllowedToEdit)
   {
     $pathBack = "./learningPathAdmin.php";
   }
   else
   {
     $pathBack = "./learningPath.php";
   }

   echo "<small><a href=\"".$pathBack."\"><<< ".$langBackModule."</a></small><br><br>";

   //####################################################################################\\
   //############################ PROGRESS  AND  START LINK #############################\\
   //####################################################################################\\

    /* Display PROGRESS */
    
    if($module['contentType'] != CTLABEL_) //
    {
        if (mysql_num_rows($resultBrowsed) && $module['contentType'] != CTLABEL_)
        {
    
            $list = mysql_fetch_array($resultBrowsed);
            $contentType_img = selectImage($list['contentType']);
            $contentType_alt = selectAlt($list['contentType']);
    
            if ($list['contentType']== CTSCORM_   ) { $contentDescType = $langSCORMTypeDesc;    }
            if ($list['contentType']== CTEXERCISE_ ) { $contentDescType = $langEXERCISETypeDesc; }
            if ($list['contentType']== CTDOCUMENT_ ) { $contentDescType = $langDOCUMENTTypeDesc; }
    
            echo "<b>".$langProgInModuleTitle."</b><br><br>";
    
            echo "<table align=\"center\" class=\"claroTable\" border=\"0\" cellspacing=\"2\">
                  <tr class=\"headerX\">
                    <th>
                      ".$langInfoProgNameTitle."
                    </th>
                    <th>
                      ".$langPersoValue."
                    </th>
                  </tr>
                  <tbody>";
    
            //display type of the module
    
            echo "<tr>
                    <td>
                    ".$langTypeOfModule."
                    </td>
                    <td>
                     <img src=\"".$clarolineRepositoryWeb."img/".$contentType_img."\" alt=\"".$contentType_alt."\" border=\"0\" />".$contentDescType."
                    </td>
                  </tr>";
    
            //display total time already spent in the module
    
            echo "<tr>
                    <td>
                    ".$langTotalTimeSpent."
                    </td>
                    <td>
                    ".$list['total_time']."
                    </td>
                  </tr>";
    
            //display time passed in last session
    
            echo "<tr>
                    <td>
                    ".$langLastSessionTimeSpent."
                    </td>
                    <td>
                    ".$list['session_time']."
                    </td>
                  </tr>";
            /*
            //display number of attempts
    
            echo "<tr>
                    <td>
                    ".$langNumbAttempt."
                    </td>
                    <td>
                    ".$langBrowsed." ".$list['Attempt']." ".$langTimes."
                    </td>
                  </tr>";
             */
             //display user best score
            if ($list['scoreMax'] > 0) {$raw = round($list['raw']/$list['scoreMax']*100);} else {$raw = 0;}
    
            if ($raw<0) {$raw = 0;}
            
            if (($list['contentType'] == CTSCORM_ ) && ($list['scoreMax'] <= 0) && (  ( ($list['lesson_status'] == "COMPLETED") || ($list['lesson_status'] == "PASSED") ) || ($list['raw'] != -1) ) ) {$raw = 100;}
               // no sens to display a score in case of a document module
    
            if (($list['contentType'] != CTDOCUMENT_))
            {
                echo "<tr>
                        <td>
                        ".$langYourBestScore."
                        </td>
                        <td>
                        ".
               claro_disp_progress_bar($raw, 1).
               " ".$raw."%
                        </td>
                      </tr>";
           }
    
           //display lesson status
    
               // document are just browsed or not, but not completed or passed...
    
           if (($list['contentType']== CTDOCUMENT_))
           {
              if ($list['lesson_status']=="COMPLETED")
              {
                 $statusToDisplay = $langAlreadyBrowsed;
              }
              else
              {
                $statusToDisplay = $langNeverBrowsed;
              }
           }
           else
           {
              $statusToDisplay = $list['lesson_status'];
           }
            echo "<tr>
                    <td>
                    ".$langLessonStatus."
                    </td>
                    <td>
                    ".$statusToDisplay."
                    </td>
                  </tr>";
            echo  "</tbody>
                   ";
    
            echo "</table>";
    
         } //end display stats
    
        /* START */
        // check if module.startAssed_id is set and if an asset has the corresponding asset_id
        // asset_id exists ?  for the good module  ?
        $sql = "SELECT *
                  FROM `".$TABLEASSET."`
                 WHERE `asset_id` = ".$module['startAsset_id']."
                   AND `module_id` = ".$_SESSION['module_id'];
        $result = claro_sql_query($sql);
        $asset = @mysql_fetch_array($result);
    
    
        if(( $module['startAsset_id'] != "" && $asset['asset_id'] == $module['startAsset_id'] && ( $delete != $asset['path'] ) )
                 || ( $submitStartAsset && isset($startAsset))
           )
        {
    
             echo "<center>
                     <form action=\"./navigation/viewer.php\" method=\"post\">
                       <input type=\"submit\" value=\"".$langStartModule."\">
                     </form>
                   </center>";
        }
        else
        {
             echo "<p><center>$langNoStartAsset</center></p>";
        }
    }// end if($module['contentType'] != CTLABEL_) 
    // if module is a label, only allow to change its name.
    
   //####################################################################################\\
   //################################# ADMIN DISPLAY ####################################\\
   //####################################################################################\\
    if( $is_AllowedToEdit ) // for teacher only
    {
        switch ($module['contentType'])
        {
                case CTDOCUMENT_ :
                     include("./include/document.inc.php");
                     break;
                case CTEXERCISE_ :
                     include("./include/exercise.inc.php");
                     break;
                case CTSCORM_ :
                     include("./include/scorm.inc.php");
                     break;
                case CTCLARODOC_ :
                     break;
                case CTLABEL_ :
                    break;
        }
    } // if ($is_AllowedToEdit)
     // footer
     @include($includePath."/claro_init_footer.inc.php");
?>
