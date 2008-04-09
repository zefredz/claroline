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
	            'cl_desktop_portlet'
	        );

	        // convert to Claroline course table names
	        $tbl_lp_names = get_module_course_tbl( $tblNameList, claro_get_current_course_id() );
	        $this->tblDesktopPortlet = $tbl_lp_names['cl_desktop_portlet'];
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
?>