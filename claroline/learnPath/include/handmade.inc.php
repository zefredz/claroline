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
  | Authors:                                                             |
  +----------------------------------------------------------------------+

  DESCRIPTION:
  ****

*/
      $maxFilledSpace = 100000000;

      $courseDir   = $_course['path']."/modules/";
      $baseWorkDir = $coursesRepositorySys.$courseDir;




      if ($uncompress == 1)
              include($includePath."/lib/pclzip/pclzip.lib.php");

      // if the directory doesn't exist , create it
      if (!is_dir($baseWorkDir)) mkdir($baseWorkDir, 0777);


       //####################################################################################\\
       //##################################### COMMANDS #####################################\\
       //####################################################################################\\

            //## UPLOAD ##
            /*
             * check the request method in place of a variable from POST
             * because if the file size exceed the maximum file upload
             * size set in php.ini, all variables from POST are cleared !
             */

            if ( ($REQUEST_METHOD == 'POST' && isset($uploadPath)) && !$submitImage && !$cancelSubmitImage && !$submitStartAsset)
            {
                    /*
                     * Check if the file is valide (not to big and exists)
                     */

                    if(!is_uploaded_file($userFile))
                    {
                            $dialogBox .= $langFileError.'<br>'.$langNotice.' : '.$langMaxFileSize.' '.get_cfg_var('upload_max_filesize');
                    }

                    /*
                     * Check the file size doesn't exceed
                     * the maximum file size authorized in the directory
                     */

                    elseif ( ! enough_size($HTTP_POST_FILES['userFile']['size'], $baseWorkDir, $maxFilledSpace))
                    {
                            $dialogBox .= $langNoSpace;
                    }

                    /*
                     * Unzipping stage
                     */

                    elseif ($uncompress == 1 && preg_match("/.zip$/", $HTTP_POST_FILES['userFile']['name']) )
                    {
                            $zipFile = new pclZip($userFile);
                            $is_allowedToUnzip = true ; // default initialisation

                            // Check the zip content (real size and file extension)

                            $zipContentArray = $zipFile->listContent();

                            foreach($zipContentArray as $thisContent)
                            {
                                    if ( preg_match("~.(php.*|phtml)$~", $thisContent['filename']) )
                                    {
                                            $dialogBox .= $langZipNoPhp;
                                            $is_allowedToUnzip = false;
                                            break;
                                    }

                                    $realFileSize += $thisContent['size'];
                            }


                            if ( ($realFileSize + $alreadyFilledSpace) > $maxFilledSpace) // check the real size.
                            {
                                    $dialogBox .= $langNoSpace;
                                    $is_allowedToUnzip = false;
                            }

                            if ($is_allowedToUnzip)
                            {        /*
                                     * Uncompressing phase
                                     */

                                    if (PHP_OS == "Linux" && ! get_cfg_var("safe_mode"))
                                    {
                                            // Shell Method - if this is possible, it gains some speed

                                            exec("unzip -d \"".$baseWorkDir.$uploadPath."/\"".$fileName." "
                                                 .$HTTP_POST_FILES['userFile']['tmp_name']);
                                    }
                                    else
                                    {
                                            // PHP method - slower...

                                            chdir($baseWorkDir.$uploadPath);
                                            $unzippingSate = $zipFile->extract();
                                    }

                                    $dialogBox .= $langDownloadAndZipEnd;
                            }
                    }
                    else // if $uncompress
                    {
                            $fileName = trim ($HTTP_POST_FILES['userFile']['name']);

                            /* CHECK FOR NO DESIRED CHARACTERS */
                            $fileName = replace_dangerous_char($fileName);
                            //$fileName = str_replace(" ", "_", $fileName);

                            /* TRY TO ADD AN EXTENSION TO FILES WITOUT EXTENSION */
                            $fileName = add_ext_on_mime($fileName);

                            /* HANDLE PHP FILES */
                            $fileName = php2phps($fileName);

                            /* COPY THE FILE TO THE DESIRED DESTINATION */
                            copy ($userFile, $baseWorkDir.$uploadPath."/".$fileName);

                            $dialogBox .= $langDownloadEnd;

                    } // end else


                    /*
                     * In case of HTML file, looks for image needing to be uploaded too
                     */

                    if (strrchr($fileName, ".") == ".htm" || strrchr($fileName, ".") == ".html")
                    {
                            $fp = fopen($baseWorkDir.$uploadPath."/".$fileName, "r");

                            // search and store occurences of the <IMG> tag in an array

                            $buffer = fread ($fp, filesize ($baseWorkDir.$uploadPath."/".$fileName));

                            if ( preg_match_all("~<[:space:]*img[^>]*>~i",
                                                    $buffer, $matches) )
                            {
                                    $imgTagList = $matches[0];
                            }

                            fclose ($fp); unset($buffer);

                            // Search the image file path from all the <IMG> tag detected

                            if (sizeof($imgTagList)  > 0)
                            {
                                    $imgFilePath=array();

                                    foreach($imgTagList as $thisImgTag)
                                    {
                                            if ( preg_match("~src[:space:]*=[:space:]*[\"]{1}([^\"]+)[\"]{1}~i",
                                                                $thisImgTag, $matches) )
                                            {
                                                    $imgFilePath[] = $matches[1];
                                            }
                                    }

                                    $imgFilePath = array_unique($imgFilePath);                // remove duplicate entries
                            }

                            /*
                             * Generate Form for image upload
                             */

                            if ( sizeof($imgFilePath) > 0)
                            {
                                    $dialogBox .= "<br><b>$langMissingImagesDetected</b><br>\n"
                                                 ."<form method=\"post\" action=\"$PHP_SELF\""
                                                 ."enctype=\"multipart/form-data\">\n"
                                                 ."<input type=\"hidden\" name=\"relatedFile\""
                                                 ."value=\"".$uploadPath."/".$fileName."\">\n"
                                                 ."<table border=\"0\">\n";

                                    foreach($imgFilePath as $thisImgFilePath )
                                    {
                                            $dialogBox .= "<tr>\n"
                                                         ."<td>".basename($thisImgFilePath)." : </td>\n"
                                                         ."<td>"
                                                         ."<input type=\"file\"        name=\"imgFile[]\">"
                                                         ."<input type=\"hidden\" name=\"imgFilePath[]\" "
                                                         ."value=\"".$thisImgFilePath."\">"
                                                         ."</td>\n"
                                                         ."</tr>\n";
                                    }

                                    $dialogBox .= "</table>\n"

                                                 ."<div align=\"right\">"
                                                 ."<input type=\"submit\" name=\"cancelSubmitImage\" value=\"".$langCancel."\">\n"
                                                 ."<input type=\"submit\" name=\"submitImage\" value=\"".$langOk."\"><br>"
                                                 ."</div>\n"
                                                 ."</form>\n";
                            }                                                        // end if ($imgFileNb > 0)
                    }                                                                // end if (strrchr($fileName) == "htm"
            }


            /*
             *  UPLOAD OF RELATED IMAGES
             */
            if ($submitImage)
            {
                    $uploadImgFileNb = sizeof($imgFile);

                    if ($uploadImgFileNb > 0)
                    {
                            // Try to create  a directory to store the image files

                            $imgDirectory = $relatedFile."_files";

                            while (file_exists($baseWorkDir.$imgDirectory))
                            {
                                    $nb += 1;
                                    $imgDirectory = $relatedFile."_files".$nb;
                            }

                            mkdir($baseWorkDir.$imgDirectory, 0777);

                            $mkInvisibl = $imgDirectory;

                            // move the uploaded image files into the corresponding image directory

                            for ($i=0; $i < $uploadImgFileNb; $i++)
                            {
                                    if (is_uploaded_file($imgFile[$i]))
                                    {
                                            move_uploaded_file($imgFile[$i], $baseWorkDir.$imgDirectory."/".$imgFile_name[$i]);
                                    }
                            }


                            /*
                             * Open the old html file and replace the src path into the img tag
                             */

                            $fp = fopen($baseWorkDir.$relatedFile, "r");

                            while ( !feof($fp) )
                            {
                                    $buffer   = fgets($fp, 4096);

                                    for ($i=0; $i<$uploadImgFileNb ; $i++)
                                    {
                                            $buffer = str_replace(        $HTTP_POST_VARS['imgFilePath'][$i],
                                                                                            "./".basename($imgDirectory)."/".$imgFile_name[$i],
                                                                                            $buffer);
                                    }

                                    $newHtmlFileContent .= $buffer;
                            }

                            fclose ($fp);

                            /*
                             * Write the resulted new file
                             */

                            $fp = fopen($baseWorkDir.$relatedFile, "w");
                            fwrite($fp, $newHtmlFileContent);
                    }                                                                                        // end if ($uploadImgFileNb > 0)
            }


            /*
             *
             * SET START ASSET ID OF THE MODULE
             *
             */
            if( $submitStartAsset && isset($startAsset) )
            {
                 // check if a record exists for the selected file
                 $sql = "SELECT *
                           FROM `".$TABLEASSET."`
                          WHERE `path` = '".$startAsset."'
                            AND `module_id` = ".$_SESSION['module_id'];
                 $query = claro_sql_query($sql);
                 $num = mysql_numrows($query);
                 if ($num == 0)
                 {
                    $sql = "INSERT INTO `$dbTable` SET `path`=\"".$startAsset."\", `comment`=\"\",`module_id` = ".$_SESSION['module_id'];
                    $query = claro_sql_query($sql);
                    $thisAssetId = mysql_insert_id();

                 }
                 else
                 {
                    $row = @mysql_fetch_array($query);
                    $thisAssetId = $row['asset_id'];
                 }
                 $sql = "UPDATE `".$TABLEMODULE."` SET `startAsset_id`=\"".$thisAssetId."\" WHERE `module_id` = ".$_SESSION['module_id'];
                 $query = claro_sql_query($sql);

                 $dialogBox .= $langStartAssetSet;

            }
            /*
             * The code begin with STEP 2
             * so it allows to return to STEP 1 if STEP 2 unsucceeds
             */

            /*-------------------------------------
                    MOVE FILE OR DIRECTORY : STEP 2
            --------------------------------------*/

            if (isset($moveTo))
            {
                    if ( move($baseWorkDir.$source,$baseWorkDir.$moveTo) )
                    {
                            //update_db_info("update", $source, $moveTo."/".basename($source));
                            $query = "UPDATE `$dbTable`
                                         SET `path` = CONCAT(\"".$moveTo."/".basename($source)."\", SUBSTRING(path, LENGTH(\"".$source."\")+1) )
                                       WHERE `path` LIKE \"".$source."%\"
                                         AND `module_id` = ".$_SESSION['module_id'];
                            claro_sql_query($query);
                            $dialogBox = $langDirMv;
                    }
                    else
                    {
                            $dialogBox = $langImpossible;

                            /* return to step 1 */
                            $move = $source;
                            unset ($moveTo);
                    }

            }


            /*-------------------------------------
                    MOVE FILE OR DIRECTORY : STEP 1
            --------------------------------------*/

            if (isset($move))
            {
                    $dialogBox .= form_dir_list("source", $move, "moveTo", $baseWorkDir);
            }
            /*--------------------------------------
             *
             * DELETE A FILE OR DIRECTORY
             *
             *-------------------------------------*/

            if ( isset($delete) )
            {
                    // do not delete if the asset is the start asset
                    $result = claro_sql_query ("SELECT *
                                              FROM `$dbTable`
                                             WHERE `path` LIKE \"".$delete."%\"
                                               AND `module_id` = ".$_SESSION['module_id']); // the same path can appear in different modules
                    $row = mysql_fetch_array($result);

                    if ( my_delete($baseWorkDir.$delete))
                    {
                            /*** DELETE ***/
                            $query = "DELETE
                                        FROM `$dbTable`
                                       WHERE `path` LIKE \"".$delete."%\"
                                         AND `module_id` = ".$_SESSION['module_id'];
                            claro_sql_query($query);
                            $dialogBox = $langDocDeleted;
                    }

                    if ( $row['asset_id'] == $module['startAsset_id'] )
                    {
                            $dialogBox = $langNoMoreStartAsset;
                    }
            }


             /*======================================
                            ADD/UPDATE/REMOVE COMMENT
              ======================================*/

            /*
             * The code begin with STEP 2
             * so it allows to return to STEP 1
             * if STEP 2 unsucceds
             */

            /*--------------------------------------
                         COMMENT : STEP 2
              --------------------------------------*/

            if (isset($newComment))
            {
                    $newComment = trim($newComment); // remove spaces

                    /* Check if there is yet a record for this file in the DB */

                    $result = claro_sql_query ("SELECT *
                                              FROM `$dbTable`
                                             WHERE `path`=\"".$commentPath."\"
                                               AND `module_id` = ".$_SESSION['module_id']);

                    while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                    {
                            $attribute['path'      ] = $row['path'      ];
                            $attribute['comment'   ] = $row['comment'   ];
                            $attribute['asset_id'  ] = $row['asset_id'  ];
                    }


                    /* Determine the correct query to the DB */

                    if ($newComment == "" && ( $attribute['asset_id'] != $module['startAsset_id'] )  )    // if comment empty and NOT start asset
                    {
                            echo  $attribute['asset_id'] ." != ". $module['startAsset_id'] ;
                            $query = "DELETE FROM `$dbTable` WHERE path=\"".$commentPath."\" AND `module_id` = ".$_SESSION['module_id'];
                    }
                    elseif ($attribute['path'] != "" && $newComment != "")  // if comment empty and a record exists for this asset
                    {
                            $query= "UPDATE `$dbTable` SET comment=\"".addslashes($newComment)."\" WHERE path=\"".$commentPath."\" AND `module_id` = ".$_SESSION['module_id'];
                    }
                    elseif ($attribute['asset_id'] == $module['startAsset_id']) // if it's the start asset
                    {
                            $query= "UPDATE `$dbTable` SET comment=\"".addslashes($newComment)."\" WHERE path=\"".$commentPath."\" AND `module_id` = ".$_SESSION['module_id'];
                    }
                    else     // if it is not the start asset  and comment not empty
                    {
                            $query = "INSERT INTO `$dbTable` SET path=\"".$commentPath."\", comment=\"".addslashes($newComment)."\",`module_id` = ".$_SESSION['module_id'];
                    }

                    claro_sql_query($query);
                    unset($attribute);

                    $dialogBox = $langComMod;
            }

            /*--------------------------------------
                         COMMENT : STEP 1
              --------------------------------------*/

            if (isset($comment))
            {
                    /* Search the old comment */
                    $result = claro_sql_query ("SELECT `comment`
                                              FROM `$dbTable`
                                             WHERE `path`=\"".$comment."\"
                                               AND `module_id` = ".$_SESSION['module_id']);
                    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) $oldComment = $row['comment'];

                    $fileName = basename($comment);

                    $dialogBox .=         "<!-- comment -->\n"
                                                    ."<form>\n"
                                                    ."<input type=\"hidden\" name=\"commentPath\" value=\"".$comment."\">\n"
                                                    .$langAddComment." ".htmlentities($fileName)."\n"
                                                    ."<textarea rows=2 cols=50 name=\"newComment\">".$oldComment."</textarea>\n"
                                                    ."<input type=\"submit\" value=\"".$langOk."\">\n"
                                                    ."</form>\n";
            }


            /*-------------------------------------
                              RENAME : STEP 2
            --------------------------------------*/

            if (isset($renameTo))
            {
                    if ( my_rename($baseWorkDir.$sourceFile, $renameTo) )
                    {
                            //update_db_info("update", $sourceFile, dirname($sourceFile)."/".$renameTo);
                            $query = "UPDATE `$dbTable`
                                         SET `path` = CONCAT(\"".dirname($sourceFile)."/".$renameTo."\", SUBSTRING(path, LENGTH(\"".$sourceFile."\")+1) )
                                       WHERE `path` LIKE \"".$sourceFile."%\"
                                         AND `module_id` = ".$_SESSION['module_id'];
                            claro_sql_query($query);
                            $dialogBox = $langElRen;
                    }
                    else
                    {
                            $dialogBox = $langFileExists;

                            /* return to step 1 */
                            $rename = $sourceFile;
                            unset($sourceFile);
                    }
            }


            /*-------------------------------------
                                    RENAME : STEP 1
            --------------------------------------*/

            if (isset($rename))
            {
                    $fileName = basename($rename);
                    $dialogBox .=          "<!-- rename -->\n"
                                                    ."<form>\n"
                                                    ."<input type=\"hidden\" name=\"sourceFile\" value=\"".$rename."\">\n"
                                                    .$langRename." ".htmlentities($fileName)." ".$langIn." :\n"
                                                    ."<input type=\"text\" name=\"renameTo\" value=\"".$fileName."\">\n"
                                                    ."<input type=\"submit\" value=\"".$langOk."\">\n"
                                                    ."</form>\n";
            }




             //## CREATE DIRECTORY
             /*
             * The code begin with STEP 2
             * so it allows to return to STEP 1
             * if STEP 2 unsucceds
             */

            /*-------------------------------------
                               STEP 2
              --------------------------------------*/
            if (isset($newDirPath) && isset($newDirName))
            {
                    $newDirName = replace_dangerous_char(trim($newDirName));

                    if(check_name_exist($baseWorkDir.$newDirPath."/".$newDirName) )
                    {
                            $dialogBox = $langFileExists;
                            $createDir = $newDirPath; unset($newDirPath);// return to step 1
                    }
                    else
                    {
                            mkdir($baseWorkDir.$newDirPath."/".$newDirName, 0700);
                            $dialogBox = $langDirCr;
                    }
            }


            /*-------------------------------------
                            STEP 1
              --------------------------------------*/

            if (isset($createDir))
            {
                    $dialogBox .=         "<!-- create dir -->\n"
                                                    ."<form>\n"
                                                    ."<input type=\"hidden\" name=\"newDirPath\" value=\"$createDir\">\n"
                                                    .$langNameDir." : \n"
                                                    ."<input type=\"text\" name=\"newDirName\">\n"
                                                    ."<input type=\"submit\" value=\"".$langOk."\">\n"
                                                    ."</form>\n";
            }

       /*
        *  DEFINE CURRENT DIRECTORY
        */
       if (isset($openDir)  || isset($moveTo) || isset($createDir) || isset($newDirPath) || isset($uploadPath) ) // $newDirPath is from createDir command (step 2) and $uploadPath from upload command
       {
               $curDirPath = $openDir . $createDir . $moveTo . $newDirPath . $uploadPath;
               /*
                * NOTE: Actually, only one of these variables is set.
                * By concatenating them, we eschew a long list of "if" statements
                */
       }
       elseif ( isset($delete) || isset($move) || isset($rename) || isset($sourceFile) || isset($comment) || isset($commentPath) || isset($mkVisibl) || isset($mkInvisibl)) //$sourceFile is from rename command (step 2)
       {
               $curDirPath = dirname($delete . $move . $rename . $sourceFile . $comment . $commentPath . $mkVisibl . $mkInvisibl);
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


       /*--------------------------------------
         SEARCHING FILES & DIRECTORIES INFOS
                     ON THE DB
         --------------------------------------*/

       /* Search infos in the DB about the current directory the user is in */

       $result = claro_sql_query ("SELECT * FROM `".$TABLEASSET."`
                                WHERE `path` LIKE \"".$curDirPath."/%\"
                                  AND `path` NOT LIKE \"".$curDirPath."/%/%\"
                                  AND `module_id` = ".$_SESSION['module_id']);

       while($row = mysql_fetch_array($result, MYSQL_ASSOC))
       {
               $attribute['path'      ][] = $row['path'      ];
               $attribute['comment'   ][] = $row['comment'   ];
               $attribute['asset_id'  ][] = $row['asset_id'  ];
       }

       /*--------------------------------------
          LOAD FILES AND DIRECTORIES INTO ARRAYS
          --------------------------------------*/
        chdir (realpath($baseWorkDir.$curDirPath))
        or die("<center>
                <b>Wrong directory ! : ".$baseWorkDir.$curDirPath."</b>
                <br> Please contact your platform administrator.</center>");
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
                                $fileList['asset_id'  ][] = $attribute['asset_id'  ][$keyAttribute];
                }
                else
                {
                                $fileList['comment'   ][] = false;
                                $fileList['asset_id'  ][] = false;
                }
        }                                // end while ($file = readdir($handle))

        /*
         * Sort alphabetically the File list
         */

        if ($fileList)
        {
                array_multisort($fileList['type'], $fileList['name'],
                                $fileList['size'], $fileList['date'],
                                $fileList['comment'], $fileList['asset_id']);
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
                                $queryClause .= "`path` LIKE \"".$recToDel[$i]."%\"";
                                if ($i < $nbrRecToDel-1) {$queryClause .=" OR ";}
                        }

                        claro_sql_query("DELETE FROM `$TABLEASSET`
                                      WHERE ".$queryClause);
                        claro_sql_query("DELETE FROM `$TABLEASSET`
                                      WHERE `comment` LIKE ''");
                        /* The second query clean the DB 'in case of' empty records (no comment an visibility=v)
                           These kind of records should'nt be there, but we never know... */
                }
        }                                // end if ($attribute)



        closedir($handle);
        unset($attribute);








         /*
          *
          *  UPDATE INFOS ABOUT MODULE (some changes have probably been made)
          *
          */
          $sql = "SELECT *
                    FROM `".$TABLEMODULE."`
                   WHERE `module_id` = ".$_SESSION['module_id'];

          $query = claro_sql_query($sql);
          $module = @mysql_fetch_array($query);





         echo "<hr noshade=\"noshade\" size=\"1\" />";
         //####################################################################################\\
         //################################## ASSETS LISTS ####################################\\
         //####################################################################################\\

         echo "<h4>".$langModuleAdmin."</h4><p><small>".$langModuleHelpHandmade."</small></p>";

         $dspCurDirName = htmlentities($curDirName);
         $cmdCurDirPath = rawurlencode($curDirPath);
         $cmdParentDir  = rawurlencode($parentDir);


         echo "<table width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"4\">
                  <tr>";

           /*--------------------------------------
              DIALOG BOX SECTION
             --------------------------------------*/

           if ($dialogBox)
           {
              claro_disp_message_box($dialogBox);
           }

           /*--------------------------------------
                       UPLOAD SECTION
             --------------------------------------*/
           echo        "<!-- upload  -->",
                           "<td align=\"right\">",
                           "<form action=\"$PHP_SELF\" method=\"post\" enctype=\"multipart/form-data\">",
                           "<input type=\"hidden\" name=\"uploadPath\" value=\"$curDirPath\">",
                           $langDownloadFile," : ",
                           "<input type=\"file\" name=\"userFile\">",
                           "<input type=\"submit\" value=\"$langDownload\"><br>",
                           "<input type=\"checkbox\" name=\"uncompress\" value=\"1\">",
                           $langUncompress,
                           "</form>",
                           "</td>\n";

         echo "       </tr>
               </table>";


         echo "<form action=\"".$PHP_SELF."\" method=\"POST\">
                   <table width=\"100%\" border=\"0\" cellspacing=\"2\">";



              /*--------------------------------------
                        CURRENT DIRECTORY LINE
                --------------------------------------*/

              echo         "<tr>\n",
                              "<td colspan=8>\n";


              /* GO TO PARENT DIRECTORY */

              if ($curDirName) /* if the $curDirName is empty, we're in the root point
                                  and we can't go to a parent dir */
              {
                      echo         "<!-- parent dir -->\n",
                                      "<a href=\"$PHP_SELF?openDir=".$cmdParentDir."\">\n",
                                      "<img src=\"../img/parent.gif\" border=0 align=\"absbottom\" hspace=\"5\" />\n",
                                      "<small>$langUp</small>\n",
                                      "</a>\n";
              }


              /* CREATE DIRECTORY */

              echo        "<!-- create dir -->\n",
                              "<a href=\"$PHP_SELF?createDir=".$cmdCurDirPath."\">",
                              "<img src=\"../img/dossier.gif\" border=0 align=\"absbottom\" hspace=\"5\" />",
                              "<small> $langCreateDir</small>",
                              "</a>",

                              "</tr>\n",
                              "</td>\n";



              /* CURRENT DIRECTORY */

              if ($curDirName) /* if the $curDirName is empty, we're in the root point
                                  and there is'nt a dir name to display */
              {
                      echo        "<!-- current dir name -->\n",
                                      "<tr>\n",
                                      "<td colspan=\"8\" align=\"left\" bgcolor=\"#4171B5\">\n",
                                      "<img src=\"../img/opendir.gif\" align=\"absbottom\" vspace=\"2\" hspace=\"5\" />\n",
                                      "<font color=\"white\"><b>".$dspCurDirName."</b></font>\n",
                                      "</td>\n",
                                      "</tr>\n";
              }

              echo                "<tr bgcolor=\"$color2\" align=\"center\" valign=\"top\">";

              echo                "<td>$langName</td>\n",
                                      "<td>$langSize</td>\n",
                                      "<td>$langDate</td>\n";


              echo        "<td>$langDelete</td>\n",
                              "<td>$langMove</td>\n",
                              "<td>$langRename</td>\n",
                              "<td>$langComment</td>\n",
                              "<td>$langStartAsset</td>\n";

              echo                "</tr>\n";


              /*--------------------------------------
                           DISPLAY FILE LIST
                --------------------------------------*/

              if ($fileList)
              {
                      while (list($fileKey, $fileName) = each ($fileList['name']))
                      {
                              $dspFileName = htmlentities($fileName);
                              $cmdFileName = $curDirPath."/".$fileName;

                              if ($fileList['type'][$fileKey] == A_FILE)
                              {
                                      $image       = choose_image($fileName);
                                      $size        = format_file_size($fileList['size'][$fileKey]);
                                      $date        = format_date($fileList['date'][$fileKey]);
                                      //$urlFileName = "document_goto.php?doc_url=".urlencode($cmdFileName);
                                      $urlFileName = $coursesRepositoryWeb.$_course['path']."/modules/module_".$_SESSION['module_id'].$cmdFileName;
                                      //$urlFileName = "goto/?doc_url=".urlencode($cmdFileName);
                              }
                              elseif ($fileList['type'][$fileKey] == A_DIRECTORY)
                              {
                                      $image       = 'dossier.gif';
                                      $size        = '';
                                      $date        = '';
                                      $urlFileName = $PHP_SELF.'?openDir='.$cmdFileName;
                              }

                              echo        "<tr align=\"center\"",$style,">\n",
                                              "<td align=\"left\">",
                                              "<a href=\"".$urlFileName."\"".$style.">",
                                              "<img src=\"./../img/",$image,"\" border=0 hspace=5>",$dspFileName,"</a>",
                                              "</td>\n",

                                              "<td><small>",$size,"</small></td>\n",
                                              "<td><small>",$date,"</small></td>\n";

                              /* NB : Before tracking implementation the url above was simply
                               * "<a href=\"",$urlFileName,"\"",$style,">"
                               */


                               /* DELETE COMMAND */
                               echo         "<td>",
                                               "<a href=\"",$PHP_SELF,"?delete=",$cmdFileName,"\" ",
                                               "onClick=\"return confirmation('",addslashes($dspFileName),"');\">",
                                               "<img src=\"../img/supprimer.gif\" border=0>",
                                               "</a>",
                                               "</td>\n";

                               /* COPY COMMAND */
                               echo        "<td>",
                                               "<a href=\"",$PHP_SELF,"?move=",$cmdFileName,"\">",
                                               "<img src=\"../img/deplacer.gif\" border=0>",
                                               "</a>",
                                               "</td>\n";

                               /* RENAME COMMAND */
                               echo        "<td>",
                                               "<a href=\"",$PHP_SELF,"?rename=",$cmdFileName,"\">",
                                               "<img src=\"../img/renommer.gif\" border=0>",
                                               "</a>",
                                               "</td>\n";

                               /*COMMENT COMMAND */
                               echo        "<td>",
                                               "<a href=\"",$PHP_SELF,"?comment=",$cmdFileName,"\">",
                                               "<img src=\"../img/comment.gif\" border=0>",
                                               "</a>",
                                               "</td>\n";
                               if ($fileList['type'][$fileKey] == A_FILE)
                               {
                                   if ( ($fileList['asset_id'][$fileKey] == $module['startAsset_id']) && $fileList['asset_id'][$fileKey] )
                                   {
                                       $checked = "checked=\"checked\"";
                                   }
                                   else
                                   {
                                       $checked = "";
                                   }
                                   echo        "<td>",
                                                   "<input type=\"radio\" name=\"startAsset\" value=\"",$curDirPath."/".$fileName,"\" $checked>",
                                                   "</td>\n";
                               }


                              echo        "</tr>\n";

                              /* COMMENTS */

                              if ($fileList['comment'][$fileKey] != "" )
                              {
                                      $fileList['comment'][$fileKey] = htmlentities($fileList['comment'][$fileKey]);
                                      $fileList['comment'][$fileKey] = nl2br($fileList['comment'][$fileKey]);

                                      echo        "<tr align=\"left\">\n",
                                                      "<td colspan=\"$colspan\">",
                                                      "<div class=\"comment\">",
                                                      $fileList['comment'][$fileKey],
                                                      "</div>",
                                                      "</td>\n",
                                                      "</tr>\n";
                              }

                      }  // end each ($fileList)
                      echo "<tr>
                              <td colspan='7'>&nbsp;</td>
                              <td align='center'>
                               <input type='hidden' name='openDir' value = '$curDirPath'>
                               <input type='submit' name='submitStartAsset' value='$langOk'>
                               </td>
                              </tr>";
              } // end if ( $fileList)

              echo        "</table>\n",
                             "</form>\n",
                              "</div>\n";

         echo "</table>";
?>
