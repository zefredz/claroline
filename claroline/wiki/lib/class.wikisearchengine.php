<?php // $Id$
     
    // vim: expandtab sw=4 ts=4 sts=4:
     
    /**
     * CLAROLINE
     *
     * @version 1.8 $Revision$
     *
     * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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
     
    define ( "CLWIKI_SEARCH_ANY", "CLWIKI_SEARCH_ANY" );
    define ( "CLWIKI_SEARCH_ALL", "CLWIKI_SEARCH_ALL" );
    define ( "CLWIKI_SEARCH_EXP", "CLWIKI_SEARCH_EXP" );
    
    /**
     * Search engine for the Wiki
     */
    class WikiSearchEngine
    {
        var $connection = null;
        var $config = array(
            'tbl_wiki_pages' => 'wiki_pages',
            'tbl_wiki_pages_content' => 'wiki_pages_content',
            'tbl_wiki_properties' => 'wiki_properties',
            'tbl_wiki_acls' => 'wiki_acls'
        );
        
        /**
         * Constructor
         * @param DatabaseConnection connection
         * @param Array config
         */
        function WikiSearchEngine( &$connection, $config )
        {
            if ( is_array( $config ) )
            {
                $this->config = array_merge( $this->config, $config );
            }
            
            $this->connection =& $connection;
        }
        
        /**
         * Generate search string for a given pattern in wiki pages
         * @param String pattern
         * @param Const mode
         * @return String
         */
        function makePageSearchQuery( $pattern, $mode = CLWIKI_SEARCH_ANY )
        {
            # FIXME Duplicate code
            $pattern = addslashes( $pattern );
            $pattern = str_replace('_', '\_', $pattern);
            $pattern = str_replace('%', '\%', $pattern);
            $pattern = str_replace('?', '_' , $pattern);
            $pattern = str_replace('*', '%' , $pattern);
            
            switch( $mode )
            {
                case CLWIKI_SEARCH_ALL:
                {
                    $impl = "AND";
                    $keywords = preg_split( '~\s~', $pattern );
                    break;
                }
                case CLWIKI_SEARCH_EXP:
                {
                    $impl = "";
                    $keywords = array( $pattern );
                    break;
                }
                case CLWIKI_SEARCH_ANY:
                default:
                {
                    $impl = "OR";
                    $keywords = preg_split( '~\s~', $pattern );
                    break;
                }
            }
            
            $searchTitleArr = array();
            $searchPageArr = array();
            
            foreach ( $keywords as $keyword )
            {
                $searchTitleArr[] = " p.`title` LIKE '%".$keyword."%' ";
                $searchPageArr[] = " c.`content` LIKE '%".$keyword."%' ";
            }
            
            $searchTitle = implode ( $impl, $searchTitleArr );
            if ( count ( $searchTitleArr ) > 1 )
            {
                $searchTitle = " ( " . $searchTitle . ") ";
            }
            
            $searchPage = implode ( $impl, $searchPageArr );
            if ( count ( $searchPageArr ) > 1 )
            {
                $searchPage = " ( " . $searchPage . ") ";
            }
            
            $searchStr = "( c.`id` = p.`last_version` AND " . $searchTitle . " ) OR "
                . "( c.`id` = p.`last_version` AND " . $searchPage . " )"
                ;
            
            return "($searchStr)";
        }
        
        function makeWikiPropertiesSearchQuery( $pattern, $mode = CLWIKI_SEARCH_ANY )
        {
            # FIXME code duplication !!!!
            $pattern = addslashes( $pattern );
            $pattern = str_replace('_', '\_', $pattern);
            $pattern = str_replace('%', '\%', $pattern);
            $pattern = str_replace('?', '_' , $pattern);
            $pattern = str_replace('*', '%' , $pattern);
            
            switch( $mode )
            {
                case CLWIKI_SEARCH_ALL:
                {
                    $impl = "AND";
                    $keywords = preg_split( '~\s~', $pattern );
                    break;
                }
                case CLWIKI_SEARCH_EXP:
                {
                    $impl = "";
                    $keywords = array( $pattern );
                    break;
                }
                case CLWIKI_SEARCH_ANY:
                default:
                {
                    $impl = "OR";
                    $keywords = preg_split( '~\s~', $pattern );
                    break;
                }
            }
            
            $searchWikiArr = array();
            
            foreach ( $keywords as $keyword )
            {
                $searchTitleArr[] = " (w.`title` LIKE '%".$keyword."%' "
                    . "OR w.`description` LIKE '%".$keyword."%') "
                    ;
            }
            
            $searchStr = implode ( $impl, $searchTitleArr );
            
            return "($searchStr)";
        }
        
        /**
         * Search for a given pattern in Wiki pages in a given Wiki
         * @param int wikiId
         * @param String pattern
         * @param Const mode
         * @return Array of Wiki pages
         */
        function searchInWiki( $wikiId, $pattern, $mode = CLWIKI_SEARCH_ANY )
        {
            if ( ! $this->connection->isConnected() )
            {
                $this->connection->connect();
            }
            
            $searchStr = WikiSearchEngine::makePageSearchQuery( $pattern, $mode );
            
            $sql = "SELECT p.`id`, p.`wiki_id`, p.`title`, c.`content` "
                . "FROM `"
                . $this->config['tbl_wiki_pages']."` AS p, `"
                . $this->config['tbl_wiki_pages_content']."` AS c "
                . "WHERE p.`wiki_id` = " . (int) $wikiId 
                . " AND " . $searchStr
                ;
                
            $ret = $this->connection->getAllRowsFromQuery( $sql );
            
            if ( $this->connection->hasError() )
            {
                return false;
            }
            else
            {
                return $ret;
            }
        }
        
        /**
         * Search for a given pattern in Wiki pages in a given Wiki, light version
         * @param int wikiId
         * @param String pattern
         * @param Const mode
         * @return Array of Wiki pages ids and titles
         */
        function lightSearchInWiki( $wikiId, $pattern, $mode = CLWIKI_SEARCH_ANY )
        {
            if ( ! $this->connection->isConnected() )
            {
                $this->connection->connect();
            }
            
            $searchStr = WikiSearchEngine::makePageSearchQuery( $pattern, $mode );
            
            $sql = "SELECT p.`id`, p.`title` "
                . "FROM `"
                . $this->config['tbl_wiki_pages']."` AS p, `"
                . $this->config['tbl_wiki_pages_content']."` AS c "
                . "WHERE p.`wiki_id` = " . (int) $wikiId 
                . " AND " . $searchStr
                ;
                
            $ret = $this->connection->getAllRowsFromQuery( $sql );
            
            if ( $this->connection->hasError() )
            {
                return false;
            }
            else
            {
                return $ret;
            }
        }
        
        /**
         * Search for a given pattern in all Wiki pages
         * @param String pattern
         * @param int groupId (default null) FIXME magic value !
         * @param Const mode
         * @return Array of Wiki properties
         */
        function searchAllWiki( $pattern, $groupId = null, $mode = CLWIKI_SEARCH_ANY )
        {
            if ( ! $this->connection->isConnected() )
            {
                $this->connection->connect();
            }
            
            $ret = array();
            $wikiList = array();
            
            $searchPageStr = WikiSearchEngine::makePageSearchQuery( $pattern, $mode );
            
            $groupStr = (! is_null( $groupId ) ) 
                ? "( w.`group_id` = " . (int) $groupId . " ) AND" 
                : ""
                ;
                
            $searchWikiStr = WikiSearchEngine::makeWikiPropertiesSearchQuery( $pattern, $mode );
            
            $sql = "SELECT DISTINCT w.`id`, w.`title`, w.`description` "
                . "FROM `"
                . $this->config['tbl_wiki_properties']."` AS w, `"
                . $this->config['tbl_wiki_pages']."` AS p, `"
                . $this->config['tbl_wiki_pages_content']."` AS c "
                . "WHERE " . $groupStr . " "
                . $searchPageStr . " "
                . " OR " . $searchWikiStr . " "
                . "AND w.`id` = p.`wiki_id`"
                ;
                
            $wikiList = $this->connection->getAllRowsFromQuery( $sql );
            
            if ( $this->connection->hasError() )
            {
                return false;
            }
            
            # search for Wiki pages
            foreach ( $wikiList as $wiki )
            {
                $pages = $this->lightSearchInWiki( $wiki['id'], $pattern, $mode );
                if ( false !== $pages )
                {
                    $wiki['pages'] = is_null($pages) ? array() : $pages;
                    $ret[] = $wiki;
                }
                else
                {
                    return false;
                }
                
                unset( $wiki );
            }
            
            unset( $wikiList );
            
            if ( $this->connection->hasError() )
            {
                return false;
            }
            else
            {
                return $ret;
            }
        }
        
        // error handling
        var $error = null;
        var $errno = 0;

        function setError( $errmsg = '', $errno = 0 )
        {
            $this->error = ($errmsg != '') ? $errmsg : "Unknown error";
            $this->errno = $errno;
        }

        function getError()
        {
            if ( $this->connection->hasError() )
            {
                return $this->connection->getError();
            }
            else if (! is_null( $this->error ) )
            {
                $errno = $this->errno;
                $error = $this->error;
                $this->error = null;
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
            return ( ! is_null( $this->error ) ) || $this->connection->hasError();
        }
    }
?>