<?php // $Id$
/**
 * CLAROLINE
 *
 * This script display list of configuration file
 *
 * @version 1.6 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package CONFIG
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent   <mla@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * 
 */

$cidReset=TRUE;
$gidReset=TRUE;

// include init and library files

require '../../inc/claro_init_global.inc.php';

include($includePath.'/lib/debug.lib.inc.php');
include($includePath.'/lib/course.lib.inc.php');
include($includePath.'/lib/config.lib.inc.php');

// define
$langConfiguration          = 'Configuration';
$nameTools          = $langConfiguration;
$interbredcrump[]   = array ('url'=>$rootAdminWeb, 'name'=> $langAdministration);
$noQUERY_STRING     = TRUE;

/* ************************************************************************** */
/*  INITIALISE VAR
/* ************************************************************************** */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_tool = $tbl_mdb_names['tool'];

$urlEditConf = 'config_edit.php';

$toolNameList = array('CLANN___' => $langAnnouncement,
                          'CLFRM___' => $langForums,
                          'CLCAL___' => $langAgenda,
                          'CLCHT___' => $langChat,
                          'CLDOC___' => $langDocument,
                          'CLDSC___' => $langDescriptionCours,
                          'CLGRP___' => $langGroups,
                          'CLLNP___' => $langLearningPath,
                          'CLQWZ___' => $langExercises,
                          'CLWRK___' => $langWork,
                          'CLUSR___' => $langUsers);

/* ************************************************************************** */
/*  SECURITY CHECKS
/* ************************************************************************** */

$is_allowedToAdmin  = $is_platformAdmin;

if(!$is_allowedToAdmin)
{
    claro_disp_auth_form(); // display auth form and terminate script
}

/**
 * Display
 */

include($includePath."/claro_init_header.inc.php");

// display tool title

claro_disp_tool_title($nameTools);

// display control message

if ( !empty($controlMsg) )
{
    claro_disp_msg_arr($controlMsg);
}

// Get the list of definition files. 
// Each one corresponding to a config file.

$def_list = get_def_file_list();

if ( is_array($def_list) )
{
    foreach( $def_list as $type_def_list)
    {
		if ( is_array($type_def_list['conf']) )
		{
		    echo '<h4>' . $type_def_list['name'] . '</h4>' . "\n";

			asort($type_def_list['conf']);

    		echo '<ul>' . "\n";
            foreach ($type_def_list['conf'] as $code => $name)
    		{
    			echo '<li><a href="'.$urlEditConf . '?config_code=' . $code .'">' . $name  . '</a></li>' . "\n";
    		}
    		echo '</ul>' . "\n";
		}
    }
}

// Display footer
include($includePath."/claro_init_footer.inc.php");

?>
