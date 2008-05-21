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

    class PortletInsertConfigDB
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
                        `visibility`
                    FROM `".$this->tblDesktopPortlet."`
                    WHERE label = '" . claro_sql_escape( $label ) . "'";

            $data = claro_sql_query_get_single_row($sql);

            if( empty($data) )
            {
                return false;
            }
            else
            {
                return $data;
            }
        }

        public function loadAll( $visibility = false )
        {
            $sql = "SELECT
                        `label`,
                        `name`,
                        `rank`,
                        `visibility`
                    FROM `".$this->tblDesktopPortlet."`
                    WHERE TRUE "
                    . ( $visibility == true ? "AND visibility = 'visible'" : '' ) .
                    "ORDER BY `rank` ASC";

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
                        `rank` = '" . claro_sql_escape($this->getRank()) . "'";

            if( claro_sql_query($sql) == false ) return false;

            return true;
        }

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
            $this->rank = (int) $value;
        }
    }

    class PortletConfig
    {

        private $tblDesktopPortlet = '';
        
        const UP = 'up';
        const DOWN = 'down';
        const VISIBLE = 'visible';
        const INVISIBLE = 'invisible';

        public function __construct()
        {
            $tblNameList = array(
                'desktop_portlet'
            );

            // convert to Claroline course table names
            $tbl_lp_names = get_module_main_tbl( $tblNameList, claro_get_current_course_id() );
            $this->tblDesktopPortlet = $tbl_lp_names['desktop_portlet'];
        }

        private function movePortlet($label, $direction)
        {
            switch ($direction)
            {
                case self::UP :
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
                case self::DOWN :
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

        public function moveUp( $label )
        {
            $this->movePortlet( $label, self::UP );
        }
        
        public function moveDown( $label )
        {
            $this->movePortlet( $label, self::DOWN );
        }
        
        private function setVisibility( $label, $visibility )
        {
            $sql = "UPDATE `".$this->tblDesktopPortlet."`
                    SET `visibility` = '" . $visibility . "'
                    WHERE `label` = '" . $label . "'"
                    ;

            if( claro_sql_query($sql) == false ) return false;

            return true;
        }

        public function setVisible( $label )
        {
            $this->setVisibility( $label, self::VISIBLE);
        }

        public function setInvisible( $label )
        {
            $this->setVisibility( $label, self::INVISIBLE);
        }
    }
?>