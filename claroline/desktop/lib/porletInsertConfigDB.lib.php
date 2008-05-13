<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package Desktop
 *
 * @author Claroline team <info@claroline.net>
 *
 */
    // vim: expandtab sw=4 ts=4 sts=4 foldmethod=marker:

    class porletInsertConfigDB
    {
        private $tblDesktopPortlet = '';
        
        private $label = '';
        private $name = '';
        private $rank = '';
        private $activated = '';

        public function __construct()
        {
            $tblNameList = array(
                'desktop_portlet'
            );

            // convert to Claroline course table names
            $tbl_lp_names = get_module_main_tbl( $tblNameList, claro_get_current_course_id() );
            $this->tblDesktopPortlet = $tbl_lp_names['desktop_portlet'];
        }


        // load
        public function load( $label )
        {
            $sql = "SELECT
                        `label`,
                        `name`,
                        `rank`,
                        `activated`
                    FROM `".$this->tblDesktopPortlet."`
                    WHERE label = '" . $label . "'"
                    //ORDER BY `rank` ASC"
                    ;

            $data = claro_sql_query_get_single_row($sql);
            
            if( !empty($data) )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        public function loadAll()
        {
            $sql = "SELECT
                        `label`,
                        `name`,
                        `rank`,
                        `activated`
                    FROM `".$this->tblDesktopPortlet."`
                    WHERE activated = '1'
                    ORDER BY `rank` ASC"
                    ;

            if ( false === ( $data = claro_sql_query_fetch_all_rows($sql) ) )
            {
                return false;
            }
            else
            {
                return $data;
            }
        }

        // save
        public function save()
        {
            // insert
            $sql = "INSERT INTO `".$this->tblDesktopPortlet."`
                    SET `label` = '" . claro_sql_escape($this->getLabel()) . "',
                        `name` = '" . claro_sql_escape($this->getName()) . "',
                        `rank` = '" . claro_sql_escape($this->getRank()) . "',
                        `activated` = '" . claro_sql_escape($this->getActivated()) . "'"
                    ;                            

            if( claro_sql_query($sql) == false ) return false;

            return true;
        }
/*
        // delete
        public function delete()
        {
            if( !$this->getLabel() ) return true;

            $sql = "DELETE FROM `" . $this->tblDesktopPortlet . "`
                    WHERE `label` = '" . $this->getLabel() ."'"
                    ;

            if( claro_sql_query($sql) == false ) return false;

            $this->setLabel();
            return true;
        }
*/
        // label
        public function getLabel()
        {
            return $this->label;
        }

        public function setLabel( $value )
        {
            $this->label = trim($value);
        }

        // name
        public function getName()
        {
            return $this->name;
        }

        public function setName($value)
        {
            $this->name = trim($value);
        }

        // rank
        public function getRank()
        {
            return $this->rank;
        }

        public function setRank($value)
        {
            $this->rank = trim($value);
        }

        // activated
        public function getActivated()
        {
            return $this->activated;
        }

        public function setActivated($value)
        {
            $this->activated = trim($value);
        }
    }

    class porletConfigAvatar
    {
        private $tblDesktopAvatar = '';
        
        public function __construct()
        {
            $tblNameList = array(
                'desktop_portlet_avatar'
            );

            // convert to Claroline course table names
            $tbl_lp_names = get_module_main_tbl( $tblNameList, claro_get_current_course_id() );
            $this->tblDesktopAvatar = $tbl_lp_names['desktop_portlet_avatar'];
        }
        
        public function load()
        {
            $sql = "SELECT
                        `avatar`
                    FROM `".$this->tblDesktopAvatar."`
                    WHERE idUser = '". claro_get_current_user_id() ."'"
                    ;

            $data = claro_sql_query_get_single_value($sql);
            
            if( !empty($data) )
            {
                return $data;
            }
            else
            {
                return false;
            }
        }
        
        public function save( $avatar = 'smile' )
        {
            // insert
            $sql = "INSERT INTO `".$this->tblDesktopAvatar."`
                    SET `idUser` = '" . claro_get_current_user_id() . "',
                        `avatar` = '" . $avatar . "'"
                    ;                            
                    
            if( claro_sql_query($sql) == false ) return false;

            return true;
        }
        
        public function update( $avatar = 'smile' )
        {
            // insert
            $sql = "UPDATE `".$this->tblDesktopAvatar."`
                    SET `avatar` = '" . $avatar . "'
                    WHERE `idUser` = '" . claro_get_current_user_id() . "'"
                    ;                            
                    
            if( claro_sql_query($sql) == false ) return false;

            return true;
        }
    }
    
    class PortletConfig
    {

        private $tblDesktopPortlet = '';
        
        public function __construct()
        {
            $tblNameList = array(
                'desktop_portlet'
            );

            // convert to Claroline course table names
            $tbl_lp_names = get_module_main_tbl( $tblNameList, claro_get_current_course_id() );
            $this->tblDesktopPortlet = $tbl_lp_names['desktop_portlet'];
        }
        
        function move_portlet($label, $direction)
        {
            switch ($direction)
            {
                case 'up' :
                {
                    //1-find value of current module rank in the dock
                    $sql = "SELECT `rank`
                            FROM `" . $this->tblDesktopPortlet . "`
                            WHERE `label`='" . addslashes($label) . "'"
                            ;
                            
                    $result = claro_sql_query_get_single_value( $sql );
                    

                    //2-move down above module
                    $sql = "UPDATE `" . $this->tblDesktopPortlet . "`
                            SET `rank` = `rank`+1
                            WHERE `label` != '" . addslashes($label) . "'
                            AND `rank`       = " . (int) $result['rank'] . " -1 "
                            ;

                    claro_sql_query( $sql );

                    //3-move up current module
                    $sql = "UPDATE `" . $this->tblDesktopPortlet . "`
                            SET `rank` = `rank`-1
                            WHERE `label` = '" . addslashes($label) . "'
                            AND `rank` > 1"
                            ; 
                            
                    claro_sql_query($sql);

                    break;
                }
                case 'down' :
                {
                    //1-find value of current module rank in the dock
                    $sql = "SELECT `rank`
                            FROM `" . $this->tblDesktopPortlet . "`
                            WHERE `label`='" . addslashes($label) . "'"
                            ;
                            
                    $result = claro_sql_query_get_single_value($sql);

                    //this second query is to avoid a page refreshment wrong update

                    $sqlmax = "SELECT MAX(`rank`) AS `max_rank`
                              FROM `" . $this->tblDesktopPortlet . "`"
                              ;
                              
                    $resultmax = claro_sql_query_get_single_value( $sqlmax );
                    
                    if ( $resultmax['max_rank'] == $result['rank'] ) break;

                    //2-move up above module
                    $sql = "UPDATE `" . $this->tblDesktopPortlet . "`
                            SET `rank` = `rank` - 1
                            WHERE `label` != '" . addslashes($label) . "'
                            AND `rank` = " . (int) $result['rank'] . " + 1
                            AND `rank` > 1"
                            ;
                            
                    claro_sql_query($sql);

                    //3-move down current module
                    $sql = "UPDATE `" . $this->tblDesktopPortlet . "`
                            SET `rank` = `rank` + 1
                            WHERE `label`='" . addslashes($label) . "'"
                            ;
                            
                    claro_sql_query($sql);

                    break;
                }
            }
        }
    
        public function saveVisibility( $label )
        {
            $sql = "UPDATE `".$this->tblDesktopPortlet."`
                    SET `visibility` = '" . getVisibility() . "'
                    WHERE `label` = '" . $label . "'"
                    ;                            
                                
            if( claro_sql_query($sql) == false ) return false;

            return true;
        }
        
        // visibility
        protected function getVisibility()
        {
            return $this->visibility;
        }

        protected function setVisibility( $visibility )
        {
            $this->visibility = ( $visibility === 'INVISIBLE' ) ? 'INVISIBLE' : 'VISIBLE';
        }

        public function setVisible()
        {
            $this->setVisibility('VISIBLE');
        }

        public function setInvisible()
        {
            $this->setVisibility('INVISIBLE');
        }

        public function isVisible()
        {
            return ( $this->getVisibility() === 'VISIBLE' );
        }    
    }
    
?>