<?php // $Id$
/*
    	+-------------------------------------------------------------------+
    	| CLAROLINE version 1.5.*
    	+-------------------------------------------------------------------+
    	| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)   |
    	+-------------------------------------------------------------------+

    	+-------------------------------------------------------------------+
    	|   Functions of this library are used to record informations when  |
    	|   some kind of event occur.                                       |
    	|   Each event has his own types of informations then each event    |
    	|   use its own function.                                           |
    	+-------------------------------------------------------------------+
*/
/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param sql : a sql query (as a string)
 * @desc return one result from a sql query (1 single result)
 */
function getOneResult($sql)
{
	$query = claro_sql_query($sql);
  $res = @mysql_fetch_array($query);
	return $res[0];
}

/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param sql : a sql query (as a string)
 * @desc Return many results of a query in a 1 column tab
 */
function getManyResults1Col($sql)
{ 
	$res = claro_sql_query($sql);
        
  $i = 0;
  while ($resA =   @mysql_fetch_array($res))
  { 
          $resu[$i++]=$resA[0];
  }

	return $resu;
}
/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param sql : a sql query (as a string)
 * @desc Return many results of a query
 */
function getManyResults2Col($sql)
{ 
	$res = claro_sql_query($sql);
        
  $i = 0;
  while ($resA = @mysql_fetch_array($res))
  { 
          $resu[$i][0] = $resA[0];
          $resu[$i][1] = $resA[1];
          $i++;
  }

	return $resu;
}

/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param sql : a sql query (as a string)
 * @desc Return many results of a query in a 3 column tab
         in $resu[$i][0], $resu[$i][1],$resu[$i][2]
 */
function getManyResults3Col($sql)
{ 
	$res = claro_sql_query($sql);
        
  $i = 0;
  while ($resA = @mysql_fetch_array($res))
  { 
      $resu[$i][0]=$resA[0];
      $resu[$i][1]=$resA[1];
      $resu[$i][2]=$resA[2];
      $i++; 
  }
	return $resu;
}

/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param sql : a sql query (as a string)
 * @desc Return many results of a query in a X column tab
         in $resu[$i][0], $resu[$i][1],$resu[$i][2],...
         this function is more 'standard' but use a little
         more ressources 
         So I encourage to use the dedicated for 1, 2 or 3
         columns of results
 */
function getManyResultsXCol($sql,$X)
{ 
	$res = claro_sql_query($sql);
      
  $i = 0;
  while ($resA = @mysql_fetch_array($res))
  { 
      for($j = 0; $j < $X ; $j++)
      {
          $resu[$i][$j]=$resA[$j];
      }
      $i++; 
  }
	return $resu;
}
/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param sql : a sql query (as a string)
 * @return hours_array 
 * @desc        Return an assoc array.  Keys are the hours, values are
                the number of time this hours was found.
                key "total" return the sum of all number of time hours
                appear
 */
function hoursTab($sql)
{

    $query = claro_sql_query( $sql );
    
    $hours_array["total"] = 0;
    
    while( $row = @mysql_fetch_row( $query ) )
    {
        $date_array = getdate($row[0]);
        
        if($date_array["hours"] == $last_hours )
        {
            $hours_array[$date_array["hours"]]++;
        }
        else
        {
            $hours_array[$date_array["hours"]] = 1;
            $last_hours = $date_array["hours"];
        }

        $hours_array["total"]++;
    }
        
    return $hours_array;
}

/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param sql : a sql query (as a string)
 * @return days_array
 * @desc        Return an assoc array.  Keys are the days, values are
                the number of time this hours was found.
                key "total" return the sum of all number of time days
                appear
 */
function daysTab($sql)
{

    global $langMonthNames;

    
    $query = claro_sql_query( $sql );
    
    $days_array["total"] = 0;
    while( $row = @mysql_fetch_row( $query ) )
    {
        $date_array = getdate($row[0]);
        $display_date = $date_array["mday"]." ".$langMonthNames['short'][$date_array["mon"]-1]." ".$date_array["year"];
        if ($date_array["mday"] == $last_day)
        {
            $days_array[$display_date]++;
        }
        else
        {
            $days_array[$display_date] = 1;
            $last_day = $display_date;
        }
        $days_array["total"]++;
    }
    
    return $days_array;
}

/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param sql : a sql query (as a string)
 * @return month_array 
 * @desc        Return an assoc array.  Keys are the days, values are
                the number of time this hours was found.
                key "total" return the sum of all number of time days
                appear
 */
function monthTab($sql)
{

    global $langMonthNames;

    
    $query = claro_sql_query( $sql );
    
    // init tab with all month
    for($i = 0;$i < 12; $i++)
    {
        $month_array[$langMonthNames['long'][$i]] = 0;
        
    }
    // and with total    
    $month_array["total"] = 0;
    
    while( $row = @mysql_fetch_row( $query ) )
    {
        $date_array = getdate($row[0]);
        $month_array[$langMonthNames['long'][$date_array["mon"]-1]]++;
        $month_array["total"]++;
    }
    return $month_array;
}
/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param period_array : an array provided by hoursTab($sql) or daysTab($sql)
 * @param periodTitle : title of the first column, type of period
 * @param linkOnPeriod : 
 * @desc        Display a 4 column array
                Columns are : hour of day, graph, number of hits and %
                First line are titles
                next are informations
                Last is total number of hits
 */
function makeHitsTable($period_array,$periodTitle,$linkOnPeriod = "???")
{
    global $langHits;
    global $langTotal,
	$clarolineRepositoryWeb;

    echo "<table class=\"claroTable\" width=\"100%\" cellpadding=\"0\" cellspacing=\"1\" align=\"center\">";
    // titles
    echo "<tr class=\"headerX\">\n"
              ."<th width=\"15%\">".$periodTitle."</th>\n"
              ."<th width=\"60%\">&nbsp;</th>\n"
              ."<th width=\"10%\">".$langHits."</th>\n"
              ."<th width=\"15%\"> % </th>\n"
              ."</tr>\n\n"
              ."<tbody>";
    $factor = 4;
    $maxSize = $factor * 100; //pixels
    while(list($periodPiece,$cpt) = each($period_array))
    {
        if($periodPiece != "total")    
        {
            if($period_array["total"] == 0 )
            {
                $pourcent = 0;
            }
            else
            {
                $pourcent = round(100 * $cpt / $period_array["total"]);
            }
            
            $barwidth = $factor * $pourcent ;
            echo "<tr>\n"
                    ."<td align=\"center\" width=\"15%\">".$periodPiece."</td>\n"
                    ."<td width=\"60%\" align=\"center\">".claro_disp_progress_bar ( $pourcent, 4)."</td>\n"
                    ."<td align=\"center\" width=\"10%\">".$cpt."</td>\n"
                    ."<td align=\"center\" width=\"15%\">".$pourcent." %</td>\n"
                    ."</tr>\n\n";
        }
    }
    
    // footer 
    echo "</tbody>\n\n"
              ."<tfoot>\n"
              ."<tr>\n"
              ."<td width=\"15%\" align=\"center\">".$langTotal."</td>\n"
              ."<td align=\"right\" width=\"60%\">&nbsp;</td>\n"
              ."<td align=\"center\" width=\"10%\">".$period_array["total"]."</td>\n"
              ."<td width=\"15%\">&nbsp;</td>\n"
              ."</tr>\n"
              ."</tfoot>\n\n"
              ."</table>\n\n";
}
/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param tableau : a 2 columns array
 * @param leftTitle : string, title of the left column
 * @param rightTitle : string, title of the ... right column
 * @desc        display a 2 column tab from an array
                this tab has no title
 */
function buildTab2Col($array_of_results, $leftTitle = "", $rightTitle = "")
{
    global $langNoResult;
    global $langNbLines;
    
    echo "<table class=\"claroTable\" cellpadding=\"2\" cellspacing=\"1\" align=\"center\">";
    
    if($leftTitle != "" || $rightTitle != "")
    {
        echo "<tr class=\"headerX\">\n"
                ."<th>&nbsp;".$leftTitle."</th>\n"
                ."<th>&nbsp;".$rightTitle."</th>\n"
                ."</tr>\n";
    }
    
    echo "<tr class=\"headerX\">\n"
            ."<th colspan=\"2\">".$langNbLines." : ".count($array_of_results)." </th>\n"
            ."</tr>\n\n"
            ."<tbody>";
    if (is_array($array_of_results))
    {
        for($j = 0 ; $j < count($array_of_results) ; $j++)
        {
            echo "<tr>\n" 
                      ."<td>".$array_of_results[$j][0]."</td>\n"
                      ."<td align=\"right\">".$array_of_results[$j][1]."</td>\n"
                      ."</tr>\n\n";
        }

    }
    else
    {
        echo "<tr>\n" 
              ."<td colspan=\"2\"><center>".$langNoResult."</center></td>\n"
              ."</tr>\n\n";
    }
    echo "</tbody>\n</table>\n\n";

}

/**

 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 * @param tableau : a 2 columns array
 * @desc        this function is used to display
                integrity errors in the platform
                if array_of_results is not an array there is 
                no error, else errors are displayed
 */
function buildTabDefcon($array_of_results)
{
    global $langDefcon;
    global $langAllRight;
    global $langNULLValue;
    global $langNbLines;
    
    echo "<table class=\"claroTable\" width=\"60%\" cellpadding=\"2\" cellspacing=\"1\" align=\"center\">\n";
    if (is_array($array_of_results))
    { 
        // there is some strange cases ... 
        echo "<tr class=\"headerX\">\n"
                ."<th colspan=\"2\" align=\"center\"><font color=\"#ff0000\">".$langDefcon."</font></th>\n"
                ."</tr>\n"
                ."<tr class=\"headerX\">\n" 
                ."<th colspan=\"2\">".$langNbLines." : ".count($array_of_results)." </th>\n"
                ."</tr>\n";
                
        for($j = 0 ; $j < count($array_of_results) ; $j++)
        { 
            if($array_of_results[$j][0] == "")
            {
                $key = $langNULLValue;
            }
            else
            {
                $key = $array_of_results[$j][0];
            }
            echo "<tr>\n"
                    ."<td width=\"70%\">".$key."</td>\n"
                    ."<td width=\"30%\" align=\"right\">".$array_of_results[$j][1]."</td>"
                    ."</tr>\n\n";
        }
    
    }
    else
    {
        // all right
        echo "<tr>\n"
                ."<td colspan=\"2\" align=\"center\"><font color=\"#00ff00\">".$langAllRight."</font></td>\n"
                ."</tr>\n";
    }
    echo "</table>\n\n";
}

/**
 * changeResultOfVisibility($array_of_results)
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param array_of_results
 * @desc        complete the content of visibility column a with the litteral meaning
 */
function changeResultOfVisibility($array_of_results)
{
    global $langNoResult;
	$visibilityLabel[0]="closed - hide";
	$visibilityLabel[1]="open - hide";
	$visibilityLabel[2]="open - visible";
	$visibilityLabel[3]="closed - visible";

    if (is_array($array_of_results))
    {
        for($j = 0 ; $j < count($array_of_results) ; $j++)
        {
			$array_of_results[$j][0] = $array_of_results[$j][0]." <small>(".$visibilityLabel[$array_of_results[$j][0]].")</small>";
			$array_of_results[$j][1] = $array_of_results[$j][1];
        }
    }

	return $array_of_results;
}
