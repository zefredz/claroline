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
$dialogBox = new DialogBox();

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

/* ************************************************************************** */
/*  Initialise variables and include libraries
/* ************************************************************************** */

require_once get_path('incRepositorySys') . '/lib/configHtml.class.php';
require_once get_path('incRepositorySys') . '/lib/user.lib.php';

/* ************************************************************************** */
/* Process
/* ************************************************************************** */

$form = '';

if ( !isset($_REQUEST['config_code']) )
{
    $dialogBox->error( get_lang('Wrong parameters') );
}
else
{
    // get config_code
    $config_code = trim($_REQUEST['config_code']);
    $newPropertyList = isset($_REQUEST['property']) ?$_REQUEST['property']:array();

    // new config object
    $config = new ConfigHtml($config_code, 'config_list.php');

    // load configuration
    if ( $config->load() )
    {
        $section = isset($_REQUEST['section'])?$_REQUEST['section']:null;

        // display section menu
        $form .= $config->display_section_menu($section);

        // init config name
        $config_name = $config->get_conf_name();
        if ( isset($_REQUEST['cmd']) && !empty($newPropertyList) )
        {
            if ( 'save' == $_REQUEST['cmd'] )
            {
                // validate config
                if ( $config->validate($newPropertyList) )
                {
                    // save config file
                    if ( $config->save() )
                    {
                        $dialogBox->success( get_lang('Properties for %config_name, (%config_code) are now effective on server.'
                    , array('%config_name' => get_lang($config_name), '%config_code' => $config_code)));
                    }
                    else
                    {
                        $error = true ;
                        $dialogBox->error( $config->backlog->output() );
                    }
                }
                else
                {
                    // no valid
                    $error = true ;
                    $dialogBox->error( $config->backlog->output() );
                }
            }
            // display form
            $form .= $config->display_form($newPropertyList,$section);
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
        $dialogBox->error( $config->backlog->output() );
    }

    if ( $config->is_modified() )
    {
        $dialogBox->warning( 'Note. This configuration file has been manually changed. The system will try to retrieve all the configuration values, but it can not guarantee to retrieve additional settings manually inserted');
    }

}

if ( !isset($config_name) )
{
    $nameTools = get_lang('Configuration');
    ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, $_SERVER['PHP_SELF'] );
}
else
{
    // tool name and url to edit config file
    $nameTools = get_lang( $config->get_conf_name() );
    ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, $_SERVER['PHP_SELF'] . '?config_code=' . $config_code );
}

/*************************************************************************** */
/* Display
/*************************************************************************** */

// define bredcrumb
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Configuration'), get_path('rootAdminWeb').'tool/config_list.php' );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

// display claroline header
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

// display tool title
echo claro_html_tool_title(array('mainTitle'=>get_lang('Configuration'),'subTitle'=>$nameTools)) ;

// display error message
echo $dialogBox->render();

// display edition form
if ( !empty($form) )
{
    echo $form ;
}

// display footer
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>
