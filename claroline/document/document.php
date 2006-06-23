<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLDOC
 *
 * @author Hugues Peeters <hugues@claroline.net>
 * @author Claro Team <cvs@claroline.net>
 *
 */
/**
 *
 * DESCRIPTION:
 * ****
 * This PHP script allow user to manage files and directories on a remote http server.
 *  The user can : - navigate trough files and directories.
 *                 - upload a file
 *                 - rename, delete, copy a file or a directory
 *
 *  The script is organised in four sections.
 *
 *  * 1st section execute the command called by the user
 *                Note: somme commands of this section is organised in two step.
 *                The script lines always begin by the second step,
 *                so it allows to return more easily to the first step.
 *
 * * 2nd section define the directory to display
 *
 * * 3rd section read files and directories from the directory defined in part 2
 *
 *  * 4th section display all of that on a HTML page
 */

/*= = = = = = = = = = = = = = = = =
       CLAROLINE MAIN
  = = = = = = = = = = = = = = = = = = = =*/

$tlabelReq = 'CLDOC___';
require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed) claro_disp_auth_form(true);

/*
 * Library for images
 */

require_once $includePath . '/lib/image.lib.php';
require_once $includePath . '/lib/pager.lib.php';


/*
 * Library for the file display
 */

require_once $includePath . '/lib/fileDisplay.lib.php';

/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                     FILEMANAGER BASIC VARIABLES DEFINITION
  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =*/

$baseServDir = $coursesRepositorySys;
$baseServUrl = $urlAppend . '/';

$dialogBox = '';

/*
 * The following variables depends on the use context
 * The document tool can be used at course or group level
 * (one document area for each group)
 */

if ($_gid && $is_groupAllowed)
{
    $groupContext      = TRUE;
    $courseContext     = FALSE;

    $maxFilledSpace    = get_conf('maxFilledSpace_for_groups');
    $courseDir         = $_course['path'] . '/group/' . $_group['directory'];
    $groupDir          = urlencode('group/' . $_group['directory']);

    $is_allowedToEdit  = $is_groupMember || $is_groupTutor|| $is_courseAdmin;
    $is_allowedToUnzip =  FALSE;

    if ( ! $is_groupAllowed )
    {
      die('<center>You are not allowed to see this group\'s documents!!!</center>');
    }
}
else
{
    $groupContext     = FALSE;
    $courseContext    = TRUE;

    $courseDir   = $_course['path'].'/document';

    // initialise view mode tool
    claro_set_display_mode_available(TRUE);

    $is_allowedToEdit  = claro_is_allowed_to_edit();
    $is_allowedToUnzip = claro_is_allowed_to_edit();
    $maxFilledSpace    = get_conf('maxFilledSpace_for_course');

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

event_access_tool($_tid, $_courseTool['label']);


require_once $includePath . '/lib/fileManage.lib.php';

if($is_allowedToEdit) // for teacher only
{
    require_once $includePath . '/lib/fileUpload.lib.php';

    if (isset($_REQUEST['uncompress']) && $_REQUEST['uncompress'] == 1)
    {
        require_once $includePath . '/lib/pclzip/pclzip.lib.php';
    }
}


// clean information submited by the user from antislash

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

if ( isset($_REQUEST['docView']) ) $docView = $_REQUEST['docView'];
else                               $docView = 'files';


                  /* > > > > > > MAIN SECTION  < < < < < < <*/


if( $is_allowedToEdit ) // Document edition are reserved to certain people
{


    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                                  UPLOAD FILE
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */


    /*
     * check the request method in place of a variable from POST
     * because if the file size exceed the maximum file upload
     * size set in php.ini, all variables from POST are cleared !
     */

    if ('exUpload' == $cmd)
    {
        if( ! isset( $_FILES['userFile'] ) )
        {
            $dialogBox .= 'Error. No file uploaded';
        }
        else
        {
            if (   isset($_REQUEST['uncompress'])
                && $_REQUEST['uncompress'] == 1
                && $is_allowedToUnzip)                $unzip = 'unzip';
            else                                      $unzip = '';

            $_REQUEST['cwd'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['cwd']);

            $uploadedFileName = treat_uploaded_file($_FILES['userFile'], $baseWorkDir,
                                    $_REQUEST['cwd'], $maxFilledSpace, $unzip);

            if ($uploadedFileName !== false)
            {
                if (isset($_REQUEST['uncompress']) && $_REQUEST['uncompress'] == 1)
                {
                    $dialogBox .= ' ' . get_lang("Zip file uploaded and uncompressed");
                }
                else
                {
                    $dialogBox .= get_lang("The upload is finished");

                    if ( isset( $_REQUEST['comment'] ) && trim($_REQUEST['comment']) != '') // insert additional comment
                    {
                        update_db_info('update', $_REQUEST['cwd'] . '/' . $uploadedFileName,
                                        array('comment' => trim($_REQUEST['comment']) ) );
                    }
                }
            }
            else
            {
                $uploadFailure = claro_failure::get_last_failure();
                switch ( $uploadFailure )
                {
                    case 'not_enough_space':
                        $dialogBox .= get_lang("The upload has failed. There is not enough space in your directory");
                        break;
                    case 'php_file_in_zip_file':
                        $dialogBox .= get_lang("The zip file can not contain .PHP files");
                        break;
                    case 'file_exceeds_php_upload_max_filesize' :
                        $dialogBox .= 'File size exeeds.'
                                   .  '<br />'.get_lang("Notice") . ' : ' . get_lang("Max file size")
                                   . ' ' . get_cfg_var('upload_max_filesize');
                        break;
                    case 'file_exceeds_html_max_file_size':
                        $dialogBox .= 'File size exceeds.' ;
                        break;
                    case 'file_partially_uploaded':
                        $dialogBox .= 'File upload incomplete.';
                        break;
                    case 'no_file_uploaded':
                        $dialogBox .= 'No file uploaded.';
                        break;
                    default:
                        $dialogBox .= 'File upload failed.';
                }

            }

            //notify that a new document has been uploaded

            $eventNotifier->notifyCourseEvent('document_file_added'
                                             , $_cid
                                             , $_tid
                                             , $_REQUEST['cwd'] . '/' . $uploadedFileName
                                             , $_gid
                                             , '0');



            /*--------------------------------------------------------------------
               IN CASE OF HTML FILE, LOOKS FOR IMAGE NEEDING TO BE UPLOADED TOO
              --------------------------------------------------------------------*/


            if (   strrchr($uploadedFileName, '.') == '.htm'
                || strrchr($uploadedFileName, '.') == '.html')
            {
                $imgFilePath = search_img_from_html($baseWorkDir . $_REQUEST['cwd'] . '/' . $uploadedFileName);

                /*
                 * Generate Form for image upload
                 */

                if ( sizeof($imgFilePath) > 0)
                {
                    $dialogBox .= '<br /><b>' . get_lang("Missing images detected") . '</b><br />' . "\n"
                    .             '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" '
                    .             'enctype="multipart/form-data">' . "\n"
                    .             '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />'
                    .             '<input type="hidden" name="cmd" value="submitImage" />' . "\n"
                    .             '<input type="hidden" name="relatedFile" '
                    .             ' value="' . $_REQUEST['cwd'] . '/' . $uploadedFileName . '" />' . "\n"
                    .             '<table border="0">' . "\n"
                    ;

                    foreach($imgFilePath as $thisImgKey => $thisImgFilePath )
                    {
                        $dialogBox .= '<tr>' . "\n"
                        .             '<td>' . "\n"
                        .             '<label for="' . $thisImgKey . '">' . basename($thisImgFilePath) . ' : </label>' . "\n"
                        .             '</td>' . "\n"
                        .             '<td>'
                        .             '<input type="file"  id="' . $thisImgKey . '" name="imgFile[]">' . "\n"
                        .             '<input type="hidden" name="imgFilePath[]" '
                        .             ' value="' . $thisImgFilePath . '">'
                        .             '</td>' . "\n"
                        .             '</tr>' . "\n"
                        ;
                    }

                    $dialogBox .= 'tr>'
                    .             '<td></td>'
                    .             '<td>'
                    .             '<input type="submit" name="submitImage" value="' . get_lang("Ok") . '"> '
                    .             claro_html_button($_SERVER['PHP_SELF']
                    .            '?cmd=exChDir&file=' . htmlspecialchars($_REQUEST['cwd']), get_lang("Cancel") )
                    .             '</td>'
                    .             '</tr>'
                    .             '</table>' . "\n"
                    .             '</form>' . "\n"
                    ;
                }                            // end if ($imgFileNb > 0)
            }                                // end if (strrchr($fileName) == "htm"
        }                                    // end if is_uploaded_file
    }                                        // end if ($cmd == 'exUpload')

    if ($cmd == 'rqUpload')
    {
        /*
         * Prepare dialog box display
         */

        $spaceAlreadyOccupied = dir_total_space($baseWorkDir);

        /*
         * Technical note: 'cmd=exUpload' is added into the 'action'
         * attributes of the form, rather than simply put in a post
         * hidden input. That way, this parameter is concatenated with
         * the URL, and it guarantees than it will be received by the
         * server. The reason of this trick, is because, sometimes,
         * when file upload fails, no form data are received at all by
         * the server. For example when the size of the sent file is so
         * huge that its reception exceeds the max execution time
         * allowed for the script. When no 'cmd' argument are sent it is
         * impossible to manage this error gracefully. That's why,
         * exceptionally, we pass 'cmd' in the 'action' attribute of
         * the form.
         */

        $dialogBox .= "<form action=\"".$_SERVER['PHP_SELF']."?cmd=exUpload\" method=\"post\" enctype=\"multipart/form-data\">"
                     ."<input type=\"hidden\" name=\"cmd\" value=\"exUpload\">"
                     ."<input type=\"hidden\" name=\"cwd\" value=\"".$_REQUEST['cwd']."\">"
                     ."<label for=\"userFile\">".get_lang("Upload file")." : </label><br />"
                     ."<input type=\"file\" id=\"userFile\" name=\"userFile\"> "
                     ."<table border='0'>"
                     ."<tr>"
                     ."<td><small>".get_lang('Max file size')." :</small></td>"
                     ."<td><small> : ".format_file_size( get_max_upload_size($maxFilledSpace,$baseWorkDir) )."</small></td>"
                     ."</tr>"
                     ."<tr>"
                     ."<td><small>Disk space available</small></td>"
                     ."<td><small>  : ".claro_html_progress_bar( $spaceAlreadyOccupied / $maxFilledSpace * 100 , 1) .' '.format_file_size($maxFilledSpace - $spaceAlreadyOccupied)."</small></td>"
                     ."</tr>"
                     ."</table>\n";

        if ($is_allowedToUnzip)
        {
            $dialogBox .= "<img src=\"".$imgRepositoryWeb."zip.gif\" alt=\"\">"
                          ."<input type=\"checkbox\" id=\"uncompress\" name=\"uncompress\" value=\"1\">"
                          ."<label for=\"uncompress\">".get_lang("uncompress zipped (.zip) file on the server")."</label>";
        }

        if ($courseContext)
        {
            if (!isset($oldComment)) $oldComment = "";
        $dialogBox .= "<p>\n"
                        ."<label for=\"comment\">".get_lang("Add a comment (optionnal) :")."</label>"
                        ."<br /><textarea rows=2 cols=50 id=\"comment\" name=\"comment\">"
                        .$oldComment
                        ."</textarea>\n"
                        ."</p>\n";
        }

        $dialogBox .= "<input style=\"font-weight: bold\" type=\"submit\" value=\"".get_lang("Ok")."\"> "
                   .claro_html_button($_SERVER['PHP_SELF']. '?cmd=exChDir&file='.htmlspecialchars($_REQUEST['cwd']),
                                      get_lang("Cancel"))
                   ."</form>";
    }


    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                           UPLOAD RELATED IMAGE FILES
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    if ('submitImage' == $cmd )
    {

        $uploadImgFileNb = sizeof($_FILES['imgFile']);

        if ($uploadImgFileNb > 0)
        {
            // Try to create  a directory to store the image files
            $_REQUEST['relatedFile'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['relatedFile']);

            $imgDirectory = $_REQUEST['relatedFile'].'_files';
            $imgDirectory = create_unexisting_directory($baseWorkDir.$imgDirectory);

            // set the makeInvisible command param appearing later in the script
            $mkInvisibl = str_replace($baseWorkDir, '', $imgDirectory);

            // move the uploaded image files into the corresponding image directory

            // Try to create  a directory to store the image files
            $newImgPathList = move_uploaded_file_collection_into_directory($_FILES['imgFile'], $imgDirectory);


            $newImgPathList = array_map('urlencode', $newImgPathList);
            // urlencode() does too much. We don't need to replace '/' by '%2F'
            $newImgPathList = str_replace('%2F', '/', $newImgPathList);

            replace_img_path_in_html_file($_REQUEST['imgFilePath'],
                                          $newImgPathList,
                                          $baseWorkDir.$_REQUEST['relatedFile']);

        }                                            // end if ($uploadImgFileNb > 0)
    }                                        // end if ($submitImage)



    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                             CREATE DOCUMENT
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    /*------------------------------------------------------------------------
                            CREATE DOCUMENT : STEP 2
      ------------------------------------------------------------------------*/

    $htmlContentHeader = '<html>' . "\n"
        . '<head>' . "\n"
        . '<meta http-equiv="Content-Type" content="text/HTML; charset=' . $charset . '"  />' . "\n"
        . '</head>' . "\n"
        . '<body>' . "\n";

    $htmlContentFooter = '</body></html>';

    if ('exMkHtml' == $cmd)
    {
        $fileName = replace_dangerous_char(trim($_REQUEST['fileName']));
        $_REQUEST['cwd'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['cwd']);

        if (! empty($fileName) )
        {
            if ( ! in_array( strtolower (get_file_extension($_REQUEST['fileName']) ),
                           array('html', 'htm') ) )
            {
                $fileName = $fileName.'.htm';
            }

            $_REQUEST['cwd'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['cwd']);

            $htmlContent =  $htmlContentHeader . $_REQUEST['htmlContent'] . $htmlContentFooter;

            create_file($baseWorkDir.$_REQUEST['cwd'].'/'.$fileName,
                        $htmlContent);

            $eventNotifier->notifyCourseEvent("document_htmlfile_created",$_cid, $_tid, $_REQUEST['cwd'].'/'.$fileName, $_gid, "0");
            $dialogBox .= get_lang("File created");
        }
        else
        {
            $dialogBox .= get_lang("File name is missing");

            if (!empty($_REQUEST['htmlContent']))
            {
                $dialogBox .= "<p>\n"
                             ."<a href=\"rqmkhtml.php"
                             ."?cmd=rqMkHtml"
                             ."&amp;cwd=".urlencode($_REQUEST['cwd'])
                             ."&amp;htmlContent=".urlencode($_REQUEST['htmlContent'])."\">\n"
                             .get_lang("Back to the editor")."\n"
                             ."</p>\n";
            }
        }
    }


    /*------------------------------------------------------------------------
                            CREATE DOCUMENT : STEP 1
      ------------------------------------------------------------------------*/

      // see rqmkhtml.php ...

    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                             EDIT DOCUMENT CONTENT
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    if ('exEditHtml' == $cmd)
    {
        $_REQUEST['file'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['file']);
        $fp = fopen($baseWorkDir.$_REQUEST['file'], 'w');

        if ($fp)
        {
            $htmlContent =  $htmlContentHeader . $_REQUEST['htmlContent'] . $htmlContentFooter;

            if ( fwrite($fp, $htmlContent) )
            {
                $eventNotifier->notifyCourseEvent("document_htmlfile_edited",$_cid, $_tid, $_REQUEST['file'], $_gid, "0");
                                $dialogBox .= get_lang("File content modified")."<br />";
            }

        }
    }


    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                                   CREATE URL
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    /*
     * The code begins with STEP 2
     * so it allows to return to STEP 1 if STEP 2 unsucceeds
     */

    /*------------------------------------------------------------------------
                              CREATE URL : STEP 2
    --------------------------------------------------------------------------*/

    if ( 'exMkUrl' == $cmd )
    {
        $fileName = replace_dangerous_char(trim($_REQUEST['fileName']));
        $url = trim($_REQUEST['url']);

        $_REQUEST['cwd'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['cwd']);

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

            if (   isset($_REQUEST['comment'])
                && trim($_REQUEST['comment']) != ''
                && $courseContext                     )
            {
                update_db_info('update', $_REQUEST['cwd'].'/'.$fileName.$linkFileExt,
                                array('comment' => trim($_REQUEST['comment']) ) );
            }
        }
        else
        {
            $dialogBox .= get_lang("File Name or URL is missing.");
            $cmd        = 'rqMkUrl';
        }
    }

    /*------------------------------------------------------------------------
                              CREATE URL : STEP 1
    --------------------------------------------------------------------------*/

    if ('rqMkUrl' == $cmd )
    {
        $dialogBox .= "<h4>".get_lang("Create hyperlink")."</h4>\n"
                     ."<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n"
                     ."<input type=\"hidden\" name=\"cmd\" value=\"exMkUrl\">\n"
                     ."<input type=\"hidden\" name=\"cwd\" value=\"".$_REQUEST['cwd']."\">"
                     ."<label for=\"fileName\">".get_lang("Name")." : </label><br />\n"
                     ."<input type=\"text\" id=\"fileName\" name=\"fileName\"><br />\n"
                     ."<label for=\"url\">".get_lang("URL : ")."</label><br />\n"
                     ."<input type=\"text\" id=\"url\" name=\"url\" value=\"\">\n"
                     ."<br /><br />\n";

        if ($courseContext)
        {
            $dialogBox .= "<label for=\"comment\">\n"
                        ."Add a comment (optionnal) :\n"
                        ."</label>\n"
                        ."<br />\n"
                        ."<textarea rows=\"2\" cols=\"50\" id=\"comment\" name=\"comment\"></textarea>\n"
                        ."<br />\n";
        }

        $dialogBox .= "<input type=\"submit\" value=\"".get_lang("Ok")."\">\n"
                     .claro_html_button($_SERVER['PHP_SELF']. '?cmd=exChDir&file='.htmlspecialchars($_REQUEST['cwd']),
                                       get_lang("Cancel"))
                     ."</form>\n";

    }

    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                             MOVE FILE OR DIRECTORY
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */


    /*------------------------------------------------------------------------
                        MOVE FILE OR DIRECTORY : STEP 2
    --------------------------------------------------------------------------*/

    if ('exMv' == $cmd )
    {
        $_REQUEST['file'       ] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['file'       ]);
        $_REQUEST['destination'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['destination']);

        if ( claro_move_file($baseWorkDir.$_REQUEST['file'],$baseWorkDir.$_REQUEST['destination']) )
        {
            if ($courseContext)
            {
                update_db_info( 'update', $_REQUEST['file'],
                                array('path' => $_REQUEST['destination'].'/'.basename($_REQUEST['file'])) );
                update_Doc_Path_in_Assets("update",$_REQUEST['file'],
                                                   $_REQUEST['destination'].'/'.basename($_REQUEST['file']));
            }
            $ressource['old_uri'] = $_REQUEST['file'];
            $ressource['new_uri'] = $_REQUEST['destination'].'/'.basename($_REQUEST['file']);
            $eventNotifier->notifyCourseEvent('document_moved', $_cid, $_tid, $ressource, $_gid, '0');

            $dialogBox = get_lang("Element moved").'<br />';
        }
        else
        {
            $dialogBox = get_lang("Operation impossible").'<br />';

            if ( claro_failure::get_last_failure() == 'FILE EXISTS' )
            {
                $dialogBox .= 'A file with the same name already exists.';
            }
            elseif (claro_failure::get_last_failure() == 'MOVE INSIDE ITSELF')
            {
                $dialogBox .= 'You can not move an element inside itself.';
            }

            /* return to step 1 */

            $cmd = 'rqMv';
            unset ($_REQUEST['destination']);
        }
    }


    /*------------------------------------------------------------------------
                        MOVE FILE OR DIRECTORY : STEP 1
    --------------------------------------------------------------------------*/

    if ('rqMv' == $cmd )
    {
        $dialogBox .= form_dir_list($_REQUEST['file'], $baseWorkDir);
    }



    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                            DELETE FILE OR DIRECTORY
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */


    if ('exRm' == $cmd )
    {
        $file = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['file']);

        if ( claro_delete_file($baseWorkDir.$file))
        {
            if ($courseContext)
            {
                update_db_info('delete', $file);
                update_Doc_Path_in_Assets('delete', $file, '');
            }

            //notify that a document has been deleted

            $eventNotifier->notifyCourseEvent("document_file_deleted",$_cid, $_tid, $_REQUEST['file'], $_gid, "0");

            $dialogBox = get_lang("Document deleted");
        }
    }




    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                                      EDIT
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    /*
     * The code begin with STEP 2
     * so it allows to return to STEP 1
     * if STEP 2 unsucceds
     */


    /*------------------------------------------------------------------------
                                 EDIT : STEP 2
      ------------------------------------------------------------------------*/

    if ('exEdit' == $cmd )
    {
        if ( isset($_REQUEST['url']))
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

        $_REQUEST['newName'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', trim($_REQUEST['newName']));


        if ( ! empty($_REQUEST['newName']) )
        {
            $newPath = $directoryName . '/' . $_REQUEST['newName'];
        }
        else
        {
            $newPath = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['file']);
        }

        $newPath = claro_rename_file($baseWorkDir.$_REQUEST['file'], $baseWorkDir.$newPath);

        if ( $newPath )
        {
            $newPath = substr($newPath, strlen($baseWorkDir) );
            $dialogBox = get_lang("Element renamed").'<br />';

            if ($courseContext)
            {
                $newComment = trim($_REQUEST['newComment']); // remove spaces

                update_db_info('update', $_REQUEST['file'],
                                array( 'path'    => $newPath,
                                       'comment' => $newComment ) );

                update_Doc_Path_in_Assets('update', $_REQUEST['file'], $newPath);

                if ( ! empty($newComment) ) $dialogBox .= get_lang("Comment modified").'<br />';
            }

            $ressource['old_uri'] = str_replace('..', '', $_REQUEST['file']);
            $ressource['new_uri'] = $newPath;
            $eventNotifier->notifyCourseEvent("document_file_modified",$_cid, $_tid, $ressource , $_gid, "0");
        }
        else
        {
            $dialogBox .= get_lang("Operation impossible.<br />A file with this name already exists.");

            /* return to step 1 */

            $cmd = 'rqEdit';
        }
    }


    /*------------------------------------------------------------------------
                                 EDIT : STEP 1
    -------------------------------------------------------------------------*/

    if ('rqEdit' == $cmd )
    {
        $fileName = basename($_REQUEST['file']);

        $dialogBox .=     "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">"
                        ."<input type=\"hidden\" name=\"cmd\" value=\"exEdit\">\n"
                        ."<input type=\"hidden\" name=\"file\" value=\"".$_REQUEST['file']."\">\n"
                        ."<p>\n"
                        ."<label for=\"newName\">".get_lang("Rename")." ".htmlspecialchars($fileName)
                        ." ".get_lang("in")." : </Label>\n"
                        ."<br /><input type=\"text\" id=\"newName\" name=\"newName\" value=\"". htmlspecialchars($fileName) ."\">\n"
                        ."</p>\n";

        if ('url' == get_file_extension($baseWorkDir.$_REQUEST['file']) )
        {
            $url = get_link_file_url($baseWorkDir.$_REQUEST['file']);

            $dialogBox .= "<p><label for=\"url\">".get_lang("URL : ")."</label><br />\n"
                         ."<input type=\"text\" id=\"url\" name=\"url\" value=\"".htmlspecialchars($url)."\">\n"
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

        if (!isset($oldComment)) $oldComment = "";

            $dialogBox .= "<p>\n<label for=\"newComment\">"
                          .get_lang("Add/modify a comment to")." ".htmlspecialchars($fileName)."</label>\n"
                          ."<br /><textarea rows=\"2\" cols=\"50\" name=\"newComment\" id=\"newComment\">"
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
                          ."<a href=\"rqmkhtml.php?cmd=rqEditHtml&amp;file=".$_REQUEST['file']."\">"
                          .get_lang("Edit file content")
                          ."</a>"
                          ."</p>";
        }

        $dialogBox .= "<br /><input type=\"submit\" value=\"".get_lang("Ok")."\">\n"
                      .claro_html_button($_SERVER['PHP_SELF']. '?cmd=exChDir&file='.htmlspecialchars(claro_dirname($_REQUEST['file'])),
                                         get_lang("Cancel"))
                     ."</form>\n";

    } // end if cmd == rqEdit




    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                                CREATE DIRECTORY
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    /*
     * The code begin with STEP 2
     * so it allows to return to STEP 1
     * if STEP 2 unsucceds
     */

    /*------------------------------------------------------------------------
                                     STEP 2
      ------------------------------------------------------------------------*/

    if ('exMkDir' == $cmd )
    {
        $newDirName = replace_dangerous_char(trim($_REQUEST['newName']));

        $_REQUEST['cwd'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['cwd']);

        if( check_name_exist($baseWorkDir.$_REQUEST['cwd'].'/'.$newDirName) )
        {
            $dialogBox = get_lang("Operation impossible.<br />A file with this name already exists.");
            $cmd = 'rqMkDir';
        }
        else
        {
            claro_mkdir($baseWorkDir.$_REQUEST['cwd'].'/'.$newDirName, CLARO_FILE_PERMISSIONS);

            if ( trim($_REQUEST['comment']) != '' && $courseContext)
            {
                update_db_info('update', $_REQUEST['cwd'].'/'.$newDirName,
                                array('comment' => trim($_REQUEST['comment']) ) );
            }

            $dialogBox = get_lang("Directory created");
            $eventNotifier->notifyCourseEvent("document_file_added",$_cid, $_tid, $_REQUEST['cwd'].'/'.$newDirName, $_gid, "0");
        }
    }


    /*------------------------------------------------------------------------
                                     STEP 1
      ------------------------------------------------------------------------*/

    if ('rqMkDir' == $cmd )
    {
        $dialogBox .= "<form>\n"
                      ."<input type=\"hidden\" name=\"cmd\" value=\"exMkDir\">\n"
                      ."<input type=\"hidden\" name=\"cwd\" value=\"".$_REQUEST['cwd']."\">\n"
                      ."<label for=\"newName\">".get_lang("Name of the new directory")." : </label><br />\n"
                      ."<input type=\"text\" id=\"newName\" name=\"newName\">\n"
                      ."<br />"
                      ."<label for=\"comment\">\n"
                      ."Add a comment (optionnal) :\n"
                      ."</label>\n"
                      ."<br />\n"
                      ."<textarea rows=\"2\" cols=\"50\" id=\"comment\" name=\"comment\"></textarea>\n"
                      ."<br />\n"
                      ."<input type=\"submit\" value=\"".get_lang("Ok")."\">\n"
                      .claro_html_button($_SERVER['PHP_SELF']. '?cmd=exChDir&file='.htmlspecialchars($_REQUEST['cwd']),
                                                get_lang("Cancel"))
                      ."</form>\n";
    }

    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                              VISIBILITY COMMANDS
      = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    if ('exChVis'  == $cmd && $courseContext)
    {
        $_REQUEST['file'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['file']);

        update_db_info('update', $_REQUEST['file'], array('visibility' => $_REQUEST['vis']) );

    $dialogBox = get_lang("Visibility modified");

        //notify claroline that visibility changed

    if ($_REQUEST['vis'] == 'v')
        {
        $eventNotifier->notifyCourseEvent("document_visible",$_cid, $_tid, $_REQUEST['file'], $_gid, "0");
        }
        else
            {
                $eventNotifier->notifyCourseEvent("document_invisible",$_cid, $_tid, $_REQUEST['file'], $_gid, "0");
            }
    }
} // END is Allowed to Edit



if ('rqSearch' == $cmd )
{
    $searchMsg = empty($_REQUEST['cwd']) ? get_lang("Search")." :" : get_lang("Search in ").$_REQUEST['cwd']." :" ;
    $dialogBox .=     "<form>\n"
                    ."<input type=\"hidden\" name=\"cmd\" value=\"exSearch\">\n"
                    ."<label for=\"searchPattern\">".$searchMsg."</label><br />\n"
                    ."<input type=\"text\" id=\"searchPattern\" name=\"searchPattern\">\n"
                    ."<input type=\"hidden\" name=\"cwd\" value=\"".$_REQUEST['cwd']."\"><br />\n"
                    ."<input type=\"submit\" value=\"".get_lang("Ok")."\">\n"
                    .claro_html_button($_SERVER['PHP_SELF']. '?cmd=exChDir&file='.htmlspecialchars($_REQUEST['cwd']),
                                       get_lang("Cancel"))

                    ."</form>\n";
}

if ('exDownload' == $cmd )
{
    /*
     * PREPARE THE FILE COLLECTION
     */

    if ( isset($_REQUEST['file'] ) )
    {
        $requestDownloadPath = $baseWorkDir
                             . preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['file']);
        $searchDownloadPattern = '';
    }
    elseif( isset($_REQUEST['searchPattern']) )
    {
            $requestDownloadPath   = $baseWorkDir;
            $searchDownloadPattern = $_REQUEST['searchPattern'];
    }

    if (! $is_allowedToEdit && $courseContext)
    {
        // Build an exclude file list to prevent simple user
        // to see document contained in "invisible" directories

        $sql = "SELECT path FROM `".$dbTable."` WHERE visibility ='i'";

        $searchExcludeList = claro_sql_query_fetch_all_cols($sql);
        $searchExcludeList = $searchExcludeList['path'];
    }
    else
    {
        $searchExcludeList = array();
    }

    $filePathList = claro_search_file(search_string_to_pcre($searchDownloadPattern),
                                      $requestDownloadPath,
                                      true,
                                      'ALL',
                                      $searchExcludeList);

    /*
     * BUILD THE ZIP ARCHIVE
     */

    require_once $includePath . '/lib/pclzip/pclzip.lib.php';

    $downloadArchivePath = $requestDownloadPath.'/'.uniqid('').'.zip';
    $downloadArchiveName = basename($requestDownloadPath.'.zip');
    $downloadArchiveName = str_replace('/', '', $downloadArchiveName);
    if ( $downloadArchiveName == '.zip') $downloadArchiveName = get_lang("Documents and Links").'.zip';

    $downloadArchive     = new PclZip($downloadArchivePath);

    $downloadArchive->add($filePathList,
                          PCLZIP_OPT_REMOVE_PATH,
                          $requestDownloadPath);

    if ( file_exists($downloadArchivePath) )
    {
        $downloadArchiveSize = filesize($downloadArchivePath);

        /*
         * SEND THE ZIP ARCHIVE FOR DOWNLOAD
         */

        header('Expires: Wed, 01 Jan 1990 00:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-type: application/zip');
        header('Content-Length: '.$downloadArchiveSize);
        header('Content-Disposition: attachment; filename="'.$downloadArchiveName.'";');

        readfile($downloadArchivePath);
        unlink($downloadArchivePath);
        exit();
    }
    else
    {
        $dialogBox .= get_lang("Unable to create zip file");
    }
}


/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                            DEFINE CURRENT DIRECTORY
  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

if (in_array($cmd, array('rqMv', 'exRm', 'rqEdit', 'exEdit', 'exEditHtml',
                         'exChVis', 'rqComment', 'exComment', 'submitImage')))
{
    $curDirPath = claro_dirname(isset($_REQUEST['file']) ? $_REQUEST['file'] : $_REQUEST['relatedFile']);
}
elseif (in_array($cmd, array('rqMkDir', 'exMkDir', 'rqUpload', 'exUpload',
                             'rqMkUrl', 'exMkUrl', 'reqMkHtml', 'exMkHtml', 'rqSearch')))
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
elseif ($docView == 'image' || $docView == 'thumbnails' )
{
    $curDirPath = $_REQUEST['cwd'];
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




/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                         READ CURRENT DIRECTORY CONTENT
  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

/*----------------------------------------------------------------------------
                     LOAD FILES AND DIRECTORIES INTO ARRAYS
  ----------------------------------------------------------------------------*/

// $resultFileList = array();

if ($cmd == 'exSearch')
{
    if (! $is_allowedToEdit && $courseContext)
    {
        // Build an exclude file list to prevent simple user
        // to see document contained in "invisible" directories

        $sql = "SELECT path FROM `".$dbTable."`
                WHERE visibility ='i'";

        $searchExcludeList = claro_sql_query_fetch_all_cols($sql);
        $searchExcludeList = $searchExcludeList['path'];

        for( $i=0; $i < count($searchExcludeList); $i++ )
        {
            $searchExcludeList[$i] = $baseWorkDir.$searchExcludeList[$i];
        }
    }
    else
    {
      $searchExcludeList = array();
    }

    $_REQUEST['cwd'] = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $_REQUEST['cwd']);

    $searchPattern    = $_REQUEST['searchPattern'];
    $searchPatternSql = $_REQUEST['searchPattern'];

    $searchPatternSql = str_replace('_', '\\_', $searchPatternSql);
    $searchPatternSql = str_replace('%', '\\%', $searchPatternSql);
    $searchPatternSql = str_replace('?', '_' , $searchPatternSql);
    $searchPatternSql = str_replace('*', '%' , $searchPatternSql);

    $searchRecursive = true;
    $searchBasePath  = $baseWorkDir.$_REQUEST['cwd'];
}
else
{
    $searchPattern   = '';
    $searchRecursive = false;
    $searchBasePath  = $baseWorkDir.$curDirPath;
    $searchExcludeList = array();
}

$searchBasePath = preg_replace('~^(\.\.)$|(/\.\.)|(\.\./)~', '', $searchBasePath);

$filePathList = claro_search_file( search_string_to_pcre($searchPattern),
                                  $searchBasePath,
                                  $searchRecursive,
                                  'ALL',
                                  $searchExcludeList);

for ($i =0; $i < count($filePathList); $i++ )
{
    $filePathList[$i] = str_replace($baseWorkDir, '', $filePathList[$i]);
}

if ($cmd == 'exSearch' && $courseContext)
{
    $sql = "SELECT path FROM `".$dbTable."`
            WHERE comment LIKE '%".addslashes($searchPatternSql)."%'";

    $dbSearchResult = claro_sql_query_fetch_all_cols($sql);

    if (! $is_allowedToEdit)
    {
        for ($i = 0; $i < count($searchExcludeList) ; $i++)
        {
            for ($j = 0; $j < count($dbSearchResult['path']) ; $j++)
            {
                if (preg_match('|^'.$searchExcludeList[$i].'|', $dbSearchResult['path'][$j]) )
                {
                    unset($dbSearchResult['path'][$j]);
                }
            }
        }
    }

    $filePathList = array_unique( array_merge($filePathList, $dbSearchResult['path']) );
}

$fileList = array();

if ( count($filePathList) > 0 )
{
    /*--------------------------------------------------------------------------
                 SEARCHING FILES & DIRECTORIES INFOS ON THE DB
      ------------------------------------------------------------------------*/

    /*
     * Search infos in the DB about the current directory the user is in
     */

    if ($courseContext)
    {
        $sql = "SELECT `path`, `visibility`, `comment`
                FROM `".$dbTable."`
                WHERE path IN ('".implode("', '", array_map('addslashes', $filePathList) )."')";

        $xtraAttributeList = claro_sql_query_fetch_all_cols($sql);
    }
    else
    {
        $xtraAttributeList = array('path' => array(), 'visibility'=> array(), 'comment' => array() );
    }


    define('A_DIRECTORY', 1);
    define('A_FILE',      2);

    foreach($filePathList as $thisFile)
    {
        $fileAttributeList['path'] = $thisFile;

        if( is_dir($baseWorkDir.$thisFile) )
        {
            $fileAttributeList['type'] = A_DIRECTORY;
            $fileAttributeList['size'] = false;
            $fileAttributeList['date'] = false;
        }
        elseif( is_file($baseWorkDir.$thisFile) )
        {
            $fileAttributeList['type'] = A_FILE;
            $fileAttributeList['size'] = claro_get_file_size($baseWorkDir.$thisFile);
            $fileAttributeList['date'] = filectime($baseWorkDir.$thisFile);
        }

        $xtraAttributeKey = array_search($thisFile, $xtraAttributeList['path']);

        if ($xtraAttributeKey !== false)
        {
            $fileAttributeList['comment'   ] = $xtraAttributeList['comment'   ][$xtraAttributeKey];
            $fileAttributeList['visibility'] = $xtraAttributeList['visibility'][$xtraAttributeKey];

            unset( $xtraAttributeList['path'][$xtraAttributeKey] );
        }
        else
        {
            $fileAttributeList['comment'   ] = null;
            $fileAttributeList['visibility'] = null;
        }

        $fileList[] = $fileAttributeList;
    } // end foreach $filePathList

    /*------------------------------------------------------------------------
                              CHECK BASE INTEGRITY
      ------------------------------------------------------------------------*/

    if ( count($xtraAttributeList['path']) > 0 )
    {
        $sql = "DELETE FROM `".$dbTable."`
                WHERE `path` IN ( \"".implode("\" , \"" , $xtraAttributeList['path'])."\" )";

        claro_sql_query($sql);

        $sql = "DELETE FROM `".$dbTable."`
                WHERE comment LIKE '' AND visibility LIKE 'v'";

        claro_sql_query($sql);
        /* The second query clean the DB 'in case of' empty records (no comment an visibility=v)
           These kind of records should'nt be there, but we never know... */

    }    // end if sizeof($attribute['path']) > 0

} // end if count ($filePathList) > 0


$defaultSortkeyList = array('type', 'path', 'date', 'size', 'visibility');
$fileLister = new claro_array_pager($fileList, 0, 1000);
foreach ($defaultSortkeyList as $thisSortkey) $fileLister->add_sort_key($thisSortkey, SORT_ASC);
if ( isset($_GET['sort']) ) $fileLister->set_sort_key($_GET['sort'], $_GET['dir']);

$sortUrlList = $fileLister->get_sort_url_list( $_SERVER['PHP_SELF'] . '?cmd=exChDir&file='.urlencode($curDirPath) );

$fileList = $fileLister->get_result_list();


      /* > > > > > > END: COMMON TO TEACHERS AND STUDENTS < < < < < < <*/


/*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                                    DISPLAY
  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
    if (confirm(\" ".clean_str_for_javascript(get_lang("Are you sure to delete"))." \"+ name + \" ?\"))
        {return true;}
    else
        {return false;}
}
</script>";

if ( $docView == 'image' )
{
    $noQUERY_STRING = true;
    $htmlHeadXtra[] =
"<script>
var nOriginalHeight;
var nOriginalWidth;

function zoomIn ()
{
    var oImage = document.getElementById('mainImage');
    oImage.width = nOriginalWidth;
    oImage.height = nOriginalHeight;
    oImage.onclick = function(){zoomOut();};
    oImage.setAttribute( 'title', '".clean_str_for_javascript(get_lang("click to zoom out"))."' );
    // oImage.style.cursor = '-moz-zoom-in';
}

function zoomOut ()
{
    var oImage = document.getElementById('mainImage');

    nOriginalHeight = oImage.height;
    nOriginalWidth = oImage.width;

    var nNewWidth = getWindowWidth() - 30;

    if ( nNewWidth < nOriginalWidth )
    {
        var nNewHeight = computeHeight ( nNewWidth );

        oImage.width = nNewWidth;
        oImage.height = nNewHeight;

        oImage.onclick = function(){zoomIn();};
        oImage.setAttribute( 'title', '".clean_str_for_javascript(get_lang("click to zoom in"))."' );
        // oImage.style.cursor = '-moz-zoom-out';
    }
}

function computeHeight( nWidth )
{
    var nScaleFactor = nWidth / nOriginalWidth;
    var nNewHeight = nOriginalHeight * nScaleFactor;
    return Math.floor( nNewHeight );
}

function getWindowWidth ()
{
    var ww = 0;

    if ( typeof window.innerWidth != 'undefined' )
    {
        ww = window.innerWidth;  // NN and Opera version
    }
    else
    {
        if ( document.documentElement
            && typeof document.documentElement.clientWidth!='undefined'
            && document.documentElement.clientWidth != 0 )
        {
            ww = document.documentElement.clientWidth;
        }
        else
        {
            if ( document.body
                && typeof document.body.clientWidth != 'undefined' )
            {
                ww = document.body.clientWidth;
            }
        }
   }
   return ww;
}
</script>";
    $claroBodyOnload[] = "zoomOut();";
}//

$nameTools = get_lang("Documents and Links");

$_SERVER['QUERY_STRING'] = ''; // used for the breadcrumb
                              // when one need to add a parameter after the filename

include($includePath.'/claro_init_header.inc.php');

$dspCurDirName = htmlspecialchars($curDirName);
$dspCurDirPath = htmlspecialchars($curDirPath);
$cmdCurDirPath = rawurlencode($curDirPath);
$cmdParentDir  = rawurlencode($parentDir);

//display toot title and subtitle

$titleElement['mainTitle'] = get_lang("Documents and Links");
if ( $_gid && $is_groupAllowed) $titleElement['supraTitle'] = $_group['name'];

echo claro_html_tool_title($titleElement,
                      $is_allowedToEdit ? 'help_document.php' : false);

    /*= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
                IMAGE VIEWER
        = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =*/

    /*
      * get image list from file list
      */

    if( ($docView == 'image' || $docView == 'thumbnails') && isset($fileList) )
    {
        $imageList = get_image_list($fileList, $is_allowedToEdit);
    }

    /*--------------------------------------------------------------------
                           DIALOG BOX SECTION
      --------------------------------------------------------------------*/

    if (isset($dialogBox) && $dialogBox != '') echo claro_html_message_box($dialogBox);

    $is_allowedToEdit ? $colspan = 7 : $colspan = 3;


    /*------------------------------------------------------------------------
                                     VIEW IMAGE
      ------------------------------------------------------------------------*/

    if ($docView == 'image' && isset($imageList) && count($imageList) > 0)
    {
        $colspan = 3;

        // get requested image name
        if( isset( $_REQUEST['file'] ) && ! isset( $_REQUEST['viewMode'] ) )
        {
            $file = $_REQUEST['file'];
            $fileName = basename( $_REQUEST['file'] );
        }
        else
        {
            $file = $fileList['path'][$imageList[0]];
            $fileName = basename( $file );
        }

        $searchCmdUrl = "";

        if( isset( $_REQUEST['searchPattern'] ) )
        {
            $searchCmdUrl = "&amp;cmd=exSearch&amp;searchPattern=" . urlencode( $_REQUEST['searchPattern'] );
        }

        // get requested image key in fileList
        $imgKey = image_search( $file, $filePathList );

        $current = get_current_index($imageList, $imgKey);

        $offset = "&amp;offset=" . $current;

        // compute absolute path to requested image

        if ( strstr($_SERVER['SERVER_SOFTWARE'], 'Apache')
          && get_conf('secureDocumentDownload') )
        {
            // slash argument method - only compatible with Apache
            $doc_url = 'goto/index.php'.str_replace('%2F', '/', rawurlencode($file));
        }
        else
        {
            // question mark argument method, for IIS ...
            $doc_url = 'goto/?url=' . rawurlencode($file);
        }


        // Image description table
        echo '<table class="claroTable" width="100%">' . "\n";

        // View Mode Bar

        if ($cmd == 'exSearch')
        {
            $curDirLine = get_lang("Search result");
        }
        elseif ($curDirName)
        {
               $curDirLine = '<img src="' . $imgRepositoryWeb . 'opendir.gif" '
               .             'align="absbottom" vspace="2" hspace="5" alt="" />' . "\n"
               .             $dspCurDirName . "\n"
               ;
        }
        else
        {
            $curDirLine = '&nbsp;';
        }

        if( $docView == 'files' )
        {
            $docViewToolbar = "<span class=\"claroCmdDisabled\">"
                . "<img src=\"".$imgRepositoryWeb."document.gif\" alt=\"\">\n"
                . get_lang("File list")
                . "</span>\n | ";
        }
        else
        {
            $docViewToolbar = "<a class='claroCmd' href=\"" .  $_SERVER['PHP_SELF']
                 . "?docView=files&amp;cmd=exChDir&amp;file=". $curDirPath . $searchCmdUrl ."\">"
                . "<img src=\"".$imgRepositoryWeb."document.gif\" alt=\"\">\n"
                 . get_lang("File list") ."</a>\n | ";
        }
        if( $docView == 'thumbnails' )
        {
            $docViewToolbar .= "<span class=\"claroCmdDisabled\">"
                . "<img src=\"".$imgRepositoryWeb."image.gif\" alt=\"\">\n"
                . get_lang("Thumbnails")."</span>\n";
        }
        else
        {
            $docViewToolbar .= "<a class='claroCmd' href=\"" .  $_SERVER['PHP_SELF']
                 . "?docView=thumbnails&cwd=". $curDirPath . $searchCmdUrl ."\">"
                 ."<img src=\"".$imgRepositoryWeb."image.gif\" alt=\"\">\n"
                 . get_lang("Thumbnails")."</a>\n";
        }

        echo "<!-- current dir name line -->\n"
                ."<tr>\n"
                ."<th class=\"superHeader\" colspan=\"$colspan\" align=\"left\">\n"
                ."<div style=\"float: right;\">".$docViewToolbar."</div>"
                .$curDirLine
                ."</th>\n"
                ."</tr>\n";


        // --------------------- tool bar --------------------------------------
        // create image title
        $imgTitle = htmlspecialchars($fileName);

        // create image style
        $titleStyle ='title';

        // if image invisible set style to invisible
        if ( isset( $fileList['visibility'] ) &&  $fileList['visibility'][$imgKey] == 'i')
        {
            $titleStyle = 'title invisible';
        } // if invisible

        echo '<tr class="toolbar" valign="top">' . "\n";

        // --------------------- display link to previous image ------------------

        display_link_to_previous_image($imageList, $fileList, $current);

        // --------------------- display title of current image ------------------

        echo '<th class="' . $titleStyle . '">' ."\n"
        .    $imgTitle
        .    '</th>' . "\n"
        ;

        // --------------------- display link to previous image ------------------

        display_link_to_next_image($imageList, $fileList, $current);

        echo '</tr>' . "\n"

        .    '</table>' . "\n"
        ;

        // ---------------------- display comment about  requested image ----------

        if ( isset ( $fileList['comment'] ) && $fileList['comment'][$imgKey])
        {
            echo '<hr />' . "\n"
            .    '<blockquote>' . $fileList['comment'][$imgKey] . '</blockquote>' . "\n"
            ;
        }
        else
        {
            echo '<!-- empty -->' . "\n";
        }// end if comment


        // --------------------- display current image --------------------------

        // system path


        $imgPath = $coursesRepositorySys . $courseDir
            . $file
            ;

        // get image info
        list($width, $height, $type, $attr ) = getimagesize($imgPath);

        // get color depth ! used to get both mime-type and color depth working together
        $depth = get_image_color_depth( $imgPath );

        // display image
        echo "<p><center><a href=\"#\"><img id=\"mainImage\" src=\"" . $doc_url . "\" alt=\""
            . $fileName . "\" /></a></center></p>\n"
            ;

        echo "<p><center><a href=\"" . $doc_url . "\">"
            . get_lang("Direct link to image") .
            "</a></center></p>\n"
            ;

        // display image info
        // -> title and size
        echo "<br /><small>[ Info : " . $imgTitle . " - " . $width
            . "x" . $height
            . " - " .format_file_size($fileList[$imgKey]['size'])
            ;

        // -> color depth
        echo " - " . $depth . "bits";

        // -> mime type
        if( version_compare(phpversion(), '4.3.0', '>') )
        {
            $mime_type = image_type_to_mime_type($type);
            echo " - " . $mime_type ;
        }

        echo " ]</small>\n";
    }

    /*-----------------------------------------------------------------------
                            VIEW THUMBNAILS
      -----------------------------------------------------------------------*/

    else if ($docView == 'thumbnails' ) // thumbnails mode
    {
        // intialize page number
         $page = 1; // if not set, set to first page

         if( isset( $_REQUEST['page'] ) )
        {
            $page = $_REQUEST['page'];
        }

        if( isset( $_REQUEST['offset'] ) )
        {
              $page = get_page_number($_REQUEST['offset']);
        }

        $searchCmdUrl = "";

        if( isset( $_REQUEST['searchPattern'] ) )
        {
            $searchCmdUrl = "&amp;cmd=exSearch&amp;searchPattern=" . urlencode( $_REQUEST['searchPattern'] );
        }

        // compute column width
         $colWidth = round(100 / get_conf('numberOfCols', 3));

        // display table
        echo "\n<table class=\"claroTable\" width=\"100%\">\n";

        // View Mode Bar

        if ($cmd == 'exSearch')
        {
            $curDirLine = get_lang("Search result");
        }
        elseif ($curDirName)
        {
               $curDirLine = "<img src=\"".$imgRepositoryWeb."opendir.gif\" "
                ."align=\"absbottom\" vspace=\"2\" hspace=\"5\" alt=\"\">\n"
                .$dspCurDirName."\n";
        }
        else
        {
            $curDirLine = '&nbsp;';
        }

        if( $docView == 'files' )
        {
            $docViewToolbar = "<span class=\"claroCmdDisabled\">"
                . "<img src=\"".$imgRepositoryWeb."document.gif\" alt=\"\">\n"
                . get_lang("File list")
                . "</span>\n | ";
        }
        else
        {
            $docViewToolbar = "<a class='claroCmd' href=\"" .  $_SERVER['PHP_SELF']
                 . "?docView=files&amp;cmd=exChDir&amp;file=". $curDirPath . $searchCmdUrl ."\">"
                . "<img src=\"".$imgRepositoryWeb."document.gif\" alt=\"\">\n"
                 . get_lang("File list") ."</a>\n | ";
        }
        if( $docView == 'thumbnails' )
        {
            $docViewToolbar .= "<span class=\"claroCmdDisabled\">"
                . "<img src=\"".$imgRepositoryWeb."image.gif\" alt=\"\">\n"
                . get_lang("Thumbnails")."</span>\n";
        }
        else
        {
            $docViewToolbar .= "<a class='claroCmd' href=\"" .  $_SERVER['PHP_SELF']
                 . "?docView=thumbnails&cwd=". $curDirPath . $searchCmdUrl ."\">"
                 ."<img src=\"".$imgRepositoryWeb."image.gif\" alt=\"\">\n"
                 . get_lang("Thumbnails")."</a>\n";
        }

        $colspan = get_conf( 'numberOfCols', 3 );

        echo "<!-- current dir name line -->\n"
                ."<tr>\n"
                ."<th class=\"superHeader\" colspan=\"$colspan\" align=\"left\">\n"
                ."<div style=\"float: right;\">".$docViewToolbar."</div>"
                .$curDirLine
                ."</th>\n"
                ."</tr>\n";

        // toolbar

        echo "<tr class=\"toolbar\">\n";
        echo "<th class=\"prev\" colspan=\"1\" style=\"width: " . $colWidth . "%;\">\n";
        if( !isset($imageList) || count($imageList) == 0)
        {
            $colspan = get_conf( 'numberOfCols', 3 );

            echo "<!-- current dir name line -->\n"
                ."<tr>\n"
                ."<td colspan=\"$colspan\" align=\"left\">\n"
                . get_lang("No image to display")
                ."</td>\n"
                ."</tr>\n";
        }
        else
        {
            if(has_previous_page($imageList, $page))
            {
                // link to previous page
                  echo "<a href=\"".$_SERVER['PHP_SELF']
                    . "?docView=thumbnails&amp;cwd=" . $curDirPath
                    . "&amp;page=" . ($page - 1) . $searchCmdUrl . "\">&lt;&lt;&nbsp;&nbsp;page&nbsp;"
                    . ($page - 1) . "</a>\n"
                    ;
            }
            else
            {
                echo "<!-- empty -->";
            }

            echo "</th>\n";

            echo "<th class=\"title\" colspan=\"" . (get_conf( 'numberOfCols', 3) - 2) . "\">\n"
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
                    . "?docView=thumbnails&amp;cwd=" . $curDirPath
                    . "&amp;page=" . ($page + 1) . $searchCmdUrl . "\">page&nbsp;"
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
                , get_conf('thumbnailWidth'), $colWidth
                , get_conf('numberOfCols'), get_conf('numberOfRows') );

        }

        echo "</table>\n";

    }
    else // current directory line
    {

        /*------------------------------------------------------------------------
                                 CURRENT DIRECTORY LINE
          ------------------------------------------------------------------------*/

        $searchCmdUrl = '';

        if( isset( $_REQUEST['searchPattern'] ) )
        {
            $searchCmdUrl = "&amp;cmd=exSearch&amp;searchPattern=" . urlencode( $_REQUEST['searchPattern'] );
        }

        /* GO TO PARENT DIRECTORY */


        echo "<p>\n";

        if ($curDirName || $cmd == 'exSearch') /* if the $curDirName is empty, we're in the root point
                                                  and we can't go to a parent dir */
        {

            echo "<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=exChDir&amp;file=".$cmdParentDir."\">\n"
                ."<img src=\"".$imgRepositoryWeb."parent.gif\" border=\"0\" alt=\"\">\n"
                .get_lang("Up")
                ."</a>\n";
        }
        else
        {
            echo "<span class=\"claroCmdDisabled\">"
                ."<img src=\"".$imgRepositoryWeb."parentdisabled.gif\" border=\"0\" alt=\"\">\n"
                .get_lang("Up")
                ."</span>\n";
        }


        echo " | "
            ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=rqSearch&amp;cwd=".$cmdCurDirPath."\">\n"
            ."<img src=\"".$imgRepositoryWeb."search.gif\" border=\"0\" alt=\"\">\n"
            .get_lang("Search")
            ."</a>\n";

        if ( trim($searchPattern) != '') $downloadArgument = 'searchPattern='.$searchPattern;
        else                             $downloadArgument = "file=".$cmdCurDirPath;

        echo " | "
            ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=exDownload&amp;".$downloadArgument."\">\n"
            ."<img src=\"".$imgRepositoryWeb."save.gif\" border=\"0\" alt=\"\">\n"
            .get_lang("Download current directory")
            ."</a>\n";


        if ($is_allowedToEdit)
        {
            /* CREATE DIRECTORY - UPLOAD FILE - CREATE HYPERLINK */

            echo " | "
                ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=rqUpload&amp;cwd=".$cmdCurDirPath."\">"
                ."<img src=\"".$imgRepositoryWeb."download.gif\" alt=\"\">"
                .get_lang('Upload file')
                ."</a>\n"
                ." | "
                ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=rqMkDir&amp;cwd=".$cmdCurDirPath."\">"
                ."<img src=\"".$imgRepositoryWeb."folder.gif\" alt=\"\">"
                .get_lang('Create Directory')
                ."</a>\n"
                ."| "
                ."<a class='claroCmd' href=\"".$_SERVER['PHP_SELF']."?cmd=rqMkUrl&amp;cwd=".$cmdCurDirPath."\">"
                ."<img src=\"".$imgRepositoryWeb."link.gif\" alt=\"\">"
                .get_lang('Create hyperlink')
                ."</a>\n"
                ." | "
                ."<a class='claroCmd' href=\"rqmkhtml.php?cmd=rqMkHtml&amp;cwd=".$cmdCurDirPath."\">"
                ."<img src=\"".$imgRepositoryWeb."html.gif\" alt=\"\">"
                .get_lang('Create Document')
                ."</a>\n";
        }

        echo "</p>\n";

        echo claro_disp_document_breadcrumb($curDirPath);

        echo "<table class=\"claroTable emphaseLine\" width=\"100%\">\n";

        /* CURRENT DIRECTORY LINE */

        if ($cmd == 'exSearch')
        {
            $curDirLine = get_lang("Search result");
        }
        elseif ($curDirName)
        {
            $curDirLine = "<img src=\"".$imgRepositoryWeb."opendir.gif\" "
                ."align=\"absbottom\" vspace=\"2\" hspace=\"5\" alt=\"\">\n"
                .$dspCurDirName."\n";
        }
        else
        {
            $curDirLine = '&nbsp;';
        }

        if( $docView == 'files' )
        {
            $docViewToolbar = "<span class=\"claroCmdDisabled\">"
                . "<img src=\"".$imgRepositoryWeb."document.gif\" alt=\"\">\n"
                . get_lang("File list")
                . "</span>\n | ";
        }
        else
        {
            $docViewToolbar = "<a class='claroCmd' href=\"" .  $_SERVER['PHP_SELF']
                 . "?docView=files&amp;cmd=exChDir&amp;file=". $curDirPath . $searchCmdUrl ."\">"
                . "<img src=\"".$imgRepositoryWeb."document.gif\" alt=\"\">\n"
                 . get_lang("File list") ."</a>\n | ";
        }
        if( $docView == 'thumbnails' )
        {
            $docViewToolbar .= "<span class=\"claroCmdDisabled\">"
                . "<img src=\"".$imgRepositoryWeb."image.gif\" alt=\"\">\n"
                . get_lang("Thumbnails")."</span>\n";
        }
        else
        {
            $docViewToolbar .= "<a class='claroCmd' href=\"" .  $_SERVER['PHP_SELF']
                 . "?docView=thumbnails&cwd=". $curDirPath . $searchCmdUrl ."\">"
                 ."<img src=\"".$imgRepositoryWeb."image.gif\" alt=\"\">\n"
                 . get_lang("Thumbnails")."</a>\n";
        }

        echo "<!-- current dir name line -->\n"
            ."<tr>\n"
            ."<th class=\"superHeader\" colspan=\"$colspan\" align=\"left\">\n"
            ."<div style=\"float: right;\">".$docViewToolbar."</div>"
            .$curDirLine
            ."</th>\n"
            ."</tr>\n";

        echo "<tr class=\"headerX\" align=\"center\" valign=\"top\">\n";

        # Patch to avoid E_NOTICE when no files in directory empty
        # FIXME find a more elegant way to solve the problem
        if ( count( $sortUrlList ) > 0 )
        {
            echo '<th><a href="'.$sortUrlList['path'].'">'.get_lang("Name").'</a></th>' . "\n"
            .    '<th><a href="'.$sortUrlList['size'].'">'.get_lang("Size").'</a></th>' . "\n"
            .    '<th><a href="'.$sortUrlList['date'].'">'.get_lang("Date").'</a></th>' . "\n"
            ;
        }
        else
        {
            echo '<th>'.get_lang("Name").'</th>' . "\n"
            .    '<th>'.get_lang("Size").'</th>' . "\n"
            .    '<th>'.get_lang("Date").'</th>' . "\n"
            ;
        }

        if ($is_allowedToEdit)
        {
            echo  "<th>".get_lang("Modify")."</th>\n"
                . "<th>".get_lang("Delete")."</th>\n"
                . "<th>".get_lang("Move")."</th>\n";

                    if ($courseContext)
                    {
                        echo "<th>".get_lang("Visibility")."</th>\n";
                    }
                    elseif ($groupContext)
                    {
                        echo "<th>".get_lang("Publish")."</th>\n";
                    }
        }

        echo "</tr>\n"
            ."<tbody>";

        /*------------------------------------------------------------------------
                                   DISPLAY FILE LIST
          ------------------------------------------------------------------------*/

                //find the recent documents with the notification system

                if (isset($_uid))
                {
                    $date = $claro_notifier->get_notification_date($_uid);

                }

                if (isset($fileList))
        {
            foreach($fileList as $thisFile )
            {
                // Note. We've switched from 'each' to 'foreach', as 'each' seems to
                // poses problems on PHP 4.1, when the array contains only
                // a single element

                $dspFileName = htmlspecialchars( basename($thisFile['path']) );
                $cmdFileName = rawurlencode($thisFile['path']);

                if ( $thisFile['visibility'] == 'i')
                {
                    if ($is_allowedToEdit)
                    {
                        $style='invisible ';
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

                //modify style if the file is recently added since last login

                if (isset($_uid) && $claro_notifier->is_a_notified_ressource($_cid, $date, $_uid, $_gid, $_tid, $thisFile['path']))
                {
                    $classItem=' hot';
                }
                else // otherwise just display its name normally
                {
                    $classItem='';
                }


                if ($thisFile['type'] == A_FILE)
                {
                    $image       = choose_image($thisFile['path']);
                    $size        = format_file_size($thisFile['size']);
                    $date        = format_date($thisFile['date']);

                    if ( strstr($_SERVER['SERVER_SOFTWARE'], 'Apache')
                        && get_conf('secureDocumentDownload') )
                    {
                        // slash argument method - only compatible with Apache
                        $urlFileName = 'goto/index.php'.str_replace('%2F', '/', $cmdFileName);
                    }
                    else
                    {
                        // question mark argument method, for IIS ...
                        $urlFileName = 'goto/?url=' . $cmdFileName;
                    }

                    //$urlFileName = "goto/?doc_url=".urlencode($cmdFileName);
                    //format_url($baseServUrl.$courseDir.$curDirPath."/".$fileName));

                    $target = ( get_conf('openNewWindowForDoc') ? 'target="_blank"' : '');
                }
                elseif ($thisFile['type'] == A_DIRECTORY)
                {
                    $image       = 'folder.gif';
                    $size        = '&nbsp;';
                    $date        = '&nbsp;';
                    $urlFileName = $_SERVER['PHP_SELF'].'?cmd=exChDir&amp;file='.$cmdFileName;

                    $target = '';
                }

                echo "<tr align=\"center\">\n"
                    ."<td align=\"left\">";

                if( is_image( $thisFile['path'] ) )
                {
                    echo "<a class=\"".$style." item".$classItem."\" href=\"". $_SERVER['PHP_SELF'],
                        "?docView=image&amp;file=" . urlencode($thisFile['path']) . "&cwd="
                        . $curDirPath . $searchCmdUrl ."\">";
                }
                else
                {
                        echo "<a class=\"".$style." item".$classItem."\" href=\"".$urlFileName."\" ".$target." >";
                } // end if is_image

                echo "<img src=\"".$imgRepositoryWeb."",
                        $image,"\" border=\"0\" alt=\"\">".$dspFileName."</a>";

                echo "</td>\n"

                    ."<td><small>",$size,"</small></td>\n"
                    ."<td><small>",$date,"</small></td>\n";

                /* NB : Before tracking implementation the url above was simply
                 * "<a href=\"",$urlFileName,"\"",$style,">"
                 */

                if($is_allowedToEdit)
                {
                    /* EDIT COMMAND */

                    echo "<td>"
                        ."<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqEdit&amp;file=".$cmdFileName."\">"
                        ."<img src=\"".$imgRepositoryWeb."edit.gif\" border=\"0\" alt=\"".get_lang("Modify")."\">"
                        ."</a>"
                        ."</td>\n";

                    /* DELETE COMMAND */

                    echo "<td>"
                        ."<a href=\"",$_SERVER['PHP_SELF'],"?cmd=exRm&amp;file=",$cmdFileName,"\" "
                        ."onClick=\"return confirmation('".clean_str_for_javascript($dspFileName)."');\">"
                        ."<img src=\"".$imgRepositoryWeb."delete.gif\" border=\"0\" alt=\"".get_lang("Delete")."\">"
                        ."</a>"
                        ."</td>\n";

                    /* MOVE COMMAND */
                    echo "<td>"
                        ."<a href=\"",$_SERVER['PHP_SELF'],"?cmd=rqMv&amp;file=",$cmdFileName,"\">"
                        ."<img src=\"".$imgRepositoryWeb."move.gif\" border=\"0\" alt=\"".get_lang("Move")."\">"
                        ."</a>"
                        ."</td>\n";


                    echo "<td>";

                    if ($groupContext)
                    {
                        /* PUBLISH COMMAND */

                        if ($thisFile['type'] == A_FILE)
                        {
                            echo "<a href=\"../work/work.php?"
                                ."submitGroupWorkUrl=".$groupDir.$cmdFileName."\">"
                                ."<small>".get_lang("Publish")."</small>"
                                ."</a>";
                        }
                        // else noop
                    }
                    elseif($courseContext)
                    {
                        /* VISIBILITY COMMAND */

                        if ($thisFile['visibility'] == "i")
                        {
                            echo "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=exChVis&amp;file=",$cmdFileName,"&amp;vis=v\">"
                                ."<img src=\"".$imgRepositoryWeb."invisible.gif\" border=\"0\" alt=\"".get_lang("Make visible")."\">"
                                ."</a>";
                        }
                        else
                        {
                            echo "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=exChVis&amp;file=",$cmdFileName,"&amp;vis=i\">"
                                ."<img src=\"".$imgRepositoryWeb."visible.gif\" border=\"0\" alt=\"".get_lang("Make invisible")."\">"
                                ."</a>";
                        }
                    }

                    echo    "</td>\n";
                } // end if($is_allowedToEdit)

                echo    "</tr>\n";

                /* COMMENTS */

                if ( $thisFile['comment'] != '' )
                {
                    $thisFile['comment'] = htmlspecialchars($thisFile['comment']);
                    $thisFile['comment'] = claro_parse_user_text($thisFile['comment']);

                    echo "<tr align=\"left\">\n"
                        ."<td colspan=\"$colspan\">"
                        ."<div class=\"comment\">"
                        .$thisFile['comment']
                        ."</div>"
                        ."</td>\n"
                        ."</tr>\n";
                }
            }               // end each ($fileList)

        }                   // end if ( $fileList)

        echo    "</tbody>",

                "</table>\n";

    } // END ELSE VIEW IMAGE

include $includePath . '/claro_init_footer.inc.php';
