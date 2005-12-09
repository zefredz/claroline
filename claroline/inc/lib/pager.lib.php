<?php

/**
 * Pager class allowing to manage the paging system into claroline
 *
 *  example : $myPager = new claro_sql_pager('SELECT * FROM USER', $offset, $step);
 *
 *            echo '<table><tr><td>';
 *
 *            echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
 *
 *            echo '</td></tr>';
 *
 *            $resultList = $myPager->get_result_list();
 *
 *            foreach($resultList as $thisresult)
 *            {
 *              echo '<tr><td>$thisresult[...]</td></tr>';
 *            }
 *
 *            echo '</table>';
 *
 * Note : The pager will request page change by the $_GET['offset'] variable
 * If it conflicts with other variable you can change this name with the 
 * set_pager_call_param_name($paramName) method.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * 
 */

class claro_sql_pager
{
    /**
     * Constructor
     *
     * @param string $sql current SQL query
     * @param  int $offset requested offset
     * @param int $step current step paging
     */

    function claro_sql_pager($sql, $offset = 0, $step = 20)
    {
        $this->sql       = $sql;
        $this->offset    = (int) $offset;
        $this->step      = (int) $step;
        $this->set_pager_call_param_name('offset');

        $sqlPrep = $this->prepare_query($this->sql, $this->offset, $this->step);

        $this->resultList       = claro_sql_query_fetch_all($sqlPrep);
        $this->totalResultCount = $this->get_total_result_count($this->sql);
        $this->offsetCount      = ceil( $this->totalResultCount / $this->step );
    }

    /**
     * Allows to change the parameter name in the url for page change request.
     * By default, this parameter name is 'offset'.
     * @param string paramName
     */

    function set_pager_call_param_name($paramName)
    {
    	$this->paramName = $paramName;
    }


    /**
     * (Private method) Rewrite the SQL query to allowing paging. It adds LIMIT 
     * parameter to the end of the query end SQL_CALC_FOUND_ROWS between the 
     * SELECT statement and the column list 
     *
     * @param  string $sql current SQL query
     * @param  int $offset requested offset
     * @param int $step current step paging
     * @return string the rewrote query
     */

    function prepare_query($sql, $offset, $step)
    {
        if ( $step > 0)
        {
            return $sql . ' LIMIT ' . $offset . ', ' . $step;

            // Insert SQL_CALC_FOUND_ROWS into the query 
            // -- Only works with mySQL 4. Useful if the scritp calls
            // SELECT FOUND_ROWS() later ( see get_total_result_count() method )
            //
            // $sql = substr_replace ($sql, 'SELECT SQL_CALC_FOUND_ROWS ', 
            //                       0   , strlen('SELECT '));
        }
        else
        {
        	return false;
        }
    }

    function get_total_result_count()
    {
        // chek the occurence of a GROUP BY statement into the query
        if ( ! eregi('[[:space:]]+(GROUP BY|HAVING|SELECT[[:space:]]+DISTINCT)[[:space:]]+',
                   $this->sql) )
        {
            // Split the whole sql query in three part and store it into an array :
            // [0]. the SELECT part
            // [1]. the FROM part
            // [2]. the ORDER BY part (ORDER statements pose problems 
            //                         on COUNT queries)
            //
            // The code mainly uses the FROM part

            $sqlPartList = split('[[:space:]]+(FROM|ORDER BY)[[:space:]]+', 
                                 $this->sql);

            // check the occurence of DISTINCT

            if ( eregi('^SELECT DISTINCT(.*)', $sqlPartList[0], $distinctDetect) )
            {
                $countWhat = 'DISTINCT ' . $distinctDetect[1];
            }
            else
            {
                $countWhat = '*';
            }

            $sql = 'SELECT COUNT(' . $countWhat . ') AS totalResultCount 
                    FROM ' . $sqlPartList[1];

            return claro_sql_query_get_single_value($sql);
        }
        else
        {
            // heavier, but we have no choice 
            // when there is COUNT and GROUP BY statements

            return mysql_num_rows( claro_sql_query($this->sql) );
        }

        // Other option, faster but only available for mySQL 4
        //
        // list($totalResultCount) = 
        // claro_sql_query_fetch_all('SELECT FOUND_ROWS() foundRows');
        //
        // return $totalResultCount['foundRows'];
    }

    /**
     * return the result of the SQL query exectued into the constructor
     *
     * @return string
     */

    function get_result_list()
    {
        return $this->resultList;
    }

    /**
     * return the offset needed to get the previous page
     *
     * @return int
     */

    function get_previous_offset()
    {
        $previousOffset = $this->offset - $this->step;

        if ($previousOffset >= 0) return $previousOffset;
        else                      return false;
    }

    /**
     * return the offset needed to get the next page
     *
     * @return int
     */

    function get_next_offset()
    {
        $nextOffset = $this->offset + $this->step;

        if ($nextOffset < $this->totalResultCount) return $nextOffset;
        else                                       return false;
    }

    /**
     * return the offset needed to get the first page
     *
     * @return int
     */

    function get_first_offset()
    {
        return 0;
    }

    /**
     * return the offset needed to get the last page
     *
     * @return int
     */

    function get_last_offset()
    {
        return (int)($this->offsetCount - 1) * $this->step;
    }

    /**
     * return the offset list needed for each page
     *
     * @return array of int
     */

    function get_offset_list()
    {

        $offsetList = array();
        
        for ($i = 0, $currentOffset = 0; 
             $i < $this->offsetCount;
             $i ++)
        {
            $offsetList [] = $currentOffset;
            $currentOffset = $currentOffset + $this->step;
        }

        return $offsetList;
    }


    /**
     * Display a standart pager tool bar
     *
     * @author Hugues Peeters <hugues.peeters@claroline.net>
     * @param  string $url - where the pager tool bar commands need to point to
     * @param  int $linkMax - (optionnal) maximum of page links in the pager tool bar
     * @return void
     */

    function disp_pager_tool_bar($url, $linkMax = 10)
    {
        if ( strrpos($url, '?') === false) $url .= '?'.$this->paramName.'=';
        else                               $url .= '&'.$this->paramName.'=';
        
        $startPage    = $this->get_first_offset();
        $previousPage = $this->get_previous_offset();
        $pageList     = $this->get_offset_list();
        $nextPage     = $this->get_next_offset();
        $endPage      = $this->get_last_offset();


        $output =                                                                                        "\n\n"
                . '<table class="claroPager" border="0" width="100%" cellspacing="0" cellpadding="0">' . "\n"
                . '<tr valign="top">'                                                                  . "\n"
                . '<td align="left" width="20%">'                                                      . "\n"
                ;

        if ($previousPage !== false)
        {
            $output .= '<b>'
                    . '<a href="' . $url . $startPage    . '">|&lt;&lt;</a>&nbsp;&nbsp;'
                    . '<a href="' . $url . $previousPage . '">&lt; </a>'
                    . '</b>'
                    ;
        }
        else
        {
            $output .= '&nbsp;';
        }

        $output .=                                     "\n"
                .  '</td>'                           . "\n"
                .  '<td align="center" width="60%">' . "\n"
                ;

        // current page
        $currentPage = (int) $this->offset / $this->step ;

        // total page
        $pageCount = $this->offsetCount; 

        // start page    
        if ( $currentPage > $linkMax ) $firstLink = $currentPage - $linkMax;
        else                           $firstLink = 0;

        // end page
        if ( $currentPage + $linkMax < $pageCount ) $lastLink = $currentPage + $linkMax;
        else                                        $lastLink = $pageCount;

        // display 1 ... {start_page}
        
        if ( $firstLink > 0 )
        {
            $output .= '<a href="' . $url . $pageList[0] . '">' . (0+1) . '</a>&nbsp;';
            if ( $firstLink > 1 ) $output .= '...&nbsp;';
        } 

        if ( $pageCount > 1) 
        {
            // display page
            for ($link = $firstLink; $link < $lastLink ; $link++)
            {
                if ( $currentPage == $link )
                {
                    $output .= '<b>' . ($link + 1) . '</b> '; // current page
                }
                else
                {
                    $output .= '<a href="' . $url . $pageList[$link] . '">' . ($link + 1) . '</a> ';
                }
            }
        }

        // display 1 ... {start_page}
        if ( $lastLink < $pageCount )
        {
            if ( $lastLink + 1 < $pageCount ) $output .= '...';

            $output .= '&nbsp;<a href="'. $url . $pageList[$pageCount-1] . '">'.($pageCount).'</a>';
        } 

        $output .=                                   "\n"
                .  '</td>'.                          "\n"
                .  '<td align="right" width="20%">'. "\n"
                ;

        if ($nextPage !== false)
        {
            $output .= '<b>'
                    .  '<a href="' . $url . $nextPage . '"> &gt;</a>&nbsp;&nbsp;'
                    .  '<a href="' . $url . $endPage  . '"> &gt;&gt;|</a>'
                    .  '</b>'
                    ;
        }
        else
        {
            $output .= '&nbsp;';
        }

        $output .=             "\n"
                .  '</td>'    ."\n"
                .  '</tr>'    ."\n"
                .  '</table>' ."\n\n"
                ;

        return $output;
    }
}


//function get_sort_url_list($url)
//{
//    $sortArgList = array_keys($this->resultList[0]);
//    $urlList = array();
//
//    foreach($sortArgList as $thisArg)
//    {
//        if ($thisArg == $this->sortKey && $this->sortDir == SORT_ASC)
//        {
//        	$direction = SORT_DESC;
//        }
//        else
//        {
//            $direction = SORT_ASC;
//        }
//        
//        $urlList[] = $url 
//                   . ( strstr($url, '?') !== false ) ? '&amp' : '?'
//                   . 'sort = ' . urlencode($thisArg)
//                   . '&ampdir=' . $direction;
//    }
//
//    return $urlList;
//}
//
//function sort_by($key, $direction)
//{
//    $this->sortKey = $key;
//    $this->sortDir = $direction;
//
//    if     ( $direction == SORT_DESC) $direction == 'DESC';
//    elseif ( $direction == SORT_ASC ) $direction == 'ASC';
//
//    $this->sql . = 'ORDER BY ' . $key . ' ' . $direction;
//}


?>
