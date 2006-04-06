<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool is write to edit setting of Claroline.
 *
 * In the old claroline, there was a central config file
 * in next release a conf repository
 * was build with conf files.
 *
 * To not owerwrite on the following release,
 * was rename  from .conf.inc.php to .conf.inc.php.dist
 * installer was eable to rename from .conf.inc.php.dist
 * to .conf.inc.php
 *
 * The actual config file is build
 * to merge new and active setting.
 *
 * The system as more change than previous evolution
 * Tool are released with a conf definition file.
 *
 * This file define for each property a name,
 * a place but also some control for define accepted content.
 *
 * And finally some comment, explanation or info
 *
 * this version do not include
 * - trigered procedure (function called when a property
 *   is switch or set to a particular value)
 * - renaming or deletion of properties from config
 * - locking  of edit file (This tools can't really be
 *   in the active part of the day in prod. )
 *   I need to change that to let
 *   admin sleep during the night
 *
 * To make transition,
 * - a section can parse old file to found old properties
 *   and his values.
 *   This script would be continue
 *   to generate a def conf file.
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
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
$error = false ;
$error_msg = array();

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

// temporary css style sheet for tab navigation

$htmlHeadXtra[] = '<style type="text/css" media="screen">

<!--
#navlist, .tabTitle
{
padding: 3px 0;
margin-left: 0;
border-bottom: 1px solid #778;
font: bold 12px Verdana, sans-serif;
}

#navlist li, .tabTitle li
{
list-style: none;
margin: 0;
display: inline;
}

#navlist li a, .tabTitle li a
{
padding: 3px 0.5em;
margin-left: 3px;
border: 1px solid #778;
border-bottom: none;
background: #DDE;
text-decoration: none;
}

#navlist li a:link { color: #448; }
#navlist li a:visited { color: #667; }

#navlist li a:hover
{
color: #000;
background: #AAE;
border-color: #227;
}

#navlist li a.current
{
background: white;
border-bottom: 1px solid white;
}

#navlist li a.viewall
{
align : right;
background: white;
border-right: 0px solid white;
border-top: 0px solid white;
border-left: 0px solid white;
}

.configSectionDesc
{
    padding: 3px 0.5em;
    margin-left: 10px;
    background: #eD2;
    border: 1px solid #778;
    // Yes its awfull but volontary to be changed

}
-->
</style>';

/* ************************************************************************** */
/*  Initialise variables and include libraries
/* ************************************************************************** */

require_once $includePath . '/lib/config.lib.inc.php';

/* ************************************************************************** */
/* Process
/* ************************************************************************** */

$form = '';

if ( !isset($_REQUEST['config_code']) )
{
    $message[] = get_lang('No configuration code');
}
else
{
    // get config_code
    $config_code = trim($_REQUEST['config_code']);

    // new config object
    $config = new Config($config_code);

    // load configuration
    if ( $config->load() )
    {
        $section = isset($_REQUEST['section'])?$_REQUEST['section']:null;

        // display section menu
        $form .= $config->display_section_menu($section);

        // init config name
        $config_name = $config->config_code;

        if ( isset($_REQUEST['cmd']) && isset($_REQUEST['property']) )
        {
            if ( $_REQUEST['cmd'] == 'save')
            {
                if ( ! empty($_REQUEST['property']) )
                {
                    // validate config
                    if ( $config->validate($_REQUEST['property']) )
                    {
                        // save config file
                        $config->save();
                        $message[] = get_lang('Properties for %config_name, (%config_code) are now effective on server.'
                                         , array('%config_name' => $config_name, '%config_code' => $config_code));
                    }
                    else
                    {
                        // no valid
                        $error = true ;
                        $message = $config->get_error_message();
                    }
                }
            }
            // display form
            $form .= $config->display_form($_REQUEST['property'],$section);
        }
        else
        {
            // display form
            $form .= $config->display_form(null,$section);
        }
    }
    else
    {
        // error loading the configuration
        $error = true ;
        $message = $config->get_error_message();
    }

    if ( $config->is_modified() )
    {
        $message[] = 'Note. This configuration file has been manually changed. The system will try to retrieve all the configuration values, but it can not guarantee to retrieve additional settings manually inserted.<br />';
    }

}

if ( !isset($config_name) )
{
    $nameTools = get_lang('Configuration');
}
else
{
    // tool name and url to edit config file
    $nameTools = $config->get_conf_name(); // the name of the configuration page
    $_SERVER['QUERY_STRING'] = 'config_code=' . $config_code;
}

/*************************************************************************** */
/* Display
/*************************************************************************** */

// define bredcrumb
$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[] = array ('url' => $rootAdminWeb . 'tool/config_list.php', 'name' => get_lang('Configuration'));

// display claroline header
include $includePath . '/claro_init_header.inc.php';

// display tool title
echo claro_html_tool_title(array('mainTitle'=>get_lang('Configuration'),'subTitle'=>$nameTools)) ;

// display error message
if ( ! empty($message) ) echo claro_html_message_box(implode('<br />',$message));

// display edition form
if ( !empty($form) )
{
    echo $form ;
}

// display footer
include $includePath . '/claro_init_footer.inc.php';

?>
