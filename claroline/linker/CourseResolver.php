<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
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
require_once ('resolver.lib.php');

/**
    * Class Resolver
    * is a abstact class
    * @package CLLINKER
    *
    */
class CourseResolver extends Resolver
{
    /*-------------------------
    variable
    ------------------------*/
    var $_basePath;

    /*----------------------------
    public method
    ---------------------------*/

    /**
         * constructor
         *
         * @param string $basePath string path root directory of courses
         */
    function CourseResolver($basePath)
    {
        global $coursesRepositoryAppend;

        $this->_basePath = get_conf('rootWeb');

    }

    /**
        * translated a crl into valid URL
        *
        * @param $CRL string a crl
        * @return string a url valide who corresponds to the crl
        */
    function resolve($crl)
    {
        global $tbl_course;

        $elementCRLArray = CRLTool::parseCRL($crl);

        if (  isset( $elementCRLArray['course_sys_code'] ) &&
        !isset( $elementCRLArray['tool_name'] ) &&
        !isset( $elementCRLArray['team'] ) &&
        !isset( $elementCRLArray['resource_id']) )
        {
            $url = $this->_basePath . 'claroline/course/index.php?cidReq=' 
                . $elementCRLArray['course_sys_code']
                ;

            return $url;

        }
        else
        {
            trigger_error('Error: missing course sys code',E_USER_ERROR);
        }
    }

    /**
        * the name of the resource which will be posted
        *
        * @param $crl a string who cotains the crl
        * @return string who contains the name of the resource
        * @throw  E_USER_ERROR if it isn't for tool chat
        **/
    function getResourceName($crl)
    {
        $elementCRLArray = CRLTool::parseCRL($crl);
        if (  isset( $elementCRLArray['course_sys_code'] ) &&
        !isset( $elementCRLArray['tool_name'] ) &&
        !isset( $elementCRLArray['team'] ) &&
        !isset( $elementCRLArray['resource_id']) )
        {
            $title  = get_toolname_title($elementCRLArray);

            return $title;
        }
        else
        {
            trigger_error('Error: missing course sys code',E_USER_ERROR);
        }
    }
}
