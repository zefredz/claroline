<?php

function claro_disp_date_form($dayFieldName, $monthFieldName, $yearFieldName, $selectedDate = 0 )
{
    global $langMonthNames;
    
    if( $selectedDate == 0)
    {
		// if not date in parameters us 'today'
        $selectedDate = date("Y-m-d");
    }
    // split selectedDate
    list($selYear, $selMonth, $selDay) = split("-", $selectedDate);
    
    // day field
    $dayField = "<select name=\"".$dayFieldName."\" id=\"".$dayFieldName."\">\n";
    for ($i=1;$i <=31; $i++)
    {
        $dayField .= "<option value=\"".$i."\"";
        if($i == $selDay)
        {
            $dayField .= " selected=\"selected\"";
        }
        $dayField .= ">".$i."</option>\n";
    }
    $dayField .="</select>\n";
    
    // month field
    $monthField = "<select name=\"".$monthFieldName."\" id=\"".$monthFieldName."\">\n";
    for ($i=1;$i <=12; $i++)
    {
        $monthField .= "<option value=\"".$i."\"";
        if($i == $selMonth)
        {
            $monthField .= " selected=\"selected\"";
        }
	
        $monthField .= ">".$langMonthNames['long'][$i-1]."</option>\n";
    }
    $monthField .="</select>\n";
    
    // year field
    $yearField = "<select name=\"".$yearFieldName."\" id=\"".$yearFieldName."\">\n";
	$thisYear = date("Y");
    for ($i= $thisYear-2;$i <= $thisYear+5; $i++)
    {
        $yearField .= "<option value=\"".$i."\"";
        if($i == $selYear)
        {
            $yearField .= " selected=\"selected\"";
        }
        $yearField .= ">".$i."</option>\n";
    }
    $yearField .='</select>';
    
    return $dayField.'&nbsp;'.$monthField.'&nbsp;'.$yearField;
}


function claro_disp_time_form($hourFieldName, $minuteFieldName, $selectedTime = 0)
{
    if(!$selectedTime)
    {
        $selectedTime = date("H:i");
    }
    
    //split selectedTime 
    list($selHour, $selMinute) = split(":",$selectedTime);
    
    if ($hourFieldName != "")
    {
        $hourField = "<select name=\"".$hourFieldName."\" id=\"".$hourFieldName."\">\n";
        for($i=0;$i < 24; $i++)
        {
            $hourField .= "<option value=\"".$i."\"";
            if($i == $selHour)
            {
                $hourField .= " selected=\"selected\"";
            }
            $hourField .= ">".$i."</option>\n";
        }
        $hourField .= "</select>";
    }
    
    if($minuteFieldName != "")
    {
        $minuteField = "<select name=\"".$minuteFieldName."\" id=\"".$minuteFieldName."\">\n";
        $i = 0;
        while($i < 60)
        {
            $minuteField .= "<option value=\"".$i."\"";
            if($i == $selMinute)
            {
                $minuteField .= " selected=\"selected\"";
            }
            $minuteField .= ">".$i."</option>\n";
            $i++;
        }
        $minuteField .= "</select>";
    }
    
    return "&nbsp;".$hourField."&nbsp;".$minuteField;
}

?>
