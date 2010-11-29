<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
* CLAROLINE
*
* Course home page portlet class
*
* @version      1.9 $Revision$
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Antonin Bourguignon <antonin.bourguignon@claroline.net>
* @author       Claroline team <info@claroline.net>
*/

require_once get_path('includePath') . '/lib/portlet.class.php';

abstract class CourseHomePagePortlet extends Portlet
{
    
}

class CourseHomePagePortletList
{
    private $tblCourseHomePagePortlet;

    const UP = 'up';
    const DOWN = 'down';
    const VISIBLE = 'visible';
    const INVISIBLE = 'invisible';

    public function __construct()
    {
        // convert to Claroline course table names
        $tbl_lp_names = get_module_main_tbl( array('coursehomepage_portlet') );
        $this->tblCourseHomePagePortlet = $tbl_lp_names['coursehomepage_portlet'];
    }

    // load
    public function loadPortlet( $label )
    {
        $sql = "SELECT
                    `label`,
                    `name`,
                    `rank`,
                    `visibility`
                FROM `".$this->tblCourseHomePagePortlet."`
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
                FROM `".$this->tblCourseHomePagePortlet."`
                WHERE 1 "
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
    public function addPortlet( $label, $name, $rank = null, $visible = true )
    {
        $sql = "SELECT MAX(rank) FROM  `" . $this->tblCourseHomePagePortlet . "`";
        $maxRank = claro_sql_query_get_single_value($sql);
        
        $sqlRank = empty( $rank )
            ? $maxRank + 1
            : (int) $rank
            ;
            
        $sqlVisibility = $visible
            ? "visible"
            : "invisible"
            ;
            
        // insert
        $sql = "INSERT INTO `".$this->tblCourseHomePagePortlet."`
                SET `label` = '" . claro_sql_escape($label) . "',
                    `name` = '" . claro_sql_escape($name) . "',
                    `visibility` = '" . $sqlVisibility . "',
                    `rank` = " . $sqlRank;
                    
        return ( claro_sql_query($sql) != false );
    }

    private function movePortlet($label, $direction)
    {
        switch ($direction)
        {
            case self::UP :
            {
                //1-find value of current module rank in the dock
                $sql = "SELECT `rank`
                        FROM `" . $this->tblCourseHomePagePortlet . "`
                        WHERE `label`='" . claro_sql_escape($label) . "'"
                        ;

                $result = claro_sql_query_get_single_value( $sql );

                //2-move down above module
                $sql = "UPDATE `" . $this->tblCourseHomePagePortlet . "`
                        SET `rank` = `rank`+1
                        WHERE `label` != '" . claro_sql_escape($label) . "'
                        AND `rank`       = " . (int) $result['rank'] . " -1 "
                        ;

                claro_sql_query( $sql );

                //3-move up current module
                $sql = "UPDATE `" . $this->tblCourseHomePagePortlet . "`
                        SET `rank` = `rank`-1
                        WHERE `label` = '" . claro_sql_escape($label) . "'
                        AND `rank` > 1"
                        ;

                claro_sql_query($sql);

                break;
            }
            case self::DOWN :
            {
                //1-find value of current module rank in the dock
                $sql = "SELECT `rank`
                        FROM `" . $this->tblCourseHomePagePortlet . "`
                        WHERE `label`='" . claro_sql_escape($label) . "'"
                        ;

                $result = claro_sql_query_get_single_value($sql);

                //this second query is to avoid a page refreshment wrong update

                $sqlmax = "SELECT MAX(`rank`) AS `max_rank`
                          FROM `" . $this->tblCourseHomePagePortlet . "`"
                          ;

                $resultmax = claro_sql_query_get_single_value( $sqlmax );

                if ( $resultmax['max_rank'] == $result['rank'] ) break;

                //2-move up above module
                $sql = "UPDATE `" . $this->tblCourseHomePagePortlet . "`
                        SET `rank` = `rank` - 1
                        WHERE `label` != '" . claro_sql_escape($label) . "'
                        AND `rank` = " . (int) $result['rank'] . " + 1
                        AND `rank` > 1"
                        ;

                claro_sql_query($sql);

                //3-move down current module
                $sql = "UPDATE `" . $this->tblCourseHomePagePortlet . "`
                        SET `rank` = `rank` + 1
                        WHERE `label`='" . claro_sql_escape($label) . "'"
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
        $sql = "UPDATE `".$this->tblCourseHomePagePortlet."`
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