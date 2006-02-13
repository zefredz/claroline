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

$controlMsg = array();
//The name of the files
$filenameList = array('textzone_top.inc.html', 'textzone_right.inc.html', 'textzone_inscription.inc.html');
//The path of the files
$filePathList = array($rootSys . $filenameList[0], $rootSys . $filenameList[1], $clarolineRepositorySys . '/auth/' . $filenameList[2]);

$display = DISP_FILE_LIST;
//If choose a file to modify
//Modify a file

if ( isset($_REQUEST['modify']) )
{
    $text = trim($_REQUEST['textContent']);
    if ( trim( strip_tags( $text,'<img>' ) ) != '' )
    {
        $fp = fopen($filePathList[$_REQUEST['file']], 'w+');
        fwrite($fp,$text);
    }
    else  // remove file if empty
    {
        if ( file_exists($filePathList[$_REQUEST['file']]) )
        {
            unlink($filePathList[$_REQUEST['file']]);
        }
    }
    $controlMsg['info'][] = get_lang('The changes have been carried out correctly')
    .                       ' <br />'
    .                       '<strong>'
    .                       basename($filePathList[$_REQUEST['file']])
    .                       '</strong>'
    ;

    $display = DISP_FILE_LIST;
}

if( isset($_REQUEST['file']) )
{
    if (file_exists( $filePathList[$_REQUEST['file']] ) )
    {
        $textContent = implode("\n", file($filePathList[$_REQUEST['file']]) );
    }
    else
    {
        $textContent = false;
    }

    if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'edit'  )
    {
        $subtitle = 'Edit : ' . basename($filenameList[$_REQUEST["file"]]);
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
        $subtitle = 'Preview : '.basename($filenameList[$_REQUEST['file']]);
        $display = DISP_VIEW_FILE;
    }
}

// DISPLAY

$nameTools = get_lang('HomePageTextZone');
$interbredcrump[]    = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));

include $includePath . '/claro_init_header.inc.php';

//display titles

$titles = array('mainTitle'=>$nameTools);
if (isset($subtitle)) $titles['subTitle'] = $subtitle;

echo claro_disp_tool_title($titles)
.    claro_html::msg_list($controlMsg,1)
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
    <th ><?php echo get_lang('FileName') ?></th>
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

if( $display == DISP_EDIT_FILE )
{
    echo '<h4>' . basename($filenameList[$_REQUEST['file']]) . '</h4>'
    .    '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">'
    .    claro_disp_html_area('textContent', $textContent)
    .    '<br /><br /> &nbsp;&nbsp;' . "\n"
    .    '<input type="hidden" name="file" value="' . htmlspecialchars($_REQUEST['file']) . '" />' . "\n"
    .    '<input type="submit" class="claroButton" name="modify" value="' . get_lang('Ok') . '" />' . "\n"
    .    claro_html::cmd_button($_SERVER['PHP_SELF'], get_lang('Cancel')) . "\n"
    .    '</form>' . "\n"
    ;
}
elseif( $display == DISP_VIEW_FILE )
{
    echo '<br />'
    .    '<h4>' . basename($filenameList[$_REQUEST['file']]) . '</h4>'
    .    $textContent
    .    '<br />'
    ;

}

include $includePath . '/claro_init_footer.inc.php';
?>
