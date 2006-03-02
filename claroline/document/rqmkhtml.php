<?php # $Id$


require '../inc/claro_init_global.inc.php';
require_once $includePath . '/lib/fileManage.lib.php';

if ($_gid && $is_groupAllowed)
{
    $courseDir         = $_course['path'] .'/group/'.$_group['directory'];
    $interbredcrump[]  = array ('url' => '../group/group.php', 'name' => get_lang('Groups'));
    $interbredcrump[] = array ('url' => 'document.php', 'name' => get_lang('Documents and Links'));
}
else
{
    $courseDir   = $_course['path'] .'/document';
    $interbredcrump[] = array ('url' => 'document.php', 'name' => get_lang('Documents and Links'));
}

$noPHP_SELF = true;

$baseWorkDir = $coursesRepositorySys . $courseDir;

if( !empty($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

if( !empty($_REQUEST ['cwd']) ) $cwd = $_REQUEST ['cwd'];
else                            $cwd = '';

$nameTools = get_lang('Create/edit document');
include '../inc/claro_init_header.inc.php';

echo claro_html::tool_title(array('mainTitle' => get_lang('Documents and Links'), 'subTitle' => get_lang('CreateModifyDocument')));

/*========================================================================
                             CREATE DOCUMENT
  ========================================================================*/

if ($cmd ==  'rqMkHtml' )
{
    ?><form action="document.php" method="post">
    <input type="hidden" name="cmd" value="exMkHtml" />
    <input type="hidden" name="cwd" value="<?php echo $cwd; ?>" />
    <p>
    <b><?php echo get_lang('Document name') ?>: </b><br />
    <input type="text" name="fileName" size="80" />
    </p>
    <p>
    <b><?php echo get_lang('Document content') ?>: </b>
    <?php
    if (!empty($_REQUEST['htmlContent'])) $content = $_REQUEST['htmlContent']; else $content = "";
    
    echo claro_disp_html_area('htmlContent',$content);
    
    // the second argument _REQUEST['htmlContent'] for the case when we have to 
    // get to the editor because of an error at creation 
    // (eg forgot to give a file name)
    ?> 
    <input type="submit" value="<?php echo get_lang('Ok'); ?>" />
    <?php echo claro_html::button('./document.php?cmd=exChDir&amp;file='.$cwd, get_lang('Cancel')); ?>
    </form>
    <?php
}
elseif($cmd == "rqEditHtml" && !empty($_REQUEST['file']) )
{
    $fileContent = implode("\n",file($baseWorkDir.$_REQUEST['file']));

    $fileContent = get_html_body_content($fileContent)
      
    ?><form action="document.php" method="post">
    <input type="hidden" name="cmd" value="exEditHtml">
    <input type="hidden" name="file" value="<?php echo $_REQUEST['file']; ?>">
    <b><?php echo get_lang('DocumentName') ?></b><br />
    <?php echo $_REQUEST['file']?>
    </p>
    <p>
    <b><?php echo get_lang('DocumentContent') ?></b>
    <?php
    echo claro_disp_html_area('htmlContent', $fileContent );
    ?>
    <input type="submit" value="<?php echo get_lang('Ok'); ?>">
    <?php echo claro_html::button('./document.php?cmd=rqEdit&file='.$_REQUEST['file'], get_lang('Cancel')); ?>
    </form>
    <?php
}
?>
<br />
<br />

<?php include $includePath . '/claro_init_footer.inc.php'; ?>
