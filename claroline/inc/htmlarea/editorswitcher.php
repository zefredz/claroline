<?php # -$Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------


require '../claro_init_global.inc.php';

if($_REQUEST['switch'] == 'off')
{
    $_SESSION['htmlArea'] = 'disabled';
    $areaContent = urlencode( strip_tags($_REQUEST['areaContent']) );
}
elseif ($_REQUEST['switch'] == 'on' )
{
    $_SESSION['htmlArea'] = 'enabled';
    $areaContent = urlencode( nl2br($_REQUEST['areaContent']) );
}

header('Cache-Control: no-store, no-cache, must-revalidate');   // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');                                     // HTTP/1.0
header('Location: '.$_REQUEST['sourceUrl'].'&areaContent='.$areaContent);

?>