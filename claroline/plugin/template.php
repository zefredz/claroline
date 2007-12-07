<?php # $Id$ 

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


/*====================================== 
	   CLAROLINE MAIN 
  ======================================*/ 

// settings initialisation 
require '../inc/claro_init_global.inc.php'; 


// Optional : If you need to add some HTTP/HTML headers code 
// $httpHeadXtra[] = ""; 
// $httpHeadXtra[] = ""; 
//    ... 
// 
// $htmlHeadXtra[] = ""; 
// $htmlHeadXtra[] = ""; 
//    ... 

$nameTools = ""; // title of the page (comes from the language file) 

$_SERVER['QUERY_STRING'] =''; // used for the breadcrumb 
				  // when one needs to add a parameter after the filename 

include($includePath.'claro_init_header.inc.php'); 

/*======================================*/ 


// PUT YOUR CODE HERE ... 
echo "<h1><center>Hello world!<center></h1>";



/*====================================== 
	   CLAROLINE FOOTER 
  ======================================*/ 

include $includePath . '/claro_init_footer.inc.php'; 

?>
