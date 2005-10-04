<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$
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
require_once dirname(__FILE__) . '/linker.lib.php';
require_once dirname(__FILE__) . '/CRLTool.php';
require_once dirname(__FILE__) . '/../inc/lib/course_utils.lib.php';
require_once dirname(__FILE__) . '/../inc/lib/claro_utils.lib.php';

/**
    * Class Resolver 
    * is a abstact class   
    *
    * @author Fallier Renaud
    */
class Resolver
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
    function Resolver($basePath)
    {
        $basePath = preg_replace( '~/$~', '', $basePath );
        $this->_basePath = $basePath;
    }

    /**
        * translated a crl into valid URL 
        *
        * @param $CRL string a crl
        * @return string a url valide who corresponds to the crl  
        */
    function resolve($crl)
    {
        $elementCRLArray = CRLTool::parseCRL($crl);

        // resolve course crl
        if( isset( $elementCRLArray['course_sys_code'] )
        && !isset( $elementCRLArray['tool_name'] )
        && !isset( $elementCRLArray['team'] )
        && !isset( $elementCRLArray['resource_id'] ) )
        {
            $tool = 'CourseResolver';
            require_once dirname(__FILE__) . '/' . $tool . '.php';
            $resolver = new $tool($this->_basePath);

            return $resolver->resolve($crl);
        }
        // resolve a group crl
        else if( isset( $elementCRLArray['course_sys_code'] )
        && isset( $elementCRLArray['team'] )
        && !isset( $elementCRLArray['tool_name'] )
        && !isset( $elementCRLArray['resource_id'] ) )
        {
            $tool = 'CLGRP___Resolver';
            require_once dirname(__FILE__) . '/' . $tool . '.php';
            $resolver = new $tool($this->_basePath);

            return $resolver->resolve($crl);
        }
        // resolve a tool (course and group) crl
        else if( isset( $elementCRLArray['course_sys_code'] )
        && isset( $elementCRLArray['tool_name'] )
        && !isset( $elementCRLArray['resource_id'] ) )
        {
            return $this->_resolve($crl);
        }
        // resolve tool resource crl
        else
        {
            $tool =  $elementCRLArray['tool_name'] . 'Resolver';
            require_once dirname(__FILE__) . '/' . $tool . '.php';
            $resolver = new $tool($this->_basePath);

            return $resolver->resolve($crl);
        }
    }

    /**
        * get the id of a resource for a tool
        *
        * @param $tool_name (string) id of a tool. It is the Tlabel
        * @return integer id of a resource
        */
    function getResourceId($tool_name)
    {
        if( isset( $tool_name ) )
        {
            $tool =  $tool_name . 'Resolver';
            require_once dirname(__FILE__) . '/' . $tool . '.php';
            $resolver = new $tool($this->_basePath);

            return $resolver->getResourceId($tool_name);
        }
        else
        {
            trigger_error('Error: missing tool name ',E_USER_ERROR);
        }
    }

    /**
        * get the name of a reso
        *
        * @param $crl a crl valide
        * @return 
        */
    function getResourceName($crl)
    {
        $elementCRLArray = CRLTool::parseCRL($crl);

        if( isset( $elementCRLArray['course_sys_code'] )
        && !isset( $elementCRLArray['tool_name'] )
        && !isset( $elementCRLArray['team'] )
        && !isset( $elementCRLArray['resource_id'] ) )
        {
            $tool = 'CourseResolver';
            require_once dirname(__FILE__) . '/' . $tool . '.php';
            $resolver = new $tool($this->_basePath);

            return $resolver->getResourceName($crl);
        }
        else if( isset( $elementCRLArray['course_sys_code'] )
        && isset( $elementCRLArray['team'] )
        && !isset( $elementCRLArray['tool_name'] )
        && !isset( $elementCRLArray['resource_id'] ) )
        {
            $tool = 'CLGRP___Resolver';
            require_once dirname(__FILE__) . '/' . $tool . '.php';
            $resolver = new $tool($this->_basePath);

            return $resolver->getResourceName($crl);
        }
        else if( isset( $elementCRLArray['course_sys_code'] )
        &&  isset( $elementCRLArray['tool_name'] )
        && !isset( $elementCRLArray['resource_id'] ) )
        {
            return $this->_getResourceName($crl);
        }
        else
        {
            $tool =  $elementCRLArray['tool_name'] . 'Resolver';
            require_once dirname(__FILE__) . '/' . $tool . '.php';
            $resolver = new $tool($this->_basePath);

            return $resolver->getResourceName($crl);
        }
    }

    /**
        * translated a crl into valid URL 
        *
        * @param $CRL string a crl
        * @return string a url valide who corresponds to the crl  
        */
    function _resolve($crl)
    {
        if($crl)
        {
            $elementCRLArray = CRLTool::parseCRL($crl);

            if( !isset($elementCRLArray['tool_name']) )
            {
                trigger_error('ERROR: tool_name required',E_USER_ERROR);
            }

            $url = $this->_basePath . '/claroline/' . $this->_getToolPath($elementCRLArray['tool_name']);
            $url .= '?cidReq=' . $elementCRLArray['course_sys_code'];

            // add the gidReq at the url
            if( isset($elementCRLArray['team']) )
            {
                $url .= '&amp;gidReq=' . $elementCRLArray['team'];
            }

            // change the url if it is a group forum
            if( $elementCRLArray['tool_name'] == 'CLFRM___' && isset($elementCRLArray['team']) )
            {
                $courseInfoArray = get_info_course($elementCRLArray['course_sys_code']);
                $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray['dbNameGlu']);
                $tbl_group = $tbl_cdb_names['group_team'];

                $sql = 'SELECT `forumId` 
                        FROM `' . $tbl_group . '` 
                        WHERE `id` =' . (int)$elementCRLArray['team'];
                $forumId = claro_sql_query_get_single_value($sql);

                $url = $this->_basePath . '/claroline/phpbb/viewforum.php'
                .                         '?forum=' . $forumId
                .                         '&amp;cidReq=' . $elementCRLArray['course_sys_code']
                .                         '&amp;gidReq=' . $elementCRLArray['team']
                ;
            }

            return $url;

        }
        else
        {
            trigger_error('ERROR: crl is required',E_USER_ERROR);
        }
    }

    /**
     *  get the path of a tool
     *
     * @param $toolName (string) a Tlabel
     * @return string the path  
     */
    function _getToolPath($toolName)
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl = $tbl_mdb_names['tool'];

        $sql = "SELECT `script_url` 
                FROM `" . $tbl . "` 
                WHERE `claro_label`= '" . addslashes($toolName) . "'";
        $toolPath = claro_sql_query_get_single_value($sql);

        return $toolPath;
    }

    /**
     *  get the title of a resource
     *
     * @param $crl a crl
     * @return the title of a resource 
     */
    function _getResourceName($crl)
    {
        $elementCRLArray = CRLTool::parseCRL($crl);
        $title  = get_toolname_title($elementCRLArray);

        return $title;
    }
}
?>
