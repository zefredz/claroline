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
    require_once dirname(__FILE__) . '/lib/portlet.lib.php';

    // users authentified 
    if( ! claro_is_user_authenticated() ) claro_disp_auth_form();

    // load lib
	require_once dirname( __FILE__ ) . '/lib/portlet.lib.php';
    
	$is_allowedToEdit = claro_is_allowed_to_edit();

	$dialogBox = new DialogBox();

// }}}

// {{{ MODEL

    $cssLoader = CssLoader::getInstance();
    $cssLoader->load('desktop','all');

// }}}

// {{{ CONTROLLER

    $classList = array();
    
    $allowedExtensions = array('.php','.php3','.php4','.php4','.php6');

    $path = dirname( __FILE__ ) . '/lib/portlet';

    $dirname = realpath($path) . '/' ;
    if ( is_dir($dirname) )
    {
        $handle = opendir($dirname);
        while ( false !== ($file = readdir($handle) ) )
        {
            // skip '.', '..' and 'CVS'
            if ( file == '.' || $file == '..' || $file == 'CVS' ) continue;

            // skip folders
            if ( !is_file($dirname.$file) ) continue ;

            // skip file with wrong extension
            $ext = strrchr($file, '.');
            if ( !in_array(strtolower($ext),$allowedExtensions) ) continue;

            // add className to array
            $pos = strpos($file, '.');
            $classList[] = substr($file, '0', $pos);
            
            // add elt to array
            require_once $path . '/' . $file;
        }
    }
    else
    {
        $dialogBox->error( get_lang('Error to load portlet') );
    }

// }}}

// {{{ VIEW    

    $output = '';
    
    $nameTools = get_lang('My Desktop');

	$output .= claro_html_tool_title($nameTools);
    
    $output .= $dialogBox->render();
    
    foreach( $classList as $className )
    {
        if( !class_exists($className) ) continue;
        $portlet = new $className();
        
        if( !method_exists($portlet, 'render') ) continue;
        $output .= $portlet->render();
    }
        
    $claroline->display->body->appendContent($output);
    
    echo $claroline->display->render();

// }}}

?>