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
    * Class DocumentResolver 
    *
    *  
    *
    * @author Fallier Renaud
    */
    class CLDOC___Resolver extends Resolver 
    {
        /*-------------------------
                 variable
         ------------------------*/
        var $_basePath; 
         
        /*----------------------------
                public method
        ---------------------------*/
        
        /**
        * Constructor
        *
        * @param  $basePath string path root directory of courses 
        */
        function CLDOC___Resolver($basePath)
        { 
            $basePath = preg_replace( '~/$~', "", $basePath );
            $this->_basePath = $basePath;
        }
        
        /**
        * translated a crl into valid URL for the document tool
        *
        * @param  $CRL string a crl
        * @return string a url valide who corresponds to the crl
        * @throw  E_USER_ERROR if the path isn't valid
        * @throw  E_USER_ERROR if the crl isn't for tool document
        * @throw  E_USER_ERROR if crl is empty     
        * @global couseRepositorySys
        */
        function resolve($crl)
        {
            global $coursesRepositorySys;
            
            if($crl)
            {
                if(CRLTool::isForThisTool($crl,"CLDOC___"))
                {    
                    $elementCRLArray = CRLTool::parseCRL($crl);
                    $url = $this->_basePath.'/claroline/document';
                    
                    if( isset($elementCRLArray["tool_name"]) && isset($elementCRLArray["resource_id"]) )
                    {
                        $path = $coursesRepositorySys
                        ."/".$elementCRLArray["course_sys_code"];
                        
                        // the path is different if in groups  
                        if( isset($elementCRLArray["team"]) )
                   		{
                    		$secretDirectory = $this->_getSecretDirectory($elementCRLArray);
                    	
                     		$path .= '/group';
                     		$path .= "/".$secretDirectory;
							$path .= "/".$elementCRLArray["resource_id"];				
                    	}
                     	else
                     	{	 
                       	 	$path .= "/document"
                        	."/".$elementCRLArray["resource_id"    ]
                        	;
                        }

						$path = preg_replace("~/+~","/",$path);
                        
						if( is_dir($path)) 
                        {
                            $url .= "/document.php?cmd=exChDir&file=/"
                                .$elementCRLArray["resource_id"]
                                ;
                        }
                        else if( is_file($path)) 
                        {
                            $url .= "/goto/?doc_url=/"
                                .$elementCRLArray["resource_id"]
                                ;
                        }
                        else
                        {
                            trigger_error("ERROR: invalid path ($crl)",E_USER_ERROR);
                        }
                        
                        $url .= '&cidReq=' . $elementCRLArray["course_sys_code"] ;
                        
                        if(isset($elementCRLArray["team"]))
                        {
                            // outil + resource + group
                            $url .= '&gidReq=' . $elementCRLArray["team"] ;
                        }
                       
                    }
                  
                    return $url;
                }
                else
                {
                    trigger_error("ERROR: isn't for tool document",E_USER_ERROR);
                }
            }
            else
            {
               trigger_error("ERROR: crl is required",E_USER_ERROR);
            }     
         }
         
       /**
        * the name of the resource which will be posted
        *
        * @param $crl a string who cotains the crl
        * @return string who contains the name of the resource
        * @global $_courseToolList
		* @throw  E_USER_ERROR if it isn't for tool document
        **/
        function getResourceName($crl)
        {
        	global $_courseToolList;
        	
        	if(CRLTool::isForThisTool($crl,"CLDOC___"))
            {    
            	$elementCRLArray = CRLTool::parseCRL($crl);
            	if( isset($elementCRLArray["resource_id"]) )
            	{
            		$resourceElement = explode("/",$elementCRLArray["resource_id"]);
            		$title  = get_toolname_title( $elementCRLArray );
					foreach ($resourceElement as $item)
					{
            		   $title .= " > ". $item;
					}	
            	}

            	return $title;
            }
            else
            {
            	trigger_error("Error: missing resource id for document ",E_USER_ERROR);	
            }                  	
        } 
        
       /**
        *  search the name of the secret directory of a group.
        *  
        * @param $elementCRLArray associative array who contains the information of a crl   
        * @return string the name of the directory 
        */
        function _getSecretDirectory($elementCRLArray)
        {
        	$courseInfoArray = get_info_course($elementCRLArray["course_sys_code"]); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_group = $tbl_cdb_names['group_team'];
               		
            $sql = 'SELECT `secretDirectory` FROM `'.$tbl_group.'` WHERE `id` ='.$elementCRLArray["team"];
            $secretDirectory = claro_sql_query_get_single_value($sql);
            
            return $secretDirectory;
        }     
     }
?>
