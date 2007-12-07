<?php

/**
 * Claroline SQL query wrapper returning only the first row of the result
 * Useful in some cases because, it avoid nested arrays of results.
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return associative array containing all the result rows
 * @since  1.5.1
 * @see    claro_sql_query()
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 */


function claro_sql_query_get_single_row($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if($result)
    {
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        mysql_free_result($result);
        return $row;
    }
    else
    {
        return false;
    }
}

?>