<?php // $Id$
/**
 * CLAROLINE
 *
 * tool for bulk subscribe.
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Guillaume Lederer <guim@claroline.net>
 *
 */

$tlabelReq = 'CLUSR';
require '../inc/claro_init_global.inc.php';

//used libraries
require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';
require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/class.lib.php';
require_once get_path('incRepositorySys') . '/lib/course_user.lib.php' ;
require_once get_path('incRepositorySys') . '/lib/import_csv.lib.php';

include claro_get_conf_repository() . 'user_profile.conf.php';


/*
* See in which context of user we are and check WHO is using the tool,there are 3 possibilities :
* - adding CSV users by the admin tool                                                     (AddType=adminTool)
* - adding CSV users by the admin, but with the class tool                                  (AddType=adminClassTool)
* - adding CSV users by the user tool in a course (in this case, available to teacher too) (AddType=userTool)
*/

$can_import_user_list     = (claro_is_course_manager()
                    && get_conf('is_coursemanager_allowed_to_import_user_list') )
                    || claro_is_platform_admin();


if ( isset($_REQUEST['AddType']) ) $AddType = $_REQUEST['AddType'];
else                               $AddType = 'userTool'; // default access is the user tool

switch ($AddType)
{
    case 'adminTool' :
    case 'adminClassTool' :
        if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
        if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));
        break;

    case 'userTool' :
    default :
        if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

        if ( ! $can_import_user_list ) claro_die(get_lang('Not allowed'));
        $AddType = 'userTool' ;
        break;
}

if ( isset($_REQUEST['class_id']) )
{
    $_SESSION['admin_user_class_id'] = $_REQUEST['class_id'];
}

/*
* DB tables definition
*/

$tbl_mdb_names  = claro_sql_get_main_tbl();
$tbl_user       = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['user_category'];
$tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];

//declare temporary upload directory

$uploadTempDir = 'tmp/';

//deal with session variables to know in which step we are really and avoid doing changes twice

if (isset($_REQUEST['cmd']) && (($_REQUEST['cmd'] == 'exImpSec'  || $_REQUEST['cmd'] == 'exImp') && $_SESSION['claro_CSV_done']) || empty($_REQUEST['cmd'])) // this is to avoid a redo because of a page reload in browser
{
    $cmd = '';
    $display = 'default';
    $_SESSION['claro_CSV_done'] = FALSE;
}

//Set format, fields separator and enclosion used for CSV files

$defaultFormat = 'firstname;lastname;email;phone;username;password;officialCode';

if ( empty($_SESSION['claro_usedFormat']) )
{
    $_SESSION['claro_usedFormat'] = $defaultFormat;
}

if (isset($_REQUEST['loadDefault']) && ($_REQUEST['loadDefault'] =='yes'))
{
    $usedFormat                     = $defaultFormat;
    $_SESSION['claro_usedFormat']   = $defaultFormat;
    $_SESSION['CSV_fieldSeparator'] = ';';
    $_SESSION['CSV_enclosedBy']     = '';
    $dialogBox = get_lang('Format changed');
}

elseif (isset($_REQUEST['usedFormat']))
{
    //check if posted new format is OK

    $field_correct = claro_CSV_format_ok($_REQUEST['usedFormat'], $_REQUEST['fieldSeparator'], $_REQUEST['enclosedBy']);

    if (!$field_correct)
    {
        $dialogBox = get_lang('ERROR: The format you gave is not compatible with Claroline');
    }
    else
    {
        $dialogBox = get_lang('Format changed');
        $_SESSION['claro_usedFormat']   = $_REQUEST['usedFormat'];
        $_SESSION['CSV_fieldSeparator'] = $_REQUEST['fieldSeparator'];
        $_SESSION['CSV_enclosedBy']     = $_REQUEST['enclosedBy'];
    }
}

if (!isset($_SESSION['CSV_fieldSeparator'])) $_SESSION['CSV_fieldSeparator'] = ";";
if (!isset($_SESSION['CSV_enclosedBy']))     $_SESSION['CSV_enclosedBy'] = "\"";

$usedFormat = $_SESSION['claro_usedFormat'];

/**
 *    Execute command section
 */

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null ;

switch ($cmd)
{

    //STEP ONE : FILE UPLOADED, CHECK FOR POTENTIAL ERRORS

    case 'exImp' :

        //see if format is defined in session or in file

        if ($_REQUEST['firstLineFormat']=='YES')
        {
            $useFirstLine = true;
        }
        else
        {
            $fieldSeparator  = $_REQUEST['fieldSeparator'];
            $enclosedBy      = $_REQUEST['enclosedBy'];
            if ($_REQUEST['enclosedBy']=='dbquote')
            {
                $enclosedBy = '"';
            }
            $useFirstLine = false;
        }

        //check if a file was actually posted and that the mimetype is good

        $mimetypes = array(); //array used with supported mimetype for CSV files
        $mimetypes[] = 'text/comma-separated-values';
        $mimetypes[] = 'text/csv';
        $mimetypes[] = 'text/plain';
        $mimetypes[] = 'application/csv';
        $mimetypes[] = 'application/excel';
        $mimetypes[] = 'application/vnd.ms-excel';
        $mimetypes[] = 'application/vnd.msexcel';
        $mimetypes[] = 'text/anytext';

        if ( $_FILES['CSVfile']['size'] == 0 )
        {
            $display   = 'default';
            $dialogBox = get_lang('You must select a file');
        }
        elseif (!in_array($_FILES['CSVfile']['type'],$mimetypes) && (strpos($_FILES['CSVfile']['type'],'text')===FALSE) )
        {
            $display   = 'default';
            $dialogBox = get_lang('You must select a text file');
        }
        else
        {
            //check file content to see potentiel problems to add the users in this campus (errors are saved in session)

            claro_check_campus_CSV_File($uploadTempDir, $useFirstLine, $usedFormat, $_REQUEST['fieldSeparator'], $_REQUEST['enclosedBy']);
            $display = 'stepone';

        }

        break;

        //STEP TWO : ADD CONFIRMED, USERS ARE ADDED

    case 'exImpSec' :

        //build 2D array with users who will be add, avoiding those with error(s).

        $usersNotToAdd = array();
        $newUser = 0;
        $addUserClass = 0;
        $addUserCourse = 0;

        for ($i=0, $size=sizeof($_SESSION['claro_csv_userlist']); $i<$size; $i++)
        {
            $user = $_SESSION['claro_csv_userlist'][$i];
            
            if ( ! ( isset($_SESSION['claro_mail_synthax_error'][$i]) 
                     || isset($_SESSION['claro_officialcode_used_error'][$i])
                     || isset($_SESSION['claro_password_error'][$i])
                     || isset($_SESSION['claro_mail_duplicate_error'][$i])
                     || isset($_SESSION['claro_username_duplicate_error'][$i])
                     || isset($_SESSION['claro_officialcode_duplicate_error'][$i]) 
                    )
               )
               {     
                    if (! (isset($_SESSION['claro_username_used_error'][$i])
                         || isset($_SESSION['claro_mail_used_error'][$i])))
                    {
                        // user must be added only if we encountered exactly no error
                        //set empty fields if needed
                        if (empty($user['phone']))        $user['phone'] = '';
                        if (empty($user['email']))        $user['email'] = '';
                        if (empty($user['officialCode'])) $user['officialCode'] = '';
                        $user_id = user_create($user);
                        if   ($user_id != 0) $newUser++;      
                    }    
                    else
                    {    
                        $criterionList = array('username' => $user['username']);
                        $resultSearch =  user_search($criterionList,null,true,true);
                        $user_id  = $resultSearch[0]['uid'];
                    }
                    
                        // for each use case alos perform thze other needed action :
                    if (isset($user_id) && ($user_id != 0))
                    {
                        switch ($AddType)
                        {
                            case 'adminTool':
                                //its all done in this case
                                break;
            
                            case 'adminClassTool':
                                user_add_to_class($user_id, $_SESSION['admin_user_class_id']);
                                $addUserClass++;
                                break;
            
                            case 'userTool':
                                user_add_to_course($user_id, claro_get_current_course_id(), false, false, false);
                                $addUserCourse++;
                                break;
                          }
                    }
                      else $usersNotToAdd[] = $user;
                      
               } else $usersNotToAdd[] = $user;

        }
        // notify in session that action was done (to prevent double action if user uses back button of browser

        $_SESSION['claro_CSV_done'] = TRUE;

        // select display type

        $display = 'steptwo';

        break;

}

/**
 * Display section
 *
 * PREPARE DISPLAY
 *
 * Deal with interbredcrumps and title variable this depends
 * on the use case of the CSV import(see addType)
 *
 */

switch ($AddType)
{
    case 'adminTool':
        {
            $noQUERY_STRING   = true;
            $nameTools        = get_lang('Add a user list');
            ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') . claro_url_relay_context('?') );
        }   break;

    case 'adminClassTool' :
        {
            $noQUERY_STRING      = true;
            $nameTools           = get_lang('Add a user list in class');
            ClaroBreadCrumbs::getInstance()->prepend( get_lang('Class members'), get_path('rootAdminWeb') . 'admin_class_user.php?class_id='. $_SESSION['admin_user_class_id'] . claro_url_relay_context('&amp;') );
            ClaroBreadCrumbs::getInstance()->prepend( get_lang('Classes'), get_path('rootAdminWeb').'admin_class.php' . claro_url_relay_context('?') );
            ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') . claro_url_relay_context('?') );
        }   break;

    case 'userTool':
        {
            $noQUERY_STRING   = true;
            $nameTools        = get_lang('Add a user list in course');
            ClaroBreadCrumbs::getInstance()->prepend( get_lang('Users'), 'user.php'.claro_url_relay_context('?') );
        }   break;
}


//modify dialogbox if user asked form to change used format

if (isset($_REQUEST['chformat']) && $_REQUEST['chformat']=='yes')
{
    if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']=='dbquote') $dbquote_selected = 'selected="selected"'; else $dbquote_selected = '';
    if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']=='')   $blank_selected   = 'selected="selected"'; else $blank_selected   = '';
    if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']==',')  $coma_selected    = 'selected="selected"'; else $coma_selected    = '';
    if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']=='.')  $dot_selected     = 'selected="selected"'; else $dot_selected     = '';

    if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']==';')  $dot_coma_selected_sep = 'selected="selected"'; else $dot_coma_selected_sep = '';
    if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']==',')  $coma_selected_sep     = 'selected="selected"'; else $coma_selected_sep = '';
    if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']=='')   $blank_selected_sep    = 'selected="selected"'; else $blank_selected_sep = '';

    $compulsory_list = array('firstname','lastname','username','password');

    $dialogBox = get_lang('Modify the format') .' : ' . "\n"
    .            '<br /><br />' . "\n"
    .            get_lang('The fields <em>%field_list</em> are compulsory', array ('%field_list' => implode(', ',$compulsory_list)) )
    .            '<br /><br />'
    .            '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">'
    .            claro_form_relay_context()
    .            '<input type="hidden" name="AddType" value="' . $AddType . '" />' . "\n"
    .            '<input type="text" name="usedFormat" value="' . htmlspecialchars($usedFormat) . '" size="55" />' . "\n"
    .            '<br /><br />' . "\n"
    .            '<label for="fieldSeparator">' .  get_lang('Fields separator used') . ' </label>:'
    .            '<select name="fieldSeparator" id="fieldSeparator">'
    .            '<option value=";" ' . $dot_coma_selected_sep . '>;</option>' . "\n"
    .            '<option value="," ' . $coma_selected_sep . '    >,</option>' . "\n"
    .            '<option value=" " ' . $blank_selected_sep . '   >(' . get_lang('Blank space') . ') </option>' . "\n"
    .            '</select>'
    .' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
    .            '<label for="enclosedBy">'
    .            get_lang('Fields enclosed by') .' :'
    .            '</label>'

    .            '<select name="enclosedBy" id="enclosedBy">'
    .            ' <option value=""        '.$blank_selected.'>' . get_lang('None') . ' </option>'
    .            ' <option value="dbquote" '.$dbquote_selected.'>"</option>'
    .            ' <option value=","       '.$coma_selected.'>,</option>'
    .            ' <option value="."       '.$dot_selected.'>.</option>'
    .            '</select><br />'
    .            '<input type="submit" value="' . get_lang('Ok') . '" />' . "\n"
    .          '</form>'
    ;

}



/**
 * DISPLAY
 */

include get_path('incRepositorySys').'/claro_init_header.inc.php';
echo claro_html_tool_title($nameTools);
if( isset( $dialogBox ) ) echo claro_html_message_box($dialogBox) . '<br />';

switch ( $display )
{

    //DEFAULT DISPLAY : display form to upload

    case 'default' :
        {
            $backButtonUrl = '';
            unset($_SESSION['claro_csv_userlist']);
            if (claro_is_in_a_course())
            {
                $backButtonUrl = Url::Contextualize( get_module_entry_url('CLUSR') );
            }
            elseif (isset($addType) && $addType =='adminClassTool') //tricky fix, the use of addtype should be avoided
            {
                $backButtonUrl = Url::Contextualize( get_path('clarolineRepositoryWeb').'admin/admin_class_user.php?class_id='.$_SESSION['admin_user_class_id'] );
            }
            elseif (claro_is_platform_admin())
            {
                $backButtonUrl = Url::Contextualize( get_path('clarolineRepositoryWeb') . 'admin/' );
            }

            $_SESSION['claro_CSV_done'] = FALSE;

            echo get_lang('You must specify the CSV format used in your file') . "\n"
            .    ':' . "\n"
            .    '<br /><br />' . "\n"
            .    '<form enctype="multipart/form-data"  method="post" action="' . htmlspecialchars( $_SERVER['PHP_SELF'] ) . '">' . "\n"
            .    claro_form_relay_context()
            .    '<input type="radio" name="firstLineFormat" value="YES" id="firstLineFormat_YES" />' . "\n"
            .    ' ' . "\n"
            .    '<label for="firstLineFormat_YES">' . "\n"
            .    get_lang('Use format defined in first line of file') . '</label>' . "\n"
            .    '<br /><br />' . "\n"
            .    '<input type="radio" name="firstLineFormat" value="NO" checked="checked" id="firstLineFormat_NO" />' . "\n"
            .    '<label for="firstLineFormat_NO">' . "\n"
            .    get_lang('Use the following format') . ' : ' . "\n"
            .    '</label>' . "\n"
            .    '<br /><br />' . "\n"
            .    '<b>' . "\n"
            .    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
            .    $usedFormat . "\n"
            .    '</b>' . "\n"
            .    '<br /><br />' . "\n"
            .    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . "\n"
            .    claro_html_cmd_link( htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF']
                                    . '?display=default'
                                    . '&amp;loadDefault=yes'
                                    . '&amp;AddType=' . $AddType ))
                                    , get_lang('Load default format')
                                    ) . "\n"
            .    ' | '
            .    claro_html_cmd_link( htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF']
                                    . '?display=default'
                                    . '&amp;chformat=yes'
                                    . '&amp;AddType=' . $AddType ))
                                    , get_lang('Edit format to use')
                                    ) . "\n"
            .    '<br /><br />' . "\n"
            .    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . "\n"
            ;
            echo '<input type="hidden" name="fieldSeparator" value="';
            if (!empty($_SESSION['CSV_fieldSeparator'])) echo htmlspecialchars( $_SESSION['CSV_fieldSeparator'] );
            else                                         echo ';';
            echo '" />' . "\n"
            .    '<input type="hidden" name="enclosedBy" value="' . htmlspecialchars( $_SESSION['CSV_enclosedBy'] ) . '" />' . "\n"
            .    '<input type="hidden" name="AddType" value="' . htmlspecialchars( $AddType ) . '" />' . "\n"
            .    '<br />' . "\n"
            .    get_lang('CSV file with the user list :')
            .    '<input type="file" name="CSVfile" />' . "\n"
            .    '<br /><br />' . "\n"
            .    '<input type="submit" name="submitCSV" value="' . get_lang('Add user list') . '" />' . "\n"
            .    claro_html_button(htmlspecialchars( $backButtonUrl ),get_lang('Cancel'))  . "\n"
            .    '<input type="hidden" name="cmd" value="exImp" />' . "\n"
            .    '</form>' . "\n"
            ;

        }    break;

        // STEP ONE DISPLAY : display the possible error with uploaded file and ask for continue or cancel

    case 'stepone' :
        {

            if ((!empty($_SESSION['claro_invalid_format_error']) && $_SESSION['claro_invalid_format_error']==true) ||
            !(count($_SESSION['claro_mail_synthax_error'])==0)       ||
            !(count($_SESSION['claro_mail_used_error'])==0)          ||
            !(count($_SESSION['claro_username_used_error'])==0)      ||
            !(count($_SESSION['claro_officialcode_used_error'])==0)  ||
            !(count($_SESSION['claro_password_error'])==0)           ||
            !(count($_SESSION['claro_mail_duplicate_error'])==0)     ||
            !(count($_SESSION['claro_username_duplicate_error'])==0) ||
            !(count($_SESSION['claro_officialcode_duplicate_error'])==0))
            {
                echo '<b>' . get_lang('The following errors were found ') . ' :</b><br /><br />' . "\n";

                //display errors encountered while trying to add users

                claro_disp_CSV_error_backlog();

                $noerror = FALSE;
            }
            else
            {
                echo get_lang('No error in file found.')."<br />";

                $noerror = TRUE;
            }


            if (!(isset($_SESSION['claro_invalid_format_error'])) || ($_SESSION['claro_invalid_format_error'] == false))
            {
                echo '<br />'
                .    get_lang('Do you want to continue?')
                .    '<br />'
                ;
                if (!$noerror)
                {
                    echo get_lang('The user will be created only if all informations are correct.') . '<br />';
                    switch ($AddType)
                    {
                        case 'adminClassTool': 
                            echo  get_lang('If the user is existing in the platform, he will be added to the class only if his firstname, 
                                        lastname and username are similar.') . '<br />';
                            break;
                        case 'userTool': 
                            echo get_lang('If the user is existing in the platform, he will be added to the course only if his firstname, 
                                        lastname and username are similar.') . '<br />';
                            break;
                    }
                        
                }
                echo '<br />'
                .    '<form method="post" action="' . htmlspecialchars(Url::Contextualize( $_SERVER['PHP_SELF'] . '?cmd=exImpSec' )) . '">' . "\n"
                .    '<input type="hidden" name="AddType" value="' . $AddType . '" />'
                .    '<input type="submit" value="' . get_lang('Continue') .'" />' . "\n"
                .    claro_html_button( htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'] . '?AddType=' . $AddType )), get_lang('Cancel'))
                .   '</form>' . "\n";

            }
            else
            {
                echo '<br />' . claro_html_button(htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'])), get_lang('Cancel')) . '<br />';
            }
        } break;

        // STEP TWO DISPLAY : display what happened, confirm users added (LOG)

    case 'steptwo' :

        echo '<b>' . get_lang('%nb_user not to add',array( '%nb_user' => sizeof($usersNotToAdd) ) ) . '</b> <br /><br />';



        //display messages concerning actions done to new users...

        switch ($AddType)
        {
            case 'adminTool':
                echo get_lang('%newUser users added to the campus',array('%newUser'=>$newUser)). "<br />\n";
                break;

            case 'adminClassTool':
                echo get_lang('%newUser users added to the campus',array('%newUser'=>$newUser)). "<br />\n";
                echo get_lang('%addUserClass users added to the class',array('%addUserClass'=>$addUserClass)). "<br />\n";
                break;

            case 'userTool':
                echo get_lang('%newUser users added to the campus',array('%newUser'=>$newUser)). "<br />\n";
                echo get_lang('%addUserCourse users added to the course',array('%addUserCourse'=>$addUserCourse)). "<br />\n";
                break;
        }
        
        foreach ($usersNotToAdd as $user)
        {
                echo get_lang('%firstname %lastname has not been added !', array('%firstname'=>$user['firstname'],
                    '%lastname'=>$user['lastname']) ). "<br />\n";
        }        

        // display back link at the end of the log

        switch ($AddType)
        {
            case 'adminTool' :
                {
                    echo '<br />'
                    .    '<a href="'. htmlspecialchars( Url::Contextualize( get_path('clarolineRepositoryWeb') . 'admin/adminusers.php' )) . '">&gt;&gt; '
                    .    get_lang('See user list')
                    .    '</a>'
                    ;
                }   break;

            case 'adminClassTool' :
                {
                    echo '<br />'
                    .    '<a href="'.htmlspecialchars( Url::Contextualize( get_path('clarolineRepositoryWeb') . 'admin/admin_class.php' )) . '">&gt;&gt; '
                    .    get_lang('Back to class list')
                    .    '</a>'
                    ;
                }   break;

            case 'userTool' :
                {
                    echo '<br />'
                    .    '<a href="'.htmlspecialchars( Url::Contextualize( get_module_entry_url('CLUSR') )) . '">&lt;&lt; ' . get_lang('Back to user list') . '</a>'
                    ;
                }   break;
        }
        break;
}

//footer
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>