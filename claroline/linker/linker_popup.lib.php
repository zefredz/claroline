<?php // $Id$
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

   /**
    * linker_popup.lib
    * @package CLLINKER
    * is a lib of function for the linker popup.  
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    **/


    /**
    * load the Javascript which  popup.  will be necessary 
    * to the execution of popup.  
    *
    * @global array htmlHeadXtra
    */
    function linker_html_head_xtra()
    {
        global $htmlHeadXtra;

        $htmlHeadXtra[] = 
            "<SCRIPT type=\"text/javascript\">
            function popup(page) 
            {
                   window.open(page, 'linker', 'resizable=yes, location=no, width=640, height=480, menubar=no, status=no, scrollbars=yes, menubar=no');
            }
              </SCRIPT>";        
    }
    

    /**
    * the dislay of the linker 
    *
    * @param $extraGetVar integer who is id of a resource
    * @param $tLabel tlabel of a tool
    * @global get_lang('LinkerResourceAttachment')
    */    
    function linker_set_display( $extraGetVar = false, $tLabel = NULL )
    {
        $url = "../linker/linker_popup.inc.php";
        
        if( $extraGetVar !== false )
        {
            $url .= "?id=".$extraGetVar;
        }
        
        if ( ! is_null( $tLabel ) )
        {
            if ( strstr( $url, "?" ) != false )
            {
                $url .= "&amp;linkerTLabel=$tLabel";
            }
            else
            {
                $url .= "?linkerTLabel=$tLabel";
            }
        }
        
        echo "<br />\n";
        echo "<A href=\"javascript:popup('"
            . $url."')\">".get_lang('LinkerResourceAttachment')
            ."</A><br /><br />\n";
    }

//--------------------------------------------------------------------------------------------------------
    
    class AttachmentList
    {    
        /**
        * constructor
        *
        * init the array in the session 
        **/
        function AttachmentList()
        {
            if( !isset ( $_SESSION['AttachmentList'] ) )
               {    
                $_SESSION['AttachmentList'] = array();
                $_SESSION['AttachmentList']['crl'] = array();
                $_SESSION['AttachmentList']['title'] = array();
                $_SESSION['servAdd'] = array();
                $_SESSION['servDel'] = array();
               }    
        }

        /**
        * remove the crl of session array
        *
        * @param $crl (string) a crl
        * @return boolean (boolean) true if the crl is found in the list of attachament
        * @throws E_USER_ERROR if the list of attachament is not valid (empty)
        **/
        function removeItem( $crl )
        {
            if( is_array($_SESSION['AttachmentList']) && count($_SESSION['AttachmentList']) > 0 )
               {                
                $ret = $this->_removeCrlFromShopping( $crl , $_SESSION['AttachmentList'] );

                if( !$this->_contains( $crl , $_SESSION['servAdd']) )
                {
                    $_SESSION['servDel'][]= $crl;
                }    
                else
                {
                    $this->_removeCrlFromArray( $crl , $_SESSION['servAdd']);        
                }        
            
                return $ret;
                
            }
            else
            {            
                trigger_error("Error: the list of attachament is not valid (empty)",E_USER_ERROR);
                return false;
            }
        }
        
        /**
        * add the crl of session array
        *
        * @param $crl (string) a crl 
        * @return boolean false if the crl is already in the array
        **/
        function addItem($crl)
        {
            if( !$this->_contains( $crl , $_SESSION['AttachmentList']['crl'] ) )
            {
                $res = new Resolver("");
                   $title = $res->getResourceName($crl);
                
                $_SESSION['AttachmentList']['crl'][] = $crl;
                $_SESSION['AttachmentList']['title'][] = $title;
                $_SESSION['servAdd'][] = $crl;
                
                if( $this->_contains( $crl , $_SESSION['servDel']) )
                {
                    $this->_removeCrlFromArray( $crl , $_SESSION['servDel']);
                }    
                
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        
        /**
        * return the AttachmentList
        *
        * @return array an array of strings (crl)
        **/
        function getAttachmentList()
        {
            return $_SESSION['AttachmentList'];
        }
        
        /**
        * load the crl are already in database
        *
        * @global $crlSource
        */
        function initAttachmentList()
        {
            global $crlSource;

            if( isset($crlSource) 
                // do not reload links in database if linker 
                // was already opened for current resource
                && ( is_array($_SESSION["servAdd"]) && count($_SESSION["servAdd"]) == 0 )
                && ( is_array($_SESSION["servDel"]) && count($_SESSION["servDel"]) == 0 ))
            {
                $_SESSION['AttachmentList'] = array();
                $crlDBList = linker_get_link_list($crlSource);
            
                if( (is_array($crlDBList)) && (count($crlDBList) > 0) )
                {
                    foreach($crlDBList as $item)
                    {
                        if( !$this->_contains( $item , $_SESSION['AttachmentList'] ) )
                        {
                            $_SESSION['AttachmentList']["crl"][] = $item['crl'];
                            $_SESSION['AttachmentList']["title"][] = $item['title'];
                        }
                    }    
                }
            }
        }
    
        /**
        * check if the array contains a crl
        *
        * @param $crl (string) a valid crl  
        * @param $tbl (array) the array to check
        * @return boolean (boolean) true if the array is an array
        **/
        function _contains( $crl , $tbl )    
        {
               if(  is_array( $tbl ) && count($tbl) > 0 )
               {
                   if( in_array ( $crl , $tbl ) )
                {
                    return TRUE;
                }    
                else
                {       
                    return FALSE;
                }
            }    
            else
            {
                return FALSE;
            }       
        }
        
        /**
        * remove the crl   
        *
        * @param $crl (string) a valid crl  
        * @param $tbl (array) a reference to the array to check
        * @return 
        **/
        /* TODO MOVE IN CLARO_MAIN_LIB ???*/
        function _removeCrlFromArray( $crl , &$tbl )
        {
            $temp = array();
            $passed = FALSE;
            
            foreach($tbl as $itemTbl )
            {
                if( $itemTbl != $crl)
                {
                    $temp[] = $itemTbl;    
                }
                else
                {
                    $passed = TRUE;    
                }            
            }
            $tbl = $temp;
            
            return $passed;
        }
        
        /**
        * remove the crl from the AttachmentList  
        *
        * @param $crl (string) a valid crl  
        * @param $tbl (array) a reference to the array to check
        * @return 
        **/
        function _removeCrlFromShopping( $crl, &$tbl )
        {
            $temp = array();
            $temp['crl'] = array();
            $temp['title'] = array();
            $passed = FALSE;
            
            $arraySize = count( $tbl['crl'] );
            
            for( $i = 0; $i < $arraySize; $i++ )
            {
                if( $tbl['crl'][$i] != $crl)
                {
                    $temp['crl'][] = $tbl['crl'][$i];    
                    $temp['title'][] = $tbl['title'][$i];
                }
                else
                {
                    $passed = TRUE;    
                }            
            }
            
            $tbl = $temp;
            
            return $passed;
        }
    }    
?>
