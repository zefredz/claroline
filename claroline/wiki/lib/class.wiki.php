<?php // $Id$
     
    // vim: expandtab sw=4 ts=4 sts=4:
     
    if (strtolower(basename($_SERVER['PHP_SELF'] ) )
        == strtolower(basename(__FILE__ ) ) )
    {
        die("This file cannot be accessed directly! Include it in your script instead!");
    }
     
    /**
     * @version CLAROLINE 1.7
     *
     * @copyright 2001-2005 Universite catholique de Louvain (UCL)
     *
     * @license GENERAL PUBLIC LICENSE (GPL)
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */
     
    require_once dirname(__FILE__) . "/class.dbconnection.php";
    require_once dirname(__FILE__) . "/class.wikipage.php";

    // Error codes
    !defined( "WIKI_NO_TITLE_ERROR" ) && define( "WIKI_NO_TITLE_ERROR", "Missing title" );
    !defined( "WIKI_NO_TITLE_ERRNO" ) && define( "WIKI_NO_TITLE_ERRNO", 1 );
    !defined( "WIKI_ALREADY_EXISTS_ERROR" ) && define( "WIKI_ALREADY_EXISTS_ERROR", "Wiki already exists" );
    !defined( "WIKI_ALREADY_EXISTS_ERRNO" ) && define( "WIKI_ALREADY_EXISTS_ERRNO", 2 );
    !defined( "WIKI_CANNOT_BE_UPDATED_ERROR" ) && define( "WIKI_CANNOT_BE_UPDATED_ERROR", "Wiki cannot be updated" );
    !defined( "WIKI_CANNOT_BE_UPDATED_ERRNO" ) && define( "WIKI_CANNOT_BE_UPDATED_ERRNO", 3 );
    !defined( "WIKI_NOT_FOUND_ERROR" ) && define( "WIKI_NOT_FOUND_ERROR", "Wiki not found" );
    !defined( "WIKI_NOT_FOUND_ERRNO" ) && define( "WIKI_NOT_FOUND_ERRNO", 4 );
     
    class Wiki
    {
        var $wikiId;
        var $title;
        var $desc;
        var $accessControlList;
        var $groupId;
        
        var $con;
        
        // default configuration
        var $config = array(
                'tbl_wiki_pages' => 'wiki_pages',
                'tbl_wiki_pages_content' => 'wiki_pages_content',
                'tbl_wiki_properties' => 'wiki_properties',
                'tbl_wiki_acls' => 'wiki_acls'
            );

        // error handling
        var $error = '';
        var $errno = 0;
        
        function Wiki( &$con, $config = null )
        {
            if ( is_array( $config ) )
            {
                $this->config = array_merge( $this->config, $config );
            }
            $this->con =& $con;
            
            $this->wikiId = 0;
        }
        
        // accessors

        function setTitle( $wikiTitle )
        {
            $this->title = $wikiTitle;
        }

        function getTitle()
        {
            return $this->title;
        }

        function setDescription( $wikiDesc = '' )
        {
            $this->desc = $wikiDesc;
        }

        function getDescription()
        {
            return $this->desc;
        }

        function setACL( $accessControlList )
        {
            $this->accessControlList = $accessControlList;
        }

        function getACL()
        {
            return $this->accessControlList;
        }

        function setGroupId( $groupId )
        {
            $this->groupId = $groupId;
        }

        function getGroupId()
        {
            return $this->groupId;
        }

        function setWikiId( $wikiId )
        {
            $this->wikiId = $wikiId;
        }
        
        function getWikiId()
        {
            return $this->wikiId;
        }
        
        // load and save

        function load( $wikiId )
        {
            if( $this->wikiIdExists($wikiId) )
            {
                $this->loadProperties( $wikiId );
                $this->loadACL( $wikiId );
            }
            else
            {
                $this->setError( WIKI_NOT_FOUND_ERROR, WIKI_NOT_FOUND_ERRNO );
            }
        }
        
        function loadProperties( $wikiId )
        {
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id`, `title`, `description`, `group_id` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `id` = ".$wikiId
                ;
                
            $result = $this->con->getRowFromQuery( $sql );

            $this->setWikiId( $result['id'] );
            $this->setTitle( $result['title'] );
            $this->setDescription($result['description']);
            $this->setGroupId($result['group_id']);
        }
        
        function loadACL( $wikiId )
        {
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `flag`, `value` "
                . "FROM `".$this->config['tbl_wiki_acls']."` "
                . "WHERE `wiki_id` = " . $wikiId
                ;

            $result = $this->con->getAllRowsFromQuery( $sql );
            
            $acl = array();
            
            if( is_array( $result ) )
            {
                foreach ( $result as $row )
                {
                    $value = ( $row['value'] == 'true' ) ? true : false;
                    $acl[$row['flag']] = $value;
                }
            }

            $this->setACL( $acl );
        }

        function save()
        {
            $this->saveProperties();
            
            $this->saveACL();
            
            if ( $this->hasError() )
            {
                return 0;
            }
            else
            {
                return $this->wikiId;
            }
        }
        
        function saveACL()
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            

            $sql = "SELECT `wiki_id` FROM `"
                . $this->config['tbl_wiki_acls']."` "
                . "WHERE `wiki_id` = " . $this->getWikiId()
                ;
                    
            if ( $this->con->queryReturnsResult( $sql ) )
            {
                $acl = $this->getACL();
                    
                foreach ( $acl as $flag => $value )
                {
                    $value = ( $value == false ) ? 'false' : 'true';

                    $sql = "UPDATE `" . $this->config['tbl_wiki_acls'] . "` "
                        . "SET `value`='" . $value . "'"
                        . "WHERE `wiki_id`=" . $this->getWikiId() . " "
                        . "AND `flag`='" . $flag . "'"
                        ;

                    $this->con->executeQuery( $sql );
                }
            }
            else
            {
                $acl = $this->getACL();

                foreach ( $acl as $flag => $value )
                {
                    $value = ( $value == false ) ? 'false' : 'true';

                    $sql = "INSERT INTO "
                        . "`".$this->config['tbl_wiki_acls']."`"
                        . "("
                        . "`wiki_id`, `flag`, `value`"
                        . ") "
                        . "VALUES("
                        . $this->getWikiId() . ","
                        . "'" . $flag . "',"
                        . "'" . $value . "'"
                        . ")"
                        ;

                    $this->con->executeQuery( $sql );
                }
            }
        }
        
        function saveProperties()
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }
            
            if ( $this->getWikiId() === 0 )
            {
                /* if ($this->wikiExists($this->getTitle()))
                {
                    $this->setError(
                        WIKI_ALREADY_EXISTS_ERROR
                        , WIKI_ALREADY_EXISTS_ERRNO
                        );

                    return;
                } */
                // INSERT PROPERTIES
                $sql = "INSERT INTO `"
                    . $this->config['tbl_wiki_properties']
                    . "`("
                    . "`title`,`description`,`group_id`"
                    . ") "
                    . "VALUES("
                    . "'".$this->getTitle()."', "
                    . "'" . $this->getDescription() . "', "
                    . "'" . $this->getGroupId() . "'"
                    . ")"
                    ;
                    
                // GET WIKIID
                $this->con->executeQuery( $sql );

                if ( ! $this->con->hasError() )
                {
                    $wikiId = $this->con->getLastInsertId();
                    $this->setWikiId( $wikiId );
                }
            }
            else
            {
                // UPDATE PROPERTIES
                $sql = "UPDATE `" . $this->config['tbl_wiki_properties'] . "` "
                    . "SET "
                    . "`title`='".$this->getTitle()."', "
                    . "`description`='".$this->getDescription()."', "
                    . "`group_id`='".$this->getGroupId()."' "
                    . "WHERE `id`=" . $this->getWikiId()
                    ;
                    
                $this->con->executeQuery( $sql );
            }
        }
        
        // Page methods

        function pageExists( $title )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id` "
                . "FROM `".$this->config['tbl_wiki_pages']."` "
                . "WHERE `title` = '".$title."' "
                . "AND `wiki_id` = " . $this->wikiId
                ;

            return $this->con->queryReturnsResult( $sql );
        }
        
        function wikiExists( $title )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `title` = '".$title."'"
                ;

            return $this->con->queryReturnsResult( $sql );
        }
        
        function wikiIdExists( $id )
        {
            // reconnect if needed
            if ( ! $this->con->isConnected() )
            {
                $this->con->connect();
            }

            $sql = "SELECT `id` "
                . "FROM `".$this->config['tbl_wiki_properties']."` "
                . "WHERE `id` = '".$id."'"
                ;

            return $this->con->queryReturnsResult( $sql );
        }
        
        // error handling

        function setError( $errmsg = '', $errno = 0 )
        {
            $this->error = ($errmsg != '') ? $errmsg : 'Unknown error';
            $this->errno = $errno;
        }

        function getError()
        {
            if ( $this->con->hasError() )
            {
                return $this->con->getError();
            }
            else if ($this->error != '')
            {
                $errno = $this->errno;
                $error = $this->error;
                $this->error = '';
                $this->errno = 0;
                return $errno.' - '.$error;
            }
            else
            {
                return false;
            }
        }

        function hasError()
        {
            return ( $this->error != '' ) || $this->con->hasError();
        }
    }
?>
