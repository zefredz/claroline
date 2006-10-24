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
 * @package CLMANAGE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @todo use modifiy is use in a cmd request
 */

define('DISP_FILE_LIST', __LINE__);
define('DISP_EDIT_FILE', __LINE__);
define('DISP_VIEW_FILE', __LINE__);

$cidReset=TRUE;

require '../../inc/claro_init_global.inc.php';
require_once $includePath . '/lib/fileManage.lib.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

$controlMsg = array();

//The name of the files
$filenameList = array( get_conf('rootSys') . 'textzone_top.inc.html',
                       get_conf('rootSys') . 'textzone_right.inc.html',
                       $clarolineRepositorySys . '/auth/textzone_inscription.inc.html',
                       get_conf('rootSys') . 'platform/textzone/course_subscription_locked.inc.html',
                       get_conf('rootSys') . 'platform/textzone/course_subscription_locked_by_key.inc.html',
                       get_conf('rootSys') . 'platform/textzone/textzone_inscription_form.inc.html',
                       get_conf('rootSys') . 'platform/textzone/textzone_edit_profile_form.inc.html'
                       );

$display = DISP_FILE_LIST;

// Get command
$validCmdList = array('rqEdit','exEdit','exView');

$cmd = (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$validCmdList)? $_REQUEST['cmd'] : null);

// input Datas
$fileId = (int) isset($_REQUEST['file']) ? $_REQUEST['file'] : null;
if (!in_array($fileId,array_keys($filenameList)))
{
    $fileId=null;
    $controlMsg['error'][] = get_lang('Wrong parameters');
};

//If choose a file to modify
//Modify a file

if ( !is_null($fileId) )
{

    if ( $cmd == 'exEdit' )
    {
        $text = isset($_REQUEST['textContent']) ? trim($_REQUEST['textContent']) : null;

        if( !file_exists($filenameList[$fileId]) )
        {
            claro_mkdir(dirname($filenameList[$fileId]),CLARO_FILE_PERMISSIONS,true);
        }
        $fp = fopen($filenameList[$fileId], 'w+');
        fwrite($fp,$text);

        $controlMsg['info'][] = get_lang('The changes have been carried out correctly')
        .                       ' <br />'
        .                       '<strong>'
        .                       basename($filenameList[$fileId])
        .                       '</strong>'
        ;

        $display = DISP_FILE_LIST;
    }

    if ( $cmd == 'rqEdit' || $cmd = 'exView' )
    {
        $textContent = (file_exists( $filenameList[$fileId] ) ) ? implode("\n", file($filenameList[$fileId]) ) : null;

        if ( $cmd == 'rqEdit' )
        {
            $subtitle = 'Edit : ' . basename($filenameList[$fileId]);
            $display = DISP_EDIT_FILE;
        }
        else
        {
            if ( trim( strip_tags( $textContent,'<img>' ) ) == '' )
            $textContent = '<blockquote>' . "\n"
            .              '<font color="#808080">- <em>' . "\n"
            .              get_lang('No Content') . "\n"
            .              '</em> -</font><br />' . "\n"
            .              '</blockquote>' . "\n"
            ;
            $subtitle = 'Preview : '.basename($filenameList[$fileId]);
            $display = DISP_VIEW_FILE;
        }
    }
}

// DISPLAY

$nameTools = get_lang('Home page text zones');
$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$noQUERY_STRING = true;

include $includePath . '/claro_init_header.inc.php';

//display titles

$titles = array('mainTitle'=>$nameTools);
if (isset($subtitle)) $titles['subTitle'] = $subtitle;

echo claro_html_tool_title($titles)
.    claro_html_msg_list($controlMsg,1)
;

if ( $display == DISP_EDIT_FILE )
{
    echo '<h4>' . basename($filenameList[$fileId]) . '</h4>'
    .    '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n"
    .    '<input type="hidden" name="file" value="' . htmlspecialchars($fileId) . '" />' . "\n"
    .    '<input type="hidden" name="cmd" value="exEdit" />' . "\n"
    .    claro_html_textarea_editor('textContent', $textContent)
    .    '<p>' . "\n"
    .    '<input type="submit" class="claroButton" value="' . get_lang('Ok') . '" />&nbsp; '
    .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel')) . '</p>' . "\n"
    .    '</form>' . "\n"
    ;
}
elseif( $display == DISP_VIEW_FILE )
{
    echo '<br />'
    .    '<h4>' . basename($filenameList[$fileId]) . '</h4>'
    .    $textContent
    .    '<br />'
    ;

}

if( $display==DISP_FILE_LIST || $display==DISP_EDIT_FILE || $display==DISP_VIEW_FILE )
{
   echo '<p>'
   .    get_lang('Here you can modify the content of the text zones displayed on the platform home page.')
   .    '<br />'
   .    get_lang('See below the files you can edit from this tool.')
   .    '</p>' . "\n"
   .    '<table cellspacing="2" cellpadding="2" border="0" class="claroTable emphaseLine">' . "\n"
   .    '<tr class="headerX">' . "\n"
   .    '<th >' . get_lang('Filename') . '</th>' . "\n"
   .    '<th >' . get_lang('Edit') . '</th>' . "\n"
   .    '<th >' . get_lang('Preview') . '</th>' . "\n"
   .    '</tr>' . "\n"
   ;

    foreach($filenameList as $idFile => $filename)
    {
        echo '<tr>' . "\n"
        .    '<td >' . basename($filename) . '</td>' . "\n"
        .    '<td align="center">' . "\n"
        .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEdit&amp;file=' . $idFile . '">'
        .    '<img src="' . $imgRepositoryWeb . 'edit.gif" border="0" alt="' . get_lang('Edit') . '" >' . "\n"
        .    '</a>' . "\n"
        .    '</td>' . "\n"
        .    '<td align="center">' . "\n"
        .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exView&amp;file=' . $idFile . '">'
        .    '<img src="' . $imgRepositoryWeb . 'preview.gif" border="0" alt="' . get_lang('Preview') . '" >' . "\n"
        .    '</a>' . "\n"
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        ;

    }

    echo '</table>' . "\n"
    .    '<br />' . "\n"
    ;

}

include $includePath . '/claro_init_footer.inc.php';
?>