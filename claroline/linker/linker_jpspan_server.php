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
 * @author Renaud Fallier <captren@gmail.com>
 * @author Frédéric Minne <minne@ipm.ucl.ac.be>
 *
 * @package CLLINKER
 *
 */
    //-----------------------------------------------------------------------------------
    // include for JPSPAN
    //-----------------------------------------------------------------------------------

    require_once ('../inc/claro_init_global.inc.php');

    if ( !defined('JPSPAN') )
    {
        define('JPSPAN', '../inc/lib/JPSpan/JPSpan/' );
    }

    define ('JPSPAN_ERROR_DEBUG', TRUE);

    require_once ('../inc/lib/JPSpan/JPSpan.php');
    require_once JPSPAN.'Server/PostOffice.php';

    //-----------------------------------------------------------------------------------

    require_once ("CRLTool.php");
    require_once ("navigator.lib.php");
    require_once ("resolver.lib.php");
    require_once ("linker_sql.lib.php");

   /**
    * Class NavigatorJSP
    *
    *
    **/
    class NavigatorJPSPAN
    {
       /**
        * get the resource for a crl
        *
        * @param string $crl a crl
        * @return array a array with the resource
        * @global $coursesRepositorySys
        * @global $rootWeb
        **/
        function getResource($crl = false)
        {
            global $coursesRepositorySys;
            global $rootWeb;

            if($crl)
            {
                $crl = urldecode($crl);
            }

            $baseServDir = $coursesRepositorySys;
            $baseServUrl = $rootWeb;

            $nav = new Navigator($baseServDir, $crl);
            $tab = $nav->getArrayRessource();

            return $tab;
        }

       /**
        * get navigator toolbar
        *
        * @param string $crl a crl
        * @return array a array with the navigator toolbar
        **/
        function getToolBar($crl = false)
        {
            if($crl)
            {
                $crl = urldecode($crl);
            }

            $tab = array();

            $tab["title"]["name"] = htmlentities($this->_getCourseTitle($crl));
            $tab["parent"] = $this->_getParent($crl);

            return $tab;
        }

       /**
        * get the list of the other courses of the teacher
        *
        * @param string $crl a crl
        * @return array a array with the resource of the other courses of the teacher
        * @global $coursesRepositorySys
        * @global $platform_id
        * @global $_course
        **/
        function getOtherCourse()
        {
            global $coursesRepositorySys;
            global $platform_id;
            global $_course;

            $baseServDir = $coursesRepositorySys;

            $crl = CRLTool::createCRL($platform_id, $_course['sysCode']);
            $nav = new Navigator($baseServDir, $crl);
            $tab = $nav->getOtherCoursesArray();

            return $tab;
        }

        /**
        * get the list of the other courses of the teacher
        *
        * @param string $crl a crl
        * @return array a array with the resource of the other courses of the teacher
        * @global $coursesRepositorySys
        * @global $platform_id
        * @global $_course
        **/
        function getPublicCourses()
        {
            global $coursesRepositorySys;
            global $platform_id;
            global $_course;

            $baseServDir = $coursesRepositorySys;

            $crl = CRLTool::createCRL($platform_id, $_course['sysCode']);
            $nav = new Navigator($baseServDir, $crl);
            $tab = $nav->getPublicCoursesArray();

            return $tab;
        }
       /**
        * give the parent of a crl
        *
        * @param string $crl a crl
        * @return array a array with the crl and the name of the button
        * @global $coursesRepositorySys
        **/
        function _getParent($crl = false)
        {
            global $coursesRepositorySys;
            $tab = array();

            if ($crl)
            {
                $baseServDir = $coursesRepositorySys;

                $nav = new Navigator($baseServDir, $crl);

                $tab["crl"] = $nav->getParent();
            }

            return $tab;
        }

       /**
        * get the title of a course
        *
        * @param string $crl a crl
        * @return string the title of a course
        * @global $coursesRepositorySys
        * @global $platform_id
        * @global $_course
        **/
        function _getCourseTitle($crl = false)
        {
            global $coursesRepositorySys;
            global $platform_id;
            global $_course;

            $baseServDir = $coursesRepositorySys;

            $nav = new Navigator($baseServDir, $crl);
            $courseTitle = $nav->getCourseTitle();

            return $courseTitle;
        }

        /**
        * register array with the crl that one must add and delete in session
        *
        * @param array $servAdd array with the crl that one must add
        * @param array $servDel array with the crl that one must delete
        **/
        function registerAttachementList( $servAdd , $servDel )
        {
            $_SESSION['servAdd'] = array();
            $_SESSION['servDel'] = array();

            if( is_array($servAdd) && count($servAdd) != 0 )
            {
                $_SESSION['servAdd'] = array_map("urldecode",$servAdd);
            }

            if( is_array($servDel) && count($servDel) != 0 )
            {
                   $_SESSION['servDel'] = array_map("urldecode",$servDel);
               }

               return true;
        }

       /**
        * give crl which are stored in dB
        *
        * @param string $crl a crl
        * @return array  a array witch the crl and title of the crl
        * @global $baseServUrl
        * @global $rootWeb
        **/
        function getResourceDB($crl)
        {
            global $baseServUrl,$rootWeb;

            $baseServUrl = $rootWeb;
               $crlListe = linker_get_link_list($crl);
               $resourceListe = array();

               foreach($crlListe as $crlElement)
               {
                   $infoResource = array();

                $infoResource["crl"] = urlencode($crlElement["crl"]);
                $infoResource["title"] = htmlentities($crlElement["title"]);

                $resourceListe[] = $infoResource;
            }

            return $resourceListe;
        }
    }

    $jpspan = & new JPSpan_Server_PostOffice();
    $jpspan->addHandler(new NavigatorJPSPAN());

    //-----------------------------------------------------------------------------------

    if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'], 'client') == 0)
    {
        //define('JPSPAN_INCLUDE_COMPRESS',true);
        $jpspan->displayClient();
    }
    else
    {
		// DEBUG_MODE
        if ( defined("DEBUG_MODE") && DEBUG_MODE == true )
		{
			require_once JPSPAN . 'ErrorHandler.php';
		}
        $jpspan->serve();
    }
?>