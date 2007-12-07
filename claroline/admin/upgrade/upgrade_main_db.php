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
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

/*=====================================================================
  Init Section
 =====================================================================*/ 

require '../../inc/claro_init_global.inc.php';

/*---------------------------------------------------------------------
  Security Check
 ---------------------------------------------------------------------*/ 

if (!$is_platformAdmin) claro_disp_auth_form();

/*---------------------------------------------------------------------
  Include version file and initialize variables
 ---------------------------------------------------------------------*/
if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');
include ($includePath.'/installedVersion.inc.php');

/*---------------------------------------------------------------------
  Include library
 ---------------------------------------------------------------------*/

include ($includePath.'/lib/config.lib.inc.php');


if ( !function_exists(mysql_info) )
{

    /**
     * This is a fake function declared if mysql_info don't exist
     * The output is use for additional info.
     * @return string empty.
     */
    function mysql_info() {return '';} // mysql_info is used in verbose mode
}

/**#@+
 * Steps of Display 
 */
DEFINE('DISPLAY_WELCOME_PANEL', 1);
DEFINE('DISPLAY_RESULT_PANEL',  2);
/**#@-*/

/*=====================================================================
  Statements Section
 =====================================================================*/

/**
 * Initialise variables
 */

if ( isset($_REQUEST['verbose']) ) $verbose = TRUE;
else                               $verbose = FALSE;

$display = DISPLAY_WELCOME_PANEL;

/**
 * Define display
 */

if (isset($_REQUEST['cmd']) && $_REQUEST['cmd']=='run')
{

    /**
     * Upgrade Main Database
     */

    include('./sql_statement_main_db.php');

    /**
     * Upgrade Tracking
     */    

    if ($is_trackingEnabled)
    {
		include('./sql_statement_tracking.php');
	}

    $accepted_error_list = array(1060,1062,1091,1054);

    $display = DISPLAY_RESULT_PANEL;

} // if ($cmd=="run")

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <title>-- Claroline upgrade -- version <?php echo $clarolineVersion ?></title>
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
  </style>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">

<center>

<table cellpadding="10" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
<tbody>
<tr bgcolor="navy">
<td valign="top" align="left">

<div id="header">
<?php
    printf("<h1>Claroline (%s) - " . $langUpgrade . "</h1>",$clarolineVersion);
?>
</div>
</td>
</tr>
<tr valign="top" align="left">
<td>
<div id="content">

<?php

switch ($display)
{
    case DISPLAY_WELCOME_PANEL:

        echo  sprintf("<h2>%s</h2>",$langUpgradeStep2)
            . '<p>' . $langIntroStep2 . '</p>' . "\n"
            . '<center>' 
            . sprintf($langLaunchStep2, $_SERVER['PHP_SELF'].'?cmd=run') 
            . '</center>'
            ;  

        break;
        
    case DISPLAY_RESULT_PANEL :
    
        echo  sprintf('<h2>%s</h2>',$langUpgradeStep2)
            . '<h3>' 
            . sprintf ($lang_p_UpgradeMainClarolineDatabase_s, $mainDbName) 
            .'</h3>' . "\n"
            ;

        if ($_REQUEST['verbose']) 
        {
        	echo '<p class="info">' . $langModeVerbose . ':</p>' . "\n";
        }

        echo '<ol>' . "\n";

        $nbError = 0;

        $tbl_mdb_names = claro_sql_get_main_tbl();
        while (list($key,$sqlTodo) = each($sqlForUpdate))
        {
        	if ($sqlTodo[0] == "#")
        	{
        		if ($verbose)
        		{
        			echo '<p class="comment">' . 'Comment:' . $sqlTodo . '</p>' . "\n";
        		}
        	}
        	else
        	{
        		$res = @mysql_query($sqlTodo);
        		if ($_REQUEST['verbose'])
        		{
        			echo  '<li>' . "\n"
        			    . '<p class="tt">' . $sqlTodo . '</p>' . "\n"
        			    . '<p>' 
        			    . sprintf($lang_p_d_affected_rows,mysql_affected_rows()) . '<br />' 
        			    . mysql_info() 
        			    . '</p>' . "\n";
        		}
        		if (mysql_errno() > 0)
        		{
                    if ( in_array(mysql_errno(),$accepted_error_list) )
        			{
        				if ($verbose)
        				{
        					echo '<p class="success">' . mysql_errno(). ': ' . mysql_error() . '</p>' . "\n";
        				}
        			}
        			else
        			{
        				echo '<p class="error">' . "\n"
                            . (++$nbError) . '<strong>' . 'n°' . mysql_errno() . '</strong>: '. mysql_error() . '<br />' . "\n"
        				    . '<code>' . $sqlTodo . '</code>' . "\n"
        				    . '</p>' . "\n";
        			}
        		}
        		if ($verbose) {
        			echo '</li>' . "\n";
				flush();
        		}
        	}
        }
        
        echo '</ol>' . "\n";
	
    	// For Each def file add a hash code in  the new table config_list
        $def_file_list = get_def_file_list();
    	foreach ( $def_file_list as $def_file_bloc)
    	{
    	    if (is_array($def_file_bloc['conf']))
    	    {   // blocs are use in visual config tool to list 
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
    	
    	mysql_close();

        if ($nbError > 0 )
        {
        	echo '<p class="error">' . sprintf($lang_p_d_errorFound,$nbError) . '</p>' . "\n";
    		echo sprintf('<p><button onclick="document.location=\'%s\';" >'.$lang_RetryWithMoreDetails.'</button></p>', $_SERVER['PHP_SELF'].'?cmd=run&amp;verbose=true');
        }
        else
        {
           /**
            * Update config file
            * Set version db
            */

           echo '<p class="success">'  .$lang_TheClarolineMainTablesHaveBeenSuccessfullyUpgraded. '</p>' . "\n";

           // store in currentVarsion the version of central DB.
           $fp_currentVersion = fopen($includePath .'/currentVersion.inc.php','w');
           if($fp_currentVersion)
           {
               $currentVersionStr = '<?php
$clarolineVersion = "'.$version_file_cvs.'";
$versionDb = "'.$version_db_cvs.'";
?>';
               fwrite($fp_currentVersion, $currentVersionStr);
               fclose($fp_currentVersion);
               echo '<div align="right">' . sprintf($langNextStep,'upgrade_courses.php') . '</div>';
           }
           else
           {
               echo '<p class="error">' . 'Can\'t save success in <span class="filename">currentVersion.inc.php</span>' . '</p>'  . "\n";
           }
        }
        break;
        
    default : 
        die('display unknow');
}

?>
</div>

</td>
</tr>
</tbody>
</table>

</body>
</html>