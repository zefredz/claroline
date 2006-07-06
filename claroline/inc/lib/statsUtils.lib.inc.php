<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLTRACK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sébastien Piraux <piraux@claroline.net>
 *
 * @todo
 *
 */

/**
 * Return an assoc array.  Keys are the hours, values are
 * the number of time this hours was found.
 * key 'total' return the sum of all number of time hours
 * appear
 *
 * @param string sql query
 *
 * @return array hours
 */
function hoursTab($sql)
{
    $query = claro_sql_query( $sql );

    $hours_array['total'] = 0;
    $last_hours = -1;

    while( $row = @mysql_fetch_row( $query ) )
    {
        $date_array = getdate($row[0]);

        if($date_array['hours'] == $last_hours )
        {
            $hours_array[$date_array['hours']]++;
        }
        else
        {
            $hours_array[$date_array['hours']] = 1;
            $last_hours = $date_array['hours'];
        }

        $hours_array['total']++;
    }

    return $hours_array;
}

/**
 * Return an assoc array.  Keys are the days, values are
 * the number of time this hours was found.
 * key 'total' return the sum of all number of time days
 * appear
 *
 * @param string sql query
 *
 * @return days_array
 *
 */
function daysTab($sql)
{

    $langMonthNames = get_lang_month_name_list('short');

    $query = claro_sql_query( $sql );

    $days_array['total'] = 0;
    $last_day = -1;
    while( $row = @mysql_fetch_row( $query ) )
    {
        $date_array = getdate($row[0]);
        $display_date = $date_array['mday'] . ' '
        .               $langMonthNames[$date_array['mon']-1] . ' '
        .               $date_array['year']
        ;

        if ($date_array['mday'] == $last_day)
        {
            $days_array[$display_date]++;
        }
        else
        {
            $days_array[$display_date] = 1;
            $last_day = $display_date;
        }
        $days_array['total']++;
    }

    return $days_array;
}

/**
 * Return an assoc array.  Keys are the days, values are
 * the number of time this hours was found.
 * key 'total' return the sum of all number of time days
 * appear
 *
 * @param string sql query
 *
 * @return array month
 *
 */
function monthTab($sql)
{

    $langMonthNames = get_lang_month_name_list('long');

    // init tab with all month
    for($i = 0;$i < 12; $i++)
    {
        $month_array[$langMonthNames[$i]] = 0;

    }
    // and with total
    $month_array['total'] = 0;

    $query = claro_sql_query( $sql );
    while( $row = @mysql_fetch_row( $query ) )
    {
        $date_array = getdate($row[0]);
        $month_array[$langMonthNames[$date_array['mon']-1]]++;
        $month_array['total']++;
    }
    return $month_array;
}

/**
 * Display a 4 column array
 * Columns are : hour of day, graph, number of hits and %
 * First line are titles
 * next are informations
 * Last is total number of hits
 *
 * @param period_array : an array provided by hoursTab($sql) or daysTab($sql)
 * @param periodTitle : title of the first column, type of period
 * @param linkOnPeriod :
 *
 * @return
 *
 * @todo variable $linkOnPeriod n'apparaît qu'une fois
 * @todo déclaration de globale inutilisée :  $clarolineRepositoryWeb
 * @todo La valeur de la variable $maxSize n'est jamais utilisée
 * @todo La variable $barwidth n'apparaît qu'une fois
 *
 */
function makeHitsTable($period_array,$periodTitle,$linkOnPeriod = "???")
{
    global $clarolineRepositoryWeb;

    echo '<table class="claroTable" width="100%" cellpadding="0" cellspacing="1" align="center">' . "\n";
    // titles
    echo '<tr class="headerX">' . "\n"
    .    '<th width="15%">' . $periodTitle . '</th>' . "\n"
    .    '<th width="60%">&nbsp;</th>' . "\n"
    .    '<th width="10%">' . get_lang('Hits') . '</th>' . "\n"
    .    '<th width="15%"> % </th>' . "\n"
    .    '</tr>' . "\n\n"
    .    '<tbody>' . "\n\n"
    ;
    $factor = 4;
    $maxSize = $factor * 100; //pixels
    while(list($periodPiece,$cpt) = each($period_array))
    {
        if($periodPiece != 'total')
        {
            if($period_array['total'] == 0 ) $pourcent = 0;
            else                             $pourcent = round(100 * $cpt / $period_array['total']);

            $barwidth = $factor * $pourcent ;
            echo '<tr>' . "\n"
                .'<td align="center" width="15%">'.$periodPiece.'</td>' . "\n"
                .'<td width="60%" align="center">'.claro_html_progress_bar($pourcent, 4).'</td>' . "\n"
                .'<td align="center" width="10%">'.$cpt.'</td>' . "\n"
                .'<td align="center" width="15%">'.$pourcent.' %</td>' . "\n"
                .'</tr>' . "\n\n";
        }
    }

    // footer
    echo '</tbody>' . "\n\n"
          .'<tfoot>' . "\n"
          .'<tr>' . "\n"
          .'<td width="15%" align="center">'.get_lang('Total').'</td>' . "\n"
          .'<td align="right" width="60%">&nbsp;</td>' . "\n"
          .'<td align="center" width="10%">'.$period_array['total'].'</td>' . "\n"
          .'<td width="15%">&nbsp;</td>' . "\n"
          .'</tr>' . "\n"
          .'</tfoot>' . "\n\n"
          .'</table>' . "\n\n";
}
/**
 * Display a 2 column tab from an array
 * this tab has no title
 *
 * @param results : a 2 columns array
 * @param leftTitle : string, title of the left column
 * @param rightTitle : string, title of the ... right column
 *
 * @return
 */
function buildTab2Col($results, $leftTitle = "", $rightTitle = "")
{
    echo '<table class="claroTable" cellpadding="2" cellspacing="1" align="center">' . "\n";

    if($leftTitle != '' || $rightTitle != '')
    {
        echo '<tr class="headerX">' . "\n"
        .    '<th>&nbsp;' . $leftTitle .' </th>' . "\n"
        .    '<th>&nbsp;' . $rightTitle . '</th>' . "\n"
        .    '</tr>' . "\n"
        ;
    }

    echo '<tr class="headerX">' . "\n"
    .    '<th colspan="2">' . get_lang('Number of rows') . ' : ' . count($results) . ' </th>' . "\n"
    .    '</tr>' . "\n\n"
    .    '<tbody>' . "\n\n"
    ;
    if( !empty($results) && is_array($results) )
    {
        foreach( $results as $result )
        {
            $keys = array_keys($result);
            echo '<tr>' . "\n"
            .    '<td>' . $result[$keys[0]] . '</td>' . "\n"
            .    '<td align="right">' . $result[$keys[1]] . '</td>' . "\n"
            .    '</tr>' . "\n\n"
            ;
        }

    }
    else
    {
        echo '<tr>' . "\n"
        .    '<td colspan="2"><center>'.get_lang('No result').'</center></td>' . "\n"
        .    '</tr>' . "\n\n"
        ;
    }
    echo '</tbody>' . "\n"
    .    '</table>' . "\n\n"
    ;

}

/**
 * This function is used to display
 * integrity errors in the platform
 * if results is not an array there is
 * no error, else errors are displayed
 *
 * @param array $results a 2 columns array
 *
 * @return void
 */

function buildTabDefcon($results)
{
    echo '<table class="claroTable" width="60%" cellpadding="2" cellspacing="1" align="center">' . "\n";

    if( !empty($results) && is_array($results) )
    {
        // there is some strange cases ...
        echo '<tr class="headerX">' . "\n"
        .    '<th colspan="2" align="center"><span class="error">'.get_lang('Ooops, stranges cases detected !!').'</span></th>' . "\n"
        .    '</tr>' . "\n"
        .    '<tr class="headerX">' . "\n"
        .    '<th colspan="2">' . get_lang('Number of rows') . ' : ' . count($results) . ' </th>' . "\n"
        .    '</tr>' . "\n"
        ;

        foreach( $results as $result )
        {
            $keys = array_keys($result);

            if( !isset($result[$keys[0]]) || $result[$keys[0]] == '') $key = get_lang('Empty (or NULL)');
            else                                                      $key = $result[$keys[0]];

            echo '<tr>' . "\n"
            .    '<td width="70%">' . $key . '</td>' . "\n"
            .    '<td width="30%" align="right">' . "\n"
            ;

            if( isset($result[$keys[1]]) ) echo $result[$keys[1]];
            else                           echo '&nbsp;';

            echo '</td>' . "\n"
            .    '</tr>' . "\n\n"
            ;
        }

    }
    else
    {
        // all right
        echo '<tr>' . "\n"
        .    '<td colspan="2" align="center">'
        .    '<span class="correct">' . get_lang('There is no strange case here') . '</span>'
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        ;
    }
    echo '</table>' . "\n\n";
}

/**
 * Complete the content of visibility column a with the litteral meaning
 *
 * @param results
 *
 * @return array
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function changeResultOfVisibility($results)
{
    $visibilityLabel[0] = 'closed - hide';
    $visibilityLabel[1] = 'open - hide';
    $visibilityLabel[2] = 'open - visible';
    $visibilityLabel[3] = 'closed - visible';

    if( !empty($results) && is_array($results) )
    {
        $i = 0;
        foreach( $results as $result )
        {
            $keys = array_keys($result);

            $resultsChanged[$i][$keys[0]] = $result[$keys[0]] . ' <small>(' . $visibilityLabel[$result[$keys[0]]] . ')</small>';
            $resultsChanged[$i][$keys[1]] = $result[$keys[1]];
            $i++;
        }
    }
    return $resultsChanged;
}

/**
 * Delete track hits in a course before a date limit.
 * @param $course_id  course_id where function would delete track hits
 * @param $dateLimite timestamp which mark until wich date  the function would delete track hits
 *
 * @return boolean true
 *
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 *
 */
function resetStatForCourse($course_id, $dateLimite )
{
    //access_date DATETIME
    if (is_int($dateLimite))
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_track_e_default   = $tbl_mdb_names['track_e_default'];
        $tbl_course            = $tbl_mdb_names['course'];
        $sql = 'SELECT dbName From `'.$tbl_course.'` WHERE code = "'.$course_id.'"';
        $course_data = claro_sql_query_fetch_all($sql);
        $tbl_crs_name = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        $tbl_track_e_access    = $tbl_crs_name['track_e_access'   ];
        $tbl_track_e_downloads = $tbl_crs_name['track_e_downloads'];
        $tbl_track_e_exercices = $tbl_crs_name['track_e_exercices'];
        $tbl_track_e_uploads   = $tbl_crs_name['track_e_uploads'  ];

        $sql = 'DELETE
                    FROM  `'.$tbl_track_e_access.'`
                    WHERE UNIX_TIMESTAMP(`access_date`) < "'.$dateLimite.'"';
        claro_sql_query($sql);
        $sql = 'DELETE
                    FROM  `'.$tbl_track_e_downloads.'`
                    WHERE UNIX_TIMESTAMP(`down_date`) < "'.$dateLimite.'"';
        claro_sql_query($sql);
        $sql = 'DELETE
                    FROM  `'.$tbl_track_e_exercices.'`
                    WHERE UNIX_TIMESTAMP(`exe_date`) < "'.$dateLimite.'"';
        claro_sql_query($sql);
        $sql = 'DELETE
                    FROM  `'.$tbl_track_e_uploads.'`
                    WHERE UNIX_TIMESTAMP(`upload_date`) < "'.$dateLimite.'"';
        claro_sql_query($sql);
      // central table
        $sql = 'DELETE
                    FROM  `'.$tbl_track_e_default.'`
                    WHERE
                        `default_cours_code` = "'.$course_id.'"
                        AND
                        UNIX_TIMESTAMP(`default_date`) < "'.$dateLimite.'"';

        claro_sql_query($sql);
    }
    return true;
}

?>
