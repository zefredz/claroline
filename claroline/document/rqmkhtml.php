<?php // $Id$


require '../inc/claro_init_global.inc.php';
require_once get_path('incRepositorySys') . '/lib/fileManage.lib.php';
$_course = claro_get_current_course_data();

function is_parent_path($parentPath, $childPath)
{
    // convert the path for operating system harmonize
    $parentPath = realpath($parentPath) ;
    $childPath = realpath($parentPath . $childPath ) ;

    if ( $childPath !== false )
    {
        // verify if the file exists and if the file is under parent path
        return preg_match('|^'.preg_quote($parentPath).'|', $childPath);
    }
    else
    {
        return false;
    }
}

if (claro_is_in_a_group() && claro_is_group_allowed())
{
    $_group = claro_get_current_group_data();
    $courseDir         = claro_get_course_path() .'/group/'.claro_get_current_group_data('directory');
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Documents and Links'), 'document.php' );
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Groups'), '../group/group.php' );
}
else
{
    $courseDir   = claro_get_course_path() .'/document';
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Documents and Links'), 'document.php' );
}

$noPHP_SELF = true;

$baseWorkDir = get_path('coursesRepositorySys') . $courseDir;

if( !empty($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;

if( !empty($_REQUEST ['cwd']) ) $cwd = $_REQUEST ['cwd'];
else                            $cwd = '';

if ( isset($_REQUEST['file']) /*&& is_download_url_encoded($_REQUEST['file']) */ )
{
    $_REQUEST['file'] = download_url_decode( $_REQUEST['file'] );
}

$nameTools = get_lang('Create/edit document');
include '../inc/claro_init_header.inc.php';

echo claro_html_tool_title(array('mainTitle' => get_lang('Documents and Links'), 'subTitle' => get_lang('Create/edit document')));

/*========================================================================
CREATE DOCUMENT
========================================================================*/

if ($cmd ==  'rqMkHtml' )
{
    ?><form action="<?php echo htmlspecialchars(Url::Contextualize(get_module_entry_url('CLDOC')));?>" method="post">
    <input type="hidden" name="cmd" value="exMkHtml" />
    <input type="hidden" name="cwd" value="<?php echo htmlspecialchars(strip_tags($cwd)); ?>" />
    <p>
    <b><?php echo get_lang('Document name') ?>&nbsp;: </b><br />
    <input type="text" name="fileName" size="80" />
    </p>
    <p>
    <b><?php echo get_lang('Document content') ?>&nbsp;: </b>
    <?php
    if (!empty($_REQUEST['htmlContent'])) $content = $_REQUEST['htmlContent']; else $content = "";

    echo claro_html_textarea_editor('htmlContent',$content);

    // the second argument _REQUEST['htmlContent'] for the case when we have to
    // get to the editor because of an error at creation
    // (eg forgot to give a file name)
    ?>
    </p>
    <p>
    <input type="submit" value="<?php echo get_lang('Ok'); ?>" />&nbsp;
    <?php echo claro_html_button(htmlspecialchars(Url::Contextualize('./document.php?cmd=exChDir&amp;file='.strip_tags($cwd))), get_lang('Cancel')); ?>
    </p>
    </form>
    <?php
}
elseif($cmd == "rqEditHtml" && !empty($_REQUEST['file']) )
{
    if ( is_parent_path($baseWorkDir, $_REQUEST['file'] ) )
    {
        $fileContent = implode("\n",file($baseWorkDir.$_REQUEST['file']));
    }
    else
    {
        claro_die('WRONG PATH');
    }


    $fileContent = get_html_body_content($fileContent)

    ?><form action="<?php echo htmlspecialchars(Url::Contextualize(get_module_entry_url('CLDOC')));?>" method="post">
    <input type="hidden" name="cmd" value="exEditHtml" />
    <input type="hidden" name="file" value="<?php echo htmlspecialchars(base64_encode($_REQUEST['file'])); ?>" />
    <b><?php echo get_lang('Document name') ?> : </b><br />
    <?php echo $_REQUEST['file']?>
    </p>
    <p>
    <b><?php echo get_lang('Document content') ?> : </b>
    <?php
    echo claro_html_textarea_editor('htmlContent', $fileContent );
    ?>
    </p>
    <p>
    <input type="submit" value="<?php echo get_lang('Ok'); ?>" />&nbsp;
    <?php echo claro_html_button(htmlspecialchars(Url::Contextualize('./document.php?cmd=rqEdit&file='.$_REQUEST['file'])), get_lang('Cancel')); ?>
    </p>
    </form>
    <?php
}
?>
<br />
<br />

<?php
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>