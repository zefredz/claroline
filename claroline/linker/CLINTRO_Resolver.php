<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.7
 *
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

    require_once dirname(__FILE__) . '/resolver.lib.php';

    /**
    * Class External ressource Resolver
    *
    * @package CLEXT
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLINTRO_Resolver extends Resolver
    {
        /*----------------------------
                public method
        ---------------------------*/

        /**
        * Constructor
        *
        * @param  $basePath string path root directory of courses
        */
        function CLINTRO_Resolver($basePath)
        {
        }

        /**
        * translated a crl into valid URL for the forum tool
        *
        * @param  $CRL string a crl
        * @return string a url valide who corresponds to the crl
        * @throws E_USER_ERROR if tool_name is empty
        * @throws E_USER_ERROR if it isn't for tool extern tool
        * @throws E_USER_ERROR if the crl is empty
        */
        function resolve($crl)
        {
            return false;
        }

       /**
        * the name of the resource which will be posted
        *
        * @param $crl a string who cotains the crl
        * @return string who contains the name of the resource
        * @throws  E_USER_ERROR if it isn't for extern tool
        **/
        function getResourceName($crl)
        {
            return false;
        }

        function getResourceId($toolName)
        {
            if     ( isset($GLOBALS['introId'])  ) return $GLOBALS['introId'];
            elseif ( isset($_REQUEST['introId']) ) return $_REQUEST['introId'];
            else                                   return null;
        }
    }
?>