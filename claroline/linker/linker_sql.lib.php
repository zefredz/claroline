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

//    require_once dirname(__FILE__) . '/' . '../inc/claro_init_global.inc.php';
    
    // link allready exists return value 
    define( 'LINK_ALLREADY_EXISTS', -1);
    
    /**
     * Remove a resource and all its links
     * @param string resourceCRL CRL of the resource
     */
    function linker_remove_ressource( $resourceCRL )
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_links = $tbl_cdb_names['links'];
        $tbl_resources = $tbl_cdb_names['resources'];
        
        // 1. get resource id
        
        $get_resourceId = "SELECT `id` FROM `".$tbl_resources."` "
            . "WHERE `crl` = '" . addslashes( $resourceCRL ) . "'"
            ;
            
        $resourceId = claro_sql_query_get_single_value( $get_resourceId );
        
        // 2. delete resource itself
        
        $remove_resource = "DELETE FROM `".$tbl_resources."` "
            . "WHERE `crl` = '" . addslashes( $resourceCRL ) . "' "
            ;
            
        claro_sql_query( $remove_resource );
        
         // 3. delete links where $sourceCRL is destination or source

        $remove_links = "DELETE FROM `".$tbl_links."` "
            . "WHERE `src_id` = " . (int)$resourceId . " "
            . "OR `dest_id` = " . (int)$resourceId
            ;

        claro_sql_query( $remove_links );
    }
    
    /**
     * Remove all resources and all their links for a tool
     * @param string toolCRL CRL of the tool
     */
    function linker_remove_all_tool_resources( $toolCRL )
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_links = $tbl_cdb_names['links'];
        $tbl_resources = $tbl_cdb_names['resources'];
        
        // 1. get resource id for the tool expect the tool itself

        $get_resourceId = "SELECT `id` FROM `".$tbl_resources."` "
            . "WHERE `crl` LIKE '%" . addslashes( $toolCRL ) . "/%'"
            ;

        $resourceIdList = claro_sql_query_fetch_all( $get_resourceId );

        // 2. delete links where $resourceCRL is destination or source
        
        foreach( $resourceIdList as $resource )
        {

            $remove_links = "DELETE FROM `".$tbl_links."` "
                . "WHERE `src_id` = " . $resource['id'] . " "
                . "OR `dest_id` = " . $resource['id']
                ;

            claro_sql_query( $remove_links );
        }

        // 3. delete resources themselves but not the tool resource itself

        $remove_resources = "DELETE FROM `".$tbl_resources."` "
            . "WHERE `crl` LIKE '%" . addslashes( $toolCRL ) . "/%' "
            ;

        claro_sql_query( $remove_resources );
    }
     
    /**
     * update of the dB of links 
     *
     * @param  $crlSource (string) a crl of a resource
     * @param  $tblToAdd  (array) with links which will be to add in the dB
     * @param  $tblToDel  (array) with links which will be to add in the dB
     * @return  string an error message if error else nothing
     */
    function linker_update_attachament_list( $crlSource , $tblToAdd , $tblToDel )
    {
        $msg='';
        
        if( is_array($tblToAdd) )
        {
            $ret = linker_insert_link_list( $crlSource , $tblToAdd );
            
            if( isset($ret) && $ret === FALSE )
            {
				// workaround for bug #303
				// TODO : FIXME
                // $msg .= 'error in the insert of the link';
            }    
        }
        
        if( is_array($tblToDel) )
        {
            $ret = linker_delete_link_list( $crlSource , $tblToDel );
            
            if( isset($ret) && $ret === FALSE )
            {
				// workaround for bug #303
				// TODO : FIXME
                // $msg .= 'error in the delete of the link';
            }
        }
        
        return $msg;
    }

    /**
     * insert link list into database 
     *
     * @param  $crlSource (string) the crl of the source of the link
     * @param  $tblToAdd (array) table of database
     * @return  a boolean (boolean) true if all links have been added else false
     */
    function linker_insert_link_list( $crlSource , $tblToAdd )
    {
        $numberOfLinksInserted = 0;
        
        foreach($tblToAdd as $item)
        {    
            $numberOfLinksInserted += linker_insert_link( $crlSource , $item );
            /* not yet used (for maintenance service)
            linker_insert_link_in_main_db( $crlSource , $item );*/
        }
        
        if( count($tblToAdd) == $numberOfLinksInserted )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * delete link list into database 
     *
     * @param  $crlSource (string) the crl of the source of the link
     * @param  $tblToDel (array) table of database
     * @return  a boolean (boolean) true if all links have been deleted else false
     */
    function linker_delete_link_list( $crlSource , $tblToDel )
    {
        $numberOfLinksDeleted = 0;
        
        foreach($tblToDel as $item)
        {    
            $numberOfLinksDeleted += linker_delete_link(    $crlSource , $item );
        }
        
        if( count($tblToDel) == $numberOfLinksDeleted )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * Create a link with the crl source and destination
     *
     * @param  $crlSource string a crl
     * @param  $crlDestination string a crl
     * @return boolean TRUE if the link is create 
     */
    function linker_insert_link( $crlSource , $crlDestination , $isMain = FALSE )
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_links = $tbl_cdb_names['links'];
            
        $source_id = linker_insert_resource($crlSource);
        $destination_id = linker_insert_resource($crlDestination);
       
        //check if the link already exist
        $sql = "SELECT `id` 
                FROM `" . $tbl_links . "` 
                WHERE `src_id` = " . (int)$source_id . "
                  AND `dest_id` = " . (int)$destination_id ;
        $result = claro_sql_query_fetch_all($sql); 
        
        if( isset($result[0]) )
        { 
            $isLink = $result[0];
        }
         
        if( !isset($isLink) )
        {
           $sql = "INSERT INTO `" . $tbl_links . "` (`id`, `src_id` , `dest_id` ) "
           .      "VALUES ('', '" . (int)$source_id . "', '" . (int)$destination_id . "')";
           if ( claro_sql_query_affected_rows($sql) == 1  )
           {
                   return 1;
           }  
           else
           {
                return 0;
           }
        }
        else
        {
            return LINK_ALLREADY_EXISTS;
        } 
    }
    
    /**
    * NOT YET USED (for maintenance service)
    * Create a link with the couse code source and destination
    *
    * @param  $crlSource string a crl
    * @param  $crlDestination string a crl
    * @return a boolean TRUE if the link is create 
    */
    function linker_insert_link_in_main_db( $crlSource , $crlDestination )
    {
        $tbl_cdb_names = claro_sql_get_main_tbl();
        $tbl_links = $tbl_cdb_names['links'];
             
        $elementCrlSource = CRLTool::parseCRL($crlSource);
        $elementCrlDestination = CRLTool::parseCRL($crlDestination);
        
        $id = NULL;
        
        // check if the course sys code is différent
        if( $elementCrlSource['course_sys_code'] != $elementCrlDestination['course_sys_code'] ) 
        {
            $source_id = linker_insert_course_in_main_db( $elementCrlSource['course_sys_code'] );
            $destination_id = linker_insert_course_in_main_db( $elementCrlDestination['course_sys_code'] );
            
            //check if the link already exist
            $sql = 'SELECT `id` 
                    FROM `'.$tbl_links.'` 
                    WHERE `src_id` = '. (int)$source_id.' 
                      AND `dest_id` = '. (int)$destination_id ;
            $isExist = claro_sql_query_get_single_value($sql);
             
            if( $isExist == FALSE )
            {
                $sql = "INSERT INTO `".$tbl_links."` (`id`, `src_id` , `dest_id` ) 
                        VALUES ('', '" . (int)$source_id . "', '" . (int)$destination_id . "')";
                $id = claro_sql_query_insert_id($sql);
            }   
            else
            {
                $id = LINK_ALLREADY_EXISTS;
            }          
        }
        
        return $id;    
    }
    
    /**
     * Delete a link with the crl source and destination
     *
     * @param  $crlSource string a crl
     * @param  $crlDestination string a crl
     * @return a boolean TRUE if the link is create 
     */
    function linker_delete_link($crlSource,$crlDestination)
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_links = $tbl_cdb_names['links'];
        $tbl_resources = $tbl_cdb_names['resources'];
        
        $sql = 'SELECT `id` 
                FROM `'.$tbl_resources.'` 
                WHERE `crl` = "'.addslashes($crlSource).'"';
        $result = claro_sql_query_fetch_all($sql);
        
        if( isset($result[0]) )
        {
            $sourceId = $result[0]['id'];
        }    
        
        $sql = 'SELECT `id` 
                FROM `'.$tbl_resources.'` 
                WHERE `crl` = "'.addslashes($crlDestination).'"';
        $result = claro_sql_query_fetch_all($sql);
        
        if( isset($result[0]) )
        {
            $destId = $result[0]['id'];
        }    
        
        if( isset($sourceId) && isset($destId) )
        {
            $sql = "DELETE FROM `".$tbl_links."` 
                    WHERE `dest_id` = ". (int)$destId." 
                      AND `src_id` = ". (int)$sourceId;             
        
            if ( claro_sql_query_affected_rows($sql) == 1  )
            {
                return 1;
            } 
            else
            {
                return 0;
            }
        }
        else
        {
            return 0; 
        }
     }
    
    /**
     * insert a resource into the course table
     *
     * @param   $resource string a crl 
     * @return  integer the idenfiant of the resource
     */
    function linker_insert_resource($resource)
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_resources = $tbl_cdb_names['resources'];
        
        $sql = "SELECT `id` FROM `" . $tbl_resources . "`
                WHERE `crl` = '".addslashes($resource) . "'";
        $result = claro_sql_query_fetch_all($sql);
        
        if( isset($result[0]) )
        {
            $ressourceInfo = $result[0];
        }
        
        //check if the crl of the source of the link exist in the table of dB
        if( !isset($ressourceInfo) )
        {    
            $res = new Resolver("");
            $title = $res->getResourceName($resource);
            
            $sql = "INSERT INTO `".$tbl_resources."` (`id`, `crl`, `title`) 
                    VALUES ('', '".addslashes($resource)."' , '".addslashes($title)."')";
            $resource_id = claro_sql_query_insert_id($sql);
        }
        else
        {
            $resource_id = $ressourceInfo['id'];    
        } 
            
        return $resource_id;
    } 
    
    /**
    * get the id of a resource
    *
    * @param   $resource string a crl 
    * @return  integer the idenfiant of the resource
    */
    /*function linker_get_id_resource($resource)
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_resources = $tbl_cdb_names['resources'];
        
        $sql = 'SELECT `id` FROM `'.$tbl_resources.'` WHERE `crl` LIKE "'.addslashes($resource).'"';
        $result = claro_sql_query_fetch_all($sql);    
        
        if( isset($result[0]) )
        {
            return $result[0];
        }
        else
        {
            return FALSE;
        }
    }*/
    
    /**
     * NOT YET USED (for maintenance service)
     * insert a resource into the main table
     *
     * @param   $resource string a course sys code 
     * @return  integer the idenfiant of the resource
     */
    function linker_insert_course_in_main_db($course_sys_code)
    {
        $tbl_cdb_names = claro_sql_get_main_tbl();
        $tbl_resources = $tbl_cdb_names['resources'];            

        $sql = 'SELECT `id` 
                FROM `'.$tbl_resources.'` 
                WHERE `course` = "'. addslashes($course_sys_code).'"';
        $result = claro_sql_query_get_single_value($sql);
         
        if( $result == FALSE )
        {
            $sql = "INSERT INTO `".$tbl_resources."` (`id`, `course`) 
                    VALUES ('', '" . addslashes($course_sys_code) . "')";
            $resource_id = claro_sql_query_insert_id($sql);        
            
            return $resource_id;
        }
        else
        {    
            return $result;    
        }

    }     
    
    /**
     * listing of the link for a crl source
     *
     * @param   $crl_source string a crl 
     * @return  (array) an array of crl
     */
    function linker_get_link_list( $crl_source )
    {  
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_links = $tbl_cdb_names['links'];
        $tbl_resources = $tbl_cdb_names['resources'];

        $sql = "SELECT `dest`.`crl`,`dest`.`title` 
                FROM `".$tbl_links."` as `l`,`".$tbl_resources."` as `dest`,`".$tbl_resources."` as `src` 
                WHERE `src`.`crl` = '". addslashes($crl_source) ."' 
                  AND `dest`.`id` = `l`.`dest_id` 
                  AND `src`.`id` = `l`.`src_id`";
        $linkList = claro_sql_query_fetch_all($sql); 
         
        return $linkList;
    }
    
    /**
    * NOT YET USED 
    * create table for the linker
    * 
    * @global $mainTblPrefix
    */
    function linker_create_table()
    {    
         global $mainTblPrefix;

        // main table -> for maintenance 
        $sql = "CREATE TABLE IF NOT EXISTS `" . $mainTblPrefix . "links` (
                    `id` int(11) NOT NULL auto_increment,
                      `src_id` int(11) NOT NULL default '0',
                      `dest_id` int(11) NOT NULL default '0',
                      `creation_time` timestamp(14) NOT NULL,
                      PRIMARY KEY  (`id`)
                    ) TYPE=MyISAM PACK_KEYS=0";
        echo 'creating ' . $mainTblPrefix . 'links... ';
        claro_sql_query($sql);
        echo 'done<br/>' . "\n";
               
        $sql = "CREATE TABLE IF NOT EXISTS `".$mainTblPrefix."resources` (
                    `id` int(11) NOT NULL auto_increment,
                   `course` varchar(40) NOT NULL,
                   PRIMARY KEY  (`id`)
                  ) TYPE=MyISAM PACK_KEYS=0";
        echo 'creating ' . $mainTblPrefix . 'resources... ';
        claro_sql_query($sql);
        echo "done<br/><br/>\n";
        
        // course table
        $tbl_db_name = claro_sql_get_main_tbl();
        $tbl_course = $tbl_db_name['course'];
         
        $sql = 'SELECT `dbName` FROM `'.$tbl_course.'`';
        $dbCoursesName = claro_sql_query_fetch_all_cols($sql);

        foreach($dbCoursesName['dbName'] as $dbname )
        {
            $sql = "CREATE TABLE IF NOT EXISTS `".$dbname."_links` (
                    `id` int(11) NOT NULL auto_increment,
                    `src_id` int(11) NOT NULL default '0',
                    `dest_id` int(11) NOT NULL default '0',
                    `creation_time` timestamp(14) NOT NULL,
                      PRIMARY KEY  (`id`)
                    ) TYPE=MyISAM PACK_KEYS=0";
            echo 'creating ' . $dbname . '_links... ';
               claro_sql_query($sql);
               echo "done<br/>\n";
               
               $sql = "CREATE TABLE IF NOT EXISTS `".$dbname."_resources` (
                      `id` int(11) NOT NULL auto_increment,
                      `crl` text NOT NULL,
                      `title` text NOT NULL,
                      PRIMARY KEY  (`id`)
                     ) TYPE=MyISAM PACK_KEYS=0";
            echo 'creating '.$dbname.'_resources... ';
               claro_sql_query($sql);
               echo 'done<br/>' . "\n";
         }
    }
?>
