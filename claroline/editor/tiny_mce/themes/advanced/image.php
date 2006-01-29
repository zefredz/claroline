<?php // $Id$
/**
 * CLAROLINE
 *
 * 
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package EDITOR
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sébastien Piraux <pir@cerdecam.be>
 *
 */

include_once '../../../../inc/claro_init_global.inc.php';

// initialiase variables

$imgUrl = '';

if ( isset($_FILES['imgFile']) && $_cid && $is_courseAdmin )
{
    include_once $includePath . '/lib/fileUpload.lib.php';
    include_once $includePath . '/lib/fileManage.lib.php';

    $imgFile = $_FILES['imgFile'];

    if ($_cid)
    {
        $uploadDirPathWeb = $coursesRepositoryWeb.$_course['path'].'/document/images';
        $uploadDirPathSys = $coursesRepositorySys.$_course['path'].'/document/images';
    }

    if ( ! file_exists($uploadDirPathSys) )
    {
        claro_mkdir($uploadDirPathSys);
    }

    if( is_dir($uploadDirPathSys) && strstr($imgFile['type'], 'image') )
    {
        $imgFile['name'] = replace_dangerous_char($imgFile['name'],'strict');
        $imgFile['name'] = get_secure_file_name($imgFile['name']);

        if ( move_uploaded_file($imgFile['tmp_name'],
                                $uploadDirPathSys . '/' . $imgFile['name'] ) )
        {
            $imgUrl = $uploadDirPathWeb . '/' . $imgFile['name'];
        }
        // else $imgUrl = '';
    }
    // else $imgUrl = '';
}

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{$lang_insert_image_title}</title>
    <script language="javascript" type="text/javascript" src="../../tiny_mce_popup.js"></script>
    <script language="javascript" type="text/javascript" src="../../utils/form_utils.js"></script>
    <script language="javascript" type="text/javascript" src="jscripts/image.js"></script>

</head>
<body onload="<?php echo (empty($imgUrl))?'init();"':'getImageData()'; ?>" style="display: none">

<p class="title">{$lang_insert_image_title}</p>

<form onsubmit="insertImage();return false;" name="imgDetails">
  <table border="0" cellpadding="0" cellspacing="0" width="200">
    <tr>
      <td align="center" valign="middle"><table border="0" cellpadding="4" cellspacing="0">
          <tr>
            <td nowrap="nowrap">{$lang_insert_image_src}:</td>
            <td><table border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td><input name="src" type="text" id="src" value="<?php echo htmlspecialchars($imgUrl)?>" style="width: 200px" onchange="getImageData();" /></td>
                  <td><script language="javascript" type="text/javascript">renderBrowser('srcbrowser','src','image','theme_advanced_image');</script></td>
                </tr>
              </table></td>
          </tr>
          <!-- Image list -->
          <script language="javascript">
            if (typeof(tinyMCEImageList) != "undefined" && tinyMCEImageList.length > 0) {
                var html = "";

                html += '<tr><td>{$lang_image_list}:</td>';
                html += '<td><select name="image_list" style="width: 200px" onchange="this.form.src.value=this.options[this.selectedIndex].value;resetImageData();getImageData();">';
                html += '<option value="">---</option>';

                for (var i=0; i<tinyMCEImageList.length; i++)
                    html += '<option value="' + tinyMCEImageList[i][1] + '">' + tinyMCEImageList[i][0] + '</option>';

                html += '</select></td></tr>';

                document.write(html);
            }
          </script>
          <!-- /Image list -->
          <tr>
            <td nowrap="nowrap">{$lang_insert_image_alt}:</td>
            <td><input name="alt" type="text" id="alt" value="" style="width: 200px" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap">{$lang_insert_image_align}:</td>
            <td><select name="align">
                <option value="">{$lang_insert_image_align_default}</option>
                <option value="baseline">{$lang_insert_image_align_baseline}</option>
                <option value="top">{$lang_insert_image_align_top}</option>
                <option value="middle">{$lang_insert_image_align_middle}</option>
                <option value="bottom">{$lang_insert_image_align_bottom}</option>
                <option value="texttop">{$lang_insert_image_align_texttop}</option>
                <option value="absmiddle">{$lang_insert_image_align_absmiddle}</option>
                <option value="absbottom">{$lang_insert_image_align_absbottom}</option>
                <option value="left">{$lang_insert_image_align_left}</option>
                <option value="right">{$lang_insert_image_align_right}</option>
              </select></td>
          </tr>
          <tr>
            <td nowrap="nowrap">{$lang_insert_image_dimensions}:</td>
            <td><input name="width" type="text" id="width" value="" size="4" maxlength="4" />
              x
              <input name="height" type="text" id="height" value="" size="4" maxlength="4" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap">{$lang_insert_image_border}:</td>
            <td><input name="border" type="text" id="border" value="" size="3" maxlength="3" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap">{$lang_insert_image_vspace}:</td>
            <td><input name="vspace" type="text" id="vspace" value="" size="3" maxlength="3" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap">{$lang_insert_image_hspace}:</td>
            <td><input name="hspace" type="text" id="hspace" value="" size="3" maxlength="3" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap"><input type="button" id="insert" name="insert" value="{$lang_insert}" onclick="insertImage();" />
            </td>
            <td align="right"><input type="button" id="cancel" name="cancel" value="{$lang_cancel}" onclick="tinyMCEPopup.close();" /></td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>
<?php
if ($_cid && $is_courseAdmin)
{
?>
<form name="imgFileUpload" method="post" action="image.php" enctype="multipart/form-data">
<fieldset>
<legend>Add an image file</legend>
    <input type="hidden" name="sent" value="1" />
    <input type="file" name="imgFile" size="25" value="" />
    <input type="submit" name="upload" value="Upload" />
</fieldset>
</form>
<?php
} // end if _cid && is_courseAdmin
?>
</body>
</html>
