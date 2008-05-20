<?php // $Id$
/**
 * The miss named class bufferize dock output.
 * Docks areone of the module type aivailable in claroline
 *
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     KERNEL
 */
if ( count( get_included_files() ) == 1 ) die( '---' );

// TODO rename this lib to dock.lib.php

class Buffer
{
    var $buffer;

    function Buffer()
    {
        $this->buffer = '';
    }

    function init()
    {
        $this->buffer = '';
    }

    function clear()
    {
        $this->buffer = '';
    }

    function append( $str )
    {
        $this->buffer .= $str;
    }

    function getContent()
    {
        return $this->buffer;
    }

    function flush()
    {
        $buffer = $this->buffer;
        $this->clear();
        return $buffer;
    }
}

/**
 * return the list of dock and module actived in there
 *
 * @param boolean $force whether force to load from db ignoring memory cache.
 * @return array of array
 */

function claro_get_docks_module_list($force = false)
{
    static $dockList = null;

    if( is_null($dockList) || $force)
    {

        $tbl_name = claro_sql_get_main_tbl();

        $sql = "SELECT M.`label` AS `label`,
                       D.`name` AS `dock`
                FROM `" . $tbl_name['module'] . "` AS M,
                     `" . $tbl_name['dock'] . "` AS D
                 WHERE D.`module_id` = M.`id`
                   AND M.`activation` = 'activated'
                   ORDER BY D.`rank` ";
        $moduleList = claro_sql_query_fetch_all($sql);

        $dockList = array();

        if ( $moduleList )
        {
            foreach ( $moduleList as $module )
            {
                if ( ! array_key_exists( $module['dock'], $dockList ) )
                {
                    $dockList[$module['dock']] = array();
                }

                $dockList[$module['dock']][] = array( 'label' => $module['label']);
            }


        }
    }
    return $dockList;
}

function getAppletList($dock)
{
    static $moduleList = array();

    if ( empty( $moduleList ) )
    {
        $moduleList = claro_get_docks_module_list();
    }

    $dockModuleList = array_key_exists( $dock, $moduleList )
    ? $moduleList[$dock]
    : array()
    ;

    $appletList = array();

    // stack each entry point of plugins supposed to display output in this buffer

    foreach ($dockModuleList as $module)
    {
        if (file_exists(get_module_path($module['label']) . '/entry.php'))
        {
            $applet = array();
            $applet['path']  = get_module_path($module['label']) . '/entry.php';
            $applet['label'] = $module['label'];
            $appletList[]    = $applet;
        }
    }

    return $appletList;
}

/* Dock class to contain the display buffered by the modules */

class Dock
{
    var $name;
    var $appletList;
    var $kernelOutput;
    var $kernelOutputAtEnd;

    function Dock($name)
    {
       $this->name = $name;
       // TODO move call to getAppletList outside the constructor
       // this is magical code !!!!
       $appletList = getAppletList($this->name);
       $this->setAppletList($appletList);
    }

    function setAppletList($appletList = array())
    {
        $this->appletList = $appletList;
    }

    function addElement($applet)
    {
        $this->appletList[] = $applet;
    }

    function addOutput($string, $atEnd = false)
    {
        if ($atEnd)
        {
            $this->kernelOutputAtEnd .= $string;
        }
        else
        {
            $this->kernelOutput .= $string;
        }
    }

    function render()
    {
        $claro_buffer = new Buffer();
        $claro_buffer->append("\n" . '<div id="' . $this->name.'">' . "\n");

        $claro_buffer->append($this->kernelOutput);

        foreach ( $this->appletList as $applet )
        {
            # entry appends appletoutput to $buffer
            include $applet['path'];
        }

        $claro_buffer->append($this->kernelOutputAtEnd);

        $claro_buffer->append("\n" . '</div>' . "\n\n");

        return $claro_buffer->getContent();
    }
}
