<?php
     /**
      * This functions lybrary is used by most of the pages of the learning path tool
      *
      * @package learningpath
      * @filesource
      */

     /**
      * content type
      */
      define ( "CTCLARODOC_", "CLARODOC" );
     /**
      * content type
      */
      define ( "CTDOCUMENT_", "DOCUMENT" );
     /**
      * content type
      */
      define ( "CTEXERCISE_", "EXERCISE" );
     /**
      * content type
      */
      define ( "CTSCORM_", "SCORM" );
    /**
      * content type
      */
      define ( "CTLABEL_", "LABEL" );
      

     /**
      * mode used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
      */
      define ( "DISPLAY_", 1 );
     /**
      * mode used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
      */
      define ( "UPDATE_", 2 );
      define ( "UPDATENOTSHOWN_", 4 );
      
     /**
      * mode used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
      */
      define ( "DELETE_", 3 );

     /**
      * type used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
      */
      define ( "ASSET_", 1 );
     /**
      * type used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
      */
      define ( "MODULE_", 2 );
      define ( "LEARNINGPATH_", 3 );
      define ( "LEARNINGPATHMODULE_", 4 );
      

     /**
      * DEPRECATED : Cleaning of LP sessions vars
      *
      * @param string $reset "path_id" clean all LP session vars. This string can also be "module_id", "publicModule" or "inPathMode"
      *
      * @author Piraux Sébastien <pir@cerdecam.be>
      * @author Lederer Guillaume <led@cerdecam.be>
      */
     function LPcleanSession($reset)
     {
     
        switch($reset)
        {
        
                case "path_id" :
                      session_unregister('path_id');            // id of the learning path the user is in
                case "module_id" :
                      session_unregister('module_id');          // id of the module the user is in
                case "publicModule" :
                      session_unregister('openCatTable');       // array used by insertPublicModule to display courses categories
                      session_unregister('openCourseTable');    // array used by insertPublicModule to display modules of each course
                case "asStudent" :
                      session_unregister('asStudent');          // used for teachers to know if they are testing or administrating module_id
                case "inPathMode" :
                      session_unregister('inPathMode');         // used in exercises to know if the user come from learning path
                      break;
                      
        }
      
     }

     /**
      * This function is used to display comments of module or learning path with admin links if needed.
      * Admin links are 'edit' and 'delete' links.
      *
      * @param string $type MODULE_ , LEARNINGPATH_ , LEARNINGPATHMODULE_
      * @param string $mode DISPLAY_ , UPDATE_ , DELETE_
      *
      * @author Piraux Sébastien <pir@cerdecam.be>
      * @author Lederer Guillaume <led@cerdecam.be>
      */
     function commentBox($type, $mode)
     {
           // globals
           global $is_AllowedToEdit;
           global $urlAppend;
           global $TABLELEARNPATH, $TABLEMODULE, $TABLELEARNPATHMODULE;
           global $langModify, $langOk, $langErrorNameAlreadyExists, $langAddComment, $langConfirmYourChoice;
           global $langDefaultLearningPathComment, $langDefaultModuleComment , $langDefaultModuleAddedComment, $clarolineRepositoryWeb;
           // will be set 'true' if the comment has to be displayed
           $dsp = false;

           // those vars will be used to build sql queries according to the comment type
           switch ( $type )
           {
                case MODULE_ :
                     $defaultTxt = $langDefaultModuleComment;
                     $col_name = "comment";
                     $tbl_name = $TABLEMODULE;
		     if ( isset($_REQUEST['module_id'] ) )
			$module_id = $_REQUEST['module_id'];
		     else 
			$module_id = $_SESSION['module_id'];
                     $where_cond = "`module_id` = ".$module_id;  // use backticks ( ` ) for col names and simple quote ( ' ) for string
                     break;
                case LEARNINGPATH_ :
                     $defaultTxt = $langDefaultLearningPathComment;
                     $col_name = "comment";
                     $tbl_name = $TABLELEARNPATH;
                     $where_cond = "`learnPath_id` = ".$_SESSION['path_id'];  // use backticks ( ` ) for col names and simple quote ( ' ) for string
                     break;
                case LEARNINGPATHMODULE_ :
                     $defaultTxt = $langDefaultModuleAddedComment;
                     $col_name = "specificComment";
                     $tbl_name = $TABLELEARNPATHMODULE;
                     $where_cond = "`learnPath_id` = ".$_SESSION['path_id']."
                                        AND `module_id` = ".$_SESSION['module_id'];  // use backticks ( ` ) for col names and simple quote ( ' ) for string
                     break;
           }

           // update mode
	   // allow to chose between 
	   // - update and show the comment and the pencil and the delete cross (UPDATE_)
  	   // - update and nothing displayed after form sent (UPDATENOTSHOWN_)	  	
           if ( ( $mode == UPDATE_ || $mode == UPDATENOTSHOWN_ )  && $is_AllowedToEdit )
           {
                 if ( isset($_POST['insertCommentBox']) )
                 {
                        $sql = "UPDATE `".$tbl_name."`
                                   SET `".$col_name."` = \"".claro_addslashes($_POST['insertCommentBox'])."\"
                                 WHERE ".$where_cond;
                        //echo "<1 upd> ".$sql."<br>";
                        claro_sql_query($sql);
                        if($mode == UPDATE_)	
				$dsp = true;
			elseif($mode == UPDATENOTSHOWN_) 
				$dsp = false;
                 }
                 else // display form
                 {
                     // get info to fill the form in
                     $sql = "SELECT *
                               FROM `".$tbl_name."`
                              WHERE ".$where_cond;
                     $query = claro_sql_query($sql);
                     $oldComment = @mysql_fetch_array($query);
                     echo        "<form method=\"POST\" action=\"$PHP_SELF\">\n",
                                    //"<textarea name=\"insertCommentBox\" rows=\"8\" cols=\"55\" wrap=\"virtual\">",
                                    claro_disp_html_area('insertCommentBox', $oldComment[$col_name], 15, 55),
                                    //htmlentities($oldComment[$col_name])."</textarea>\n",
                                    "<br>\n",
                                    "<input type=\"hidden\" name=\"cmd\" value=\"update".$col_name."\">",
                                    "<input type=\"submit\" value=\"$langOk\">\n",
                                    "<br>\n",
                                  "</form>\n";
                 }

           }
	   
           // delete mode
           if ( $mode == DELETE_ && $is_AllowedToEdit)
           {
                  $sql =  "UPDATE `".$tbl_name."`
                              SET `".$col_name."` = \"\"
                            WHERE ".$where_cond;
                  $query = claro_sql_query($sql);
                  $dsp = true;

           }

           // display mode only or display was asked by delete mode or update mode
           if ( $mode == DISPLAY_ || $dsp == true )
           {
                $sql = "SELECT *
                          FROM `".$tbl_name."`
                         WHERE ".$where_cond;
                //echo "<4 dsp> ".$sql."<br>";
                $query = claro_sql_query($sql);
                $currentComment = @mysql_fetch_array($query);

                // display nothing if this is default comment and not an admin
                if ( ($currentComment[$col_name] == $defaultTxt) && !$is_AllowedToEdit ) return 0;

                if ( empty($currentComment[$col_name]) )
                {
                     // if no comment and user is admin : display link to add a comment
                     if ( $is_AllowedToEdit )
                     {
                           echo        "<p>\n",
                                       "<small>\n",
                                       "<a href=\"$PHP_SELF?cmd=update".$col_name."\">\n",$langAddComment,"</a>\n",
                                       "</small>\n",
                                       "</p>\n";
                     }
                }
                else
                {
                     // display comment
                     echo "<p>".$currentComment[$col_name]."</p>";
                     // display edit and delete links if user as the right to see it
                     if ( $is_AllowedToEdit )
                     {
                        echo
                               "<p>\n",
                               "<small>\n",
                               "<a href=\"$PHP_SELF?cmd=update".$col_name."\"><img src=\"".$clarolineRepositoryWeb."img/edit.gif\" alt=\"",$langModify,"\" border=\"0\"></a>\n",
                               "<a href=\"$PHP_SELF?cmd=del".$col_name."\" onclick=\"javascript:if(!confirm('".addslashes(htmlentities($langConfirmYourChoice))."')) return false;\"><img src=\"".$clarolineRepositoryWeb."img/delete.gif\" alt=\"",$langDelete,"\" border=\"0\"></a>\n",
                               "</small>\n",
                               "</p>\n";
                     }
                }
           }

          return 0;
     }

     /**
      * This function is used to display name of module or learning path with admin links if needed
      *
      * @param string $type MODULE_ , LEARNINGPATH_
      * @param string $mode display(DISPLAY_) or update(UPDATE_) mode, no delete for a name
      * @author Piraux Sébastien <pir@cerdecam.be>
      * @author Lederer Guillaume <led@cerdecam.be>
      */
     function nameBox($type, $mode)
     {
           // globals
           global $is_AllowedToEdit;
           global $urlAppend;
           global $TABLELEARNPATH, $TABLEMODULE;
           global $langModify, $langOk, $langErrorNameAlreadyExists, $clarolineRepositoryWeb;

           // $dsp will be set 'true' if the comment has to be displayed
           $dsp = false;

           // those vars will be used to build sql queries according to the name type
           switch ( $type )
           {
                case MODULE_ :
                     $col_name = "name";
                     $tbl_name = $TABLEMODULE;
                     $where_cond = "`module_id` = ".$_SESSION['module_id'];
                     break;
                case LEARNINGPATH_ :
                     $col_name = "name";
                     $tbl_name = $TABLELEARNPATH;
                     $where_cond = "`learnPath_id` = ".$_SESSION['path_id'];
                     break;
           }

           // update mode
           if ( $mode == UPDATE_ && $is_AllowedToEdit)
           {

                 if ( isset($_POST['newName']) && !empty($_POST['newName']) )
                 {

                           $sql = "SELECT `".$col_name."`
                                     FROM `".$tbl_name."`
                                    WHERE `".$col_name."` = '".claro_addslashes($_POST['newName'])."'
                                      AND !(".$where_cond.")";
                           $query = claro_sql_query($sql);
                           $num = mysql_num_rows($query);
                           if ($num == 0)  // name doesn't already exists
                           {

                               $sql = "UPDATE `".$tbl_name."`
                                          SET `".$col_name."` = '".claro_addslashes($_POST['newName'])."'
                                        WHERE ".$where_cond;

                               claro_sql_query($sql);
                               $dsp = true;
                           }
                           else
                           {
                                echo "<font color=\"red\">".$langErrorNameAlreadyExists."</font><br>";
                                $dsp = true;
                           }
                 }
                 else // display form
                 {
                     $sql = "SELECT *
                               FROM `".$tbl_name."`
                              WHERE ".$where_cond;

                     $query = claro_sql_query($sql);
                     $oldName = @mysql_fetch_array($query);
                     echo        "<form method=\"POST\" action=\"".$_SERVER['PHP_SELF']."\">\n",
                                    "<input type=\"text\" name=\"newName\" size=\"50\" maxlength=\"255\" value=\"".htmlentities($oldName['name'])."\"",
                                    "<br />\n",
                                    "<input type=\"hidden\" name=\"cmd\" value=\"updateName\">\n",
                                    "<input type=\"submit\" value=\"$langOk\">\n",
                                    "<br>\n",
                                 "</form>\n";
                 }

           }

           // display if display mode or asked by the update
           if ( $mode == DISPLAY_ || $dsp == true )
           {
                $sql = "SELECT *
                          FROM `".$tbl_name."`
                         WHERE ".$where_cond;

                $query = claro_sql_query($sql);
                $currentName = @mysql_fetch_array($query);

                echo "<h4>".$currentName['name'];

                if ( $is_AllowedToEdit )
                         echo "<br><a href=\"$PHP_SELF?cmd=updateName\"><img src=\"".$clarolineRepositoryWeb."img/edit.gif\" alt=\"",$langModify,"\" border=\"0\"></a>\n";
                echo "</h4>";
           }

          return 0;
     }

     /**
      * This function is used to display the correct image in the modules lists
      * It looks for the correct type in the array, and return the corresponding image name if found
      * else it returns a default image
      *
      * @param  string $contentType type of content in learning path
      * @return string name of the image with extension
      * @author Piraux Sébastien <pir@cerdecam.be>
      * @author Lederer Guillaume <led@cerdecam.be>
      */
     function selectImage($contentType)
     {

          $imgList[CTDOCUMENT_] = "documents.gif";
          $imgList[CTCLARODOC_] = "clarodoc.gif";
          $imgList[CTEXERCISE_] = "quiz.gif";
          $imgList[CTSCORM_] = "scorm.gif";

          if (array_key_exists( $contentType , $imgList ))
          {
              return $imgList[$contentType];
          }

          return "defaut.gif";

     }
     /**
      * This function is used to display the correct alt texte for image in the modules lists.
      * Mainly used at the same time than selectImage() to add an alternate text on the image.
      *
      * @param  string $contentType type of content in learning path
      * @return string text for the alt
      * @author Piraux Sébastien <pir@cerdecam.be>
      * @author Lederer Guillaume <led@cerdecam.be>
      */
     function selectAlt($contentType)
     {
          global $langAlt;

          $altList[CTDOCUMENT_] = $langAlt['document'];
          $altList[CTCLARODOC_] = $langAlt['clarodoc'];
          $altList[CTEXERCISE_] = $langAlt['exercise'];
          $altList[CTSCORM_] = $langAlt['scorm'];

          if (array_key_exists( $contentType , $altList ))
          {
              return $altList[$contentType];
          }

          return "defaut.gif";
     }

     /**
      * This function receives an array like $table['idOfThingToOrder'] = $requiredOrder and will return a sorted array
      * like $table[$i] = $idOfThingToOrder
      * the id list is sorted according to the $requiredOrder values
      *
      * @param array $formValuesTab an array like these sent by the form on learingPathAdmin.php for an exemple
      * @return array an array of the sorted list of ids
      * @author Piraux Sébastien <pir@cerdecam.be>
      * @author Lederer Guillaume <led@cerdecam.be>
      */
      function setOrderTab ( $formValuesTab )
      {
              global $langErrorInvalidParms, $langErrorValuesInDouble;
              global $dialogBox;

              $tabOrder = array(); // declaration to avoid bug in "elseif (in_array ... "
              $i = 0;
              foreach ( $formValuesTab as $key => $requiredOrder)
              {
                 // error if input is not a number
                 if( !is_num($requiredOrder) )
                 {
                    $dialogBox .= $langErrorInvalidParms;
                    return 0;
                 }
                 elseif( in_array($requiredOrder, $tabOrder) )
                 {
                    $dialogBox .= $langErrorValuesInDouble;
                    return 0;
                 }
                 // $tabInvert = required order => id module
                 $tabInvert[$requiredOrder] = $key;
                 // $tabOrder = required order : unsorted
                 $tabOrder[$i] = $requiredOrder;
                 $i++;
              }
              // $tabOrder = required order : sorted
              sort($tabOrder);
              $i = 0;
              foreach ($tabOrder as $key => $order)
              {
                // $tabSorted = new Order => id learning path
                $tabSorted[$i] = $tabInvert[$order];
                $i++;
              }
              return $tabSorted;
      }


      /**
       * Check if an input string is a number
       *
       * @param string $var input to check
       * @return bool true if $var is a number, false otherwise
       * @author Piraux Sébastien <pir@cerdecam.be>
       */
      function is_num($var)
      {
         for ( $i = 0; $i < strlen($var); $i++ )
         {
             $ascii = ord($var[$i]);

             // 48 to 57 are decimal ascii values for 0 to 9
             if ( $ascii >= 48 && $ascii <= 57)
                 continue;
             else
                 return false;
         }

             return true;
      }


      /*
       *  This function allows to display the modules content of a learning path.
       *  The function must be called from inside a learning path where the session variable path_id is known.
       */
      function display_path_content()
      {
          // global variables : tables names
          global $TABLELEARNPATH;
          global $TABLELEARNPATHMODULE;
          global $TABLEUSERMODULEPROGRESS;
          global $TABLEMODULE;
          global $TABLEASSET;
          global $_cid;
          global $langModule;
	  global $clarolineRepositoryWeb;
          
          $sql = "SELECT M.`name`, M.`contentType`, LPM.`learnPath_module_id`, LPM.`parent`, A.`path`
            FROM `$TABLELEARNPATH` AS LP, `$TABLELEARNPATHMODULE` AS LPM, `$TABLEMODULE` AS M
            LEFT JOIN `".$TABLEASSET."` AS A
              ON M.`startAsset_id` = A.`asset_id`
            WHERE LP.`learnPath_id` = ".$_SESSION['path_id']."
              AND LP.`learnPath_id` = LPM.`learnPath_id`
              AND LPM.`module_id` = M.`module_id`
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
          
          // look for maxDeep
          $maxDeep = 1; // used to compute colspan of <td> cells
          for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
          {
            if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
          }
          
          echo "<table class=\"claroTable\" width=\"100%\"  border=\"0\" cellspacing=\"2\">
             <tr class=\"headerX\" align=\"center\" valign=\"top\">
               <th colspan=\"".($maxDeep+1)."\">".$langModule."</th>
             </tr>\n <tbody> ";
          
          foreach ($flatElementList as $module)
          {
            $spacingString = "";
            for($i = 0; $i < $module['children']; $i++)
              $spacingString .= "<td width='5'>&nbsp;</td>";
            $colspan = $maxDeep - $module['children']+1;
            
            echo "<tr align=\"center\"".$style.">\n".$spacingString."<td colspan=\"".$colspan."\" align=\"left\">";
                         
            if ($module['contentType'] == CTLABEL_) // chapter head
            {
                echo "<b>".$module['name']."</b>";
            }
            else // module
            {
                if($module['contentType'] == CTEXERCISE_ ) 
                  $moduleImg = "quiz.gif";
                else
                  $moduleImg = choose_image(basename($module['path']));
                $contentType_alt = selectAlt($module['contentType']);
              
                echo "<img src=\"".$clarolineRepositoryWeb."img/".$moduleImg."\" alt=\"".$contentType_alt."\" border=\"0\">"
                             .$module['name'];
            }
            echo "</td>\n</tr>\n";
          }
          echo "</tbody></table>";
      }
   
    /**
      * Compute the progression into the $lpid learning path in pourcent
      * 
      * @param $lpid id of the learning path
      * @param $lpUid user id
      *
      * @return integer percentage of progression os user $mpUid in the learning path $lpid
      */
      function get_learnPath_progress($lpid, $lpUid)
      {
          global $TABLELEARNPATH, $TABLELEARNPATHMODULE, $TABLEUSERMODULEPROGRESS, $TABLEMODULE;
    
          // find progression for this user in each module of the path
    
          $sql = "SELECT UMP.`raw` AS R, UMP.`scoreMax` AS SMax, M.`contentType` AS CTYPE, UMP.`lesson_status` AS STATUS
                 FROM `".$TABLELEARNPATH."` AS LP,
                      `".$TABLELEARNPATHMODULE."` AS LPM,
                      `".$TABLEUSERMODULEPROGRESS."` AS UMP,
                      `".$TABLEMODULE."` AS M
                WHERE LP.`learnPath_id` = LPM.`learnPath_id`
                  AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
                  AND UMP.`user_id` = ".$lpUid."
                  AND LP.`learnPath_id` = ".$lpid."
                  AND LPM.`visibility` =\"SHOW\"
                  AND M.`module_id` = LPM.`module_id`
                  AND M.`contentType` != '".CTLABEL_ ."'";
          
	  $result = claro_sql_query($sql);
    
          //echo $sql."<br>";
          //echo mysql_error();
    
          $progress = 0;
          if (mysql_num_rows($result)==0)
          {
               $progression = 0;
          }
          else
          {
   
              //progression is calculated in pourcents
    
              while ($list = mysql_fetch_array($result))
              {
		 if( $list['SMax'] <= 0 )		
			$modProgress = 0 ;
		 else
                 	$modProgress = @round($list['R']/$list['SMax']*100);

                 // in case of scorm module, progression depends on the lesson status value
                 if (($list['CTYPE']=="SCORM") && ($list['SMax'] <= 0) && (( $list['STATUS'] == 'COMPLETED') || ($list['STATUS'] == 'PASSED')))
                 {
                    $modProgress = 100;
    
                 }
                 if ($modProgress>=0)
                 {
                    $progress += $modProgress;
    
                 }
              }
              // find number of visible modules in this path
              $sqlnum = "SELECT M.`module_id`
                        FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                              `".$TABLEMODULE."` AS M
                        WHERE LPM.`learnPath_id` = ".$lpid."
                        AND LPM.`visibility` = \"SHOW\"
                        AND M.`contentType` != '".CTLABEL_."'
                        AND M.`module_id` = LPM.`module_id`
                        ";
              $countResult = claro_sql_query($sqlnum);
    
              //echo $sqlnum." : ".mysql_num_rows($countResult)."<br>";
              $progression = @round($progress/mysql_num_rows($countResult));
              
    
          }
          return $progression;
      }
      /**
       * This function displays the list of available exercises in this course
       * With the form to add a selected exercise in the learning path
       *
       * @param string $dialogBox Error or confirmation text
       *
       * @author Piraux Sébastien <pir@cerdecam.be>
       * @author Lederer Guillaume <led@cerdecam.be>
       */
      function display_my_exercises($dialogBox)
      {
               global $langAddModule;
               global $langAddModulesButton;
               global $langExercise;
               global $langNoMoreModuleToAdd;
               global $langAddOneModuleButton;
	       global $clarolineRepositoryWeb;

               global $TABLEEXERCISES;

               echo        "<!-- display_my_exercises output -->\n";
               /*--------------------------------------
                  DIALOG BOX SECTION
                 --------------------------------------*/
               $colspan = 4;
               if ($dialogBox)
               {
                       claro_disp_message_box($dialogBox);
               }
               echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">
                      <tr class=\"headerX\" align=\"center\" valign=\"top\">
                        <th width=\"10%\">
                          ".$langAddModule."
                        </th>
                        <th>
                          ".$langExercise."
                        </th>
                      </tr><tbody>\n";

               // Display available modules
               echo "<form method=\"POST\" name=\"addmodule\" action=\"",$PHP_SELF,"?cmdglobal='add'\">";
               $atleastOne = false;
               $sql = "SELECT *
                         FROM `".$TABLEEXERCISES."`
                     ORDER BY  `titre`, `id`";
               $result = claro_sql_query($sql);
               while ($exercise = mysql_fetch_array($result))
               {
                      echo "
                           <tr>
                              <td align=\"center\">
                                 <input type=\"checkbox\" name=\"check_".$exercise['id']."\" id=\"check_".$exercise['id']."\" value=\"".$exercise['id']."\">
                              </td>

                              <td align=\"left\">
                                 <label for=\"check_".$exercise['id']."\" ><img src=\"".$clarolineRepositoryWeb."img/quiz.gif\" alt=\"".$langExercise."\" />".$exercise['titre']."</label>
                              </td>
                           </tr>";

                      // COMMENT

                      if ($exercise['description']!=null) {
                          echo "
                                <tr>
                                   <td>&nbsp;</td>
                                   <td colspan=\"5\">
                                          <small>".$exercise['description']."</small>
                                   </td>
                                </tr>";
                          }
                      $atleastOne = true;
                      }//end while another module to display
                echo "</tbody>\n<tfoot>";
                if ($atleastOne == false) {echo "<tr><td colspan=\"6\"><font color=\"red\">".$langNoMoreModuleToAdd."</font></td></tr>";}

               // Display button to add selected modules

               echo "<tr><td colspan=\"6\"><hr noshade size=\"1\"></td></tr>";
               if ($atleastOne == true) {
                   echo "<tr><td colspan=\"6\"><input type=\"submit\" name =\"insertExercise\" value=\"".$langAddModulesButton."\"></input></td></tr>";
                   }
               echo "</form></tfoot></table>";

               echo        "<!-- end of display_my_exercises output -->\n";
     }

     /**
      * This function is used to display the list of document available in the course
      * It also displays the form used to add selected document in the learning path
      *
      * @param string $dialogBox Error or confirmation text
      * @return nothing
      * @author Piraux Sébastien <pir@cerdecam.be>
      * @author Lederer Guillaume <led@cerdecam.be>
      */

     function display_my_documents($dialogBox)
     {
          global $is_AllowedToEdit;

          global $curDirName;
          global $curDirPath;
          global $parentDir;

          global $langUp;
          global $langName;
          global $langSize;
          global $langDate;
          global $langOk;
          global $langAddModulesButton;

          global $fileList;
	  global $clarolineRepositoryWeb;
          global $color2;

          /*==========================
                      DISPLAY
            ==========================*/
          echo        "<!-- display_my_documents output -->\n";

          $dspCurDirName = htmlentities($curDirName);
          $cmdCurDirPath = rawurlencode($curDirPath);
          $cmdParentDir  = rawurlencode($parentDir);

          echo "
                 <br>
                 <form action=\"".$PHP_SELF."\" method=\"POST\">";

          /*--------------------------------------
             DIALOG BOX SECTION
            --------------------------------------*/
          $colspan = 4;
          if ($dialogBox)
         {
                 claro_disp_message_box($dialogBox);
         }
          /*--------------------------------------
                    CURRENT DIRECTORY LINE
            --------------------------------------*/

          /* GO TO PARENT DIRECTORY */
          if ($curDirName) /* if the $curDirName is empty, we're in the root point 
	                    and we can't go to a parent dir */
          {
            echo 	"<a href=\"$PHP_SELF?cmd=exChDir&file=".$cmdParentDir."\">\n",
                "<img src=\"".$clarolineRepositoryWeb."img/parent.gif\" border=\"0\" align=\"absbottom\" hspace=\"5\" alt=\"\">\n",
                "<small>$langUp</small>\n",
                "</a>\n";
          }
          /* CURRENT DIRECTORY */
          echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">";  
          if ($curDirName) /* if the $curDirName is empty, we're in the root point
                              and there is'nt a dir name to display */
          {
                  echo	"<!-- current dir name -->\n",
                      "<tr>\n",
                      "<th class=\"superHeader\" colspan=\"$colspan\" align=\"left\">\n",
                      "<img src=\"".$clarolineRepositoryWeb."img/opendir.gif\" align=\"absbottom\" vspace=2 hspace=5 alt=\"\">\n",
                              $dspCurDirName,"\n",
                      "</td>\n",
                      "</tr>\n";
          }
          
          echo                "<tr class=\"headerX\" align=\"center\" valign=\"top\">";

          echo                "<th>&nbsp;</th>\n",
                              "<th>$langName</th>\n",
                              "<th>$langSize</th>\n",
                              "<th>$langDate</th>\n";

          echo                "</tr><tbody>\n";


          /*--------------------------------------
                       DISPLAY FILE LIST
            --------------------------------------*/

          if ($fileList)
          {
                  $iterator = 0;

                  while (list($fileKey, $fileName) = each ($fileList['name']))
                  {

                          $dspFileName = htmlentities($fileName);
                          $cmdFileName = rawurlencode($curDirPath."/".$fileName);

                          if ($fileList['visibility'][$fileKey] == "i")
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

                          if ($fileList['type'][$fileKey] == A_FILE)
                          {
                                  $image       = choose_image($fileName);
                                  $size        = format_file_size($fileList['size'][$fileKey]);
                                  $date        = format_date($fileList['date'][$fileKey]);
                                  $urlFileName = "../document/goto/?doc_url=".urlencode($cmdFileName);
                                  //$urlFileName = "goto/?doc_url=".urlencode($cmdFileName);
                                  //format_url($baseServUrl.$courseDir.$curDirPath."/".$fileName));
                          }
                          elseif ($fileList['type'][$fileKey] == A_DIRECTORY)
                          {
                                  $image       = 'dossier.gif';
                                  $size        = '&nbsp;';
                                  $date        = '&nbsp;';
                                  $urlFileName = $PHP_SELF.'?openDir='.$cmdFileName;
                          }

                          echo        "<tr align=\"center\"",$style,">\n";

                          if ($fileList['type'][$fileKey] == A_FILE)
                          {
                              $iterator++;
                              echo        "<td>",
                                              "<input type=\"checkbox\" name=\"insertDocument_".$iterator."\" id=\"insertDocument_".$iterator."\" value=\"",$curDirPath."/".$fileName,"\" />",
                                              "</td>\n";

                          }
                          else
                          {
                                  echo "<td>&nbsp;</td>";
                          }
                          echo           "<td align=\"left\">",
                                          "<a href=\"".$urlFileName."\"".$style.">",
                                          "<img src=\"".$clarolineRepositoryWeb."img/",$image,"\" border=\"0\" hspace=\"5\" alt=\"\" />",$dspFileName,"</a>",
                                          "</td>\n",

                                          "<td><small>",$size,"</small></td>\n",
                                          "<td><small>",$date,"</small></td>\n";

                          /* NB : Before tracking implementation the url above was simply
                           * "<a href=\"",$urlFileName,"\"",$style,">"
                           */


                          echo        "</tr>\n";

                          /* COMMENTS */

                          if ($fileList['comment'][$fileKey] != "" )
                          {
                                  $fileList['comment'][$fileKey] = htmlentities($fileList['comment'][$fileKey]);
                                  $fileList['comment'][$fileKey] = claro_parse_user_text($fileList['comment'][$fileKey]);

                                  echo        "<tr align=\"left\">\n",
                                                  "<td>&nbsp;</td>",
                                                  "<td colspan=\"$colspan\">",
                                                  "<div class=\"comment\">",
                                                  $fileList['comment'][$fileKey],
                                                  "</div>",
                                                  "</td>\n",
                                                  "</tr>\n";
                          }
                  }                                // end each ($fileList)
                  // form button
                  echo "</tbody><tfoot>";
                  echo "<tr><td colspan=\"6\"><hr noshade size=\"1\"></td></tr>";

                  echo "<tr>
                                <td colspan=\"$colspan\" align=\"left\">
                                 <input type=\"hidden\" name=\"openDir\" value =\"$curDirPath\" />
                                 <input type=\"hidden\" name=\"maxDocForm\" value =\"$iterator\" />
                                 <input type=\"submit\" name=\"submitInsertedDocument\" value=\"$langAddModulesButton\" />
                                 </td>
                                </tr>";
          }                                        // end if ( $fileList)

          echo        "</tfoot></table>\n",
                         "</form>\n";
          echo        "<!-- end of display_my_documents output -->\n";

     }
     
    /** 
     * Recursive Function used to find the deep of a module in a learning path
     * DEPRECATED : no more since the display has been reorganised
     *
     * @param $id id_of_module that we are looking for deep
     * @param $searchInarray of parents of modules in a learning path $searchIn[id_of_module] = parent_of_this_module
     *
     * @author Piraux Sébastien <pir@cerdecam.be>
     */
    function find_deep($id, $searchIn)
    {
      if ( $searchIn[$id] == 0 || !isset($searchIn[$id]) && $id == $searchIn[$id]) 
        return 0;
      else        
        return find_deep($searchIn[$id],$searchIn) + 1;
    }
    
    /**
     * Build an tree of $list from $id using the 'parent' field of lp_rel_learnPath_module
     * table. (recursive function)
     *
     * @param $list modules of the learning path list
     * @param $id learnPath_module_id of the node to build
     * @return tree of the learning path 
     *
     * @author Piraux Sébastien <pir@cerdecam.be>     
     */
    function build_element_list($list, $id = 0)
    {
      $tree= array();
      foreach ($list as $element)
      {
        if( $element['learnPath_module_id'] == $id )
        {
          $tree = $element; // keep all $list informations in the returned array
           // explicitly add 'name' and 'value' for the claro_build_nested_select_menu function 
          //$tree['name'] = $element['name']; // useless since 'name' is the same word in db and in the  claro_build_nested_select_menu function 
          $tree['value'] = $element['learnPath_module_id'];
          break;
        }
      }
      
      foreach ($list as $element)
      {
        if($element['parent'] == $id && ( $element['parent'] != $element['learnPath_module_id'] ))
        {
          if($id == 0)
            $tree[] = build_element_list($list,$element['learnPath_module_id']);
          else
            $tree['children'][] = build_element_list($list,$element['learnPath_module_id']);
        }
      }
      return $tree;
    }
    
    
    /**
     * return a flattened tree of the modules of a learnPath after having add
     * 'up' and 'down' fields to let know if the up and down arrows have to be 
     * displayed. (recursive function)
     * 
     * @param $elementList a tree array as one returned by build_element_list
     * @param $deepness
     * @return array containing infos of the learningpath, each module is an element 
        of this array and each one has 'up' and 'down' boolean and deepness added in
     *
     * @author Piraux Sébastien <pir@cerdecam.be>
     */
    function build_display_element_list($elementList, $deepness = 0)
    {
        $count = 0;
        $first = true;
        $last = false;
        $displayElementList = array();
        
        foreach($elementList as $thisElement)
        {
            $count++;
            
            $temp = $thisElement['children'];
            // we use 'children' to calculate the deepness of the module, it will be displayed
            // using a spacing multiply by deepness
            $thisElement['children'] = $deepness;
            
            //--- up and down arrows displayed ?
            if ($count == count($elementList) )
                $last = true;
            
            $thisElement['up'] = $first ? false : true;
            $thisElement['down'] = $last ? false : true;
            
            //--- 
            $first = false;
            
            $displayElementList[] = $thisElement;
    
            if (   isset( $temp )
                && sizeof($temp ) > 0)
            {
                $displayElementList = array_merge( $displayElementList,
                                              build_display_element_list($temp, $deepness + 1 ) );
            }
        }
        return  $displayElementList;
    } 
    
    /**
     * This function set visibility for all the nodes of the tree module_tree
     *
     * @param $module_tree tree of modules we want to change the visibility
     * @param $visibility ths visibility string as requested by the DB
     *
     * @author Piraux Sébastien <pir@cerdecam.be>
     */
    function set_module_tree_visibility($module_tree, $visibility)
    {
      global $TABLELEARNPATHMODULE;
      
      foreach($module_tree as $module)
      {
        if($module['visibility'] != $visibility)
        {
            $sql = "UPDATE `".$TABLELEARNPATHMODULE."`
                            SET `visibility` = '$visibility'
                            WHERE `learnPath_module_id` = ".$module['learnPath_module_id']."
                              AND `visibility` != '$visibility'";
            $query = claro_sql_query ($sql);
        }
        if ( is_array($module['children']) ) set_module_tree_visibility($module['children'], $visibility);
      }
    }
    
    /**
     * This function deletes all the nodes of the tree module_tree
     *
     * @param $module_tree tree of modules we want to change the visibility
     *
     * @author Piraux Sébastien <pir@cerdecam.be>
     */
    function delete_module_tree($module_tree)
    {
        global $TABLEMODULE, $TABLELEARNPATHMODULE, $TABLEASSET, $TABLEUSERMODULEPROGRESS;
        
        foreach($module_tree as $module)
        {
            switch($module['contentType'])
            {
              case CTSCORM_ :
                  // delete asset if scorm
                  $delAssetSql = "DELETE
                                        FROM `".$TABLEASSET."`
                                        WHERE `module_id` =  ".$module['module_id']."
                                        ";
                  claro_sql_query($delAssetSql);

              case CTLABEL_ : // delete module if scorm && if label
                  $delModSql = "DELETE
                                      FROM `".$TABLEMODULE."`
                                      WHERE `module_id` =  ".$module['module_id']."
                                       ";
                  claro_sql_query($delModSql);

              default : // always delete LPM and UMP
                claro_sql_query("DELETE
                               FROM `".$TABLELEARNPATHMODULE."`
                              WHERE `learnPath_module_id` = ".$module['learnPath_module_id']);
                claro_sql_query("DELETE
                               FROM `".$TABLEUSERMODULEPROGRESS."`
                               WHERE `learnPath_module_id` = ".$module['learnPath_module_id']);

              break;
            }
        }
        if ( is_array($module['children']) ) delete_module_tree($module['children'], $visibility);
    }
    /**
     * This function return the node with $module_id (recursive)
     * 
     *
     * @param $lpModules array the tree of all modules in a learning path
     * @param $iid node we are looking for
     * @param $field type of node we are looking for (learnPath_module_id, module_id,...)
     *
     * @return array the requesting node (with all its children)
     *
     * @author Piraux Sébastien <pir@cerdecam.be>
     */
      function get_module_tree( $lpModules , $id, $field = 'module_id')
     {
        foreach( $lpModules as $module)
        {
            if( $module[$field] == $id)
            {
                return $module;
            }
            elseif ( is_array($module['children']) )
            {
                $temp = get_module_tree($module['children'], $id);
                if( is_array($temp) ) 
                  return $temp;
                // else check next node   
            }

        }
     }
     
    /**
      * This function allow to see if a time string is the SCORM requested format : hhhh:mm:ss.cc
      *
      * @param $time a suspected SCORM time value, returned by the javascript API
      *
      * @author Lederer Guillaume <led@cerdecam.be>
      */
     function isScormTime($time)
     {
        $mask = "/^[0-9]{2,4}:[0-9]{2}:[0-9]{2}.?[0-9]?[0-9]?$/";
        if (preg_match($mask,$time))
         {
           return true;
         }
    
        return false;
     }
    
     /**
      * This function allow to add times saved in the SCORM requested format : hhhh:mm:ss.cc
      *
      * @param $time1 a suspected SCORM time value, total_time,  in the API
      * @param $time2 a suspected SCORM time value, session_time to add, in the API
      *
      * @author Lederer Guillaume <led@cerdecam.be>
      *
      */
     function addScormTime($time1, $time2)
     {
       if (isScormTime($time2))
       {
          //extract hours, minutes, secondes, ... from time1 and time2
    
          $mask = "/^([0-9]{2,4}):([0-9]{2}):([0-9]{2}).?([0-9]?[0-9]?)$/";
    
          preg_match($mask,$time1, $matches);
          $hours1 = $matches[1];
          $minutes1 = $matches[2];
          $secondes1 = $matches[3];
          $primes1 = $matches[4];
    
          preg_match($mask,$time2, $matches);
          $hours2 = $matches[1];
          $minutes2 = $matches[2];
          $secondes2 = $matches[3];
          $primes2 = $matches[4];
    
          // calculate the resulting added hours, secondes, ... for result
    
          $primesReport = false;
          $secondesReport = false;
          $minutesReport = false;
          $hoursReport = false;
    
             //calculate primes
    
          if ($primes1 < 10) {$primes1 = $primes1*10;}
          if ($primes2 < 10) {$primes2 = $primes2*10;}
          $total_primes = $primes1 + $primes2;
          if ($total_primes >= 100)
          {
            $total_primes -= 100;
            $primesReport = true;
          }
    
             //calculate secondes
    
          $total_secondes = $secondes1 + $secondes2;
          if ($primesReport) {$total_secondes ++;}
          if ($total_secondes >= 60)
          {
            $total_secondes -= 60;
            $secondesReport = true;
          }
    
            //calculate minutes
    
          $total_minutes = $minutes1 + $minutes2;
          if ($secondesReport) {$total_minutes ++;}
          if ($total_minutes >= 60)
          {
            $total_minutes -= 60;
            $minutesReport = true;
          }
    
            //calculate hours
    
          $total_hours = $hours1 + $hours2;
          if ($minutesReport) {$total_hours ++;}
          if ($total_hours >= 10000)
          {
            $total_hours -= 10000;
            $hoursReport = true;
          }
    
             // construct and return result string
    
          if ($total_hours < 10) {$total_hours = "0".$total_hours;}
          if ($total_minutes < 10) {$total_minutes = "0".$total_minutes;}
          if ($total_secondes < 10) {$total_secondes = "0".$total_secondes;}
    
          $total_time = $total_hours.":".$total_minutes.":".$total_secondes.".".$total_primes;
          return $total_time;
       }
       else
       {
          return $time1;
       }
     }
?>
