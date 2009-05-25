<?php // $Id$
require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Groups help');
$hide_banner = true;
$hide_footer = true;
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

$out = '';

$tpl = new PhpTemplate( get_path( 'incRepositorySys' ) . '/templates/help_group.tpl.php' );

$out .= $tpl->render();

$claroline->setDisplayType(Claroline::POPUP);
$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>