<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * CLAROLINE
 *
 * CSV class
 *
 * @version 1.9 $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package KERNEL
 * @author Claro Team <cvs@claroline.net>
 */


class csv
{
    var $separator; // ; or ,
    var $quote; // " or '
    var $recordList = array();

    /**
       * constructor.
       *
       * @param $separator The fields separator.
       * @param $quote The character used to delimit a field.
       * @author Sébastien Piraux <pir@cerdecam.be>
       */
     function csv($separator = ',', $quote = '"')
    {
        $this->separator = $separator;
        $this->quote = $quote;
        $this->recordList = array();
    }

    /**
       * Protect the field using $this->quote if needed.
       *
       * @param $field The data to protect.
       * @author Sébastien Piraux <pir@cerdecam.be>
       */
    function protect_field( $field )
    {
        // field must be quoted when
        // - it contains one or more 'separator'
        // - it contains one or more 'quote'
        // - it contains one or more end line character (\n)
        // - it has leading or trailing spaces
        if(     strstr($field,$this->separator) !== false
            ||     strstr($field,$this->quote) !== false
            ||     strstr($field,"\n") !== false
            ||  strlen($field) > strlen(trim($field))
        )
        {
            return $this->quote.str_replace($this->quote, $this->quote.$this->quote,$field).$this->quote;
        }
        else
        {
            return $field;
        }
    }

    /**
     * Export the fields
     *
     *
     * @author Sébastien Piraux <pir@cerdecam.be>
     */
    function export()
    {
        $csvContent = '';

        foreach( $this->recordList as $record )
        {
             foreach( $record as $field )
            {
                $csvContent .= $this->protect_field($field).$this->separator;
            }
            // delete the last separator and create a new line
            $csvContent = substr($csvContent, 0, -1)."\n";
        }

        if( !empty($csvContent) )     return $csvContent;
        else                        return "";
    }
}
