<?php # $Id$

$langFile = "document";

require '../inc/claro_init_global.inc.php';

if ( ! $_cid) claro_disp_select_course();

if ($_gid && $is_groupAllowed)
{
    $courseDir         = $_course['path'] .'/group/'.$_group['directory'];
    $interbredcrump[]  = array ('url'=>'group.php', 'name'=> $langGroupManagement);
}
else
{
    $courseDir   = $_course['path'] .'/document';
    $interbredcrump[] = array ('url'=>'document.php', 'name'=> $langDoc);
}

$baseWorkDir = $coursesRepositorySys . $courseDir;

$nameTools = $langCreateModifyDocument;
include('../inc/claro_init_header.inc.php');

claro_disp_tool_title(array('mainTitle' => $langDoc, 'subTitle' => $langCreateModifyDocument));

/*========================================================================
                             CREATE DOCUMENT
  ========================================================================*/

/*------------------------------------------------------------------------
                        CREATE DOCUMENT : STEP 2
--------------------------------------------------------------------------*/


/*------------------------------------------------------------------------
                        CREATE DOCUMENT : STEP 1
--------------------------------------------------------------------------*/

if ($cmd ==  'rqMkHtml')
{
    ?><form action="document.php" method="post">
    <input type="hidden" name="cmd" value="exMkHtml">
    <input type="hidden" name="cwd" value="<?php echo $_REQUEST['cwd']?>">
    <p>
    <b><?php echo $langDocumentName ?></b><br />
    <input type="text" name="fileName" size="80">
    </p>
    <p>
    <b><?php echo $langDocumentContent ?></b>
    <?php
    claro_disp_html_area('htmlContent',$_REQUEST['htmlContent']);
    // the second argument _REQUEST['htmlContent'] for the case when we have to 
    // get to the editor because of an error at creation 
    // (eg forgot to give a file name)
    ?> 
    <input type="submit" value="OK">
    </form>
    <?php
}
elseif($cmd == "rqEditHtml")
{
    $fileContentList = file($baseWorkDir.$_REQUEST['file']);
      
    ?><form action="document.php" method="post">
    <input type="hidden" name="cmd" value="exEditHtml">
    <input type="hidden" name="file" value="<?php echo $_REQUEST['file']?>">
    <b><?php echo $langDocumentName ?></b><br />
    <?php echo $_REQUEST['file']?>
    </p>
    <p>
    <b><?php echo $langDocumentContent ?></b>
    <?php
    claro_disp_html_area('htmlContent', implode("\n", $fileContentList));
    ?>
    <input type="submit" value="OK">
    </form>
    <?php
}
?>
<br />
<br />

<?php @include($includePath."/claro_init_footer.inc.php"); ?>
