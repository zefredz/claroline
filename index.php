<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

$langFile = 'index';
unset($includePath);

$cidReset = TRUE;
$tidReset = TRUE;
					/* Flag forcing the 'current course' reset,
                     as we're not anymore inside a course */

require './claroline/inc/claro_init_global.inc.php'; // main init

require $includePath.'/lib/events.lib.inc.php'; //stats
require $includePath.'/lib/text.lib.php';

if ($_REQUEST['logout']) session_destroy();

/*
 * DB tables definition
 */
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_admin             = $tbl_mdb_names['admin'            ];
$tbl_courses           = $tbl_mdb_names['course'           ];
$tbl_link_user_courses = $tbl_mdb_names['rel_course_user'  ];
$tbl_courses_nodes     = $tbl_mdb_names['category'         ];
$tbl_user              = $tbl_mdb_names['user'             ];

$tbl_trackLogin        = $statsDbName."`.`track_e_login";

/*
 * CLAROLINE HEADER AND BANNER
 */

require $includePath.'/claro_init_header.inc.php';


if ( isset($_uid) )
{
    /*
     * AUTHENTICATED USER SECTION
     */

    if($submitAuth) event_login();
    require $includePath.'/index_authenticated.inc.php';
}
else
{
    /*
     * ANONYMOUS (DEFAULT) SECTION
     */

    event_open();
    require $includePath.'/index_anonymous.inc.php';
}

/*
 * CLAROLINE FOOTER
 */

require $includePath.'/claro_init_footer.inc.php';

?>