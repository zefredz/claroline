<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
* CLAROLINE
*
* User desktop index
*
* @version      1.9 $Revision$
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/

// reset course and groupe
$cidReset = TRUE;
$gidReset = TRUE;
$uidRequired = TRUE;

// load Claroline kernel
require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';

if( ! claro_is_user_authenticated() ) claro_disp_auth_form();

// load libraries
uses('user.lib', 'utils/finder.lib');
require_once dirname(__FILE__) . '/lib/portlet.lib.php';
require_once dirname(__FILE__) . '/lib/userprofilebox.lib.php';

$dialogBox = new DialogBox();

define( 'KERNEL_PORTLETS_PATH', dirname( __FILE__ ) . '/lib/portlet' );

// Load and register (if needed) portlets
try
{
    $portletList = new PortletList;
    
    $fileFinder = new Claro_FileFinder_Extension( KERNEL_PORTLETS_PATH, '.class.php', false );

    foreach ( $fileFinder as $file )
    {
        $fileName = $file->getFilename();
        $filePath = $file->getRealPath();

        // add elt to array
        require_once $filePath;

        // add className to array
        $pos = strpos($fileName, '.');
        $className = substr($fileName, '0', $pos);

        // load db
        $portletInDB = $portletList->loadPortlet($className);

        // si present en db on passe
        if( !$portletInDB )
        {
            if( class_exists($className) )
            {
                $portletList->addPortlet( $className, $className );
            }
        }
    }
    
    $moduleList = get_module_label_list();
    
    foreach ( $moduleList as $moduleId => $moduleLabel )
    {
        $portletPath = get_module_path( $moduleLabel )
            . '/connector/desktop.cnr.php'
            ;
        
        if ( file_exists( $portletPath ) )
        {
            require_once $portletPath;
            
            $className = "{$moduleLabel}_Portlet";
            $label = strtolower($className);
            
            $portletInDB = $portletList->loadPortlet($label);

            // si present en db on passe
            if( !$portletInDB )
            {
                if ( class_exists($className) )
                {
                    $portletList->addPortlet( $label, $className );
                }
            }
        }
    }
}
catch (Exception $e)
{
    $dialogBox->error( get_lang('Cannot load portlets') );
    pushClaroMessage($e->__toString());
}

// Generate Output from Portlet

$outPortlet = '';

$portletList = $portletList->loadAll( true );

foreach ( $portletList as $portlet )
{
    // load portlet
    if( !class_exists($portlet['label']) ) continue;
    $portlet = new $portlet['label']();

    if( !method_exists($portlet, 'render') ) continue;
    $outPortlet .= $portlet->render();
}

// Generate Script Output

$cssLoader = CssLoader::getInstance();
$cssLoader->load('desktop','all');

$output = '';

$nameTools = get_lang('My desktop');

$output .= claro_html_tool_title($nameTools);

$output .= $dialogBox->render();

$userProfileBox = new UserProfileBox();

$output .= $userProfileBox->render();

$output .= $outPortlet;

$claroline->display->body->appendContent($output);

echo $claroline->display->render();

?>