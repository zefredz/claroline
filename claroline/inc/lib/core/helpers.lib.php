<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * Helper functions and classes
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     KERNEL
 */

FromKernel::uses ( 'core/url.lib' );

/**
 * Create an html attribute list from an associative array attribute=>value
 * @param   array $attributes
 * @return  string
 */
function make_attribute_list( $attributes )
{
    $attribList = '';
    
    if ( is_array( $attributes ) && !empty( $attributes ) )
    {
        foreach ( $attributes as $attrib => $value )
        {
            $attribList .= ' ' . $attrib . '="'
                . htmlspecialchars($value) . '"'
                ;
        }
    }
    
    return $attribList;
}
 
/**
 * Create an html link to the given url with the given text and attributes
 * @param   string text
 * @param   string url
 * @param   array attributes (optional)
 * @return  string
 */
function link_to ( $text, $url, $attributes = null )
{
    $url = htmlspecialchars_decode( $url );
    
    $link = '<a href="'
        . htmlspecialchars( $url ) . '"'
        . make_attribute_list( $attributes )
        . '>' . htmlspecialchars( $text ) . '</a>'
        ;
        
    return $link;
}

/**
 * Create an html link to the given url inside claroline with the given
 * text and attributes
 * @param   string text
 * @param   string url inside claroline
 * @param   array context (cid, gid)
 * @param   array attributes (optional)
 * @return  string
 */
function link_to_claro ( $text, $url = null, $context = null, $attributes = null )
{
    if ( empty ( $url ) )
    {
        $url = get_path( 'url' ) . '/index.php';
    }
    
    $urlObj = new Url( $url );
    
    if ( $context )
    {
        $urlObj->relayContext($context);
    }
    else
    {
        $urlObj->relayCurrentContext();
    }
    
    $url = $urlObj->toUrl();
    
    return link_to ( $text, $url, $attributes );
}

/**
 * Create an html link to the given course or course tool
 * text and attributes
 * @param   string text
 * @param   string courseId
 * @param   array attributes (optional)
 * @return  string
 */
function link_to_course ( $text, $courseId, $attributes = null )
{
    $url = get_path( 'url' ) . '/claroline/course/index.php?cid='.$courseId;
    $urlObj = new Url( $url );
    
    $url = $urlObj->toUrl();
    
    return link_to ( $text, $url, $attributes );
}

/**
 * Create an html link to the given course or course tool
 * text and attributes
 * @param   string text
 * @param   string toolLabel
 * @param   array context (cid, gid)
 * @param   array attributes (optional)
 * @return  string
 */
function link_to_tool ( $text, $toolLabel = null, $context = null, $attributes = null )
{
    $url = get_module_entry_url( $toolLabel );
    
    return link_to_claro ( $text, $url, $context, $attributes );
}

/**
 * Include the rendering of the given dock
 * @param string dock name
 * @return string rendering
 */
function include_dock( $dockName )
{
    $dock = new ClaroDock( $dockName );
    return $dock->render();
}

/**
 * Include a template file
 * @param   string $template name of the template
 */
function include_template( $template )
{
    $template = secure_file_path( $template );
    
    $customTemplatePath = get_path('rootSys') . '/platform/templates/'.$template;
    $defaultTemplatePath = get_path('includePath') . '/templates/'.$template;
    
    if ( file_exists( $customTemplatePath ) )
    {
        include $customTemplatePath;
    }
    elseif ( file_exists( $defaultTemplatePath ) )
    {
        include $defaultTemplatePath;
    }
    else
    {
        throw new Exception("Template not found {$templatePath} "
            . "at custom location {$customTemplatePath} "
            . "or default location {$defaultTemplatePath} !");
    }
}

/**
 * Include the link to a given css
 * @param name of the css without the complete path
 * @param css media
 * @return string
 */
function link_to_css( $css, $media = 'all' )
{
    return '<link rel="stylesheet" type="text/css" href="' 
        . get_path('clarolineRepositoryWeb') . 'css/' . $css
        . '" media="'.$media.'" />'
        ;
}
