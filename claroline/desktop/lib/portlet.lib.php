<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * User desktop portlet classes.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel.desktop
 * @author      Claroline Team <info@claroline.net>
 */

require_once get_path('includePath') . '/lib/portlet.class.php';

/**
 * Abstract desktop portlet to be implemented by module connectors
 */
abstract class UserDesktopPortlet extends Portlet
{
    /**
     * @var String
     */
    protected $name, $label;
    
    /**
     * Get the name of the portlet
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get the label of the portlet
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
    
    /**
     * Set the name of the protlet
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Set the label of the portlet
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
}

/**
 * List of portlets
 */
class PortletList
{
    private $tblDesktopPortlet;

    const UP = 'up';
    const DOWN = 'down';
    const VISIBLE = 'visible';
    const INVISIBLE = 'invisible';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // convert to Claroline course table names
        $tbl_lp_names = get_module_main_tbl( array('desktop_portlet') );
        $this->tblDesktopPortlet = $tbl_lp_names['desktop_portlet'];
    }
    
    /**
     * Load the properties of a portlet given its label
     * @param string $label
     * @return boolean|array portlet properties
     */
    public function loadPortlet( $label )
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
    
    /**
     * Load the properties of all portlets
     * @param boolean $visibility if set to true only visible portlets will be loaded
     * @return boolean|array portlet properties
     */
    public function loadAll( $visibility = false )
    {
        $sql = "SELECT
                    `label`,
                    `name`,
                    `rank`,
                    `visibility`
                FROM `".$this->tblDesktopPortlet."`
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
    /**
     * Add a portlet
     * @param string $label
     * @param string $name
     * @param int $rank default null, will be generated if none given
     * @param boolean $visible default true, visible by default
     * @return boolean
     */
    public function addPortlet( $label, $name, $rank = null, $visible = true )
    {
        if ( Claroline::getDatabase()->query("SELECT `label` FROM `{$this->tblDesktopPortlet}` WHERE `label` = '" . claro_sql_escape($label) . "'")->numRows() )
        {
            return false;
        }
        
        $sql = "SELECT MAX(rank) FROM  `" . $this->tblDesktopPortlet . "`";
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
        $sql = "INSERT INTO `".$this->tblDesktopPortlet."`
                SET `label` = '" . claro_sql_escape($label) . "',
                    `name` = '" . claro_sql_escape($name) . "',
                    `visibility` = '" . $sqlVisibility . "',
                    `rank` = " . $sqlRank;
                    
        return ( claro_sql_query($sql) != false );
    }
    
    /**
     * Move a portlet in the list
     * @param string $label label of the portlet
     * @param string $direction PortletList::UP or PortletList::DOWN, default PortletList::UP
     */
    private function movePortlet($label, $direction)
    {
        switch ($direction)
        {
            case self::UP :
            {
                //1-find value of current module rank in the dock
                $sql = "SELECT `rank`
                        FROM `" . $this->tblDesktopPortlet . "`
                        WHERE `label`='" . claro_sql_escape($label) . "'"
                        ;

                $result = claro_sql_query_get_single_value( $sql );

                //2-move down above module
                $sql = "UPDATE `" . $this->tblDesktopPortlet . "`
                        SET `rank` = `rank`+1
                        WHERE `label` != '" . claro_sql_escape($label) . "'
                        AND `rank`       = " . (int) $result['rank'] . " -1 "
                        ;

                claro_sql_query( $sql );

                //3-move up current module
                $sql = "UPDATE `" . $this->tblDesktopPortlet . "`
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
                        FROM `" . $this->tblDesktopPortlet . "`
                        WHERE `label`='" . claro_sql_escape($label) . "'"
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
                        WHERE `label` != '" . claro_sql_escape($label) . "'
                        AND `rank` = " . (int) $result['rank'] . " + 1
                        AND `rank` > 1"
                        ;

                claro_sql_query($sql);

                //3-move down current module
                $sql = "UPDATE `" . $this->tblDesktopPortlet . "`
                        SET `rank` = `rank` + 1
                        WHERE `label`='" . claro_sql_escape($label) . "'"
                        ;

                claro_sql_query($sql);

                break;
            }
        }
    }
    
    /**
     * Move the portlet one step up in the list
     * @param string $label label of the portlet
     */
    public function moveUp( $label )
    {
        $this->movePortlet( $label, self::UP );
    }
    
    /**
     * Move the portlet one step down in the list
     * @param string $label label of the portlet
     */
    public function moveDown( $label )
    {
        $this->movePortlet( $label, self::DOWN );
    }
    
    /**
     * Set the visibility of the portlet
     * @param string $label label of the portlet
     * @param boolean $visibility true to make visible, false to hide
     * @return boolean
     */
    private function setVisibility( $label, $visibility )
    {
        $sql = "UPDATE `".$this->tblDesktopPortlet."`
                SET `visibility` = '" . $visibility . "'
                WHERE `label` = '" . $label . "'"
                ;

        if( claro_sql_query($sql) == false ) return false;

        return true;
    }
    
    /**
     * Make the portlet visible
     * @param string $label label of the portlet
     */
    public function setVisible( $label )
    {
        $this->setVisibility( $label, self::VISIBLE);
    }
    
    /**
     * Make the portlet invisible
     * @param string $label label of the portlet
     */
    public function setInvisible( $label )
    {
        $this->setVisibility( $label, self::INVISIBLE);
    }
}
