<?php
    // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.3.2 $Revision$                            |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
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

  $langFile = "learningPath";


  ////////////////////////////////////////////

  include ('../include/claro_init_global.inc.php');

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
  $interbredcrump[]= array ("url"=>"../learnPath/learningPathAdmin.php", "name"=> $langToolName." ".$langAdmin);
  $nameTools = $langInsertPublicModuleToolName;

  //header

  @include($includePath."/claro_init_header.inc.php");

  // tables names

  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

  //Session variables

  $_SESSION['openCatTable'];
  $_SESSION['openCourseTable'];

  //Clarodoc librairies needed

  @include($includePath."/libs/learnPath.lib.inc.php");

  // $_SESSION
  if ( !isset($_SESSION['path_id']) )
  {
        die ("<center> Not allowed ! (path_id not set :@ )</center>");
  }
  
  @include($includePath."/fileManagelib.inc.php");
  @include($includePath."/fileUploadlib.inc.php");

  //Handle interbredcrumb

  $interbredcrump[]= array ("url"=>"../learningPath/learningPathList.php", "name"=> $langLearningPathList);
  $interbredcrump[]= array ("url"=>"../learningPath/learningPathAdmin.php", "name"=> $langToolName." ".$langAdmin);
  $nameTools = $langInsertPublicModuleToolName;

  // main page

   if (! $is_courseAllowed && !$is_courseAdmin) die ("<center>Not allowed !</center>");

   $is_AllowedToEdit = $is_courseAdmin;

   if ($is_AllowedToEdit)
   {
        // CALLED COMMAND HANDLING

        switch ($cmd) {

            // INSERT COMMAND HANDLING

            case "insert" :

               // 3) Duplicate the assets,learningPathModules and modules'references in the current course DB

                 //build INSERT query to duplicate module's references into current course DB

               $courseModuleTable = getTableName($courseCode,'module');
               $query = "SELECT *
                          FROM ".$courseModuleTable."
                          WHERE `accessibility`=1
                          ";

                $result = mysql_query($query);

                while ($list = mysql_fetch_array($result))
                {

                   // DUPLICATE CURRENT SELECTED MODULE------------------------------------------

                   // see if check box was checked if so , we add the module in the current course module list

                   if ($_POST['check_'.$list['module_id']])
                   {

                       //insert new module in the current course DB

                       $insertQuery = "INSERT INTO `".$TABLEMODULE."`
                                (`name`, `comment`, `accessibility`, `contentType`, `launchData`) VALUES ";
                       $insertQuery .=" ('".$list['name']."', '".$list['comment']."', '1', '".$list['contentType']."',  '".$list['launchData']."')";
                       mysql_query($insertQuery);

                       //get ID of newly created modules in current course DB

                       $thisInsertedModuleId = mysql_insert_id();

                       //get order where to place the new learning path module

                       $result2 = mysql_query("SELECT MAX(`order`)
                                          FROM `".$TABLELEARNINGPATHMODULE."`
                                          WHERE learningPath_id = '".$_SESSION['path_id']."'
                                          ");
                       list($orderMax) = mysql_fetch_row($result2);
                       $order = $orderMax + 1;

                       //insert new learningPathModule in the current Course DB

                       $addedComment = $langImportedCourse." ".$courseCode;
                       $insertQuery = "INSERT INTO `".$TABLELEARNINGPATHMODULE."`
                                       (`learningPath_id`, `module_id`, `addedComment`, `order`, `credit`) VALUES ";
                       $insertQuery .=" ('".$_SESSION['path_id']."', '".$thisInsertedModuleId."', '".$addedComment."', '".$order."',0)
                                       ";
                       mysql_query($insertQuery);

                       echo "Modules have been added to your course and to this learning path";

                       // DUPLICATE NEEDED ASSETS AND DB INFORMATIONS FOR THIS MODULE -----------------

                       // (Procedure is different for each contenTtype possible)

                       switch ($list['contentType'])
                       {
                          case "HANDMADE" :

                                  // 1) Find the assets that must be duplicated

                               $courseModuleTable = getTableName($courseCode,'asset');
                               $assetQuery = "SELECT *
                                              FROM ".$courseAssetTable."
                                              WHERE `module_id` = ".$list['module_id']."
                                              ";
                               $assetResult = mysql_query($assetQuery);


                                  // 2) Duplicate the assets files and directories on the server :
                                  //    we copy the directory and rename it after

                               $origDirPath = $rootSys.$courseCode."/modules/module_".$list['module_id'];
                               $destination = $rootSys.$_cid."/modules";

                               echo $origDirPath."  vers  ".$destination;

                               copyDirToAndRename($origDirPath, $destination, "module_".$thisInsertedModuleId);

                                   // 3) duplicate all assets references in the DB

                               if (mysql_fetch_row($assetResult)!=0)
                               {
                                    while ($assetList= mysql_fetch_array($assetResult))
                                    {
                                           $insertAssetQuery = "INSERT INTO `".$TABLEASSET."`
                                                       (`module_id`, `path`, `comment`) VALUES ";
                                           $insertAssetQuery .= "('".$thisInsertedModuleId."','".$assetList['path']."', '".$assetList['comment']."'), ";
                                           mysql_query($insertAssetQuery);
                                    }
                               }
                               echo $assetQuery;
                               echo "duplicate files";
                               break;
                          case "CLARODOC" :
                                break;
                          case "DOCUMENT" :
                                break;
                          case "EXERCISE" :
                                break;
                          case "SCORM" :
                                break;

                       }  // end of case file and Assets duplication for insert

                   } //end if checkbox was checked

                }//end list of module addables
                break;

            // OPEN A SPECIFIC CATEGORY COMMAND HANDLING

            case "category" :

               $_SESSION['openCatTable'][$currentCat]=1;
               break;

            // OPEN A SPECIFIC COURSE COMMAND HANDLING

            case "course" :

               $_SESSION['openCourseTable'][$currentCourse]=1;
               break;

            //CLOSE A SPECIFIC CATEGORY COMMAND HANDLING

            case "closecat" :

               $_SESSION['openCatTable'][$currentCat]=0;
               break;

            // CLOSE A SPECIFIC COURSE COMMAND HANDLING

            case "closecourse" :

               $_SESSION['openCourseTable'][$currentCourse]=0;
               break;
        }
   }

  //  Display title

  echo "<h3>".$langGetModuleFromOtherCourse."</h3>";



  //STEP ONE : display form to add public moduels of other courses----------------------------------




  $atLeastOneAddable = FALSE;
  $queryCat = "SELECT * FROM faculte";
  $result = mysql_query($queryCat);



  echo "<table width=100%>
           <tr bgcolor='#E6E6E6' align = 'center'>
             <td width=40% >
               ".$langCategories."
             </td>
             <td>
               ".$langPublicModule."
             </td>
           </tr>
         ";

   //    (1)  Display link to close all categories

   /*echo "<tr>
            <td>
               <a href= \"",$PHP_SELF,"?close=do\">close all</a>
            </td>
         </tr>
          ";

   if ($close=='do') {
       $close = '';
       $_SESSION['openCatTable'][]=0;
       $_SESSION['openCourseTable'][]=0;
       }   */

   //   (2)   Display list of the faculties

  while ($list=mysql_fetch_array($result))
  {

         if ($_SESSION['openCatTable'][$list['id']]!=1)
         {

             //display the closed category

               // check if category must be a link (if it contains at least one course)

             $queryExist = "SELECT * FROM cours WHERE `faculte`='".$list['code']."' AND `code` !='".$_cid."'";
             $resultExist = mysql_query($queryExist);

             if (mysql_num_rows($resultExist)!=0)
             {

               //display with a link if at least one course in this category

                 echo "<tr>
                           <td>+ <a href=\"",$PHP_SELF,"?cmd=category&currentCat=".$list['id']."\">
                           ".$list['name']."</a>
                          </td>
                       </tr>
                 ";
             }
             else
             {

               //display without a link when no course available

                 echo "<tr>
                          <td>+ ".$list['name']."</a>
                          </td>
                       </tr>
                 ";
             } //end if .. else ..

         } //end if closed category

         // display the opened category

         else
         {
             display_opened_cat();
         }

  } // end of while
  echo "</table>";


  //####################################################################################\\
   //################################## MODULES LIST ####################################\\
   //####################################################################################\\


   echo "<h3>".$langPathContentTitle."</h3>";

   // build array of parameters to display

   $param_array[1] = "contentType";
   $param_array[2] = "name";
   $param_array[3] = "addedComment";

   $table[1] = $TABLELEARNINGPATHMODULE;
   $table[2] = $TABLEMODULE;

   // display the learning path content
   // tab header is not set by function so ... display it
   echo "<table width='100%'  border='0' cellspacing='2'>
             <tr bgcolor='#E6E6E6' align='center' valign='top'>
               <td width='30%' >".$langModule."</td>
               <td>".$langAddedComment."</td>
             </tr>";

   display_path_content($param_array, $table);
   // same as header
   echo "</table>";


   // footer

   @include($includePath."/claro_init_footer.inc.php");


/*###############################################################################################*/
/*###################################### END OF MAIN ############################################*/
/*###############################################################################################*/

/*-------------------------DECLARATION OF FUNCTION SPECIFIC TO THIS SCRIPT-----------------------*/
  /**
    *  This function allows to display the current category 's contain,
    *  indeed its course and eventually the content of opened courses
    *
    *
    *
    */

  function display_opened_cat()
  {

     global $list;
     global $cat_id;
     global $langClose;
     global $TABLEMODULE;
     global $atLeastOneAddable;
     global $langNoPublicModule;
     global $langAvailable;
     global $_cid;

     // display category name with link to close it

     echo "<tr>
                   <td>- ".$list['name']." <a href=\"",$PHP_SELF,"?cmd=closecat&currentCat=".$list['id']."\">
                       [".$langClose."]</a>
                      </td>
                   </tr>
             ";

     //look for courses of current category in the database

     $queryCourse = "SELECT * FROM cours WHERE `faculte` = '".$list['code']."' AND code !='".$_cid."'";
     echo $queryCourse;
     $resultCourse = mysql_query($queryCourse);

     //display courses links of this category

     while ($listCourse=mysql_fetch_array($resultCourse))
     {


            // check if the course must be a link (if it contains at least one public module) or just a texte
            $course = $listCourse['cours_id'];
            $courseCode = $listCourse['code'];
            $tableName = getTableName($courseCode,'module');
            $queryExist = "SELECT *
                              FROM `".$tableName."`
                             WHERE `accessibility` = 1";
            $resultExist = mysql_query($queryExist);

            if (mysql_num_rows($resultExist)!=0) {

                // display closed courses

                if ($_SESSION['openCourseTable'][$listCourse['cours_id']]==0)
                {
                    echo "<tr>
                              <td style=\"padding-left : 40px;\">+ <a href=\"".$PHP_SELF."?cmd=course&currentCourse=".$listCourse['cours_id']."\">
                                ".$listCourse['intitule']."</a>
                              </td>
                              <td align=\"center\">
                                ".mysql_num_rows($resultExist)." ".$langAvailable."
                              </td>
                          </tr>
                           ";
                }
                else
                {

                       //display opened courses

                       $course = $listCourse['cours_id'];
                       $courseCode = $listCourse['code'];
                       $courseName = $listCourse['intitule'];
                       echo $course;
                       echo $courseName;
                       display_opened_course($course,$courseName,$courseCode);

                 }//end if open course

            }//end if at least one course
            else
            {

                 // display course name without the link to open it

                 echo "<tr>
                        <td style=\"padding-left : 40px;\">+
                                ".$listCourse['intitule']."
                        </td>
                        <td align=\"center\">
                          ".$langNoPublicModule."
                        </td>
                       </tr>
                       ";

            }
     } // end of 'while($listCourse ...'
  } // end of display_opened_cat

  /**
    *  This function allows to display the current opened course's contain,
    *  indeed its public modules
    *
    *  @param the id of the course
    *  @param the name (or intitule) of the course
    *  @param the code of the course
    *  @return display the courses 's public modules inside the right balises
    *          for the output html table and form
    */

  function display_opened_course($course, $courseName, $courseCode)
  {

     global $TABLEMODULE;

     // look for public module(s) of the course

     global $listCourse;
     global $cat_id;
     global $langClose;
     global $langAvailable;
     global $langAddModule;

     $tableName = getTableName($courseCode,'module');
     $queryModule = "SELECT *
                       FROM `".$tableName."`
                      WHERE `accessibility` = 1";
     $moduleResult=mysql_query($queryModule);
     $nbModules = mysql_num_rows($moduleResult);

     // display name of the course

     echo "<tr>
              <td style=\"padding-left : 40px;\">
                - ".$courseName." <a href=\"",$PHP_SELF,"?cmd=closecourse&currentCourse=".$course."\">
                [Close]</a>
              </td>
              <td align=\"center\">
                 ".mysql_num_rows($moduleResult)." ".$langAvailable."
              </td>
              </tr>
               ";

    // Display the available modules  with form to add them

       // create form

     echo "<form name='addModule' method='POST' action=\"",$PHP_SELF,"\">";

       // create list with check box

     while ($moduleList=mysql_fetch_array($moduleResult))
     {

            echo "<tr>
                   <td style=\"padding-left : 60px;\">
                       <input type=checkbox name='check_".$moduleList['module_id']."'></input>
                       <img src=\"../img/".selectImage($moduleList['contentType'])." \">
                       "
                       .$moduleList['name']."
                   </td>
                  </tr>
            ";
     }
     echo "<tr>
               <td style=\"padding-left : 80px;\">
                   <input type=submit value=' ".$langAddModule." '></input>
                   <input type='hidden' name=courseModuleTable value='".$tableName."'>
                   <input type='hidden' name=courseCode value='".$courseCode."'>
                   <input type='hidden' name=cmd value='insert'>
               </td>
           </tr>
           ";
     echo "</form>";
  } // end of display_opened_course

  /**
   * This function allows to get the name of a table of a certain course.
   * It must take account that Claroline can be installed with one or several DB.
   *
   *  @param the id of the course we want to find.
   *  @return the name of the table in the DB.
   */

  function getTableName($courseCode,$tableNeeded)
  {
     global $singleDbEnabled, $dbGlu;
     if ($singleDbEnabled == true)
     {
         return "crs_".$courseCode."_".$tableNeeded."";
     }
     else
     {
         return $courseCode.$dbGlu.$tableNeeded;
     }
  }
  /**
    * This function  returns the adequate query to add some public modules
    * in the current learning path.
    *
    * @return the query to add the assets and the modules in the courrent course DB
    *
    */

  function buildAssetSQLQuery()
  {


  }
?>
