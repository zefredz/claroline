<?php // $Id$

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

$langFile = "chat";
$tlabelReq = "CLCHT___";

require '../inc/claro_init_global.inc.php';

$nameTools  = $langChat;
$noPHP_SELF = true;

include($includePath."/claro_init_header.inc.php");


/* STATS & TRACKING */

include($includePath."/lib/events.lib.inc.php");

event_access_tool($_tid, $_SESSION['_courseTool']['label']);

$titleElement['mainTitle'] = $nameTools;
if ( $_gid ) $titleElement['subTitle'] = $_group['name'];

claro_disp_tool_title($titleElement);
?>
