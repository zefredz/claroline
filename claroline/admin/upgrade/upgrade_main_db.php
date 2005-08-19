<?php // $Id$
/**
 * CLAROLINE 
 *
 * Try to create main database of claroline without remove existing content
 * 
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesch� <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

/*=====================================================================
  Init Section
 =====================================================================*/ 

require '../../inc/claro_init_global.inc.php';

// Security Check

if (!$is_platformAdmin) claro_disp_auth_form();

// Include library

include ($includePath.'/lib/config.lib.inc.php');
include ('upgrade.lib.php');

// Define display 

DEFINE('DISPLAY_WELCOME_PANEL', 1);
DEFINE('DISPLAY_RESULT_PANEL',  2);

// Initialise Upgrade
upgrade_init_global();

/*=====================================================================
  Main Section
 =====================================================================*/

if ( isset($_REQUEST['verbose']) ) $verbose = TRUE;
else                               $verbose = FALSE;

$display = DISPLAY_WELCOME_PANEL;

if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd']=='run' )
{
    // include sql to upgrade the main Database

    include('./sql_statement_main_db.php');

    /** TODO

    // include sql to upgrade tracking

    if ( $is_trackingEnabled )
    {
		include('./sql_statement_tracking.php');
	}
    
    */

    $display = DISPLAY_RESULT_PANEL;

} // if ($cmd=="run")

/*=====================================================================
  Display Section
 =====================================================================*/

// Display Header
echo upgrade_disp_header();

switch ( $display )
{
    case DISPLAY_WELCOME_PANEL:

        // Display welcome message

        echo  sprintf("<h2>%s</h2>",$langUpgradeStep2)
            . '<p>' . $langIntroStep2 . '</p>' . "\n"
            . '<center>' 
            . sprintf($langLaunchStep2, $_SERVER['PHP_SELF'].'?cmd=run') 
            . '</center>'
            ;  

        break;
        
    case DISPLAY_RESULT_PANEL :

        // Initialise
        $nbError = 0;
    
        // Display upgrade result
        
        echo  sprintf('<h2>%s</h2>',$langUpgradeStep2)
            . '<h3>' 
            . sprintf ($lang_p_UpgradeMainClarolineDatabase_s, $mainDbName) 
            .'</h3>' . "\n"
            ;

        /*---------------------------------------------------------------------
          Upgrade 1.5 to 1.6
         ---------------------------------------------------------------------*/

        if ( preg_match('/^1.5/',$currentDbVersion) )
        {
            // Apply sql query from $sqlForUpdate16 to main dataabse
            $sqlForUpdate16 = query_to_upgrade_main_database_to_16();
            $nbError += upgrade_apply_sql_to_main_database($sqlForUpdate16,$verbose);
            
            // For each configuration file add a hash code in the new table config_list (new in 1.6)

            $def_file_list = get_def_file_list();
        	foreach ( $def_file_list as $def_file_bloc)
            {
        	    if ( isset($def_file_bloc['conf']) && is_array($def_file_bloc['conf']) )
    	        {
                    // blocs are use in visual config tool to list 
    	            // in special order thes detected config files.
    	            foreach ( $def_file_bloc['conf'] as $config_code => $def_name)
        	        {
        	            $conf_file = get_conf_file($config_code);
                        // The Hash compute and store is differed after creation table use for this storage
    	                // calculate hash of the config file
    	                $conf_hash = md5_file($conf_file); 
    	                save_config_hash_in_db($config_code,$conf_hash);
        	        }
        	    }
    	    }

            if ( $nbError == 0 )
            {
                // Upgrade 1.5 to 1.6 Succeed
                echo '<p class="success">'  .$lang_TheClarolineMainTablesHaveBeenSuccessfullyUpgraded. '</p>' . "\n";
                
                // Database version is 1.6.0
                $currentDbVersion = '1.6.0';

                // Update current version file
                save_current_version_file($currentClarolineVersion, $currentDbVersion) ;
            }
        } // end upgrade 1.5 to 1.6

        /*---------------------------------------------------------------------
          Upgrade 1.6 to 1.7
         ---------------------------------------------------------------------*/

        if ( preg_match('/^1.6/',$currentDbVersion) )
        {
            // Apply sql query from $sqlForUpdate17 to main database
            $sqlForUpdate17 = query_to_upgrade_main_database_to_17();
            $nbError += upgrade_apply_sql_to_main_database($sqlForUpdate17,$verbose);

            // Add wiki tool (new in 1.7) 
            register_tool_in_main_database('CLWIKI__','wiki/wiki.php','wiki.gif');

            if ( $nbError == 0 )
            {
                // Upgrade 1.6 to 1.7 Succeed
                echo '<p class="success">'  .$lang_TheClarolineMainTablesHaveBeenSuccessfullyUpgraded. '</p>' . "\n";

                // Update current version file
                save_current_version_file($currentClarolineVersion, $newDbVersion); 
            }    
        } // End of upgrade 1.6 to 1.7

        if ( $nbError > 0 )
        {
        	echo '<p class="error">' . sprintf($lang_p_d_errorFound,$nbError) . '</p>' . "\n";
    		echo sprintf('<p><button onclick="document.location=\'%s\';" >'.$lang_RetryWithMoreDetails.'</button></p>', $_SERVER['PHP_SELF'].'?cmd=run&amp;verbose=true');
        }
        else
        {
            echo '<div align="right">' . sprintf($langNextStep,'upgrade_courses.php') . '</div>';
        }

        break;
        
    default : 
        die('display unknow');
}

// Display footer
echo upgrade_disp_footer();

?>
