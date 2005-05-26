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
    require_once("CRLTool.php");
    require_once("linker.lib.php");
    require_once("resolver.lib.php");
    require_once dirname(__FILE__) . "/../inc/lib/course_utils.lib.php";
    
    define("NOT_RESOURCE", -1);
    
    /**
    * Class Navigator  
    *
    * this class allows to display and browse 
    * in the tree of the tools of a course 
    *
    * @author Fallier Renaud
    */
    class Navigator  
    {
        /*-------------------------
                 variable
        -------------------------*/
        var $_basePath; 
        var $_node;
        var $_elementCRLArray;
         
         
        /*----------------------------
                 public method
        ---------------------------*/
        
        /**
        * Constructor
        *
        * @param  $basePath string path root directory of courses
        * @param  $node string crl of current node 
        */
        function Navigator( $basePath , $node = FALSE)
        {
            global $platform_id;  
            global $_course;
            
            $this->_basePath = $basePath;
            
            if( !$node )
            {
                $sysCode = $_course['sysCode'];
                $node = CRLTool::createCRL($platform_id,$sysCode);
            }

            $this->_node = $node;
            $this->_elementCRLArray = CRLTool::parseCRL($this->_node);
            
        }
                
        /**
        * identify the source and returns the adequate resource
        *
        * @return a ClaroContainer or a ClaroObject
        */
        function getResource()
        {
            if ( isset( $this->_elementCRLArray["tool_name"] ) )
            {
                $tool = $this->_elementCRLArray["tool_name"]."Navigator";
                require_once($tool.".php");
                $navigator = new $tool($this->_basePath);
                     
            	return $navigator->getResource($this->_node);    
            }
            else 
            {
                if ( isset( $this->_elementCRLArray["team"])  )
                {
                    return $this->_getGroupRoot();
                }
                else
                {
                    return $this->_getRoot();
                }   
            } 
        }   
        
         /**
        * get the crl of the parent of the current node
        *
        * @return string  parent crl  of the current node
        *         or FALSE if there is not a parent crl  of the current node
        * @global $platform_id
        */
        function getParent ()
        {           
            global $platform_id;
			
			// if current node has got a parent return its crl
             if( isset($this->_elementCRLArray["course_sys_code"])  
              && ( isset($this->_elementCRLArray["team"]) 
              	|| isset($this->_elementCRLArray["tool_name"]) 
              	|| isset($this->_elementCRLArray["resource_id"])))
            {
                $currentDir = "/";
                
                if( isset($this->_elementCRLArray["team"]) )
                {
                    $currentDir .= "groups/".$this->_elementCRLArray["team"]."/";
                }
                
                if( isset($this->_elementCRLArray["tool_name"]) )
                {
                    $currentDir .= $this->_elementCRLArray["tool_name"];
                    
                    if( isset($this->_elementCRLArray["resource_id"]))
                    {
                        $currentDir .= "/".$this->_elementCRLArray["resource_id"];
                    }
                }

                $currentDir = preg_replace( '~^/~', "", $currentDir );
                $currentDir = preg_replace( '~/$~', "", $currentDir );
                
                $dirParts = explode( "/", $currentDir );
                $crl = CRLTool::createCRL($platform_id,$this->_elementCRLArray["course_sys_code"]) ;

                if( count( $dirParts ) == 1 )
                { 
                    return $crl;
                }
                else
                {
                	// remove last part of ressource id to get parent
                    $parent = implode( "/", array_slice( $dirParts, 0, -1) );
                    return  $crl."/". $parent;  
                }  
            }
            // no parent then return false
            else
            {
                return FALSE;
            }
        }
        
        /**
        *  get the list of other courses from a teacher
        *
        * @return array a a assosiatif array with info of courses
        * @global $_course 
        */
        function getOtherCoursesList()
        {
            global $_course;
            
            $mainTbl = claro_sql_get_main_tbl();
        	
            $titular = $_course['titular'];

            $sql = "SELECT `code` , `intitule` , `fake_code` FROM `".$mainTbl["course"]."` WHERE `titulaires` LIKE '$titular'"; 
            $publicCourseInfo = claro_sql_query_fetch_all($sql);

	    	return $publicCourseInfo;
	    
        }
        
        /**
        *  get the list of public courses 
        *
        * @return array a a assosiatif array with info of courses
        * @global $_course a assosiatif array with info of course
        */
        function getPublicCoursesList()
        {
            global $_course;
            
            $mainTbl = claro_sql_get_main_tbl();

            $sql = "SELECT `code` , `intitule` , `fake_code` FROM `".$mainTbl["course"]."` WHERE  `visible` = 2 or `visible` = 3"; 
            $publicCoursesInfo = claro_sql_query_fetch_all($sql);

	    	return $publicCoursesInfo;
	    
        }
        
        /**
        *  get the title of a course
        *
        * @return string the title of a course
        * @global $_course a assosiatif array with info of course
        */
        function getCourseTitle()
   		{
	  		 return get_course_title($this->_elementCRLArray["course_sys_code"]);  
   		}
        
//------------------------------------------------------------------------------------------------------------------ 
       
        /**
        * function for the jpspan linker
        * reorganize the resources in a array
        *
        * @return a array with the tree of the current node
        * @global $baseServUrl,$rootWeb,$langEmpty
        */        

        function getArrayRessource()
        {
        	global $baseServUrl,$rootWeb;
        	global $langEmpty;
        	
        	$baseServUrl = $rootWeb;
            $resourceArray = array();
            $passed = FALSE;
            
            $container = $this->getResource();
            $iterator = $container->iterator();
                        
            while( $iterator->hasNext() )
            {
               if( ! $passed )
               {
                   $passed = TRUE;
               }

               $object =  $iterator->getNext();
               $elementResource = array(); 
               /*---------------------------------*
                *      TODO use htmlentities
                *---------------------------------*/
               $elementResource["name"] =  stripslashes(htmlentities($object->getName()));
               $elementResource["crl"] = urlencode($object->getCRL());
               
               if ( $object->isContainer() )
               { 
                   $elementResource["container"] = TRUE;                
               }
               else
               {
                   $elementResource["container"] = FALSE;  
               }
               
               $elementResource["linkable"] = TRUE;
               
               if( $object->isLinkable() && $object->isVisible() )
               {
                   $elementResource["visible"] = TRUE;    
               }
               else if( $object->isLinkable() && !$object->isVisible() ) 
               {
                   $elementResource["visible"] = FALSE;       
               }
               else 
               {
                   $elementResource["linkable"] = FALSE;     
               }
               
               $res = new Resolver($baseServUrl);
               $title = $res->getResourceName($object->getCRL());
               /*---------------------------------*
                *      TODO use htmlentities
                *---------------------------------*/
               $elementResource["title"] = addslashes(htmlentities($title));
               
               $resourceArray[] = $elementResource;
            }
            
           if(!$passed)
           {
               $elementResource = array();
               $elementResource["name"] = "&lt;&lt;&nbsp;".$langEmpty."&nbsp;&gt;&gt;";
               $elementResource["crl"] = $this->_node; 
               $elementResource["container"] = FALSE;      
               $elementResource["linkable"] = FALSE;   
               $elementResource["visible"] = FALSE; 
               $elementResource["title"] = FALSE; 
               
               $resourceArray[] = $elementResource;
           } 
            
            return $resourceArray;
        
        }
        
        /**
        * function for the jpspan linker
        * reorganize the list of other courses in a array
        *
        * @return array jpspan formated course info  
        *		  or an empty array if otherCoursesList is not an array 
        */        
        function getOtherCoursesArray()
        {
			$otherCoursesArray = array();            
            $otherCoursesList = $this->getOtherCoursesList();
            
            if( is_array($otherCoursesList) )
            {
            	$otherCoursesArray = $this->fillCoursesList( $otherCoursesList );
            	//$otherCoursesArray = array_map("urlencode",$otherCoursesArray);
            }
                        
            return $otherCoursesArray;
        }
        
        /**
        * function for the jpspan linker
        * reorganize the list of public courses in a array
        *
        * @return array jpspan formated course info  
        *		  or an empty array if publicCoursesListis not an array 
        */        
        function getPublicCoursesArray()
        {
        	$publicCoursesList = $this->getPublicCoursesList();
        	$publicCoursesArray = array();
        	
        	if( is_array($publicCoursesList) )
            {
        		$publicCoursesArray = $this->fillCoursesList( $publicCoursesList );
        		//$publicCoursesArray = array_map("urlencode",$publicCoursesArray);
        	}
        	
        	return $publicCoursesArray;
        }
        
        /**
        *  format the course info for the jpspan linker 
        *
        * @param (array) a list of courses
        * @return array jpspan formated course info (name,title and crl) 
        		or an empty array if courseList is not an array 
        * @global $platform_id,$rootWeb    
        */     
        function fillCoursesList( $coursesList )
        {
        	global $platform_id,$rootWeb;
        	
        	$baseServUrl = $rootWeb; 
        	$fileCoursesList = array();
            	
            foreach( $coursesList as  $courseInfo )
            {
               	$processedCoursesInfo = array();   
               	 
                $crl = CRLTool::createCRL($platform_id,$courseInfo["code"]);
                $res = new Resolver($baseServUrl);
               	$title = $res->getResourceName($crl);
                
               	/*---------------------------------*
                *      TODO use htmlentities
                *---------------------------------*/
                $processedCoursesInfo["name"] = $courseInfo["fake_code"]." : ".htmlentities($courseInfo["intitule"]);
                $processedCoursesInfo["crl"] = urlencode($crl); ;
                $processedCoursesInfo["title"] = addslashes(htmlentities($title));
                $fileCoursesList[] = $processedCoursesInfo;    
            }
            	
            return $fileCoursesList;
        }

//-----------------------------------------------------------------------------------------------------------------------
        /*----------------------------
                 private method
        ---------------------------*/
        
        /**
        * get tool list for course 
        *
        * @return ClaroContainer with the course tool
        */
        function _getRoot()
        {
        	global $groupAllowed; //-> config variable
        	
            $courseToolList = get_course_tool_list($this->_elementCRLArray["course_sys_code"]);
            
            $accessLevelList = array('ALL' => 0, 
                         'COURSE_MEMBER'   => 1, 
                         'GROUP_TUTOR'     => 2, 
                         'COURSE_ADMIN'    => 3, 
                         'PLATFORM_ADMIN'  => 4);
            
            $elementList = array();     
           
            foreach($courseToolList as $toolTbl)
            {
            	$name = $toolTbl["name"];
            	$label = $toolTbl["label"];
            	
					if(  is_NULL($label) )
					{
						$node = $this->_node."/CLEXT___/".$toolTbl["url"];
					}
					else
					{
						$node = $this->_node."/".$label;
					}
					
            	    
            	    $isVisible = TRUE; 
            	
            	    if ($accessLevelList[$toolTbl['access']] > $accessLevelList['ALL'])
   		    		{
   		        		$isVisible = FALSE; 
        	    	}

            	    if(  is_NULL($label) || !file_exists($label."Navigator.php") 
            	    	|| ( $label == "CLGRP___" && $groupAllowed == FALSE) )
            	    {
            			$toolContainer = new ClaroObject($name, $node , TRUE , FALSE , $isVisible);	
            	    } 
            	    else
            	    {
            			$toolContainer = new ClaroContainer($name, $node , FALSE , TRUE , $isVisible);  
            	    } 

            	    $elementList[] = $toolContainer;
	       		 
            }

            $name = "";
            $container = new ClaroContainer($name, $this->_node, $elementList);
            
            return $container;            
        }
        
       /**
        * get tool list for groups
        *
        * @return ClaroContainer 
        * @global $toolGroupAllowed
        */
        function _getGroupRoot()
        {
        	global $toolGroupAllowed; //-> config variable
        	
        	$courseToolList = get_course_tool_list($this->_elementCRLArray["course_sys_code"]);
        	$infoGroup = $this->_infoGroup();
             
            // list of groups tool             
            $toolGroupList = array("CLCHT___","CLDOC___","CLFRM___"); 
            $elementList = array();       
           
            foreach($courseToolList as $toolTbl)
            {
            	$name = $toolTbl["name"];
            	$label = $toolTbl["label"];
            	
            	if( in_array($label,$toolGroupList) )
				{
					$node = $this->_node."/".$label;
					$isVisible = true;
					
					if( !file_exists($label."Navigator.php") || $toolGroupAllowed == FALSE )
					{
						$toolGroupContainer = new ClaroObject($name, $node , TRUE , FALSE , $isVisible);	
					}
					else
					{
						$toolGroupContainer = new ClaroContainer($name, $node , FALSE , TRUE , $isVisible);
					}
					
					$elementList[] = $toolGroupContainer;
				}
            }
            
            $name = "";
            $container = new ClaroContainer($name, $this->_node, $elementList);
            
            return $container;        
        }
	
	
	   /**
        * list the property of groups
        *
        * @return $array a array  
        */
        function _infoGroup()
        {
        	$courseInfoArray = get_info_course($this->_elementCRLArray['course_sys_code']); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_groups = $tbl_cdb_names['group_property'];
            
        	$sql = 'SELECT `id`,`forum`,`document`,`wiki`,`chat` FROM `'.$tbl_groups.'`';
        	$groups = claro_sql_query_fetch_all($sql);
        	
        	return $groups;
        }

    }
?>
