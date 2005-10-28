<?php # $Id$


require '../inc/claro_init_global.inc.php';

if ($_gid && $is_groupAllowed)
{
    $courseDir         = $_course['path'] .'/group/'.$_group['directory'];
    $interbredcrump[]  = array ('url' => '../group/group.php', 'name' => $langGroups);
    $interbredcrump[] = array ('url' => 'document.php', 'name' => $langDocument);
}
else
{
    $courseDir   = $_course['path'] .'/document';
    $interbredcrump[] = array ('url' => 'document.php', 'name' => $langDocument);
}

$noPHP_SELF = true;

$baseWorkDir = $coursesRepositorySys . $courseDir;

if( !empty($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

if( !empty($_REQUEST ['cwd']) ) $cwd = $_REQUEST ['cwd'];
else                            $cwd = '';

$nameTools = $langCreateModifyDocument;
include '../inc/claro_init_header.inc.php';

echo claro_disp_tool_title(array('mainTitle' => $langDocument, 'subTitle' => $langCreateModifyDocument));

/*========================================================================
                             CREATE DOCUMENT
  ========================================================================*/

if ($cmd ==  'rqMkHtml' )
{
    ?><form action="document.php" method="post">
    <input type="hidden" name="cmd" value="exMkHtml" />
    <input type="hidden" name="cwd" value="<?php echo $cwd; ?>" />
    <p>
    <b><?php echo $langDocumentName ?></b><br />
    <input type="text" name="fileName" size="80" />
    </p>
    <p>
    <b><?php echo $langDocumentContent ?></b>
    <?php
    if (!empty($_REQUEST['htmlContent'])) $content = $_REQUEST['htmlContent']; else $content = "";
    
    echo claro_disp_html_area('htmlContent',$content);
    
    // the second argument _REQUEST['htmlContent'] for the case when we have to 
    // get to the editor because of an error at creation 
    // (eg forgot to give a file name)
    ?> 
    <input type="submit" value="<?php echo $langOk; ?>" />
    <?php echo claro_disp_button('./document.php?cmd=exChDir&file='.$cwd, $langCancel); ?>
    </form>
    <?php
}
elseif($cmd == "rqEditHtml" && !empty($_REQUEST['file']) )
{
    $fileContentList = file($baseWorkDir.$_REQUEST['file']);
      
    ?><form action="document.php" method="post">
    <input type="hidden" name="cmd" value="exEditHtml">
    <input type="hidden" name="file" value="<?php echo $_REQUEST['file']; ?>">
    <b><?php echo $langDocumentName ?></b><br />
    <?php echo $_REQUEST['file']?>
    </p>
    <p>
    <b><?php echo $langDocumentContent ?></b>
    <?php
    echo claro_disp_html_area('htmlContent', implode("\n", $fileContentList));
    ?>
    <input type="submit" value="<?php echo $langOk; ?>">
    <?php echo claro_disp_button('./document.php?cmd=rqEdit&file='.$_REQUEST['file'], $langCancel); ?>
    </form>
    <?php
}
?>
<br />
<br />

<?php include $includePath . '/claro_init_footer.inc.php'; ?>
