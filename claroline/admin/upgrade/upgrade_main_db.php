<?php // $Id$
/**
 * CLAROLINE 
 *
 * Try to create main database of claroline without remove existing content
 * 
 * @version 1.7 $Revision$
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
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

/*=====================================================================
  Init Section
 =====================================================================*/

// Initialise Upgrade
require 'upgrade_init_global.inc.php';

// Security Check
if (!$is_platformAdmin) upgrade_disp_auth_form();

// Define display
DEFINE('DISPLAY_WELCOME_PANEL', 1);
DEFINE('DISPLAY_RESULT_PANEL',  2);

/*=====================================================================
  Main Section
 =====================================================================*/

/**
 * Create Upgrade Status table
 */

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_upgrade_status = $tbl_mdb_names['upgrade_status'];

$sql = "CREATE TABLE IF NOT EXISTS `" . $tbl_upgrade_status . "` (
`id` INT NOT NULL auto_increment ,
`cid` VARCHAR( 40 ) NOT NULL ,
`claro_label` VARCHAR( 8 ) ,
`status` TINYINT NOT NULL ,
PRIMARY KEY ( `id` )
)";

claro_sql_query($sql);

/**
 * Initialise variables
 */

if ( isset($_REQUEST['verbose']) ) $verbose = true;

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = FALSE;

$display = DISPLAY_WELCOME_PANEL;

/**
 * Define display
 */

if ($cmd == 'run')
{
    // include sql to upgrade the main Database

    include('./upgrade_main_db.lib.php');

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

        echo  '<h2>Step 2 of 3: main platform tables upgrade</h2>
              <p>Now, the <em>Claroline Upgrade Tool</em> is going to prepare the data stored
              into the <b>main Claroline tables</b> (users, course categories, tools list, ...) 
              and set them to be compatible with the new Claroline version.</p>
              <p class="help">Note. Depending of the speed of your server or the amount of data 
              stored on your platform, this operation may take some time.</p>
              <center>
              <p><button onclick="document.location=\'' . $_SERVER['PHP_SELF'] . '?cmd=run\';">Launch main platform tables upgrade</button></p>
              </center>';

        break;

    case DISPLAY_RESULT_PANEL :

        // Initialise
        $nbError = 0;
    
        // Display upgrade result
        
        echo '<h2>Step 2 of 3: main platform tables upgrade</h2>
             <h3>Upgrading main Claroline database (<em>' . $mainDbName . '</em>)</h3>' . "\n" ;
        
        if ( ! preg_match('/^1.7/',$currentDbVersion) )
        {
            // repair tables
            sql_repair_main_database();
        }

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
                echo '<p class="success">The claroline main tables have been successfully upgraded to 1.6</p>' . "\n";
    
                // Database version is 1.6
                $currentDbVersion = '1.6';

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
                echo '<p class="success">The claroline main tables have been successfully upgraded to 1.7</p>' . "\n";

                // Update current version file
                save_current_version_file($currentClarolineVersion, $new_version);
            }
        } // End of upgrade 1.6 to 1.7

        if ( $nbError == 0 )
        {
            if ( preg_match('/^1.7/',$currentDbVersion) )
            {
                echo '<div align="right"><p><button onclick="document.location=\'upgrade_courses.php\';">Next ></button></p></div>';
            }
            else
            {
                echo '<p class="error">Db version unknown : ' . $currentDbVersion . '</p>';
            }
        }
        else
        {
            echo '<p class="error">' . sprintf(" %d errors found",$nbError) . '</p>' . "\n";
            echo '<p><button onclick="document.location=\'' . $_SERVER['PHP_SELF'].'?cmd=run&amp;verbose=true\';" >Retry with more details</button></p>';
        }

        break;

    default :
        die('Display unknow');
}

// Display footer
echo upgrade_disp_footer();

?>
