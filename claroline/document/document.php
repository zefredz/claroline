<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*

  DESCRIPTION:
  ****
  This PHP script allow user to manage files and directories on a remote http server.
  The user can : - navigate trough files and directories.
                 - upload a file
				 - rename, delete, copy a file or a directory

  The script is organised in four sections.

  * 1st section execute the command called by the user
                Note: somme commands of this section is organised in two step.
			    The script lines always begin by the second step,
			    so it allows to return more easily to the first step.

  * 2nd section define the directory to display

  * 3rd section read files and directories from the directory defined in part 2

  * 4th section display all of that on a HTML page
*/

/*======================================
       CLAROLINE MAIN
  ======================================*/
$tlabelReq = 'CLDOC___';
require '../inc/claro_init_global.inc.php';

/*
 * Library for images
 */

require_once $includePath . '/lib/image.lib.php';

/* 
 * Library for the file display
 */

require $includePath.'/lib/fileDisplay.lib.php';

/*
 * Lib for event log, stats & tracking
 * plus record of the access
 */

/*============================================================================
                     FILEMANAGER BASIC VARIABLES DEFINITION
  =============================================================================*/

$baseServDir = $coursesRepositorySys;
$baseServUrl = $urlAppend.'/';

/*
 * The following variables depends on the use context
 * The document tool can be used at course or group level 
 * (one document area for each group)
 */

if ($_gid && $is_groupAllowed)
{
    $groupContext      = TRUE;
    $courseContext     = FALSE;

    $maxFilledSpace    = isset($maxFilledSpace_for_groups)?$maxFilledSpace_for_groups:2*1024*1024;
    $courseDir         = $_course['path'].'/group/'.$_group['directory'];
    $groupDir          = 'group/'.$_group['directory']; 

    $interbredcrump[]  = array ('url'=>'../group/group.php', 'name'=> $langGroups);
	$interbredcrump[]= array ("url"=>"../group/group_space.php", "name"=> $langGroupSpace);

    $is_allowedToEdit  = $is_groupMember || $is_courseAdmin;
    $is_allowedToUnzip = FALSE;

    if (! ($is_groupMember || $is_courseAdmin || $is_groupTutor) )
    {
      die("<center>You are not allowed to see this group's documents!!!</center>");
    }
}
else
{
    $groupContext     = FALSE;
    $courseContext    = TRUE;

    $maxFilledSpace   = $maxFilledSpace_for_course;
    $courseDir   = $_course['path'].'/document';

	// initialise view mode tool
	claro_set_display_mode_available(TRUE);
	
    $is_allowedToEdit  = claro_is_allowed_to_edit();
    $is_allowedToUnzip = claro_is_allowed_to_edit();
    $maxFilledSpace    = isset($maxFilledSpace_for_course)?$maxFilledSpace_for_course:50*1024*1024;

    // table names for learning path (needed to check integrity)

    /*
     * DB tables definition
     */

    $tbl_cdb_names = claro_sql_get_course_tbl();

    $tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
    $tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
    $tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
    $tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
    $tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];

    $TABLELEARNPATH            = $tbl_lp_learnPath;
    $TABLELEARNPATHMODULE      = $tbl_lp_rel_learnPath_module;
    $TABLEUSERMODULEPROGRESS   = $tbl_lp_user_module_progress;
    $TABLEMODULE               = $tbl_lp_module;
    $TABLEASSET                = $tbl_lp_asset;

    $dbTable = $tbl_cdb_names['document'];
}

$baseWorkDir = $baseServDir.$courseDir;

include($includePath.'/lib/events.lib.inc.php');
event_access_tool($_tid, $_courseTool['label']);

if ( ! $is_courseAllowed) claro_disp_auth_form();

require $includePath.'/lib/fileManage.lib.php';


if($is_allowedToEdit) // for teacher only
{
	require $includePath.'/lib/fileUpload.lib.php';

	if ($uncompress == 1)
	{
		require $includePath.'/lib/pclzip/pclzip.lib.php';
	}
}


// clean information submited by the user from antislash

stripSubmitValue($_REQUEST);

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];

                  /* > > > > > > MAIN SECTION  < < < < < < <*/


if($is_allowedToEdit) // Document edition are reserved to certain people
{


	/*========================================================================
                                  UPLOAD FILE
	  ========================================================================*/


	/*
	 * check the request method in place of a variable from POST
	 * because if the file size exceed the maximum file upload
	 * size set in php.ini, all variables from POST are cleared !
	 */

	if ($cmd == 'exUpload')
	{
        /*
         * Check if the file is valid (not to big and exists)
         */

        if( ! is_uploaded_file($_FILES['userFile']['tmp_name']) )
        {
        	$dialogBox .= $langFileError.'<br>'.$langNotice.' : '.$langMaxFileSize.' '.get_cfg_var('upload_max_filesize');
        }

        if ($_REQUEST['uncompress'] == 1 && $is_allowedToUnzip) $unzip = 'unzip';
        else                                                    $unzip = '';

        $uploadedFileName = treat_uploaded_file($_FILES['userFile'], $baseWorkDir,
                                $_REQUEST['cwd'], $maxFilledSpace, $unzip);

        if ($uploadedFileName !== false)
        {
            if ( $_REQUEST['uncompress'] == 1)
            {
                $dialogBox .= $langUploadAndZipEnd;
            }
            else
            {
                $dialogBox .= $langUploadEnd;

                if (trim($_REQUEST['comment']) != '') // insert additional comment
                {
                    update_db_info('update', $_REQUEST['cwd'].'/'.$uploadedFileName, 
                                    array('comment' => trim($_REQUEST['comment']) ) );
                }
            }
        }
        else
        {
            if (    claro_failure::get_last_failure() == 'not_enough_space'    )
            {
                $dialogBox .= $langNoSpace;
            }
            elseif( claro_failure::get_last_failure() == 'php_file_in_zip_file')
            {
                $dialogBox .= $langZipNoPhp;
            }
        }


        /*--------------------------------------------------------------------
           IN CASE OF HTML FILE, LOOKS FOR IMAGE NEEDING TO BE UPLOADED TOO
          --------------------------------------------------------------------*/


		if (   strrchr($_FILES['userFile']['name'], '.') == '.htm'
            || strrchr($_FILES['userFile']['name'], '.') == '.html')
		{
            $imgFilePath = search_img_from_html($baseWorkDir.$_REQUEST['cwd'].'/'.$_FILES['userFile']['name']);

			/*
			 * Generate Form for image upload
			 */

			if ( sizeof($imgFilePath) > 0)
			{
				$dialogBox .= "<br><b>".$langMissingImagesDetected."</b><br>\n"
				             ."<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" "
				             ."enctype=\"multipart/form-data\">\n"
				             ."<input type=\"hidden\" name=\"cmd\" value=\"submitImage\">\n"
				             ."<input type=\"hidden\" name=\"relatedFile\""
				             ."value=\"".$_REQUEST['cwd']."/".$_FILES['userFile']['name']."\">\n"
				             ."<table border=\"0\">\n";

				foreach($imgFilePath as $thisImgKey => $thisImgFilePath )
				{
					$dialogBox .= "<tr>\n"
					             ."<td>"
                                 ."<label for=\"".$thisImgKey."\">".basename($thisImgFilePath)." : </label>"
                                 ."</td>\n"
					             ."<td>"
					             ."<input type=\"file\"	id=\"".$thisImgKey."\" name=\"imgFile[]\">"
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
			}							// end if ($imgFileNb > 0)
		}								// end if (strrchr($fileName) == "htm"
	}									// end if is_uploaded_file


    if ($cmd == 'rqUpload')
    {
        /*
         * Prepare dialog box display
         */

        $dialogBox .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" enctype=\"multipart/form-data\">"
                     ."<input type=\"hidden\" name=\"cmd\" value=\"exUpload\">"
                     ."<input type=\"hidden\" name=\"cwd\" value=\"".$_REQUEST['cwd']."\">"
                     ."<label for=\"userFile\">".$langUploadFile." : </label>"
                     ."<input type=\"file\" id=\"userFile\" name=\"userFile\"> "
                     ."<input style=\"font-weight: bold\" type=\"submit\" value=\"".$langUpload."\"><br>"
                     ."<small>".$langMaxFileSize.format_file_size( get_max_upload_size($maxFilledSpace,$baseWorkDir) )."</small><br>";


        if ($is_allowedToUnzip)
        {
            $dialogBox .= "<input type=\"checkbox\" id=\"uncompress\" name=\"uncompress\" value=\"1\">"
                          ."<label for=\"uncompress\">".$langUncompress."</label>";
        }

        if ($courseContext)
        {
            $dialogBox .= "<p>\n"
                        ."<label for=\"comment\">".$langAddCommentOptionnal."</label>"
                        ."<br><textarea rows=2 cols=50 id=\"comment\" name=\"comment\">"
                        .$oldComment
                        ."</textarea>\n"
                        ."</p>\n";
        }

        $dialogBox .= "</form>";
    }


	/*========================================================================
                           UPLOAD RELATED IMAGE FILES
	  ========================================================================*/

	if ($cmd == 'submitImage')
	{

		$uploadImgFileNb = sizeof($_FILES['imgFile']);

		if ($uploadImgFileNb > 0)
		{
			// Try to create  a directory to store the image files

            $imgDirectory = $_REQUEST['relatedFile'].'_files';
            $imgDirectory = create_unexisting_directory($baseWorkDir.$imgDirectory);

            // set the makeInvisible command param appearing later in the script
			$mkInvisibl = str_replace($baseWorkDir, '', $imgDirectory);

			// move the uploaded image files into the corresponding image directory

			// Try to create  a directory to store the image files
            $newImgPath = move_uploaded_file_collection_into_directory($_FILES['imgFile'], $imgDirectory);

            replace_img_path_in_html_file($_POST['imgFilePath'], 
                                          $newImgPath, 
                                          $baseWorkDir.$_REQUEST['relatedFile']);

		}											// end if ($uploadImgFileNb > 0)
	}										// end if ($submitImage)



    /*========================================================================
                             CREATE DOCUMENT
      ========================================================================*/

    /*------------------------------------------------------------------------
                            CREATE DOCUMENT : STEP 2
      ------------------------------------------------------------------------*/

    if ($cmd == 'exMkHtml')
    {
        $fileName = replace_dangerous_char(trim($_REQUEST['fileName']));

        if (! empty($fileName) )
        {
            if ( ! in_array( strtolower (get_file_extension($_REQUEST['fileName']) ), 
                           array('html', 'htm') ) )
            {
                $fileName = $fileName.'.htm';
            }

            create_file($baseWorkDir.$_REQUEST['cwd'].'/'.$fileName,
                        $_REQUEST['htmlContent']);

            $dialogBox .= $langFileCreated;
        }
        else
        {
            $dialogBox .= $langFileNameMissing;

            if (!empty($_REQUEST['htmlContent']))
            {
                $dialogBox .= "<p>\n"
                             ."<a href=\"rqmkhtml.php"
                             ."?cmd=rqMkHtml"
                             ."&cwd=".urlencode($_REQUEST['cwd'])
                             ."&htmlContent=".urlencode($_REQUEST['htmlContent'])."\">\n"
                             .$langBackToEditor."\n"
                             ."</p>\n";
            }
        }
    }


    /*------------------------------------------------------------------------
                            CREATE DOCUMENT : STEP 1
      ------------------------------------------------------------------------*/

      // see reqmkhtml.php ...

    /*========================================================================
                             EDIT DOCUMENT CONTENT
      ========================================================================*/

    if ($cmd == 'exEditHtml')
    {
        $fp = fopen($baseWorkDir.$_REQUEST['file'], 'w');

        if ($fp)
        {

          if ( fwrite($fp, $_REQUEST['htmlContent']) )
          {
            $dialogBox .= $langFileContentModified."<br>";
          }

        }
    }


	/*========================================================================
                                   CREATE URL
	  ========================================================================*/

	/*
	 * The code begins with STEP 2
	 * so it allows to return to STEP 1 if STEP 2 unsucceeds
	 */

	/*------------------------------------------------------------------------
                              CREATE URL : STEP 2
	--------------------------------------------------------------------------*/

	if ($cmd == 'exMkUrl')
	{
        $fileName = replace_dangerous_char(trim($_REQUEST['fileName']));
        $url = trim($_REQUEST['url']);

        // check for "http://", if the user forgot "http://" or "ftp://" or ...
        // the link will not be correct
        if( !ereg( '://',$url ) )
        {
            // add "http://" as default protocol for url
            $url = "http://".$url;
        }

        if ( ! empty($fileName) && ! empty($url) )
        {
            $linkFileExt = '.url';
            create_link_file( $baseWorkDir.$_REQUEST['cwd'].'/'.$fileName.$linkFileExt, 
                              $url);

            if ( trim($_REQUEST['cwd']) != '')
            {
                update_db_info('update', $_REQUEST['cwd'].'/'.$fileName.$linkFileExt, 
                                array('comment' => trim($_REQUEST['comment']) ) );
            }
        }
        else
        {
        	$dialogBox .= $langFileNameOrURLMissing;
            $cmd        = 'rqMkUrl';
        }
    }

	/*------------------------------------------------------------------------
                              CREATE URL : STEP 1
	--------------------------------------------------------------------------*/

    if ($cmd == 'rqMkUrl')
    {
        $dialogBox .= "<h4>".$langCreateHyperlink."</h4>\n"
                     ."<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n"
                     ."<input type=\"hidden\" name=\"cmd\" value=\"exMkUrl\">\n"
                     ."<input type=\"hidden\" name=\"cwd\" value=\"".$_REQUEST['cwd']."\">"
                     ."<label for=\"fileName\">".$langName." : </label><br />\n"
                     ."<input type=\"text\" id=\"fileName\" name=\"fileName\"><br />\n"
                     ."<label for=\"url\">".$langURL."</label><br />\n"
                     ."<input type=\"text\" id=\"url\" name=\"url\" value=\"".$url."\">\n"
                     ."<br><br>\n"
                     ."<label for=\"comment\">\n"
                     ."Add a comment (optionnal) :\n"
                     ."</label>\n"
                     ."<br>\n"
                     ."<textarea rows=\"2\" cols=\"50\" id=\"comment\" name=\"comment\"></textarea>\n"
                     ."<br>\n"
                     ."<input type=\"submit\" value=\"".$langOk."\">\n"
                     ."</form>\n";

    }

	/*========================================================================
                             MOVE FILE OR DIRECTORY
	  ========================================================================*/


	/*------------------------------------------------------------------------
                        MOVE FILE OR DIRECTORY : STEP 2
	--------------------------------------------------------------------------*/

	if ($cmd == 'exMv')
	{
		if ( claro_move_file($baseWorkDir.$_REQUEST['file'],$baseWorkDir.$_REQUEST['destination']) )
		{
			if ($courseContext)
			{
                update_db_info( 'update', $_REQUEST['file'],
                                array('path' => $_REQUEST['destination'].'/'.basename($_REQUEST['file'])) );
                update_Doc_Path_in_Assets("update",$_REQUEST['file'],
												   $_REQUEST['destination'].'/'.basename($_REQUEST['file']));
			}

			$dialogBox = $langDirMv.'<br>';
		}
		else
		{
			$dialogBox = $langImpossible.'<br>';

			/* return to step 1 */

			$cmd = 'rqMv';
			unset ($_REQUEST['destination']);
		}
	}


	/*------------------------------------------------------------------------
                        MOVE FILE OR DIRECTORY : STEP 1
	--------------------------------------------------------------------------*/

	if ($cmd == 'rqMv')
	{
		$dialogBox .= form_dir_list($_REQUEST['file'], $baseWorkDir);
	}



	/*========================================================================
                            DELETE FILE OR DIRECTORY
	  ========================================================================*/


	if ($cmd == 'exRm')
	{
        $file = $_REQUEST['file'];

        if ( claro_delete_file($baseWorkDir.$file))
		{
            if ($courseContext)
            {
                update_db_info('delete', $file);
                update_Doc_Path_in_Assets('delete', $file, '');
            }

            $dialogBox = $langDocDeleted;
		}
	}




	/*========================================================================
                                      EDIT
	  ========================================================================*/

	/*
	 * The code begin with STEP 2
	 * so it allows to return to STEP 1
	 * if STEP 2 unsucceds
	 */


    /*------------------------------------------------------------------------
                                 EDIT : STEP 2
      ------------------------------------------------------------------------*/

    if ($cmd == 'exEdit')
    {
        if ( $_REQUEST['url'])
        {
            $url = trim ($_REQUEST['url']);

            if ( ! empty($url) )
            {
                /* First check for the presence of a protocol in the url 
                 * If the user forget "http://" or "ftp://" or whatever,
                 * the link won't work. 
                 * In this case, add "http://" as default url protocol
                 */

                if( ! ereg( '://',$url ) ) $url = 'http://'.$url;

                // else $url = $url ...

                create_link_file( $baseWorkDir.$_REQUEST['file'], 
                                  $url);
            }

        }

        $directoryName = dirname($_REQUEST['file']);

        if ( $directoryName == '/' || $directoryName == '\\' )
        {
            // When the dir is root, PHP dirname leaves a '\' for windows or a '/' for Unix
            $directoryName = '';
        }

        $_REQUEST['newName'] = trim($_REQUEST['newName']);

        if ( ! empty($_REQUEST['newName']) )
        {
            $newPath = $directoryName . '/' . $_REQUEST['newName'];
        }
        else
        {
        	$newPath = $_REQUEST['file'];
        }


        if ( claro_rename_file($baseWorkDir.$_REQUEST['file'], $baseWorkDir.$newPath) )
        {
            $dialogBox = $langElRen.'<br>';

            if ($courseContext)
            {
                $newComment = trim($_REQUEST['newComment']); // remove spaces

                update_db_info('update', $_REQUEST['file'], 
                                array( 'path'    => $newPath,
                                       'comment' => $newComment ) );

                update_Doc_Path_in_Assets('update', $_REQUEST['file'], $newPath);

                if ( ! empty($newComment) ) $dialogBox .= $langComMod.'<br>';
            }
        }
        else
        {
            $dialogBox .= $langFileExists;

            /* return to step 1 */

            $cmd   = 'rqEdit';
        }
    }


	/*------------------------------------------------------------------------
                                 EDIT : STEP 1
	-------------------------------------------------------------------------*/

	if ($cmd == 'rqEdit')
	{
		$fileName = basename($_REQUEST['file']);

		$dialogBox .= 	"<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">"
						."<input type=\"hidden\" name=\"cmd\" value=\"exEdit\">\n"
						."<input type=\"hidden\" name=\"file\" value=\"".$_REQUEST['file']."\">\n"
                        ."<p>\n"
						."<label for=\"newName\">".$langRename." ".htmlspecialchars($fileName)
                        ." ".$langIn." : </Label>\n"
						."<br><input type=\"text\" id=\"newName\" name=\"newName\" value=\"".$fileName."\">\n"
                        ."</p>\n";

        if ('url' == get_file_extension($baseWorkDir.$_REQUEST['file']) )
        {
            $url = get_link_file_url($baseWorkDir.$_REQUEST['file']);

            $dialogBox .= "<p><label for=\"url\">".$langURL."</label><br />\n"
                         ."<input type=\"text\" id=\"url\" name=\"url\" value=\"".$url."\">\n"
                         ."</p>\n";
        }

        if ($courseContext)
        {
            /* Search the old comment */
            $sql = "SELECT comment 
                    FROM `".$dbTable."` 
                    WHERE path = \"".$_REQUEST['file']."\"";

            $result = mysql_query ($sql);
            while( $row = mysql_fetch_array($result, MYSQL_ASSOC) ) $oldComment = $row['comment'];

            //list($oldComment) = claro_sql_query_fetch_all($sql);

            $dialogBox .= "<p>\n<label for=\"newComment\">"
                          .$langAddModifyComment." ".htmlspecialchars($fileName)."</label>\n"
                          ."<br><textarea rows=2 cols=50 name=\"newComment\" id=\"newComment\">"
                          .$oldComment
                          ."</textarea>\n"
                          ."</p>\n";
        }

        /*
         * Add the possibility to edit on line the content of file 
         * if it is an html file
         */

        if ( in_array( strtolower (get_file_extension($_REQUEST['file']) ), 
                       array('html', 'htm') ) )
        {
            
        	$dialogBox .= "<p>"
                          ."<a href=\"rqmkhtml.php?cmd=rqEditHtml&file=".$_REQUEST['file']."\">"
                          .$langEditFileContent
                          ."</a>"
                          ."</p>";
        }

		$dialogBox .= "<br /><input type=\"submit\" value=\"".$langOk."\">\n"
					 ."</form>\n";

	} // end if cmd == rqEdit




	/*========================================================================
                                CREATE DIRECTORY
	  ========================================================================*/

	/*
	 * The code begin with STEP 2
	 * so it allows to return to STEP 1
	 * if STEP 2 unsucceds
	 */

	/*------------------------------------------------------------------------
                                     STEP 2
	  ------------------------------------------------------------------------*/

    if ($cmd == 'exMkDir')
	{
		$newDirName = replace_dangerous_char(trim($_REQUEST['newName']));

		if( check_name_exist($baseWorkDir.$_REQUEST['cwd'].'/'.$newDirName) )
		{
			$dialogBox = $langFileExists;
			$cmd = 'rqMkDir';
		}
		else
		{
			claro_mkdir($baseWorkDir.$_REQUEST['cwd'].'/'.$newDirName, 0700);
			$dialogBox = $langDirCr;
		}
	}


	/*------------------------------------------------------------------------
                                     STEP 1
	  ------------------------------------------------------------------------*/

	if ($cmd == 'rqMkDir')
	{
		$dialogBox .=	 "<form>\n"
						."<input type=\"hidden\" name=\"cmd\" value=\"exMkDir\">\n"					
						."<input type=\"hidden\" name=\"cwd\" value=\"".$_REQUEST['cwd']."\">\n"
						."<label for=\"newName\">".$langNameDir." : </label>\n"
						."<input type=\"text\" id=\"newName\" name=\"newName\">\n"
						."<input type=\"submit\" value=\"".$langOk."\">\n"
						."</form>\n";
	}

	/*========================================================================
                              VISIBILITY COMMANDS
	  ========================================================================*/

	if ($cmd == 'exChVis' && $courseContext)
	{
		$visibilityPath = $_REQUEST['file']; // At least one of these variables are empty.
		                                     // So it's okay to proceed this way
        
        update_db_info('update', $file, array('visibility' => $_REQUEST['vis']) );

		$dialogBox = $langViMod;

	}
} // END is Allowed to Edit



if ($cmd == 'rqSearch')
{
    $dialogBox .=	 "<form>\n"
                    ."<input type=\"hidden\" name=\"cmd\" value=\"exSearch\">\n"					
                    ."<label for=\"searchPattern\">".$langSearch." : </label>\n"
                    ."<input type=\"text\" id=\"searchPattern\" name=\"searchPattern\">\n"
                    ."<input type=\"submit\" value=\"".$langOk."\">\n"
                    ."</form>\n";
}



/*============================================================================
                            DEFINE CURRENT DIRECTORY
  ============================================================================*/

if (in_array($cmd, array('rqMv', 'exRm', 'rqEdit', 'exEdit', 'exEditHtml',
                         'exChVis', 'rqComment', 'exComment')))
{
	$curDirPath = claro_dirname($_REQUEST['file']);
}
elseif (in_array($cmd, array('rqMkDir', 'exMkDir', 'rqUpload', 'exUpload', 
                             'rqMkUrl', 'exMkUrl', 'reqMkHtml', 'exMkHtml')))
{
	$curDirPath = $_REQUEST['cwd'];
}
elseif ($cmd == 'exChDir')
{
		$curDirPath = $_REQUEST['file'];
}
elseif ($cmd == 'exMv')
{
	$curDirPath = $_REQUEST['destination'];
}
elseif ($cmd == 'viewImage' || $cmd == 'viewThumbs' )
{
	$curDirPath = $_REQUEST['curdir'];
}
else
{
	$curDirPath = '';
}

if ($curDirPath == '/' || $curDirPath == '\\' || strstr($curDirPath, '..'))
{
	$curDirPath = ''; // manage the root directory problem

	/*
	 * The strstr($curDirPath, '..') prevent malicious users to go to the root directory
	 */
}

$curDirName = basename($curDirPath);
$parentDir  = dirname($curDirPath);

if ($parentDir == '/' || $parentDir == '\\')
{
	$parentDir = ''; // manage the root directory problem
}




/*============================================================================
                         READ CURRENT DIRECTORY CONTENT
  ============================================================================*/

/*----------------------------------------------------------------------------
                     LOAD FILES AND DIRECTORIES INTO ARRAYS
  ----------------------------------------------------------------------------*/

// $resultFileList = array();

if ($cmd == 'exSearch')
{
    $searchPattern   = $_REQUEST['searchPattern'];

    $searchPattern   = str_replace('.', '\\.', $searchPattern);
    $searchPattern   = str_replace('*', '.*',  $searchPattern);
    $searchPattern   = str_replace('?', '.?',  $searchPattern);
    $searchPattern   = '|'.$searchPattern.'|i';

    $searchRecursive = true;
    $searchBasePath  = $baseWorkDir;
}
else
{
    $searchPattern   = '||';
    $searchRecursive = false;
    $searchBasePath  = $baseWorkDir.$curDirPath;
}

$filePathList = claro_search_file($searchPattern, 
                                  $searchBasePath, 
                                  $searchRecursive);

for ($i =0; $i < count($filePathList); $i++ )
{
    $filePathList[$i] = str_replace($baseWorkDir, '', $filePathList[$i]);
}

if ($cmd == 'exSearch')
{
	$sql = "SELECT path FROM `".$dbTable."` 
            WHERE comment LIKE '%".addslashes($searchPattern)."%'";

    $dbSearchResult = claro_sql_query_fetch_all_cols($sql);
    $filePathList = array_unique( array_merge($filePathList, $dbSearchResult['path']) );
}


if ( count($filePathList) > 0 )
{
    define('A_DIRECTORY', 1);
    define('A_FILE',      2);

    foreach($filePathList as $thisFile)
    {
        $fileList['name'][] = $thisFile;
        
        if( is_dir($baseWorkDir.$thisFile) )
        {
            $fileList['type'][] = A_DIRECTORY;
            $fileList['size'][] = false;
            $fileList['date'][] = false;
        }
        elseif( is_file($baseWorkDir.$thisFile) )
        {
            $fileList['type'][] = A_FILE;
            $fileList['size'][] = claro_get_file_size($baseWorkDir.$thisFile);
            $fileList['date'][] = filectime($baseWorkDir.$thisFile);
        }
    }
}

if ($courseContext && $fileList)
{
	/*--------------------------------------------------------------------------
                 SEARCHING FILES & DIRECTORIES INFOS ON THE DB
      ------------------------------------------------------------------------*/

    /* 
     * Search infos in the DB about the current directory the user is in
     */

        $sql = "SELECT `path`, `visibility`, `comment` 
                FROM `".$dbTable."` 
                WHERE path IN ('".implode("', '", array_map('addslashes', $fileList['name']) )."')";

    $attributeList = claro_sql_query_fetch_all_cols($sql);

    /*
     * Make the correspondance between info given by the file system 
     * and info given by the DB
     */

    foreach($fileList['name'] as $thisFile)
    {
        $keyAttribute = array_search($thisFile, $attributeList['path']);

        if ($keyAttribute !== false)
        {
            $fileList['comment'   ][] = $attributeList['comment'   ][$keyAttribute];
            $fileList['visibility'][] = $attributeList['visibility'][$keyAttribute];

            /*
             * Progressively unset the attribut to be able to check at the 
             * end if it remains unassigned attribute - which should mean 
             * there is  base integrity problem
             */

            unset ($attributeList['comment'   ][$keyAttribute],
                   $attributeList['visibility'][$keyAttribute],
                   $attributeList['path'      ][$keyAttribute]);
        }
        else
        {
            $fileList['comment'   ][] = false;
            $fileList['visibility'][] = false;
        }
    }  // end foreach fileList[name] as thisFile
    

    /*------------------------------------------------------------------------
                              CHECK BASE INTEGRITY
      ------------------------------------------------------------------------*/

    if ( count($attributeList['path']) > 0 )
    {
        $sql = "DELETE FROM `".$dbTable."` 
                WHERE `path` IN ( \"".implode("\" , \"" , $attribute['path'])."\" )";

        claro_sql_query($sql);

        $sql = "DELETE FROM `".$dbTable."` 
                WHERE comment LIKE '' AND visibility LIKE 'v'";

        claro_sql_query($sql);
        /* The second query clean the DB 'in case of' empty records (no comment an visibility=v)
           These kind of records should'nt be there, but we never know... */

    }	// end if sizeof($attribute['path']) > 0

} // end if courseContext




/*----------------------------------------------------------------------------
                       SORT ALPHABETICALLY THE FILE LIST
  ----------------------------------------------------------------------------*/

if ($fileList)
{
    if ($courseContext)
    {
        array_multisort($fileList['type'   ], $fileList['name'      ], 
                        $fileList['size'   ], $fileList['date'      ],
                        $fileList['comment'], $fileList['visibility']);
    }
    else
    {
        array_multisort($fileList['type'], $fileList['name'], 
                        $fileList['size'], $fileList['date']);
    }
}

unset($attribute);




      /* > > > > > > END: COMMON TO TEACHERS AND STUDENTS < < < < < < <*/


/*============================================================================
                                    DISPLAY
  ============================================================================*/

$htmlHeadXtra[] =
"<style type=text/css>
<!--
.comment { margin-left: 30px}
-->
</style>";

if ( $cmd == 'viewImage' || $cmd == 'viewThumbs' )
{
// declare style for thumbnail/image viewer
$htmlHeadXtra[] =
"<style type=text/css>
<!--
/* extension of claroTable class defined in central css file */
.claroTable tr th.toolbar {
	background: white;
	font-weight: normal;
}
.claroTable tr.toolbar th.prev{
	text-align:left;
}
.claroTable tr.toolbar th.title{
	font-weight:bold;
	text-align:center;
}
.claroTable tr.toolbar .invisible{
	color: silver;
}
.claroTable tr.toolbar .invisible a:link,
.claroTable tr.toolbar .invisible a:active,
.claroTable tr.toolbar .invisible a:visited,
{
	color: silver;
}
.claroTable tr.toolbar th.next{
	text-align:right;
}
 -->
 </style>";
}

$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
	if (confirm(\" ".$langAreYouSureToDelete." \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>";

$nameTools = $langDocument;

$QUERY_STRING=''; // used for the breadcrumb 
                  // when one need to add a parameter after the filename

include($includePath.'/claro_init_header.inc.php');

$dspCurDirName = htmlspecialchars($curDirName);
$cmdCurDirPath = rawurlencode($curDirPath);
$cmdParentDir  = rawurlencode($parentDir);

//display toot title and subtitle

$titleElement['mainTitle'] = $langDocument;
if ( $_gid && $is_groupAllowed) $titleElement['subTitle'] = $_group['name'];

claro_disp_tool_title($titleElement, 
                      $is_allowedToEdit ? 'help_document.php' : false);

	/*===========================================================================
				IMAGE VIEWER
  	  ===========================================================================*/

	/*
 	 * get image list from file list
 	 */

	if($cmd == 'viewImage' || $cmd == 'viewThumbs')
	{
		$imageList = get_image_list($fileList, $is_allowedToEdit);
		if(count($imageList) == 0)
		{
			$dialogBox .= $langNoImage;
		}
	}
	
    /*--------------------------------------------------------------------
                           DIALOG BOX SECTION
      --------------------------------------------------------------------*/

    if ($dialogBox)
    {
                claro_disp_message_box($dialogBox);
    }

	$is_allowedToEdit ? $colspan = 7 : $colspan = 3;


	/*------------------------------------------------------------------------
                             		VIEW IMAGE
	  ------------------------------------------------------------------------*/
	
	if ($cmd == 'viewImage' && isset($imageList) && count($imageList) > 0)
	{
		$colspan = 3;
		
		// get requested image name
		if( isset( $_REQUEST['file'] ) && ! isset( $_REQUEST['viewMode'] ) )
		{
			$file = basename( $_REQUEST['file'] );
		}
		else
		{
			$fileName = $fileList['name'][$imageList[0]];
			$file = basename( $fileName );
		}
		
		// compute relative url for requested image
		$fileUrl = $curDirPath . '/' . $file;
		
		// get requested image key in fileList
		$imgKey = image_search( $file, $fileList );
		
  		$current = get_current_index($imageList, $imgKey);
  		
    	$offset = "&offset=" . $current;
		
		// compute absolute path to requested image
		$doc_url = $coursesRepositoryWeb . $courseDir
			.implode ("/", array_map("rawurlencode", explode("/",$fileUrl)));
		
		// Image description table
		echo "<table class=\"claroTable\" width=\"100%\">\n";
		
		// lang variables
		// see images.lib.php
		
		// Display current directory if different from root
		if($curDirName)
		{
			echo "<!-- link to current dir -->\n"
				. "<tr>\n"
				. "<th class=\"superHeader\" colspan=\"". $colspan . "\" align=\"left\">\n"
				. "<img src=\"".$clarolineRepositoryWeb."img/opendir.gif\" align=\"absbottom\" vspace=\"2\" hspace=\"5\" alt=\"\">\n"
				. $dspCurDirName
				. "<small>&nbsp;&nbsp;[&nbsp;<a href=\""
				. $_SERVER['PHP_SELF']."?cmd=exChDir&file="
				. $curDirPath."\">" . $langBackToDir ."</a>&nbsp;]\n"
				. "&nbsp;&nbsp;[&nbsp;<a href=\"" .  $_SERVER['PHP_SELF']
				."?cmd=viewThumbs&curdir="
				. $curDirPath. $offset ."\">".$langThumbnailsView."</a>&nbsp;]</small>\n"
				. "</th>\n"
				. "</tr>\n"
				;
		}
		else
		{
			echo "<!-- link to current dir -->\n"
				. "<tr>\n"
				. "<th class=\"superHeader\" colspan=\"". $colspan . "\" align=\"left\">\n"
				. "<img src=\"".$clarolineRepositoryWeb."img/opendir.gif\" align=\"absbottom\" vspace=\"2\" hspace=\"5\" alt=\"\">\n"
				. $langDocument 
				. "<small>&nbsp;&nbsp;[&nbsp;<a href=\""
				. $_SERVER['PHP_SELF']."\">" . $langBackToDir . "</a>&nbsp;]\n"
                . "&nbsp;&nbsp;[&nbsp;<a href=\"" .  $_SERVER['PHP_SELF']
				."?cmd=viewThumbs&curdir="
				. $curDirPath . $offset . "\">".$langThumbnailsView."</a>&nbsp;]</small>\n"
				. "</th>\n"
				. "</tr>\n"
				;
				
		}// end if curDirName
		
		
		// --------------------- tool bar --------------------------------------
		// create image title
		$imgTitle = htmlentities($file);
		
		// create image style
		$titleStyle ='title';
		
		// if image invisible set style to invisible
		if ( $fileList['visibility'][$imgKey] == 'i')
		{
			$titleStyle = 'title invisible';
		} // if invisible

		echo "<tr class=\"toolbar\" valign=\"top\">\n";
		
  		// --------------------- display link to previous image ------------------
  		
        display_link_to_previous_image($imageList, $fileList, $current);
		
		// --------------------- display title of current image ------------------
		
		echo "<th class=\"" . $titleStyle . "\">\n";
		echo $imgTitle;
		echo "</th>\n";
		
		// --------------------- display link to previous image ------------------
		
		display_link_to_next_image($imageList, $fileList, $current);

  	echo "</tr>\n";		
		
		echo "</table>\n";
		
		// ---------------------- display comment about  requested image ----------
		
		if ($fileList['comment'][$imgKey])
		{				
			echo "<hr />\n"
				. "<blockquote>" . $fileList['comment'][$imgKey] . "</blockquote>\n"
				;
		}
		else
		{
			echo "<!-- empty -->\n";
		}// end if comment
		

		// --------------------- display current image --------------------------
		
		// system path
		
		
		$imgPath = $coursesRepositorySys . $courseDir
			. $curDirPath . '/' . basename( $file )
			;
			
		// get image info
		list($width, $height, $type, $attr ) = getimagesize($imgPath);
		
		// get color depth ! used to get both mime-type and color depth working together
		$depth = get_image_color_depth( $imgPath );	
		
		// display image
		echo "<p><center><img src=\"" . $doc_url . "\" " . $attr . " alt=\"" 
			. $file . "\" /></center></p>\n"
			;
			
		// display image info
		// -> title and size
		echo "<br /><small>[ Info : " . $imgTitle . " - " . $width 
			. "x" . $height 
			. " - " .format_file_size($fileList['size'][$imgKey])
			;	
		
		// -> color depth
		echo " - " . $depth . "bits";

		// -> mime type
		if(version_compare(phpversion(), "4.3.0", ">"))
		{
			$mime_type = image_type_to_mime_type($type);
			echo " - " . $mime_type ;
		}
		
		echo " ]</small>\n";
	}
	
	/*-----------------------------------------------------------------------
	                        VIEW THUMBNAILS
	  -----------------------------------------------------------------------*/

	else if ($cmd == 'viewThumbs' && isset($imageList) && count($imageList) > 0) // thumbnails mode
	{
	    // intialize page number
 		$page = 1; // if not set, set to first page
 		
 		if( isset( $_REQUEST['page'] ) )
		{
			$page = $_REQUEST['page'];
		}
		
		if( isset( $_REQUEST['offset'] ) )
		{
          	$page = get_page_number($offset);
		}

		// compute column width
 		$colWidth = round(100 / $numberOfCols);

		// display table
		echo "\n<table class=\"claroTable\" width=\"100%\">\n";
	

		// display current directory if different from root
		if($curDirName)
		{
			echo "<!-- link to current dir -->\n"
				. "<tr>\n"
				. "<th class=\"superHeader\" colspan=\"". $numberOfCols 
				. "\" align=\"left\">\n"
				. "<img src=\"".$clarolineRepositoryWeb
				."img/opendir.gif\" align=\"absbottom\" vspace=\"2\" hspace=\"5\" alt=\"\">\n"
				. $dspCurDirName
				. "<small>&nbsp;&nbsp;[&nbsp;<a href=\""
				. $_SERVER['PHP_SELF']."?cmd=exChDir&file="
				. $curDirPath."\">" . $langBackToDir ,"</a>&nbsp;]</small>\n"
				. "</th>\n"
				. "</tr>\n"
				;
		}
		else
		{
			echo "<!-- link to current dir -->\n"
				. "<tr>\n"
				. "<th class=\"superHeader\" colspan=\"". $numberOfCols 
				. "\" align=\"left\">\n"
				. "<img src=\"".$clarolineRepositoryWeb
				. "img/opendir.gif\" align=\"absbottom\" vspace=\"2\" hspace=\"5\" alt=\"\">\n"
            	. $langDocument."<small>&nbsp;&nbsp;[&nbsp;<a href=\"". $_SERVER['PHP_SELF']
				. "\">" . $langBackToDir . "</a>&nbsp;]</small>\n"
				. "</th>\n"
				. "</tr>\n"
				;
		}
		
		// toolbar
		
		echo "<tr class=\"toolbar\">\n";
		echo "<th class=\"prev\" colspan=\"1\" style=\"width: " . $colWidth . "%;\">\n";
		
		if(has_previous_page($imageList, $page))
		{
		    // link to previous page
          	echo "<a href=\"".$_SERVER['PHP_SELF'] 
				. "?cmd=viewThumbs&curdir=" . $curDirPath 
				. "&page=" . ($page - 1) . "\">&lt;&lt;&nbsp;&nbsp;page&nbsp;" 
				. ($page - 1) . "</a>\n"
				;
		}
		else
		{
		    echo "<!-- empty -->";
		}
		
		echo "</th>\n";
		
		echo "<th class=\"title\" colspan=\"" . ($numberOfCols - 2) . "\">\n"
			. "<p align=\"center\">page&nbsp;" . $page . "</p>"
			. "</th>\n"
			;
			
		echo "<th class=\"next\" colspan=\"1\" style=\"width: " 
			. $colWidth . "%;\">\n"
			;
		
		if(has_next_page($imageList, $page))
		{
		    // link to next page
		    echo "<a href=\"".$_SERVER['PHP_SELF'] 
				. "?cmd=viewThumbs&curdir=" . $curDirPath 
				. "&page=" . ($page + 1) . "\">page&nbsp;" 
				. ($page + 1) . "&nbsp;&nbsp;&gt;&gt;</a>\n"
				;
		}
		else
		{
		    echo "<!-- empty -->";
		}
		
		echo "</th>\n";		
		echo "</tr>\n";	

		display_thumbnails($imageList, $fileList, $page
			, $thumbnailWidth, $colWidth
			, $numberOfCols, $numberOfRows);

		echo "</table>\n";
		
	}
	else // current directory line
	{
			
	
		/*------------------------------------------------------------------------
	                             CURRENT DIRECTORY LINE
		  ------------------------------------------------------------------------*/
	
		/* GO TO PARENT DIRECTORY */
	
	    echo "<p>\n";
	
		if ($curDirName || $cmd == 'exSearch') /* if the $curDirName is empty, we're in the root point 
		                                          and we can't go to a parent dir */
		{
	
			echo "<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=exChDir&file=".$cmdParentDir."\">\n"
				."<img src=\"".$clarolineRepositoryWeb."img/parent.gif\" border=\"0\" alt=\"\">\n"
				.$langUp
				."</a>\n | ";
		}
	    else
	    {
	        echo "&nbsp;\n"
	            ."<span class='claroCmdDisabled'>"
	            ."<img src=\"".$clarolineRepositoryWeb."img/parentdisabled.gif\" border=\"0\" alt=\"\">\n"
	            .$langUp
	            ."</span>\n | ";
	    }
	    
	    
	    echo "<a class='claroCmd' href=\"" .  $_SERVER['PHP_SELF']
			. "?cmd=viewThumbs&curdir=". $curDirPath ."\">"
			. $langThumbnailsView."</a>\n";
	
	
	    echo " | "
	        ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=rqSearch\">\n"
	        ."<img src=\"".$clarolineRepositoryWeb."img/search.gif\" border=\"0\" alt=\"\">\n"
	        .$langSearch
	        ."</a>\n";
	
		if ($is_allowedToEdit)
		{
			/* CREATE DIRECTORY - UPLOAD FILE - CREATE HYPERLINK */
			
	        echo " | "
	            ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=rqUpload&cwd=".$cmdCurDirPath."\">"
	            ."<img src=\"".$clarolineRepositoryWeb."img/download.gif\" alt=\"\">"
	            .$langUploadFile
	            ."</a>\n"
	            ." | "
	            ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=rqMkDir&cwd=".$cmdCurDirPath."\">"
	            ."<img src=\"".$clarolineRepositoryWeb."img/dossier.gif\" alt=\"\">"
	            .$langCreateDir
	            ."</a>\n"
	            ."| "
	            ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=rqMkUrl&cwd=".$cmdCurDirPath."\">"
	            ."<img src=\"".$clarolineRepositoryWeb."img/liens.gif\" alt=\"\">"
	            .$langCreateHyperlink
	            ."</a>\n"
	            ." | "
	            ."<a class='claroCmd' href=\"rqmkhtml.php?cmd=rqMkHtml&cwd=".$cmdCurDirPath."\">"
	            ."<img src=\"".$clarolineRepositoryWeb."img/html.gif\" alt=\"\">"
	            .$langCreateDocument
	            ."</a>\n";
		}
	
	    echo "</p>\n";
	
	    echo "<table class=\"claroTable emphaseLine\" width=\"100%\">\n";
	
		/* CURRENT DIRECTORY */
		
		if ($curDirName) /* if the $curDirName is empty, we're in the root point 
		                    and there is'nt a dir name to display */
		{
			echo "<!-- current dir name -->\n"
				."<tr>\n"
				."<th class=\"superHeader\" colspan=\"$colspan\" align=\"left\">\n"
				."<img src=\"".$clarolineRepositoryWeb."img/opendir.gif\" align=\"absbottom\" vspace=\"2\" hspace=\"5\" alt=\"\">\n"
	            .$dspCurDirName,"\n"
				."</td>\n"
				."</tr>\n";
		}
	
		echo "<tr class=\"headerX\" align=\"center\" valign=\"top\">\n";
	
		echo "<th>".$langName."</th>\n"
			."<th>".$langSize."</th>\n"
			."<th>".$langDate."</th>\n";
				
		if ($is_allowedToEdit)			
		{
			echo "<th>".$langDelete."</th>\n"
				."<th>".$langMove."</th>\n"
				."<th>".$langModify."</th>\n";
	
	                if ($courseContext)
	                {
	                	echo "<th>".$langVisibility."</th>\n";
	                }
	                elseif ($groupContext)
	                {
	                    echo "<th>".$langPublish."</th>\n";
	                }
		}
				
		echo		"</tr>\n", 
					"<tbody>";
	
	
		/*------------------------------------------------------------------------
	                               DISPLAY FILE LIST
		  ------------------------------------------------------------------------*/
	
		if ($fileList)
		{
	        foreach($fileList['name'] as $fileKey => $fileName )
			{
	            // Note. We've switched from 'each' to 'foreach', as 'each' seems to 
	            // poses problems on PHP 4.1, when the array contains only 
	            // a single element
	
	            $dspFileName = htmlspecialchars( basename($fileName) );
				$cmdFileName = rawurlencode($fileName);
	
				if ($fileList['visibility'][$fileKey] == 'i')
				{
					if ($is_allowedToEdit)
					{
						$style=' class="invisible"';
					}
					else
					{
						continue; // skip the display of this file
					}
				}
				else 
				{
					$style='';
				}
				
				if ($fileList['type'][$fileKey] == A_FILE)
				{
					$image       = choose_image($fileName);
					$size        = format_file_size($fileList['size'][$fileKey]);
					$date        = format_date($fileList['date'][$fileKey]);
	
	                $urlFileName = 'goto/?doc_url='.urlencode($cmdFileName);
	                //$urlFileName = "goto/index.php".str_replace('%2F', '/', $cmdFileName);
	                
	                //$urlFileName = "goto/?doc_url=".urlencode($cmdFileName);
	                //format_url($baseServUrl.$courseDir.$curDirPath."/".$fileName));
				}
				elseif ($fileList['type'][$fileKey] == A_DIRECTORY)
				{
					$image       = 'dossier.gif';
					$size        = '';
					$date        = '';
					$urlFileName = $_SERVER['PHP_SELF'].'?cmd=exChDir&file='.$cmdFileName;
				}
	
				echo	"<tr align=\"center\"",$style,">\n",
						"<td align=\"left\">";
						
				if( is_image( $fileName ) )
				{
					echo "<a href=\"". $_SERVER['PHP_SELF'],
						"?cmd=viewImage&file=" . urlencode($fileName) . "&curdir=". $curDirPath ."\"". $style . ">";
				}
				else
				{
						echo "<a href=\"".$urlFileName."\"".$style.">";
				} // end if is_image
				
				echo "<img src=\"".$clarolineRepositoryWeb."img/",
						$image,"\" border=\"0\" hspace=\"5\" alt=\"\">",$dspFileName,"</a>";
				
				echo		"</td>\n",
						
						"<td><small>",$size,"</small></td>\n",
						"<td><small>",$date,"</small></td>\n";
	
				/* NB : Before tracking implementation the url above was simply
				 * "<a href=\"",$urlFileName,"\"",$style,">"
				 */
	
				if($is_allowedToEdit)
				{
					/* DELETE COMMAND */
	
					echo 	"<td>",
							"<a href=\"",$_SERVER['PHP_SELF'],"?cmd=exRm&file=",$cmdFileName,"\" ",
							"onClick=\"return confirmation('",addslashes($dspFileName),"');\">",
							"<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" border=\"0\" alt=\"$langDelete\">",
							"</a>",
							"</td>\n";
					
					/* COPY COMMAND */
	
					echo	"<td>",
							"<a href=\"",$_SERVER['PHP_SELF'],"?cmd=rqMv&file=",$cmdFileName,"\">",
							"<img src=\"".$clarolineRepositoryWeb."img/deplacer.gif\" border=\"0\" alt=\"$langMove\">",
							"</a>",
							"</td>\n";
							
					/* EDIT COMMAND */
	
					echo "<td>"
						."<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEdit&file=".$cmdFileName."\">"
						."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" border=\"0\" alt=\"".$langModify."\">"
						."</a>"
						."</td>\n";
	
	                echo	"<td>";
	
	                if ($groupContext)
	                {
	                    /* PUBLISH COMMAND */
	
	                    if ($fileList['type'][$fileKey] == A_FILE)
	                    {
	                        echo "<a href=\"../work/work.php?"
	                            ."submitGroupWorkUrl=".$groupDir.$cmdFileName."\">"
	                            ."<small>".$langPublish."</small>"
	                            ."</a>";
	                    }
	                    // else noop
	                }
	                elseif($courseContext)
	                {
	                    /* VISIBILITY COMMAND */
	
	                    if ($fileList['visibility'][$fileKey] == "i")
	                    {
	                        echo "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=exChVis&file=",$cmdFileName,"&vis=v\">"
	                            ."<img src=\"".$clarolineRepositoryWeb."img/invisible.gif\" border=\"0\" alt=\"".$langMakeVisible."\">"
	                            ."</a>";
	                    }
	                    else
	                    {
	                        echo "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=exChVis&file=",$cmdFileName,"&vis=i\">"
	                            ."<img src=\"".$clarolineRepositoryWeb."img/visible.gif\" border=\"0\" alt=\"$langMakeInvisible\">"
	                            ."</a>";
	                    }
	                }
					
					echo	"</td>\n";
				} // end if($is_allowedToEdit)
				
				echo	"</tr>\n";
				
				/* COMMENTS */
				
				if ($fileList['comment'][$fileKey] != '' )
				{
					$fileList['comment'][$fileKey] = htmlspecialchars($fileList['comment'][$fileKey]);
					$fileList['comment'][$fileKey] = claro_parse_user_text($fileList['comment'][$fileKey]);
	
					echo "<tr align=\"left\">\n"
						."<td colspan=\"$colspan\">"
						."<div class=\"comment\">"
						.$fileList['comment'][$fileKey]
						."</div>"
						."</td>\n"
						."</tr>\n";
				}
			}				// end each ($fileList)
			
		}					// end if ( $fileList)
	
		echo	"</tbody>",
	
	            "</table>\n";
	
	} // END ELSE VIEW IMAGE

include $includePath.'/claro_init_footer.inc.php';
