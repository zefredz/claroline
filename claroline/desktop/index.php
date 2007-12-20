<?php // $Id$
/**
 * CLAROLINE
 *
 * $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package Desktop
 *
 * @author Claroline team <info@claroline.net>
 *
 */

// {{{ SCRIPT INITIALISATION
    
    // reset course and groupe
    $cidReset = TRUE;
    $gidReset = TRUE;
    $uidRequired = TRUE;

    // load Claroline kernel
    require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';

    // users authentified 
    if( ! claro_is_user_authenticated() ) claro_disp_auth_form();

    // load lib
    require_once dirname( __FILE__ ) . '/lib/desktop.lib.php';
	//require_once dirname( __FILE__ ) . '/lib/portlet.lib.php';
    
	$is_allowedToEdit = claro_is_allowed_to_edit();

	$dialogBox = new DialogBox();

// }}}

// {{{ MODEL

    $cssLoader = CssLoader::getInstance();
    $cssLoader->load('desktop','all');

// }}}

// {{{ CONTROLLER



// }}}

// {{{ VIEW    

	$out = '';
    
    $nameTools = get_lang('My Desktop');

	//$out .= claro_html_tool_title($nameTools);
    
    $out .= 'rendu';
    
    $claroline->display->body->appendContent($out);

    echo $claroline->display->render();

// }}}

?>