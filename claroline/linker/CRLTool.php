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
    * Class CRLTool
    *
    * tools for the management of the crl
    * @p@package CLLINKER
    * @static public class
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
     
    class CRLTool 
    {
        /**
        * Split a crl by string
        *
        * @param $crl string a valid claroline resource locator
        * @return array an array containing the parts of the crl
        * @throws E_USER_ERROR : the crl are required 
        * @throws E_USER_ERROR : if the crl is not valid 
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
        * @throws E_USER_ERROR : if the array is not valid 
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
        * @throws E_USER_ERROR : the group_id is required when one has a group
        * @throws E_USER_ERROR : the course_sys_code is required
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
        * @throws E_USER_ERROR: resource_id without tool_name
        * @throws E_USER_ERROR : the platform_id and the course_sys_code is required
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