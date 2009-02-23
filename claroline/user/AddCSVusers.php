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
 * @author Dimitri Rambout <dimitri.rambout@uclouvain.be>
 *
 */

$tlabelReq = 'CLUSR';
require '../inc/claro_init_global.inc.php';

//used libraries
require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';
require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/class.lib.php';
require_once get_path('incRepositorySys') . '/lib/course_user.lib.php' ;
require_once get_path('incRepositorySys') . '/lib/group.lib.inc.php' ;
//require_once get_path('incRepositorySys') . '/lib/import_csv.lib.php';

require_once './csv.class.php';

include claro_get_conf_repository() . 'user_profile.conf.php';

if ( !$_cid || !$is_courseAllowed ) claro_disp_auth_form(true);

$is_allowedToEdit = claro_is_allowed_to_edit();

// courseadmin reserved page
if( !($is_allowedToEdit || $is_platformAdmin) )
{
    header("Location: ../user.php");
    exit();
}

$acceptedCmdList = array( 'rqCSV', 'rqChangeFormat', 'exChangeFormat', 'rqLoadDefautFormat', 'exLoadDefaultFormat');

if( isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList) )   $cmd = $_REQUEST['cmd'];
else                                                                            $cmd = null;

if( isset($_REQUEST['step']) )   $step = (int) $_REQUEST['step'];
else                             $step = 0;

$nameTools        = get_lang('Add a user list in course');
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Users'), get_module_url('CLUSR').'/user.php' );

$dialogBox = new DialogBox();

$defaultFormat = 'userId,lastname,firstname,username,email,officialCode,groupId,groupName';
$AddType = 'userTool';

if ( empty($_SESSION['claro_usedFormat']) )
{
    $_SESSION['claro_usedFormat'] = $defaultFormat;
}

if( empty( $_SESSION['CSV_fieldSeparator'] ) )
{
    $_SESSION['CSV_fieldSeparator'] = ',';
}

if( empty ( $_SESSION['CSV_enclosedBy'] ) )
{
    $_SESSION['CSV_enclosedBy'] = '"';
}

$usedFormat = $_SESSION['claro_usedFormat'];
switch( $cmd )
{
    case 'rqChangeFormat' :
    {
        if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']=='dbquote') $dbquote_selected = 'selected="selected"'; else $dbquote_selected = '';
        if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']=='')   $blank_selected   = 'selected="selected"'; else $blank_selected   = '';
        if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']==',')  $coma_selected    = 'selected="selected"'; else $coma_selected    = '';
        if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']=='.')  $dot_selected     = 'selected="selected"'; else $dot_selected     = '';
    
        if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']==';')  $dot_coma_selected_sep = 'selected="selected"'; else $dot_coma_selected_sep = '';
        if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']==',')  $coma_selected_sep     = 'selected="selected"'; else $coma_selected_sep = '';
        if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']=='')   $blank_selected_sep    = 'selected="selected"'; else $blank_selected_sep = '';
        
        $compulsory_list = array('firstname','lastname','username');

        $chFormatForm = get_lang('Modify the format') .' : ' . '<br /><br />' . "\n"
        .   get_lang('The fields <em>%field_list</em> are compulsory', array ('%field_list' => implode(', ',$compulsory_list)) ) . '<br /><br />' . "\n"
        .   '<form name="chFormat" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?&cmd=exChangeFormat" >' . "\n"
        .   '<input type="text" name="usedFormat" value="' . htmlspecialchars($usedFormat) . '" size="55" />' . "\n"
        .   '<br /><br />' . "\n"
        .   '<label for="fieldSeparator">' .  get_lang('Fields separator used') . ' </label> : '
        .   '<select name="fieldSeparator" id="fieldSeparator">' . "\n"
        .   ' <option value="," '.$coma_selected_sep.' >,</option>' . "\n"
        .   ' <option value=";" '.$dot_coma_selected_sep.' >;</option>' . "\n"
        .   ' <option value=" " '.$blank_selected_sep.'>(' . get_lang('Blank space') . ') </option>' . "\n"
        .   '</select>' . "\n"
        .   ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
        .   '<label for="enclosedBy">'
        .   get_lang('Fields enclosed by') .' : '
        .   '</label>' . "\n"
        .   '<select name="enclosedBy" id="enclosedBy">'
        .   ' <option value="dbquote" '.$dbquote_selected.' >"</option>' . "\n"
        .   ' <option value="," '.$coma_selected.' >,</option>' . "\n"
        .   ' <option value="." '.$dot_selected.' >.</option>' . "\n"
        .   ' <option value="" '.$blank_selected.' >' . get_lang('None') . ' </option>' . "\n"
        .   '</select><br />' . "\n"
        .   '<input type="submit" value="' . get_lang('Ok') . '" />' . "\n"
        .   '</form>'; 
        
        $dialogBox->form( $chFormatForm );
    }
    break;
    
    case 'exChangeFormat' :
    {
        if( !( isset($_REQUEST['usedFormat']) && isset($_REQUEST['fieldSeparator']) && isset($_REQUEST['enclosedBy']) ) )
        {
            $dialogBox->error( get_lang( 'Unable to load the selected format' ) );
            break;
        }
        
        $csv = new csv();
        
        if( ! $csv->format_ok($_REQUEST['usedFormat'], $_REQUEST['fieldSeparator'], $_REQUEST['enclosedBy']) )
        {
            $dialogBox->error( get_lang('ERROR: The format you gave is not compatible with Claroline') );
            break;
        }
        
        $dialogBox->success( get_lang('Format changed') );
        $_SESSION['claro_usedFormat']   = $_REQUEST['usedFormat'];
        $_SESSION['CSV_fieldSeparator'] = $_REQUEST['fieldSeparator'];
        $_SESSION['CSV_enclosedBy']     = $_REQUEST['enclosedBy'];
        
    }
    break;
}
$usedFormat = $_SESSION['claro_usedFormat'];
// Content
$content = '';
$out = '';

$backButtonUrl = Url::Contextualize( get_path('clarolineRepositoryWeb') . 'user/' );

$content_default = get_lang('You must specify the CSV format used in your file') . ':' . "\n"
.   '<br /><br />' . "\n"
.   '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" enctype="multipart/form-data"  >' . "\n"
.   '<input type="hidden" name="step" value="1" />' . "\n"
.   '<input type="radio" name="firstLineFormat" value="YES" id="firstLineFormat_YES" /> '
.   '<label for="firstLineFormat_YES">' . get_lang('Use format defined in first line of file') . '</label>' . "\n"
.   '<br /><br />' . "\n"
.   '<input type="radio" name="firstLineFormat" value="NO" checked="checked" id="firstLineFormat_NO" />' . "\n"
.   '<label for="firstLineFormat_NO">' . get_lang('Use the following format') . ' : ' . '</label>' . "\n"
.   '<br /><br />' . "\n"
.   '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
.   '<span style="font-weight: bold;">' . $usedFormat . '</span><br /><br />' . "\n"
.   '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . "\n"
.   claro_html_cmd_link( htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF']
                        . '?display=default'
                        . '&amp;cmd=rqLoadDefaultFormat'
                        . '&amp;AddType=' . $AddType ))
                        , get_lang('Load default format')
                        ) . "\n"
.   ' | '
.   claro_html_cmd_link( htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF']
                        . '?display=default'
                        . '&amp;cmd=rqChangeFormat'
                        . '&amp;AddType=' . $AddType ))
                        , get_lang('Edit format to use')
                        ) . "\n"
.   '<br /><br />' . "\n"
.   get_lang('CSV file with the user list :') . "\n"
.   '<input type="file" name="CSVfile" />' . "\n"
.   '<br /><br />' . "\n" . "\n"
.   '<input type="submit" name="submitCSV" value="' . get_lang('Add user list') . '" />' . "\n"
.   claro_html_button(htmlspecialchars( $backButtonUrl ),get_lang('Cancel'))  . "\n"
.   '</form>' . "\n";

switch( $step )
{
    case 2 : // Import users in course
    {
        $csvImport = new csvImport( $_SESSION['CSV_fieldSeparator'], $_SESSION['CSV_enclosedBy'] = '"');
            
        if( !( isset($_SESSION['_csvImport']) && isset($_SESSION['_csvUsableArray'] ) ) )
        {
            $dialogBox->error('Unable to read the content of the CSV');
            $content .= $content_default;
        }
        else
        {
            $csvContent = $_SESSION['_csvImport'];
            $csvImport->setCSVContent( $csvContent );
            
            $errors = $csvImport->importUsersInCourse( $_cid );
            
            if( !empty($errors) )
            {
                $_errors = "";
                foreach($errors as $error)
                {
                    $_errors .= $error . '<br />' . "\n";
                }
                $dialogBox->error($_errors . get_lang('Unable to import selected users'));
            }
            else
            {
                $dialogBox->success( 'Users imported successfully');
            }
            
        }
    }
    break;
    case 1 : // check csv data & display the selection
    {
        $mimetypes = array(); //array used with supported mimetype for CSV files
        $mimetypes[] = 'text/comma-separated-values';
        $mimetypes[] = 'text/csv';
        $mimetypes[] = 'text/plain';
        $mimetypes[] = 'application/csv';
        $mimetypes[] = 'application/excel';
        $mimetypes[] = 'application/vnd.ms-excel';
        $mimetypes[] = 'application/vnd.msexcel';
        $mimetypes[] = 'text/anytext';
        
        if( !isset( $_FILES['CSVfile'] ) || empty($_FILES['CSVfile']['name']) || $_FILES['CSVfile']['size'] == 0 )
        {
            $dialogBox->error(get_lang('You must select a file'));
            
            $content .= $content_default;
        }
        elseif( !in_array( $_FILES['CSVfile']['type'], $mimetypes) )
        {
            $dialogBox->error(get_lang('CSV file is in the bad format'));
            
            $content .= $content_default;
        }
        else
        {
            $csvImport = new csvImport( $_SESSION['CSV_fieldSeparator'], $_SESSION['CSV_enclosedBy'] = '"');
            if( ! $csvImport->load( $_FILES['CSVfile']['tmp_name'] ) )
            {
                $dialogBox->error(get_lang('Unable to read the content of the CSV'));
            }
            else
            {
                $csvContent = $csvImport->getCSVContent();
                $_SESSION['_csvImport'] = $csvContent;
                
                $firstLineFormat = true;
                if( isset( $_REQUEST['firstLineFormat']) )
                {
                    switch( $_REQUEST['firstLineFormat'] )
                    {
                        case 'NO' : $firstLineFormat = false; break;
                    }
                }
                
                if( !$firstLineFormat )
                {
                    $keys = split( $_SESSION['CSV_fieldSeparator'], $usedFormat);
                    $firstLine = $usedFormat;
                }
                else
                {
                    $keys = null;
                    $firstLine = $csvImport->getFirstLine();
                }
                
                $csvUseableArray = $csvImport->createUsableArray( $csvImport->getCSVContent(), $firstLineFormat, $keys) ;
                $_SESSION['_csvUsableArray'] = $csvUseableArray;
                
                $errors = $csvImport->checkFieldsErrors( $csvUseableArray );
                
                if( is_null($keys) && $firstLineFormat )
                {
                    $keys = $csvContent[0];
                    unset($csvContent[0]);
                }
                
                if( ! $csvImport->format_ok( $firstLine, $_SESSION['CSV_fieldSeparator'], $_SESSION['CSV_enclosedBy']) )
                {
                    $dialogBox->error( get_lang('ERROR: The format you gave is not compatible with Claroline') );
                    break;
                }
                
                if( !count($csvContent) )
                {
                    $dialogBox->error(get_lang('No data to import'));
                }
                else
                {
                    if( count($errors) )
                    {
                        $errorsDisplayed = '';
                        foreach( $errors as $error )
                        {
                            $errorsDisplayed .= $error;
                        }
                        $dialogBox->error($errorsDisplayed);
                    }
                    
                    $content .= '<br />' . get_lang('Select users you want to import in the course') . '<br />'
                    .   (count($errors) ? get_lang('Errors can be ignored to force the import') : '') . "\n" . '<br />' . "\n";
                    
                    $content .= '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" >' . "\n"                    
                    .   '<input type="hidden" name="step" value="2" />' . "\n"                    
                    // options
                    // TODO: check if user can create users
                    //.   get_lang('Create new users') . '<input type="checkbox" value="1" name="newUsers" />'
                    // Data
                    .   '<table class="claroTable emphaseLine" width="100%" cellpadding="2" cellspacing="1"  border="0">' . "\n"
                    .   '<thead>' . "\n"
                    .   '<tr class="headerX">' . "\n"
                    .   '<th><input type="checkbox" name="checkAll" id="checkAll" onchange="changeAllCheckbox();" checked="checked" /></th>' . "\n"
                    ;
                    foreach($keys as $key => $value)
                    {
                        $content .= '<th>' . $value . '</th>' . "\n";
                    }
                    //$content .= '<th>Errors</th>' . "\n";
                    $content .= '</tr>' . "\n"
                    .   '</thead>' . "\n";
                    
                    foreach( $csvContent as $key => $data)
                    {
                        $content .= '<tr>' . "\n"
                        .   '<td style="text-align: center;"><input type="checkbox" name="users[]" value="'. $key .'" class="checkAll" checked="checked"  /></td>' . "\n";
                        ;                        
                        foreach( $data as $d )
                        {
                            $content .= '<td>' . (!empty($d) ? $d : '&nbsp;') . '</td>' . "\n";
                        }
                        //$content .= '<td></td>' . "\n";
                        $content .= '</tr>' . "\n";                        
                    }
                    
                    $content .=   '</table>' . "\n"
                    .    '<input type="submit" name="submitCSV" value="' . get_lang('Add selected users') . '" />' . "\n"
                    .    claro_html_button(htmlspecialchars( $backButtonUrl ),get_lang('Cancel'))  . "\n"
                    .   '</form>' . "\n"
                    ;
                }
                
            }            
        }
    }
    break;
    default :
    {   
        if( isset($_SESSION['_csvImport']) )
        {
            unset($_SESSION['_csvImport']);
        }
        
        if( isset($_SESSION['_csvUsableArray']) )
        {
            unset($_SESSION['_csvUsableArray']);
        }
        $content .= $content_default; 
    }
}

$out .= claro_html_tool_title($nameTools);
$out .= $dialogBox->render();
$out .= $content;

$out .= '<script type="text/javascript">'
.   'function changeAllCheckbox()
    {
        if( $("#checkAll").attr("checked") )
        {
            $(".checkAll").attr("checked", true);
        }
        else
        {
            $(".checkAll").attr("checked", false);
        }
    }
    '
.   '</script>';

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>