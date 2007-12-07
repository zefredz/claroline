<?php // $Id$

require '../../claro_init_global.inc.php';

// initialiase variables

$imgUrl = null;
$width = null;
$height = null;

if ( isset($_FILES['imgFile']) )
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
        else
        {
            $imgUrl = null;
        }
    }
    else
    {
        $imgUrl = null;
    }
}

//////////////////////////////////////////////////////////////////////////////

?>
<html>

<head>
  <title>Insert Image</title>

<script type="text/javascript" src="popup.js"></script>

<script type="text/javascript">

window.resizeTo(400, 100);

I18N = window.opener.HTMLArea.I18N.dialogs;

function i18n(str) {
  return (I18N[str] || str);
};

function Init() {
  __dlg_translate(I18N);
  __dlg_init();
  var param = window.dialogArguments;

  if (param) {
      document.getElementById("f_url").value    = param["f_url"];
      document.getElementById("f_alt").value    = param["f_alt"];
      document.getElementById("f_border").value = param["f_border"];
      document.getElementById("f_align").value  = param["f_align"];
      document.getElementById("f_vert").value   = param["f_vert"];
      document.getElementById("f_horiz").value  = param["f_horiz"];
      window.ipreview.location.replace(param.f_url);
  }

  document.getElementById("f_url").focus();

  if (document.getElementById("f_url").value !=  '') { onPreview(); }
};

function onOK() {
  var required = {
    "f_url": "You must enter the URL"
  };
  for (var i in required) {
    var el = document.getElementById(i);
    if (!el.value) {
      alert(required[i]);
      el.focus();
      return false;
    }
  }
  // pass data back to the calling window
  var fields = ["f_align", "f_url", "f_horiz", "f_vert"];
  var param = new Object();
  for (var i in fields) {
    var id = fields[i];
    var el = document.getElementById(id);
    param[id] = el.value;
  }
  __dlg_close(param);
  return false;
};

function onCancel() {
  __dlg_close(null);
  return false;
};

function onPreview() {

  var f_url = document.getElementById("f_url");
  var url = f_url.value;

  if (!url) {
    alert("You have to enter an URL first");
    f_url.focus();
    return false;
  }

  window.ipreview.location.replace(url);
  return false;
};
</script>

<style type="text/css">
html, body {
  background: ButtonFace;
  color: ButtonText;
  font: 11px Tahoma,Verdana,sans-serif;
  margin: 0px;
  padding: 0px;
}
body { padding: 5px; }
table {
  font: 11px Tahoma,Verdana,sans-serif;
}
form p {
  margin-top: 5px;
  margin-bottom: 5px;
}
.fl { width: 8em; float: left; padding: 2px 5px; text-align: right; }
.fr { width: 9em; float: left; padding: 2px 5px; text-align: right; }
fieldset { padding: 0px 10px 5px 5px; }
select, input, button { font: 11px Tahoma,Verdana,sans-serif; }
button { width: 70px; }
.space { padding: 2px; }

.title { background: #ddf; color: #000; font-weight: bold; font-size: 120%; padding: 3px 10px; margin-bottom: 10px;
border-bottom: 1px solid black; letter-spacing: 2px;
}
form { padding: 0px; margin: 0px; }
</style>

</head>

<body onload="Init()">


<div class="title">
Insert Image
<img id="loading"
     src="<?php echo $imgRepositoryWeb ?>processing.gif"
     border="0" style="visibility: hidden;">
</div>

<!--- image file upload form --->


<?php if ($_cid && $is_courseAdmin)
{
?>
<form name="imgFileUpload" method="post" action="insert_image.php" enctype="multipart/form-data" onsubmit="javascript:document.getElementById('loading').style.visibility='visible';">

<input type="hidden" name="sent" value="1">
<div class="fl">Image file:</div>
<nobr>
<input type="file" name="imgFile" size="25" value="" />
<input type="submit" name="upload" value="Upload">
</nobr>
<div class="space"></div>

</form>
<?php
} // end if _cid && is_courseAdmin
?>

<!--- image settings --->

<form name="imgSettings" action="" method="get">

<div class="fl">Image URL:</div>
<input type="text" name="url" id="f_url" value="<?php echo htmlspecialchars($imgUrl); ?>" title="Enter the image URL here" style="width:18em" />
<button name="preview" onclick="return onPreview();" title="Preview the image in a new window">Preview</button>
<div class="space"></div>
<div class="fl">Alternate text:</div>
<input type="text" name="alt" id="f_alt" title="For browsers that don't support images"/ style="width:18em">

<div class="space"></div>

<div style="float: left;">

<div class="fl">Width:</div>
<input type="text" name="width" id="f_width" size="5" value="<?php echo htmlspecialchars($width); ?>" />

<div class="space"></div>

<div class="fl">Height:</div>
<input type="text" name="height" id="f_height" size="5" value="<?php echo htmlspecialchars($height); ?>" />

<div class="space"></div>

<div class="fl">Align:</div>
<select size="1" name="align" id="f_align"
  title="Positioning of this image">
  <option value=""                             >Not set</option>
  <option value="left"                         >Left</option>
  <option value="right"                        >Right</option>
  <option value="texttop"                      >Texttop</option>
  <option value="absmiddle"                    >Absmiddle</option>
  <option value="baseline" selected="1"        >Baseline</option>
  <option value="absbottom"                    >Absbottom</option>
  <option value="bottom"                       >Bottom</option>
  <option value="middle"                       >Middle</option>
  <option value="top"                          >Top</option>
</select>

</div>


<fieldset style="float:right; margin-right: 5px;">
<legend>Spacing</legend>

<div class="fr">Horizontal:</div>
<input type="text" name="horiz" id="f_horiz" size="5"
title="Horizontal padding" />

<div class="space"></div>

<div class="fr">Vertical:</div>
<input type="text" name="vert" id="f_vert" size="5"
title="Vertical padding" />

<div class="space"></div>

<div class="fr">Solid Border:</div>
<input type="text" name="border" id="f_border" size="5"
title="Leave empty for no border" />

<div class="space"></div>


</fieldset>

<br clear="all" />
<table width="100%" style="margin-bottom: 0.2em">
 <tr>
  <td valign="bottom">
    <span>Image Preview</span>:<br />
    <iframe name="ipreview" id="ipreview" frameborder="0" style="border : 1px solid gray;" height="200" width="300"></iframe>
  </td>
  <td valign="bottom" style="text-align: right">
    <button type="button" name="ok" onclick="return onOK();">OK</button><br>
    <button type="button" name="cancel" onclick="return onCancel();">Cancel</button>
  </td>
 </tr>
</table>
</form>
</body>
</html>