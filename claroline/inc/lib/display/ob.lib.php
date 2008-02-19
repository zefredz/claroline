<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Output buffering functions
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2008 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     display
     */

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses ( 'core/exception.lib' );
    
    function claro_ob_exception_handler( $e )
    {
        ob_end_clean();
        
        echo '<pre>' . $e->__toString() . '</pre>'; 
    }
    
    function claro_ob_start()
    {
        // set error handlers for output buffering :
        set_error_handler('exception_error_handler');
        set_exception_handler('claro_ob_exception_handler');
        // start output buffering
        ob_start();
    }
    
    function claro_ob_end_clean()
    {
        // end output buffering
        ob_end_clean();     
        // restore original error handlers
        restore_exception_handler();
        restore_error_handler();
    }
    
    function claro_ob_get_contents()
    {
        return ob_get_contents();
    }
?>