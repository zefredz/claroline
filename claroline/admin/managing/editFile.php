<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package CLMANAGE
 *
 * @author Claro Team <cvs@claroline.net>
 */

define('DISP_FILE_LIST', __LINE__);
define('DISP_EDIT_FILE', __LINE__);
define('DISP_VIEW_FILE', __LINE__);


$cidReset=TRUE;
require '../../inc/claro_init_global.inc.php';

$is_allowedToAdmin     = $is_platformAdmin;
if ( ! $is_allowedToAdmin ) claro_disp_auth_form();

include($includePath . '/lib/debug.lib.inc.php');

$controlMsg = array();
//The name of the files
$filenameList = array('textzone_top.inc.html', 'textzone_right.inc.html');
//The path of the files
$filePathList = array($rootSys . $filenameList[0], $rootSys . $filenameList[1]);

$display = DISP_FILE_LIST;
//If choose a file to modify
//Modify a file
if( isset($_REQUEST['modify']) )
{
    $text = $_REQUEST['textContent'];

    if (get_magic_quotes_gpc())
    {
        $text = stripslashes($text);
    }

    $fp = fopen($filePathList[$_REQUEST['file']], 'w+');
    fwrite($fp,$text);

    $controlMsg['info'][] = $lang_EditFile_ModifyOk
    .                       ' <br>'
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
        if ( trim( strip_tags( $textContent ) ) == '' )
        $textContent = '<blockquote>'
        .              '<font color="#808080">- <em>'
        .              $langNoContent
        .              '</em> -</font><br></blockquote>'
        ;
        $subtitle = 'Preview : '.basename($filenameList[$_REQUEST['file']]);
        $display = DISP_VIEW_FILE;
    }
}

// DISPLAY

$nameTools = $langHomePageTextZone;
$interbredcrump[]    = array ('url' => $rootAdminWeb, 'name' => $langAdministration);

include($includePath . '/claro_init_header.inc.php');

//display titles

$titles = array('mainTitle'=>$nameTools);
if (isset($subtitle)) $titles['subTitle'] = $subtitle;

claro_disp_tool_title($titles);

if ( count($controlMsg) > 0 )
{
    claro_disp_msg_arr($controlMsg);
}

//OUTPUT

if($display==DISP_FILE_LIST
|| $display==DISP_EDIT_FILE || $display==DISP_VIEW_FILE // remove this  whe  display edit  prupose a link to back to list
)
{
    ?>
        <p>
        <?php echo $langHereyoucanmodifythecontentofthetextzonesdisplayedontheplatformhomepage ?>
        <br>
        <?php echo $langSeebelowthefilesyoucaneditfromthistool ?>
        </p>

        <table cellspacing="2" cellpadding="2" border="0" class="claroTable">
<tr class="headerX">
    <th ><?php echo $langFileName ?></th>
    <th ><?php echo $langEdit ?></th>
    <th ><?php echo $langPreview ?></th>
</tr>

    <?php
    foreach($filenameList as $idFile => $fileName)
    {
    ?>
<tr>
    <td ><TT><?php echo basename($fileName); ?></TT> </td>
    <td align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?cmd=edit&amp;file=".$idFile; ?>"><img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" alt="<?php echo $langEdit ?>" ></a></td>
    <td align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?cmd=view&amp;file=".$idFile; ?>"><img src="<?php echo $imgRepositoryWeb ?>preview.gif" border="0" alt="<?php echo $langPreview ?>" ></a></td>
</tr>
    <?php
    }
    ?>
        </table><br>
        
    <?php
}

if( $display == DISP_EDIT_FILE )
{
    echo '<h4>' . basename($filenameList[$_REQUEST['file']]) . '</h4>';

    ?>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<?php
claro_disp_html_area('textContent', $textContent);
?>
            <br><br> &nbsp;&nbsp;
            <input type="hidden" name="file" value="<?php echo $_REQUEST['file']; ?>">
            <input type="submit" class="claroButton" name="modify" value=" <?php echo $langOk; ?>">
            <?php   echo claro_disp_button($_SERVER['PHP_SELF'], 'Cancel'); ?>
        </form>
    <?php
}
elseif( $display == DISP_VIEW_FILE )
{
    echo '<br>'
    .    '<h4>' . basename($filenameList[$_REQUEST['file']]) . '</h4>'
    .    $textContent
    .    '<br>'
    ;

}

include($includePath . '/claro_init_footer.inc.php');
?>