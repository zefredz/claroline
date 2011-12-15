<?php // $Id$
require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Forums help');
$hide_banner = true;
$hide_footer = true;

$out = '';

$tpl = new PhpTemplate( get_path( 'incRepositorySys' ) . '/templates/help_forum.tpl.php' );

$out .= $tpl->render();

$claroline->setDisplayType(Claroline::POPUP);
$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>