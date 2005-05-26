<?php  
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
	// include for the linker 
	require_once dirname(__FILE__).'/resolver.lib.php';
	require_once dirname(__FILE__).'/linker_sql.lib.php';
	require_once dirname(__FILE__).'/CRLTool.php';
	require_once dirname(__FILE__).'/linker.lib.php';
	require_once dirname(__FILE__).'/jpspan.lib.php'; 

	//$jpspanAllowed -> config variable
	$jpspanEnabled = $jpspanAllowed && claro_is_jpspan_enabled();
	
	//for debugging : disabled jpspan 
	//$jpspanEnabled = false; 
	if( $jpspanEnabled )
	{
		require_once dirname(__FILE__)."/linker_jpspan.lib.php";
	}
	else
	{
		require_once dirname(__FILE__)."/linker_popup.lib.php";
	}

?>
