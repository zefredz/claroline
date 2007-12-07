<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');

/**
 * Pager class allowing to manage the paging system into claroline
 *
 *  exemple : $myPager = new claro_sql_pager('SELECT * FROM USER', $offset, $step);
 *
 *            echo '<table><tr><td>';
 *
 *            $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
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
 * set_pager_call_param_name($paramName) method
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 */

/**
 * @package DISPLAY
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
            // -- Only works with mySQL 4. Usefeul if the scritp calls
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
     * @param  string $url where the pager tool bar commands need to point to
     * @return void
     */

    function disp_pager_tool_bar($url)
    {
        if (strrpos($url, '?') === false) $url .= '?'.$this->paramName.'=';
        else                             $url .= '&'.$this->paramName.'=';

        $start    = $this->get_first_offset();
        $previous = $this->get_previous_offset();
        $pageList = $this->get_offset_list();
        $next     = $this->get_next_offset();
        $end      = $this->get_last_offset();



        echo "\n\n".'<table class="claroPager" border="0" width="100%" cellspacing="0" cellpadding="0">'."\n"
            .'<tr valign="top">'."\n"
            .'<td align="left" width="20%">'."\n";

        if ($previous !== false)
        {
            echo '<b><a href="'.$url.$start.'">|&lt;&lt;</a>&nbsp;&nbsp;</b>'
                .'<b><a href="'.$url.$previous.'">&lt; </a></b>';
        }
        else
        {
			echo '&nbsp;';
		}

        echo "\n".'</td>'."\n"

            .'<td align="center" width="60%">'."\n";

        // current page
        $current_page = (int)$this->offset/$this->step ;
        // total page
        $count_page = $this->offsetCount;
        // start page
        if ( $current_page > 10 ) $start_page = $current_page - 10;
        else                      $start_page = 0;
        // end page
        if ( $current_page + 10 < $count_page ) $end_page = $current_page + 10;
        else                                    $end_page = $count_page;

        // display 1 ... {start_page}
        if ( $start_page > 0 )
        {
            echo '<a href="'.$url.$pageList[0].'">'.(0+1).'</a>&nbsp;';
            if ( $start_page > 1 ) echo '...&nbsp;';
        }

        if ( $count_page > 1)
        {
            // display page
            for ($page=$start_page; $page < $end_page ; $page++)
            {
                if ( $current_page == $page )
                {
                    echo '<b>'.($page+1).'</b> '; // current page
                }
                else
                {
                    echo '<a href="'.$url.$pageList[$page].'">'.($page+1).'</a> ';
                }
            }
        }

        // display 1 ... {start_page}
        if ( $end_page < $count_page )
        {
            if ( $end_page + 1 < $count_page ) echo '...';
            echo '&nbsp;<a href="'.$url.$pageList[$count_page-1].'">'.($count_page).'</a>';
        }

        echo "\n".'</td>'."\n"
        .    '<td align="right" width="20%">'."\n"
        ;

        if ($next !== false)
        {
            echo '<b><a href="' . $url.$next . '"> &gt;</a>&nbsp;&nbsp;</b>'
            .    '<b><a href="' . $url.$end . '"> &gt;&gt;|</a></b>'
            ;
        }
		else
        {
			echo '&nbsp;';
		}

        echo "\n".'</td>'."\n"
        .    '</tr>'."\n"
        .    '</table>'."\n\n"
        ;
    }

    function set_pager_call_param_name($paramName)
    {
    	$this->paramName = $paramName;
    }
}

?>
