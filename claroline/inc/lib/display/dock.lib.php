<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Dock display lib.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.display
 */

/**
 * Simple string buffer
 * @todo merge with Claro_StringBuffer
 */
class ClaroBuffer
{
    private $_buffer;
    
    /**
     * Initialize the buffer
     */
    public function __construct()
    {
        $this->clear();
    }
    
    /**
     * Clear the buffer
     */
    public function clear()
    {
        $this->_buffer = '';
    }
    
    /**
     * Append contents to the buffer
     * @param string $str
     */
    public function append( $str )
    {
        $this->_buffer .= $str;
    }
    
    /**
     * Replace the contents of the buffer
     * @param string $str
     */
    public function replace( $str )
    {
        $this->_buffer = $str;
    }

    /**
     * Get the contents of the buffer
     * @return string
     */
    public function getContent()
    {
        return $this->_buffer;
    }

    /**
     * Flush the buffer
     * @return string
     */
    public function flush()
    {
        $buffer = $this->_buffer;
        $this->clear();
        return $buffer;
    }
}

/**
 * Applet list of the dock
 */
class DockAppletList
{
    private static $instance = false;

    private $_dockAppletList = array();
    
    /**
     * Initialize the list
     */
    private function __construct()
    {
        $this->load();
    }
    
    /**
     * Load the list of applets in dock
     */
    public function load()
    {
        $tblNameList = claro_sql_get_main_tbl();
        
        $sql = "SELECT M.`label` AS `label`,\n"
            . "M.`script_url` AS `entry`,\n"
            . "M.`name` AS `name`,\n"
            . "M.`activation` AS `activation`,\n"
            . "D.`name` AS `dock`\n"
            . "FROM `" . $tblNameList['dock'] . "` AS D\n"
            . "LEFT JOIN `" . $tblNameList['module'] . "` AS M\n"
            . "ON D.`module_id` = M.`id`\n"
            . "ORDER BY D.`rank` "
            ;

        $appletList = claro_sql_query_fetch_all_rows( $sql );
        
        if ( $appletList )
        {
            $dockAppletList = array();
            
            foreach ( $appletList as $key => $applet )
            {
                if ( ! array_key_exists($applet['dock'], $dockAppletList) )
                {
                    $dockAppletList[$applet['dock']] = array();
                }
                
                $entryPath = get_module_path($applet['label'])
                    . '/' . $applet['entry']
                    ;

                if (file_exists( $entryPath ) )
                {
                    $applet['path'] = $entryPath;
                    // $appletList[$key] = $applet;
                    $dockAppletList[$applet['dock']][] = $applet;
                }
            }

            $this->_dockAppletList = $dockAppletList;
        }
    }
    
    /**
     * Get the list of applets for the given dock
     * @param string $dockName
     * @return array
     */
    public function getAppletList( $dockName )
    {
        if ( array_key_exists( $dockName, $this->_dockAppletList ) )
        {
            return $this->_dockAppletList[$dockName];
        }
        else
        {
            return array();
        }
    }
    
    /**
     * Get an instance of the dock list
     * @return DockAppletList
     */
    public static function getInstance()
    {
        if ( ! DockAppletList::$instance )
        {
            DockAppletList::$instance = new DockAppletList;
        }
        
        return DockAppletList::$instance;
    }
}

/**
 * Dock to display applets
 */
class ClaroDock implements Display
{
    protected $name;
    protected $appletList;
    protected $_useList = false;

    /**
     * Create a dock with the given name and initialize the dock contents
     * @param string $name
     */
    public function __construct( $name )
    {
        $this->name = $name;
        $this->loadAppletList();
    }
    
    /**
     * Use list to display the dock
     * @since Claroline 1.10
     */
    public function mustUseList()
    {
        $this->_useList = true;
    }
    
    /**
     * Check if must use list in the display
     * @since Claroline 1.10
     */
    protected function useList()
    {
        return $this->_useList;
    }
    
    /**
     * Get the name of the dock
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Load the list of applets of the dock
     */
    public function loadAppletList()
    {
        
        $dockAppletList = DockAppletList::getInstance();
        $this->appletList = $dockAppletList->getAppletList( $this->name );
    }

    /**
     * Render the dock to HTML
     * @see Display
     * @return string
     */
    public function render()
    {
        $claro_buffer = new ClaroBuffer;
        
        $claro_buffer->append("\n" . '<!-- ' . $this->name.' -->' . "\n");
        
        
        
        foreach ( $this->appletList as $applet )
        {
            set_current_module_label( $applet['label'] );
            
            // install course applet
            if ( claro_is_in_a_course() )
            {
                install_module_in_course( $applet['label']
                    , claro_get_current_course_id() ) ;
            }
            
            if ( $applet['activation'] == 'activated'
                && file_exists( $applet['path'] ) )
            {
                load_module_config();
                Language::load_module_translation();
                
                if ( $this->useList() && count( $this->appletList ) > 0 )
                {
                    $claro_buffer->append( "<li
                        id=\"dock-".$this->name."-applet-".$applet['label']."\"
                        class=\"applet dock-".$this->name." applet-".$applet['label']."\"><span>\n" );
                }
                else
                {
                    $claro_buffer->append( "<span
                        id=\"dock-".$this->name."-applet-".$applet['label']."\"
                        class=\"applet dock-".$this->name." applet-".$applet['label']."\">\n" );
                }

                include_once $applet['path'];
                
                if ( $this->useList() && count( $this->appletList ) > 0 )
                {
                    $claro_buffer->append( "\n</span></li>\n" );
                }
                else
                {
                    $claro_buffer->append( "\n</span>\n" );
                }
            }
            else
            {
                Console::debug( "Applet not found or not activated : " . $applet['label'] );
            }
            
            clear_current_module_label();
        }
        
        $claro_buffer->append("\n".'<!-- End of '.$this->name.' -->'."\n");
        
        return $claro_buffer->getContent();
    }
}