<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$ 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 * 
 * @author claroline Team <cvs@claroline.net>
 * @author Renaud Fallier <renaud.claroline@gmail.com>
 * @author Frédéric Minne <minne@ipm.ucl.ac.be>
 *
 * @package CLLINKER
 *
 */

   /**
    * check the user agent signature to see whether it is compatible with jpspan or not
    * 
    * @return a boolean (boolean) true if the brouwser is compatible wich jpspan
    */

    function claro_is_jpspan_enabled()
    {
        $jpspanEnabled = FALSE;
        
        /* IIS workaround : JPSPAN does not work on IIS */
        if ( ! (strpos( $_SERVER['SERVER_SOFTWARE'], 'IIS' ) === false) )
        {
			return false;
        }
        
        // check if the javascript is enabled 
        if (claro_is_javascript_enabled())
        {
            // check the signature with mozilla/5.0, gecko (firefox,galeaon,epiphany,mozilla) and not with khtml (konkeror)
            if ( preg_match( '~mozilla/5\.0~i', $_SERVER['HTTP_USER_AGENT'] ) 
                &&( preg_match( '~gecko~i', $_SERVER['HTTP_USER_AGENT'] ) 
                    && !preg_match( '~khtml~i', $_SERVER['HTTP_USER_AGENT'] )  ) )
            {
                $jpspanEnabled = TRUE;
            }
            
            // check the signature with mozilla/4.0, msie 5.5 or 6.0 and not with opera 
            if( preg_match( '~mozilla/4\.0~i', $_SERVER['HTTP_USER_AGENT'] ) 
                &&( preg_match( '~msie (5\.5|6\.0)~i', $_SERVER['HTTP_USER_AGENT'] ) 
                    && !preg_match( '~opera~i', $_SERVER['HTTP_USER_AGENT'] )  ) )
            {
                $jpspanEnabled = TRUE;
            }
    
            // check the signature with mozilla/5.0, gecko and safari/1.2         
            if ( preg_match( '~mozilla/5\.0~i', $_SERVER['HTTP_USER_AGENT'] ) 
                &&( preg_match( '~gecko~i', $_SERVER['HTTP_USER_AGENT'] ) 
                    && preg_match( '~safari/125~i', $_SERVER['HTTP_USER_AGENT'] )  ) )
            {
                $jpspanEnabled = TRUE;
            }    
        }
        
        return $jpspanEnabled;
    }
?>