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

    /**
    * Class CRLTool
    *
    * tools for the management of the crl
    * @static public class
    * @author Fallier Renaud
    */
     
    class CRLTool 
    {
        /**
        * Split a crl by string
        *
        * @param $crl string a valid claroline resource locator
        * @return array an array containing the parts of the crl
        * @throw E_USER_ERROR : the crl are required 
        * @throw E_USER_ERROR : if the crl is not valid 
        */
        function explode( $crl )
        {
            if( $crl != FALSE)
            {
           		$temp = explode(":", $crl);
           		
           		if( is_array($temp) && count($temp) > 1 )
           		{
            		$scheme = $temp[0];
            		$queryElementList = explode("/", $temp[1]);

	            	$crlArray = array();
	            	$crlArray[] = ( $scheme );
            
	            	foreach ( $queryElementList as $queryElement )
	            	{
	                	if ( $queryElement != FALSE )
	                	{
	                	    $crlArray[] = $queryElement;
	                	}
	            	}
	            	return $crlArray;
	            }
	            else
	            {
	            	trigger_error("Error: the crl is not valid",E_USER_ERROR);	
	            }
	        }    
            else
            {
            	trigger_error("Error: the crl are required",E_USER_ERROR);
            }
        }
        
        /**
        * Join array elements with a crl (claroline resource locator)
        *
        * @param $crlArray array array containing the crl parts
        * @return string claroline resource locator
        * @throw E_USER_ERROR : if the array is not valid 
        */ 
        function implode( $crlArray )
        {
            if ( is_array($crlArray) && count($crlArray) >= 1)
            {
            	$crl = $crlArray[0].":/";
            	
            	for($i = 1 ; $i < count($crlArray) ; $i++)
            	{
               		$crl = $crl."/".$crlArray[$i];
            	}
            	
            	return $crl;
            }
            else
            {
            	trigger_error("Error: if the array is not valid",E_USER_ERROR);	
            }
        }
        
        /**
        * Parse a claroline resource locator and return its components
        *
        * @param $crl string a valid crl
        * @return an associative array containing any of the various 
        *      components of the URL that are present
        * @throw E_USER_ERROR : the group_id is required when one has a group
        * @throw E_USER_ERROR : the course_sys_code is required
        */ 
        function parseCRL( $crl )
        {
            $crlArray = parse_url($crl);
            
            if( !(isset($crlArray["scheme"]) && $crlArray["scheme"] == "crl")  )
            {
            	trigger_error("Error: invalid crl",E_USER_ERROR);	
            }
            
            if( isset($crlArray["path"]) )
            {
                $path = $crlArray["path"];            
                $path = preg_replace('~^/~', "", $path);
                $path = preg_replace('~/$~', "", $path);
                $crlArray = explode("/", $path);
                
                $platform_id = $crlArray[0]; 
                $course = $crlArray[1];
                $elementCRL = array();
                $elementCRL["platform_id"] = $platform_id;
                $elementCRL["course_sys_code"] = $course;
                
             // groups
                if( count($crlArray) > 2 && $crlArray[2] == "groups")
                {
                    if( count($crlArray) > 3 && is_numeric($crlArray[3]))
                    {
                        $elementCRL["team"] = $crlArray[3];
                        
                        if( count($crlArray) > 4 )
                        {
                            $elementCRL["tool_name"] = $crlArray[4];
                            
                            if( count($crlArray) > 5 )
                            {
                                $resource = $crlArray[5];
                                
                                for ($i = 6 ; $i < count($crlArray) ; $i++)
                                {
                                    $resource = $resource."/".$crlArray[$i];
                                }        
                                
                                $elementCRL["resource_id"] = $resource; 
                            }
                        }   
                    }
                    else
                    {
                        $elementCRL["tool_name"] = "CLGRP___";
                    }
                }
                // it isnt a group
                else
                {
                    if( count($crlArray) > 2 )
                    {
                        $elementCRL["tool_name"] = $crlArray[2];
                        
                        if( count($crlArray) > 3 )
                        {
                            $resource = $crlArray[3];
                            
                            for ($i = 4 ; $i < count($crlArray) ; $i++)
                            {
                                $resource = $resource."/".$crlArray[$i];
                            }        
                            
                            $elementCRL["resource_id"] = $resource; 
                        }
                    } 
                }
               
               return  $elementCRL;
            }
            else
            {
                trigger_error("Error: platform_id ans course_sys_code are required",E_USER_ERROR);
            }
                
        }
        
        /**
        * create a valide crl with param's 
        *
        * @param $plateform_id string identify the platform (is required)
        * @param $course_sys_code string identify the course (is required)
        * @param $tool_name string name of a tool
        * @param $resource_id string the way of the resource
        * @param $group numeric the id of a group
        * @return string a valid claroline resource locator
        * @throw E_USER_ERROR: resource_id without tool_name
        * @throw E_USER_ERROR : the platform_id and the course_sys_code is required
        */ 
        function createCRL($platform_id , $course_sys_code , $tool_name ='' , $resource_id = '' , $group = NULL )
        {
            $crl_sheme = "crl";
            $crl_virtual_host = "claroline.net";

            if($course_sys_code && $platform_id)
            {
                $crl = $crl_sheme."://".$crl_virtual_host."/".$platform_id."/".$course_sys_code;
                
                if ( $group != NULL )
                {
                    $crl .= "/groups/".$group; 
                }
                
                if( $tool_name != "" )
                {
                    $crl .= "/".$tool_name;   
                }
                
                if( $resource_id != ""  && $tool_name == "" )
                {
                     trigger_error("ERROR: resource_id without tool_name",E_USER_ERROR);
                }
                
                if( $resource_id != "" )
                {
                    $crl .= "/".$resource_id;    
                }
            }
            else
            {
                trigger_error("ERROR: platform_id and course_sys_code are required",E_USER_ERROR);
            }
             
            return $crl;
        }
        
        /**
        * test if the crl is for a specified tool
        *
        * @param $crl string a crl valide
        * @param $tool_name string the name of a tool
        * @return boolean if the crl is for the tool 
        */ 
        function isForThisTool( $crl , $tool_name )
        {
            $array = CRLTool::parseCRL($crl);
			
            if( isset($array["tool_name"]))
            {
                return ($array["tool_name"] == $tool_name);
            }
            else
            {
                return FALSE;
            }            
        }
    }
?>