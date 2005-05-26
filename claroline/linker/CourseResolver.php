<?php
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

   require_once ("resolver.lib.php");
    
    /**
    * Class Resolver 
    * is a abstact class   
    *
    * @author Fallier Renaud
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
        	global $rootWeb,$coursesRepositoryAppend;

            $this->_basePath = $rootWeb . $coursesRepositoryAppend;

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
            
            if (  isset( $elementCRLArray["course_sys_code"] ) &&
            	 !isset( $elementCRLArray["tool_name"] ) &&
            	 !isset( $elementCRLArray["team"] ) && 
            	 !isset( $elementCRLArray["resurce_id"]) )
           	{	 
           	
           		$sql = "SELECT `directory` FROM `".$tbl_course."` WHERE `code`= '".$elementCRLArray["course_sys_code"]."'";
           		$directory = claro_sql_query_get_single_value($sql);
            	$url = $this->_basePath . $directory ."/";
                    
                return $url;    
                
            }  
            else
            {
            	trigger_error("Error: missing course sys code",E_USER_ERROR);
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
        	if (  isset( $elementCRLArray["course_sys_code"] ) &&
            	 !isset( $elementCRLArray["tool_name"] ) &&
            	 !isset( $elementCRLArray["team"] ) && 
            	 !isset( $elementCRLArray["resurce_id"]) )
            {   
            	$title  = get_toolname_title($elementCRLArray);
            	
            	return $title;
            }
            else
            {
            	trigger_error("Error: missing course sys code",E_USER_ERROR);
            } 
        }
    }