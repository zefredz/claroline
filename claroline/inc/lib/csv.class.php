<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6                                                        |
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
|   This program is free software; you can redistribute it and/or
|   modify it under the terms of the GNU General Public License
|   as published by the Free Software Foundation; either version 2
|   of the License, or (at your option) any later version.
+----------------------------------------------------------------------+
| Authors: Sébastien Piraux
+----------------------------------------------------------------------+
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

?>