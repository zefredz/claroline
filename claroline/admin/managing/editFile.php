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

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

$do=null;
$controlMsg = array();
//The name of the files
$filenameList = array('textzone_top.inc.html', 'textzone_right.inc.html', 'textzone_inscription.inc.html');
//The path of the files
$filePathList = array(get_conf('rootSys') . $filenameList[0], get_conf('rootSys') . $filenameList[1], $clarolineRepositorySys . '/auth/' . $filenameList[2]);

$display = DISP_FILE_LIST;




// preserve compatibility waiting to replaces all ?modify=1 by ?cmd=modify
if (isset($_REQUEST['modify']))  $_REQUEST['cmd'] ='modify';

// Get command
$validCmdList = array('modify','edit','view');
$cmd = (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$validCmdList)? $_REQUEST['cmd'] : null);

// input Datas
$fileId = (int) isset($_REQUEST['file']) ? $_REQUEST['file'] : null;
if (!in_array($fileId,array_keys($filenameList)))
{
    $fileId=null;
    $controlMsg['error'][] = get_lang('Wrong parameters');
};

$textContent = isset($_REQUEST['textContent']) ? $_REQUEST['textContent'] : null;

//If choose a file to modify
//Modify a file

if ( 'modify' == $cmd )
{
    $text = trim($textContent);
    if ( trim( strip_tags( $text,'<img>' ) ) != '' )
    {
        $fp = fopen($filePathList[$fileId], 'w+');
        fwrite($fp,$text);
    }
    // remove file if empty
    elseif ( file_exists($filePathList[$fileId]) ) unlink($filePathList[$fileId]);

    $controlMsg['info'][] = get_lang('The changes have been carried out correctly')
    .                       ' <br />'
    .                       '<strong>'
    .                       basename($filePathList[$fileId])
    .                       '</strong>'
    ;

    $display = DISP_FILE_LIST;
}

if( !is_null($fileId) )
{
    $textContent = (file_exists( $filePathList[$fileId] ) ) ? implode("\n", file($filePathList[$fileId]) ) : false;

    if ( 'edit' == $cmd )
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

// DISPLAY

$nameTools = get_lang('Home page text zones');
$interbredcrump[]    = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));

include $includePath . '/claro_init_header.inc.php';

//display titles

$titles = array('mainTitle'=>$nameTools);
if (isset($subtitle)) $titles['subTitle'] = $subtitle;

echo claro_html_tool_title($titles)
.    claro_html_msg_list($controlMsg,1)
;

//OUTPUT

if($display==DISP_FILE_LIST
|| $display==DISP_EDIT_FILE || $display==DISP_VIEW_FILE // remove this  whe  display edit  prupose a link to back to list
)
{
?>
<p>
<?php echo get_lang('Here you can modify the content of the text zones displayed on the platform home page.') ?>
<br />
<?php echo get_lang('See below the files you can edit from this tool.') ?>
</p>

<table cellspacing="2" cellpadding="2" border="0" class="claroTable">
<tr class="headerX">
    <th ><?php echo get_lang('Filename') ?></th>
    <th ><?php echo get_lang('Edit') ?></th>
    <th ><?php echo get_lang('Preview') ?></th>
</tr>

    <?php
    foreach($filenameList as $idFile => $fileName)
    {
    ?>
<tr>
    <td ><?php echo basename($fileName); ?></td>
    <td align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?cmd=edit&amp;file=".$idFile; ?>"><img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" alt="<?php echo get_lang('Edit') ?>" ></a></td>
    <td align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?cmd=view&amp;file=".$idFile; ?>"><img src="<?php echo $imgRepositoryWeb ?>preview.gif" border="0" alt="<?php echo get_lang('Preview') ?>" ></a></td>
</tr>
    <?php
    }
    ?>
</table><br />

    <?php
}

if( DISP_EDIT_FILE == $display )
{
    echo '<h4>' . basename($filenameList[$fileId]) . '</h4>'
    .    '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">'
    .    claro_html_textarea_editor('textContent', $textContent)
    .    '<br /><br /> &nbsp;&nbsp;' . "\n"
    .    '<input type="hidden" name="file" value="' . htmlspecialchars($fileId) . '" />' . "\n"
    .    '<input type="submit" class="claroButton" name="modify" value="' . get_lang('Ok') . '" />' . "\n"
    .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel')) . "\n"
    .    '</form>' . "\n"
    ;
}
elseif( DISP_VIEW_FILE == $display)
{
    echo '<br />'
    .    '<h4>' . basename($filenameList[$fileId]) . '</h4>'
    .    $textContent
    .    '<br />'
    ;

}

include $includePath . '/claro_init_footer.inc.php';
?>
