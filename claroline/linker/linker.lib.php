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
    
    //include the file of config 
    require_once dirname(__FILE__) . "/../inc/conf/linker.conf.php";
    require_once dirname(__FILE__) . "/../inc/lib/course_utils.lib.php";

    /**
    * Class ClaroObject
    *
    * This class is a object of claroline
    * 
    *
    * @author Fallier Renaud
    */
    class ClaroObject 
    {
        /*-------------------------
                  variable
        -------------------------*/
        var $_isContainer;
        var $_isLinkable;
        var $_isVisible;
        var $_CRL;
        var $_name;
         
        /*----------------------------
                  method
        ---------------------------*/
     
        /**
        * Constructor
        *
        * @param string $name name of a claroObject      
        * @param string $CRL crl of a claroObject   
        * @param boolean  $isLinkable default TRUE
        * @param boolean $isContainer default FALSE
        */
        function ClaroObject($name, $CRL, $isLinkable = TRUE, $isContainer = FALSE , $isVisible = TRUE)
        {
            $this->_name = $name;
            $this->_CRL = $CRL;
            $this->_isLinkable = $isLinkable;
            $this->_isContainer = $isContainer;
            $this->_isVisible = $isVisible;
        }
         
        /**
        * test if the claroObject is a container
        *
        * @return boolean TRUE if it's a container
        *                 else if it isnt a container
        */ 
        function isContainer()
        {
            return $this->_isContainer;
        }
         
       /**
        * test if the claroObject is a linkable
        *
        * @return boolean TRUE if it's linkable
        *                 else if it isnt linkable
        */ 
        function isLinkable()
        {
            return $this->_isLinkable;
        }
        
        /**
        * test if the claroObject is a linkable
        *
        * @return boolean TRUE if it's linkable
        *                 else if it isnt linkable
        */ 
        function isVisible()
        {
            return $this->_isVisible;
        }
        
        /**
        * return the crl of the claroObject
        *
        * @return string crl
        */  
        function getCRL()
        {
            return $this->_CRL;
        }
        
        /**
        * return the name of the claroObject
        *
        * @return string name of the claroObject
        */  
        function getName()
        {
            return $this->_name;
        }
    }

//-----------------------------------------------------------------------------   
    
    /**
    * Class ClaroContainer
    *
    * extend the ClaroObject class 
    * @package CLLINKER
    *
    */  
    class ClaroContainer extends ClaroObject 
    {
        /*-------------------------
                variable
        -------------------------*/
        var $_elementList;
         
        /*----------------------------
                method
        ---------------------------*/
        
        /**
        * Constructor a ClaroContainer and initialise a ClaroObject
        *
        * @param string  $name        name of a claroObject      
        * @param string  $CRL         crl of a claroObject   
        * @param array   $elementList contains a list of element
        * @param boolean $isLinkable  default TRUE
        */
        function ClaroContainer($name, $CRL, $elementList = FALSE, $isLinkable = TRUE , $isVisible = TRUE)
        {
            ClaroObject::ClaroObject($name, $CRL, $isLinkable, TRUE , $isVisible );
            
            if( $elementList )
                $this->_elementList = $elementList;
            else
                $this->_elementList = array();    
        }
        
        /**
        * return the object to the position of the index 
        *
        * @param $index numeric 
        * @return claroObject or claroContainer
        */  
        function at($index)
        {
            return $this->_elementList[$index];
        }
        
        /**
        * return the number of element
        *
        * @return numeric number of element
        */  
        function size()
        {
            return count($this->_elementList);
        }
        
        /**
        * return the first element
        *
        * @return claroObject or claroContainer
        */  
        function first()
        {
            return $this->at(0);
        }
        
        /**
        * return the last element
        *
        * @return claroObject or claroContainer
        */  
        function last()
        {
            $index = $this->size()-1;
            return $this->at($index);
        }
        
        /**
        * test if the array of element is empty
        *
        * @return boolean TRUE if it's empty else FALSE
        */ 
        function isEmpty()
        {
            return ($this->size() == 0);
        } 
        
        /**
        * create a iterator object 
        *
        * @return 
        */ 
        function iterator()
        {
            $iterator = new ClaroContainerIterator($this->_elementList);
            return $iterator;
        }
         
    }

//--------------------------------------------------------------------------------------------------------    

    /**
    * Class ClaroContainerItrator 
    *
    *
    * @author Fallier Renaud
    */
    class ClaroContainerIterator
    {
        /*-------------------------
                variable
        -------------------------*/
        var $_elementList;
        var $_currentIndex;
         
        /*----------------------------
                method
        ---------------------------*/
        
        /**
        * Constructor
        *
        * @param $elementList
        */
        function ClaroContainerIterator( $elementList )
        {
           $this->_elementList = $elementList; 
           $this->_currentIndex = -1;
        }
        
        /**
        * test if the following element exists
        *
        * @return boolean TRUE if ok else FALSE
        */ 
        function hasNext()
        {
            return ((-1 <= $this->_currentIndex) && ($this->_currentIndex < count($this->_elementList)-1));
        }
        
        /**
        * return the next element 
        * attention must be appeller 
        * after the function hasNext()
        *
        * @return a ClaroContainer or a ClaroObject
        */  
        function getNext()
        {
            $this->_currentIndex++;
            return $this->_current();  
        }
        
        /**
        * private method
        * Return the current element in an array 
        *
        * @return a ClaroContainer or a ClaroObject
        */ 
        function _current()
        {
            if(count($this->_elementList) != 0)
            {
                return $this->_elementList[$this->_currentIndex];
            }
            else
            {
                trigger_error ("Error iterator overflow", E_USER_ERROR);
            }
        }
    }
    
///--------------------------------------------------------------------------------------------------------   
   
    /**
    *  get a valid url for a resource
    *
    * @param $plateform_id (string) id of the claroline platforme
    * @param $course_sys_code (string) the sys code of a course
    * @param $tool_name (string) the Tlabel of a tool or a empty string for a external tool
    * @param $ressource_id (string) the resource
    * @param $team (integer) the id of a team
    * @return string $url a url
    * @global $rootweb
    */
    function getRessourceURL( $plateform_id, $course_sys_code,
        $tool_name = FALSE, $ressource_id = FALSE, $team = FALSE )
    {
        global $rootWeb;
            
        $crl = CRLTool::createCRL( $plateform_id, $course_sys_code, $tool_name, $ressource_id, $team );
        $resolver = new Resolver( $rootWeb );
        $url = $resolver->resolve( $crl );

        return $url;
    }
    
    
   /**
    *  get the crl for a resource 
    *
    * @param string tLable tool label if different form current tool
    * @return  string a valid crl 
    * @global $platform_id,$_course,$_courseTool,$_gid,$rootWeb
    */
    function getSourceCrl( $tLabel = NULL )
    {
        global $platform_id;
        global $_course;
        global $_courseTool;
        global $_gid;
        global $rootWeb;

        $baseServUrl = $rootWeb;
        $course_sys_code = $_course["sysCode"];

        if ( ! is_null( $tLabel ) )
        {
            $tool_name = $tLabel;
            $res = new Resolver($baseServUrl);
            $resource_id = $res->getResourceId($tool_name);
        }
        elseif ( isset( $_courseTool ) && isset( $_courseTool['label'] ) )
        {
            $tool_name = $_courseTool["label"];
            $res = new Resolver($baseServUrl);
            $resource_id = $res->getResourceId($tool_name);
        }
        else
        {
            $tool_name = '';
            $resource_id = '';
        }

        if ( $_gid )
        {
            $group = $_gid;
        }
        else
        {
            $group = NULL;
        }

        $crl_source = CRLTool::createCRL($platform_id , $course_sys_code , $tool_name  , $resource_id , $group  );

        return $crl_source;
    }

//-------------------------------------------------------------------------------------------------------
    
    /**
    * These functions are common to the
    * linker jpspan and popup.   
    *
    **/
    
    
   /**
    * initialize the variables of session
    *
    */    
    function linker_init_session(  )
    {
        $_SESSION['AttachmentList'] = array();
        $_SESSION['servAdd'] = array();
        $_SESSION['servDel'] = array();
    }
    
   /**
    * record the crl in the data base and erases the variables of sessions
    *
    * @param string tLable tool label if different form current tool
    * @return string an error message if the operation did not proceed suitably or
    *         a empty string if all it passed well 
    */    
    function linker_update( $tLabel = NULL )
    {
        global $jpspanEnabled;
        
        $crlSource = getSourceCrl( $tLabel );
        
        if ( $jpspanEnabled )
        {
            if ( isset( $_REQUEST['servAdd'] ) )
            {
                $tmpServAdd = array_map( 'urldecode', $_REQUEST['servAdd'] );
                $tmpServAdd = ( is_array( $tmpServAdd ) ) ? $tmpServAdd : array();
            }
            else // if ( ! isset( $_SESSION['servAdd'] ) )
            {
                $tmpServAdd = array();
            }
        
            if ( isset( $_REQUEST['servDel'] ) )
            {
                $tmpServDel = array_map( 'urldecode', $_REQUEST['servDel'] );
                $tmpServDel = ( is_array( $tmpServDel ) ) ? $tmpServDel : array();
            }
            else // if ( ! isset( $_SESSION['servDel'] ) )
            {
                $tmpServDel = array();
            }
        
            // to avoid links added after deletion to be ignored (bug #264)
        
            if ( ( isset( $tmpServAdd ) && is_array( $tmpServAdd ) )
                || ( isset( $tmpServDel ) && is_array( $tmpServDel ) ) )
            {
                if ( count( $tmpServAdd ) > 0 || count( $tmpServDel ) > 0 )
                {
                    foreach( $tmpServAdd as $addIndex => $addValue )
                    {
                        foreach( $tmpServDel as $delIndex => $delValue )
                        {
                            if ( $delValue == $addValue )
                            {
                                unset( $tmpServAdd[$addIndex] );
                                unset( $tmpServDel[$delIndex] );
                            }
                            else
                            {
                                continue;
                            }
                        }
                    }
                }
            
                $_SESSION['servAdd'] = $tmpServAdd;
                $_SESSION['servDel'] = $tmpServDel;
            }
        }
        
        $message = linker_update_attachament_list( $crlSource , $_SESSION['servAdd'] , $_SESSION['servDel'] );
        
        if( isset($_SESSION['servAdd'] ) )
        {
            $_SESSION['servAdd'] = array();
        }
        if( isset($_SESSION['servDel']) )
        {
            $_SESSION['servDel'] = array();
        }
        if( isset($_SESSION['AttachmentList']) )
        {
            $_SESSION['AttachmentList'] = array();
        }
        
        return $message;    
    }
    
    function linker_delete_resource( $tLabel = NULL )
    {
        $crlSource = getSourceCrl( $tLabel );
        
        linker_remove_ressource( $crlSource );
    }
    
    function linker_delete_all_tool_resources()
    {
        global $platform_id;
        global $_course;
        global $_courseTool;
        global $_gid;
        global $rootWeb;
        
        $group = ( $_gid ) ? $_gid : NULL;

        $toolCRL = CRLTool::createCRL($platform_id , $_course['sysCode'] , $_courseTool['label'] , '' , $group  );;
        
        linker_remove_all_tool_resources( $toolCRL );
    }

   /**
    * display the list of the resources which are related to a resource 
    *
    * @global $rootWeb
    * @param string tLable tool label if different form current tool
    */    
    function linker_display_resource( $tLabel = NULL )
    {
        global $rootWeb;
        
        $crlSource = getSourceCrl( $tLabel );
        $linkList = linker_get_link_list($crlSource);
        $baseServUrl = $rootWeb;
            
        if ( is_array($linkList) && count($linkList) > 0 )
        {
            //style=\"margin-top:1em;\"
            echo "<hr>\n";
            echo "<div  style=\"margin-bottom:2em;\">\n"; 
            
            foreach ( $linkList as $link )
            {
                $res = new Resolver($baseServUrl);
                   $url = $res->resolve($link["crl"]);
                $name = $link["title"];
                
                echo "<a href=\"".htmlspecialchars($url)."\">".htmlspecialchars($name)."</a><br>\n";
            }
            echo "</div>\n";
        }
    }
    
    /**
    *  
    *
    * @global $rootWeb
    * @param string tLable tool label if different form current tool
    */    
    function linker_email_resource( $tLabel = NULL )
    {
        global $rootWeb;
        
        $crlSource = getSourceCrl( $tLabel );
        $linkList = linker_get_link_list($crlSource);
        $baseServUrl = $rootWeb;
        
        $attachement = "";
        //$handle = fopen("/home/renaud/public_html/mail.txt", "a+");
            
        if ( is_array($linkList) && count($linkList) > 0 )
        {
            $attachement .= "\nAttachements : \n";
            
            foreach ( $linkList as $link )
            {
                $res = new Resolver($baseServUrl);
                   $url = $res->resolve($link["crl"]);
                $name = $link["title"];
                
                $attachement .= " < ".$name." > ".$url."\n";
            }
        }
        
        //fwrite($handle, $attachement);
        //fclose($handle);
        
        return $attachement;
    }
    
   /**
   * return the index of a tool
   *
   * @param $label the TLabel of a tool
   * @return  integer if the tlabel is found in the list of a tool or false
   */
    function get_tool_index($label)
    {
           global $_courseToolList;

           $indexTool = 0;
           foreach($_courseToolList as $toolTbl)
           {
               if($toolTbl["label"] == $label)
               {
                   return $indexTool;
               }
               $indexTool++;
           }

           return FALSE;
    }

   /**
   * return the name of a tool
   *
   * @param $label the TLabel of a tool
   * @return  integer if the tlabel is found in the list of a tool or false
   */
    function get_tool_name($course_sys_code,$label)
    {
         $courseToolList = get_course_tool_list($course_sys_code);
         $toolName = "";

         foreach($courseToolList as $toolTbl)
         {
                $name = $toolTbl["name"];
                $tLabel = $toolTbl["label"];

                if ($tLabel == $label)
                {
                     $toolName = $name;
                }
         }

         return  $toolName;
    }


    /**
    * return the title of a tool
    *
    * @param $elementCRLArray (array) an associative array containing the elements of a crl
    * @return string (string) the title of a tool
    * @global $_courseToolList
    */
    function get_toolname_title($elementCRLArray)
    {
        global $_courseToolList;

        $toolIndex = false;
        if( isset($elementCRLArray["tool_name"]) )
        {
            $toolIndex = get_tool_index($elementCRLArray["tool_name"]);
        }

        $title  = get_course_title($elementCRLArray["course_sys_code"]);

        if( isset($elementCRLArray["tool_name"]) && $elementCRLArray["tool_name"] == "CLEXT___")
        {
            $title .= " > ".$elementCRLArray["resource_id"];
        }
        else if( $toolIndex !== FALSE && $elementCRLArray["tool_name"] )
        {
            if( isset ($elementCRLArray["team"]) )
            {
                require_once('CLGRP___Resolver.php');
                $resolver = new CLGRP___Resolver("");
                $crl = CRLTool::createCRL($elementCRLArray['platform_id'],$elementCRLArray['course_sys_code'],'','',$elementCRLArray['team']);
                     
                $title = $resolver->getResourceName($crl); 
            }

            $name =  get_tool_name($elementCRLArray['course_sys_code'],$elementCRLArray["tool_name"]);
           // $name = $_courseToolList[$toolIndex]["name"];
            $title .=  " > ".$name;
        }

        return $title;
    }
    
    /**
    * return the complete Web address
    *
    * @return a string 
    */    
    function path()
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) 
            . "/../linker";
    }
?>