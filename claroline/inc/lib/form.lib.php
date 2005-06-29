<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package FORM
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */


/**
 * @param string  $dayFieldName attribute name of the input DAY
 * @param string  $monthFieldName attribute name of the input MONTH
 * @param string  $yearFieldName attribute name of the input YEAR
 * @param boolean $selectedDate 
 * 
 */ 
 
function claro_disp_date_form($dayFieldName, $monthFieldName, $yearFieldName, $selectedDate = 0 )
{
    global $langMonthNames;
    
    if( $selectedDate == 0)
    {
        // if not date in parameters us 'today'
        $selectedDate = date('Y-m-d');
    }
    // split selectedDate
    list($selYear, $selMonth, $selDay) = split('-', $selectedDate);
    
    // day field
    $dayField = '<select name="' . $dayFieldName . '" id="' . $dayFieldName . '">' ."\n";
    for ($dayCounter=1;$dayCounter <=31; $dayCounter++)
    {
        $dayField .= '<option value="' . $dayCounter . '"';
        if($dayCounter == $selDay)
        {
            $dayField .= ' selected="selected"';
        }
        $dayField .= '>' . $dayCounter . '</option>' . "\n";
    }
    $dayField .= '</select>' . "\n";
    
    // month field
    $monthField = '<select name="' . $monthFieldName . '" id="' . $monthFieldName . '">' . "\n";
    for ($monthCounter=1;$monthCounter <= 12; $monthCounter++)
    {
        $monthField .= '<option value="' . $monthCounter . '"';
        if($monthCounter == $selMonth)
        {
            $monthField .= ' selected="selected" ';
        }
    
        $monthField .= '>' . $langMonthNames['long'][$monthCounter-1] . '</option>' ."\n";
    }
    $monthField .= '</select>' . "\n";
    
    // year field
    $yearField = '<select name="' . $yearFieldName . '" id="' . $yearFieldName . '">' . "\n";
    $thisYear = date('Y');
    for ($yearCounter= $thisYear - 2; $yearCounter <= $thisYear+5; $yearCounter++)
    {
        $yearField .= '<option value="' . $yearCounter . '"';
        if($yearCounter == $selYear)
        {
            $yearField .= ' selected="selected"';
        }
        $yearField .= '>' . $yearCounter . '</option>' . "\n";
    }
    $yearField .='</select>';
    
    return $dayField . '&nbsp;' . $monthField . '&nbsp;' . $yearField;
}


function claro_disp_time_form($hourFieldName, $minuteFieldName, $selectedTime = 0)
{
    if(!$selectedTime)
    {
        $selectedTime = date("H:i");
    }
    
    //split selectedTime 
    list($selHour, $selMinute) = split(":",$selectedTime);
    
    if ($hourFieldName != '')
    {
        $hourField = '<select name="' . $hourFieldName . '" id="' . $hourFieldName . '">' . "\n";
        for($hour=0;$hour < 24; $hour++)
        {
            $hourField .= '<option value="' . $hour . '"';
            if($hour == $selHour)
            {
                $hourField .= ' selected="selected"';
            }
            $hourField .= '>' . $hour . '</option>' . "\n";
        }
        $hourField .= '</select>';
    }
    
    if($minuteFieldName != "")
    {
        $minuteField = '<select name="' . $minuteFieldName . '" id="' . $minuteFieldName . '">' . "\n";
        $minuteCounter = 0;
        while($minuteCounter < 60)
        {
            $minuteField .= '<option value="' . $minuteCounter . '"';
            if($minuteCounter == $selMinute)
            {
                $minuteField .= ' selected="selected"';
            }
            $minuteField .= '>' . $minuteCounter . '</option>' . "\n";
            $minuteCounter++;
        }
        $minuteField .= '</select>';
    }
    
    return '&nbsp;' . $hourField . '&nbsp;' . $minuteField;
}

/**
 *
 * @param string $name name of the form (other param can be adds with $attr
 * @param string $list_option 2D table where key are name and value are label 
 * @param string $preselect name of the key in $list_option would be preselected
 * @return html output from a 2D table where key are name and value are label 
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function claro_html_form_select($select_name,$list_option,$preselect,$attr)
{
    $html_select = '<select name="' . $select_name . '" ';
    foreach($attr as $attr_name=>$attr_value)
       $html_select .=' ' . $attr_name . '="' . $attr_value . '" ';
    $html_select .= '>' . "\n"
    .                claro_html_option_list($list_option,$preselect)
    .               '</select>' . "\n" 
    ;
       
    return $html_select;
}


/**
 * return a string as html form option list to plce in a <select>
 * @param string $list_option 2D table where key are name and value are label 
 * @param string $preselect name of the key in $list_option would be preselected
 * @return html output from a 2D table where key are name and value are label 
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function claro_html_option_list($list_option, $preselect)
{
    $html_option_list ='';
    foreach($list_option as $option_value => $option_label)
    {
        $html_option_list .= '<option value="' . $option_value . '"'
        .                        ($option_value ==  $preselect ?' selected="selected" ':'') . '>'
        .                        htmlspecialchars($option_label) 
        .                        '</option>' . "\n"
        ;
    }
       
    return $html_option_list;
}

?>