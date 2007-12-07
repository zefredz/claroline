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
  |  Authors: Piraux Sébastien <pir@cerdecam.be>                         |
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

  @include($includePath."/../lang/english/document.inc.php");
  @include($includePath."/../lang/".$languageInterface."/document.inc.php");

  $htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\" $langAreYouSureDeleteModule \"+ name + \" ?\"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

  $interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);
  $interbredcrump[]= array ("url"=>"../learnPath/learningPathAdmin.php", "name"=> $langLearningPathAdmin);
  $nameTools = $langInsertMyDocToolName;

  //header
  @include($includePath."/claro_init_header.inc.php");



  // tables names

  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

  // document browser vars
  $TABLEDOCUMENT     = $_course['dbNameGlu']."document";


  $courseDir   = $_course['path']."/document";
  $moduleDir   = $_course['path']."/modules";
  $baseWorkDir = $coursesRepositorySys.$courseDir;
  $moduleWorkDir = $coursesRepositorySys.$moduleDir;

  //lib of this tool
  @include($includePath."/lib/learnPath.lib.inc.php");

  include($includePath."/lib/fileDisplay.lib.php");
  include($includePath."/lib/fileManage.lib.php");

  // $_SESSION
  if ( !isset($_SESSION['path_id']) )
  {
        die ("<center> Not allowed ! (path_id not set :@ )</center>");
  }


/*======================================
       CLAROLINE MAIN
  ======================================*/


      // main page

   $is_AllowedToEdit = $is_courseAdmin;
   if (! $is_AllowedToEdit or ! $is_courseAllowed ) die ("<center>Not allowed !</center>");



   // FUNCTION NEEDED TO BUILD THE QUERY TO SELECT THE MODULES THAT MUST BE AVAILABLE

   // 1)  We select first the modules that must not be displayed because
   // as they are already in this learning path

   function buildRequestModules()
   {

     global $TABLELEARNPATHMODULE;
     global $TABLEMODULE;

     $firstSql = "SELECT `module_id`
                    FROM `".$TABLELEARNPATHMODULE."` AS LPM
                   WHERE LPM.`learnPath_id` = ".$_SESSION['path_id'];

     $firstResult = claro_sql_query($firstSql);

     // 2) We build the request to get the modules we need

     $sql = "SELECT M.*
               FROM `".$TABLEMODULE."` AS M
              WHERE 1 = 1";

     while ($list=mysql_fetch_array($firstResult))
     {
            $sql .=" AND M.`module_id` != ".$list['module_id'];
     }

     /** To find which module must displayed we can also proceed  with only one query.
       * But this implies to use some features of MySQL not available in the version 3.23, so we use
       * two differents queries to get the right list.
       * Here is how to proceed with only one

     $query = "SELECT *
                FROM `".$TABLEMODULE."` AS M
                WHERE NOT EXISTS(SELECT * FROM `".$TABLELEARNPATHMODULE."` AS TLPM
                WHERE TLPM.`module_id` = M.`module_id`)"; */

     return $sql;
   }//end function

   //####################################################################################\\
   //################################ DOCUMENTS LIST ####################################\\
   //####################################################################################\\

   // display title

  claro_disp_tool_title($nameTools);


       // FORM SENT
       /*
        *
        * SET THE DOCUMENT AS A MODULE OF THIS LEARNING PATH
        *
        */
        // evaluate how many form could be sent

        $iterator = 0;
        while ($iterator <= $_GET['maxDocForm'])
        {
           $iterator++;

           if( $submitInsertedDocument && isset($_GET['insertDocument_'.$iterator]) )
           {
                $insertDocument = $_GET['insertDocument_'.$iterator];
                if (get_magic_quotes_gpc())
                  $sourceDoc =   stripslashes($baseWorkDir.$insertDocument);
                else
                  $sourceDoc =   $baseWorkDir.$insertDocument;

                if ( check_name_exist($sourceDoc) ) // source file exists ?
                {
                        // version without duplication of the document
                        // check if a module of this course already used the same document
                        $sql = "SELECT *
                                  FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
                                 WHERE A.`module_id` = M.`module_id`
                                   AND A.`path` LIKE \"".$insertDocument."\"
                                   AND M.`contentType` = \"".CTDOCUMENT_."\"";
                        $query = claro_sql_query($sql);
                        $num = mysql_numrows($query);
                        if($num == 0)
                        {
                             // create new module
                             $sql = "INSERT
                                       INTO `".$TABLEMODULE."`
                                            (`name` , `comment`, `contentType`)
                                     VALUES ('".claro_addslashes(basename($insertDocument))."' , '".addslashes($langDefaultModuleComment)."', '".CTDOCUMENT_."' )";
                             //echo "<br /><1> ".$sql;
                             $query = claro_sql_query($sql);

                             $insertedModule_id = mysql_insert_id();

                             // create new asset
                             $sql = "INSERT
                                       INTO `".$TABLEASSET."`
                                            (`path` , `module_id` , `comment`)
                                     VALUES ('".claro_addslashes($insertDocument)."', $insertedModule_id , '')";
                             //echo "<br /><2> ".$sql;
                             $query = claro_sql_query($sql);

                             $insertedAsset_id = mysql_insert_id();

                             $sql = "UPDATE `".$TABLEMODULE."`
                                        SET `startAsset_id` = $insertedAsset_id
                                      WHERE `module_id` = $insertedModule_id";
                             //echo "<br /><3> ".$sql;
                             $query = claro_sql_query($sql);

                             // determine the default order of this Learning path
                             $result = claro_sql_query("SELECT MAX(`rank`)
                                                      FROM `".$TABLELEARNPATHMODULE."`");

                             list($orderMax) = mysql_fetch_row($result);
                             $order = $orderMax + 1;
                             // finally : insert in learning path
                             $sql = "INSERT
                                       INTO `".$TABLELEARNPATHMODULE."`
                                            (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
                                     VALUES ('".$_SESSION['path_id']."', '".$insertedModule_id."','".addslashes($langDefaultModuleAddedComment)."', ".$order.", 'OPEN')";
                             //echo "<br /><4> ".$sql;
                             $query = claro_sql_query($sql);

                              if (get_magic_quotes_gpc())
                                $addedDoc =   stripslashes(basename($insertDocument));
                              else
                                $addedDoc =  basename($insertDocument);

                             $dialogBox .= $addedDoc ." ".$langDocInsertedAsModule."<br>";
                        }
                        else
                        {
                             // check if this is this LP that used this document as a module
                             $sql = "SELECT *
                                       FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                                            `".$TABLEMODULE."` AS M,
                                            `".$TABLEASSET."` AS A
                                      WHERE M.`module_id` =  LPM.`module_id`
                                        AND M.`startAsset_id` = A.`asset_id`
                                        AND A.`path` = '".claro_addslashes($insertDocument)."'
                                        AND LPM.`learnPath_id` = ".$_SESSION['path_id'];
                             $query2 = claro_sql_query($sql);
                             $num = mysql_numrows($query2);
                             if ($num == 0)     // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                             {
                                 $thisDocumentModule = mysql_fetch_array($query);
                                 // determine the default order of this Learning path
                                 $result = claro_sql_query("SELECT MAX(`rank`)
                                                          FROM `".$TABLELEARNPATHMODULE."`");

                                 list($orderMax) = mysql_fetch_row($result);
                                 $order = $orderMax + 1;
                                 // finally : insert in learning path
                                 $sql = "INSERT
                                           INTO `".$TABLELEARNPATHMODULE."`
                                                (`learnPath_id`, `module_id`, `specificComment`, `rank`,`lock`)
                                         VALUES ('".$_SESSION['path_id']."', '".$thisDocumentModule['module_id']."','".addslashes($langDefaultModuleAddedComment)."', ".$order.",'OPEN')";
                                 //echo "<br /><4> ".$sql;
                                 $query = claro_sql_query($sql);
                                  if (get_magic_quotes_gpc())
                                    $addedDoc =   stripslashes(basename($insertDocument));
                                  else
                                    $addedDoc =  basename($insertDocument);

                                 $dialogBox .= $addedDoc ." ".$langDocInsertedAsModule."<br>";
                             }
                             else
                             {
                                 $dialogBox .= basename($insertDocument)." : ".$langDocumentAlreadyUsed."<br>";
                             }
                        }
                        /*
                            // version with duplication of the document in module repository
                        // check if a module of this course already used the same document
                        $sql = "SELECT *
                                  FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
                                 WHERE A.`module_id` = M.`module_id`
                                   AND A.`path` LIKE \"".basename($insertDocument)."%\"";
                        $query = claro_sql_query($sql);
                        $num = mysql_numrows($query);
                        if($num == 0)
                        {
                            // create new module in DB
                            // contentType = 'DOCUMENT'
                            // accessibility is private
                            // comment = doc comment if exists , else empty
                            // create unique asset for the document, in DB
                            // startAsset_id will be updated later
                            $sql = "INSERT
                                      INTO `".$TABLEMODULE."`
                                           ( `name` , `comment`, `contentType`)
                                    VALUES ('".basename($insertDocument)."' , '$comment', 'DOCUMENT' )";
                            //echo "<br /><2> ".$sql;
                            $query = claro_sql_query($sql);
                            $insertedModule_id = mysql_insert_id();

                            $target = $baseServDir.$moduleDir."/module_".$insertedModule_id;
                            // create dir if not exists
                            if (!is_dir($target) ) mkdir($target,0777);
                            $fileName = basename($sourceDoc);

                            if ( check_name_exist($target."/".$fileName) )
                            {
                                  $dialogBox .= $langFileAlreadyExistsInDestinationDir;
                            }
                            elseif ( is_file($sourceDoc) )
                            {

                                    // physical copy
                                    copy($sourceDoc , $target."/".$fileName);

                                    // logical copy
                                    // select comment of the selected document
                                    $sql = "SELECT comment
                                              FROM `".$TABLEDOCUMENT."`
                                             WHERE `path` LIKE \"".basename($insertDocument)."%\"";

                                    $query = claro_sql_query($sql);
                                    if ( $row = mysql_fetch_array($query) )
                                    {
                                        $comment = $row['comment'];
                                    }

                                    $sql = "INSERT
                                              INTO `".$TABLEASSET."`
                                                   (`path` , `module_id` , `comment`)
                                            VALUES ('".basename($insertDocument)."', $insertedModule_id , '$comment')";

                                    $query = claro_sql_query($sql);
                                    $insertedAsset_id = mysql_insert_id();

                                    // update module to set startAsset_id
                                    $sql = "UPDATE `".$TABLEMODULE."`
                                               SET `startAsset_id` = $insertedAsset_id
                                             WHERE `module_id` = $insertedModule_id";
                                    $query = claro_sql_query($sql);


                                    // determine the default order of this Learning path
                                    $result = claro_sql_query("SELECT MAX(`order`)
                                                              FROM `".$TABLELEARNPATHMODULE."`");

                                    list($orderMax) = mysql_fetch_row($result);
                                    $order = $orderMax + 1;
                                    // finally : insert in learning path
                                    $sql = "INSERT
                                              INTO `".$TABLELEARNPATHMODULE."`
                                                   (`learningPath_id`, `module_id`, `addedComment`, `rank`)
                                            VALUES ('".$_SESSION['path_id']."', '".$insertedModule_id."','', ".$order.")";
                                    $query = claro_sql_query($sql);

                                    $dialogBox .= basename($insertDocument) ." ".$langDocInsertedAsModule;
                            }
                        } // enf if (num == 0)
                        else
                        {
                            $dialogBox .= $langDocumentAlreadyUsed;
                        }

                        */
                }


           }
       }

      /*======================================
             DEFINE CURRENT DIRECTORY
        ======================================*/

      if (isset($openDir) ) // $newDirPath is from createDir command (step 2) and $uploadPath from upload command
      {
              $curDirPath = $openDir;
              /*
               * NOTE: Actually, only one of these variables is set.
               * By concatenating them, we eschew a long list of "if" statements
               */
      }
      else
      {
              $curDirPath="";
      }

      if ($curDirPath == "/" || $curDirPath == "\\" || strstr($curDirPath, ".."))
      {
              $curDirPath =""; // manage the root directory problem

              /*
               * The strstr($curDirPath, "..") prevent malicious users to go to the root directory
               */
      }

      $curDirName = basename($curDirPath);
      $parentDir  = dirname($curDirPath);

      if ($parentDir == "/" || $parentDir == "\\")
      {
              $parentDir =""; // manage the root directory problem
      }

      /*======================================
              READ CURRENT DIRECTORY CONTENT
        ======================================*/

      /*--------------------------------------
        SEARCHING FILES & DIRECTORIES INFOS
                    ON THE DB
        --------------------------------------*/

      /* Search infos in the DB about the current directory the user is in */

      $result = claro_sql_query ("SELECT *
                                FROM `".$TABLEDOCUMENT."`
                               WHERE `path` LIKE \"".$curDirPath."/%\"
                                 AND `path` NOT LIKE \"".$curDirPath."/%/%\"");

      while($row = mysql_fetch_array($result, MYSQL_ASSOC))
      {
              $attribute['path'      ][] = $row['path'      ];
              $attribute['visibility'][] = $row['visibility'];
              $attribute['comment'   ][] = $row['comment'   ];
      }


      /*--------------------------------------
        LOAD FILES AND DIRECTORIES INTO ARRAYS
        --------------------------------------*/
      @chdir (realpath($baseWorkDir.$curDirPath))
      or die("<center>
              <b>Wrong directory !</b>
              <br /> Please contact your platform administrator.</center>");
      $handle = opendir(".");

      define('A_DIRECTORY', 1);
      define('A_FILE',      2);


      while ($file = readdir($handle))
      {
              if ($file == "." || $file == "..")
              {
                      continue;                                                // Skip current and parent directories
              }

              $fileList['name'][] = $file;

              if(is_dir($file))
              {
                      $fileList['type'][] = A_DIRECTORY;
                      $fileList['size'][] = false;
                      $fileList['date'][] = false;
              }
              elseif(is_file($file))
              {
                      $fileList['type'][] = A_FILE;
                      $fileList['size'][] = filesize($file);
                      $fileList['date'][] = filectime($file);
              }


              /*
               * Make the correspondance between
               * info given by the file system
               * and info given by the DB
               */

              $keyDir = sizeof($dirNameList)-1;

              if ($attribute)
              {
                      $keyAttribute = array_search($curDirPath."/".$file, $attribute['path']);
              }

              if ($keyAttribute !== false)
              {
                              $fileList['comment'   ][] = $attribute['comment'   ][$keyAttribute];
                              $fileList['visibility'][] = $attribute['visibility'][$keyAttribute];
              }
              else
              {
                              $fileList['comment'   ][] = false;
                              $fileList['visibility'][] = false;
              }
      }                                // end while ($file = readdir($handle))

      /*
       * Sort alphabetically the File list
       */

      if ($fileList)
      {
              array_multisort($fileList['type'], $fileList['name'],
                              $fileList['size'], $fileList['date'],
                              $fileList['comment'],$fileList['visibility']);
      }




      /*----------------------------------------
              CHECK BASE INTEGRITY
      --------------------------------------*/


      if ($attribute)
      {
              /*
               * check if the number of DB records is greater
               * than the numbers of files attributes previously given
               */

              if (sizeof($attribute['path']) > (sizeof($fileList['comment']) + sizeof($fileList['visibility'])))
              {
                      /* SEARCH DB RECORDS WICH HAVE NOT CORRESPONDANCE ON THE DIRECTORY */
                      foreach( $attribute['path'] as $chekinFile)
                      {
                              if ($dirNameList && in_array(basename($chekinFile), $dirNameList))
                                      continue;
                              elseif ($fileNameList && in_array(basename($chekinFile), $fileNameList))
                                      continue;
                              else
                                      $recToDel[]= $chekinFile; // add chekinFile to the list of records to delete
                      }

                      /* BUILD THE QUERY TO DELETE DEPRECATED DB RECORDS */
                      $nbrRecToDel = sizeof ($recToDel);

                      for ($i=0; $i < $nbrRecToDel ;$i++)
                      {
                              $queryClause .= "path LIKE \"".$recToDel[$i]."%\"";
                              if ($i < $nbrRecToDel-1) {$queryClause .=" OR ";}
                      }

                      claro_sql_query("DELETE
                                     FROM `".$dbTable."`
                                    WHERE ".$queryClause);
                      claro_sql_query("DELETE
                                     FROM `".$dbTable."`
                                    WHERE `comment` LIKE ''
                                      AND `visibility` LIKE 'v'");
                      /* The second query clean the DB 'in case of' empty records (no comment an visibility=v)
                         These kind of records should'nt be there, but we never know... */
              }
      }                                // end if ($attribute)



      closedir($handle);
      unset($attribute);


   // display list of available documents
   display_my_documents($dialogBox) ;

   //####################################################################################\\
   //################################## MODULES LIST ####################################\\
   //####################################################################################\\


   claro_disp_tool_title($langPathContentTitle);
  echo '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';
  // display list of modules used by this learning path
   display_path_content($param_array, $table);

   // footer

   @include($includePath."/claro_init_footer.inc.php");
?>
