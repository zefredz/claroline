<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
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
 * @param string  $formatMonth display type of month select box : numeric, long, short
 *
 * @author Sébastien Piraux <pir@cerdecam.be>
 *
 * @return string html stream to output input tag for a date
 *
 */

function claro_disp_date_form($dayFieldName, $monthFieldName, $yearFieldName, $selectedDate = 0, $formatMonth = 'numeric' )
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
    for ($dayCounter=1;$dayCounter <=31; $dayCounter++)
      $available_days[$dayCounter] = $dayCounter;
    $dayField = claro_html_form_select( $dayFieldName
                                   , $available_days
                                   , $selDay
                                   , array('id'=> $dayFieldName)
                                   );

    // month field
    if( $formatMonth == 'numeric' )
    {
        for ($monthCounter=1;$monthCounter <= 12; $monthCounter++)
          $available_months[$monthCounter] = $monthCounter;
    }
    elseif( $formatMonth == 'long' )
    {
        for ($monthCounter=1;$monthCounter <= 12; $monthCounter++)
          $available_months[$monthCounter] = $langMonthNames['long'][$monthCounter-1];
    }
    elseif( $formatMonth == 'short' )
    {
        for ($monthCounter=1;$monthCounter <= 12; $monthCounter++)
          $available_months[$monthCounter] = $langMonthNames['short'][$monthCounter-1];
    }
    $monthField = claro_html_form_select( $monthFieldName
                                   , $available_months
                                   , $selMonth
                                   , array('id'=> $monthFieldName)
                                   );
    // year field
    $thisYear = date('Y');
    for ($yearCounter= $thisYear - 2; $yearCounter <= $thisYear+5; $yearCounter++)
        $available_years[$yearCounter] = $yearCounter;
    $yearField = claro_html_form_select( $yearFieldName
                                   , $available_years
                                   , $selYear
                                   , array('id'=> $yearFieldName)
                                   );

    return $dayField . '&nbsp;' . $monthField . '&nbsp;' . $yearField;
}

/**
 * build htmlstream for input form of a time
 *
 * @param string $hourFieldName attribute name of the input Hour
 * @param string $minuteFieldName attribute name of the input minutes
 * @param string $selectedTime current Time (default value in selection)
 *
 * @return string html stream to output input tag for an hour
 *
 * @author Sébastien Piraux <pir@cerdecam.be>
 *
 */


function claro_disp_time_form($hourFieldName, $minuteFieldName, $selectedTime = 0)
{
    if(!$selectedTime)
    {
        $selectedTime = date("H:i");
    }

    //split selectedTime
    list($selHour, $selMinute) = split(':',$selectedTime);


    if ($hourFieldName != '')
    {
        for($hour=0;$hour < 24; $hour++)  $aivailable_hours[$hour] = $hour;
        $hourField = claro_html_form_select( $hourFieldName
                                           , $aivailable_hours
                                           , $selHour
                                           , array('id'=> $hourFieldName)
                                           );
    }

    if($minuteFieldName != "")
    {
        for($minuteCounter=0;$minuteCounter < 60; $minuteCounter++)
            $available_minutes[$minuteCounter] = $minuteCounter;

        $minuteField = claro_html_form_select( $minuteFieldName
                                           , $available_minutes
                                           , $selMinute
                                           , array('id'=> $minuteFieldName)
                                           );
    }

    return '&nbsp;' . $hourField . '&nbsp;' . $minuteField;
}

/**
 *
 * @param string $name name of the form (other param can be adds with $attr
 * @param string $list_option 2D table where key are name and value are label
 * @param string $preselect name of the key in $list_option would be preselected
 * @return html output from a 2D table where key are name and value are label
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function claro_html_form_select($select_name,$list_option,$preselect=null,$attr=null)
{
    $html_select = '<select name="' . $select_name . '" ';
    if (is_array($attr)) foreach($attr as $attr_name=>$attr_value)
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
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function claro_html_option_list($list_option, $preselect)
{
    $html_option_list ='';
    if(is_array($list_option))
    {
        foreach($list_option as $option_value => $option_label)
        {
            if(empty($option_label)) $option_label = $option_value;
            //if(empty($option_label)) $option_label = '-';
            // stupid empty consider empty(0) as true
            $html_option_list .= '<option value="' . $option_value . '"'
            .                    ($option_value ==  $preselect ?' selected="selected" ':'') . '>'
            .                    htmlspecialchars($option_label)
            .                    '</option >' . "\n"
            ;
        }
        return $html_option_list;
    }
    else
    {
        trigger_error('$list_option would be array()', E_USER_NOTICE);
        return false;
    }

}

?>
