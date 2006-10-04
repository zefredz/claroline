<?php // $Id$
/**
 * CLAROLINE
 *
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLSTAT
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @todo to factorise sql
 * @todo to split work and output
 *
 */

require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed'));

include_once $includePath . '/lib/statsUtils.lib.inc.php';


$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_track_e_login           = $tbl_mdb_names['track_e_login'];

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_group_rel_team_user     = $tbl_cdb_names['group_rel_team_user'    ];
$tbl_track_e_downloads       = $tbl_cdb_names['track_e_downloads'      ];
$tbl_track_e_access          = $tbl_cdb_names['track_e_access'         ];
$tbl_track_e_exercises       = $tbl_cdb_names['track_e_exercices'      ];
$tbl_qwz_exercise			 = $tbl_cdb_names['qwz_exercise'];
$tbl_bb_topics               = $tbl_cdb_names['bb_topics'                ];
$tbl_bb_posts                = $tbl_cdb_names['bb_posts'                ];

// regroup table names for maintenance purpose

$nameTools = get_lang('Statistics');
include($includePath . '/claro_init_header.inc.php');
echo claro_html_tool_title(
    array(
        'mainTitle' => $nameTools,
        'subTitle'  => get_lang('Statistics of course : %courseCode', array('%courseCode' => $_course['officialCode']))
    )
);

// check if uid is prof of this group

if( get_conf('is_trackingEnabled'))
{
    // in $view, a 1 in X posof the $view string means that the 'category' number X
    // will be show, 0 means don't show
    echo '<small>'
        .'[<a href="'.$_SERVER['PHP_SELF'].'?view=1111111">'.get_lang('Show all').'</a>]'
        .'&nbsp;[<a href="'.$_SERVER['PHP_SELF'].'?view=0000000">'.get_lang('Show none').'</a>]'
        .'</small>'."\n\n"
        ;

    if( isset($_REQUEST['view']))   $view = $_REQUEST['view'];
    else                            $view ="0000000";

    $viewLevel = -1; //  position of the flag of the view in the $view array/string
    /***************************************************************************
     *
     *        Main
     *
     ***************************************************************************/

    $tempView = $view;
    $viewLevel++;
    echo '<p>'."\n";
    if($view[$viewLevel] == '1')
    {
        $tempView[$viewLevel] = '0';
        echo '-&nbsp;&nbsp;<b>'.get_lang('Users').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        //-- total number of user in the course
        $sql = "SELECT count(*)
                    FROM `".$tbl_rel_course_user."`
                    WHERE code_cours = '".$_cid."'";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Number of users').' : '.$count.'<br />'."\n";

        //--  student never connected
        $sql = "SELECT  U.`user_id`, U.`nom` AS `lastname`, U.`prenom` AS `firstname`
            FROM `".$tbl_user."` AS U, `".$tbl_rel_course_user."` AS CU
            LEFT JOIN `".$tbl_track_e_access."` AS A
            ON A.`access_user_id` = CU.`user_id`
            WHERE U.`user_id` = CU.`user_id`
            AND CU.`code_cours` = '".$_cid."'
            AND A.`access_user_id` IS NULL
            ";
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Never connected students : ');

        $results = claro_sql_query_fetch_all($sql);

        if( !empty($results) && is_array($results) )
        {
            echo '<ul>'."\n";
            foreach( $results as $result )
            {
                echo '<li>'
                    .'<a href="../user/userInfo.php?uInfo='.$result['user_id'].'">'.$result['firstname'].' '.$result['lastname'].'</a>'
                    .'</li>'."\n";
            }
            echo '</ul>'."\n";
        }
        else
        {
            echo '<small>'.get_lang('No result').'</small><br />'."\n";
        }
        //-- student not connected for 1 month
        $sql = "SELECT U.`user_id`, U.`nom` AS `lastname`, U.`prenom` AS `firstname`, MAX(A.`access_date`) AS `max_access_date`
            FROM `".$tbl_user."` AS U, `".$tbl_rel_course_user."` AS CU, `".$tbl_track_e_access."` AS A
            WHERE U.`user_id` = CU.`user_id`
            AND CU.`code_cours` = '".$_cid."'
            AND U.`user_id` = A.`access_user_id`
            GROUP BY A.`access_user_id`
            HAVING `max_access_date` < ( NOW() - INTERVAL 15 DAY )
            ORDER BY A.`access_date` ASC
            ";
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Not recently connected students : ');

        $results = claro_sql_query_fetch_all($sql);
        if( !empty($results) && is_array($results) )
        {
            echo '<ul>'."\n";
            foreach( $results as $result )
            {
                    echo '<li>'
                        .'<a href="../user/userInfo.php?uInfo='.$result['user_id'].'">'.$result['firstname'].' '.$result['lastname'].'</a> ( '.get_lang('Last access').' : '.$result['max_access_date'].' )'
                        .'</li>'."\n";
            }
            echo '</ul>'."\n";
        }
        else
        {
            echo '<small>'.get_lang('No result').'</small><br />'."\n";
        }
    }
    else
    {
        $tempView[$viewLevel] = '1';
        echo '+&nbsp;&nbsp;&nbsp;'
            .'<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'
            .get_lang('Users')
            .'</a>'
            ;
    }
    echo '</p>'
        ."\n\n"
        ;
    /***************************************************************************
     *        Access to this course
     ***************************************************************************/
    $tempView = $view;
    $viewLevel++;
    echo '<p>'."\n";
    if($view[$viewLevel] == '1')
    {
        $tempView[$viewLevel] = '0';
        echo '-&nbsp;&nbsp;<b>'.get_lang('Course access').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        $sql = "SELECT count(`access_id`)
                    FROM `".$tbl_track_e_access."`
                    WHERE access_tid IS NULL";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Total number of connection to this course').' : '.$count.'<br />'."\n";

        // last 31 days
        $sql = "SELECT count(`access_id`)
                    FROM `".$tbl_track_e_access."`
                    WHERE (access_date > DATE_ADD(CURDATE(), INTERVAL -31 DAY))
                        AND access_tid IS NULL";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last 31 days').' : '.$count.'<br />'."\n";

        // last 7 days
        $sql = "SELECT count(`access_id`)
                    FROM `".$tbl_track_e_access."`
                    WHERE (access_date > DATE_ADD(CURDATE(), INTERVAL -7 DAY))
                        AND access_tid IS NULL";
        $count = claro_sql_query_get_single_value($sql);
        echo '&nbsp;&nbsp;&nbsp;'.get_lang('Last 7 days').' : '.$count.'<br />'."\n";

        // today
        $sql = "SELECT count(`access_id`)
                    FROM `".$tbl_track_e_access."`
                    WHERE ( access_date > CURDATE() )
                        AND access_tid IS NULL";
        $count = claro_sql_query_get_single_value($sql);

        echo '&nbsp;&nbsp;&nbsp;'
            .get_lang('This day')
            .' : '
            .$count
            .'<br />'."\n";

        // today user list
        $sql = "SELECT U.`user_id`, U.`nom` AS `lastname`, U.`prenom` AS `firstname`,
                    MAX(A.`access_date`) AS `max_access_date`, count(A.`access_date`) AS `access_nbr`
            FROM `".$tbl_track_e_access."` AS A
            LEFT JOIN `".$tbl_user."` AS U
                ON U.`user_id` = A.`access_user_id`
            WHERE access_date > CURDATE()
            AND access_tid IS NULL
            GROUP BY A.`access_user_id`
            ORDER BY A.`access_date` ASC
            ";

        $results = claro_sql_query_fetch_all($sql);
        if( !empty($results) && is_array($results) )
        {
            echo '<ul>'."\n";
            foreach( $results as $result )
            {

                echo '<li>';
                if ( empty($result['user_id']) || empty($result['firstname']) )
                {
                    echo get_lang('Anonymous')
                        .' <small>( '.get_lang('Last access').' : '.$result['max_access_date'].' ; '.get_lang('Total').' : '.$result['access_nbr'].' )</small>';
                }
                else
                {
                    echo '<a href="../user/userInfo.php?uInfo='.$result['user_id'].'">'.$result['firstname'].' '.$result['lastname'].'</a>'
                        .' <small>( '.get_lang('Last access').' : '.$result['max_access_date'].' ; '.get_lang('Total').' : '.$result['access_nbr'].' )</small>';
                }
                echo '</li>'."\n";
            }
            echo '</ul>'."\n";
        }
        //-- view details of traffic
        echo '&nbsp;&nbsp;&nbsp;'
            .'<a href="course_access_details.php">'.get_lang('Traffic Details').'</a><br />'
            ."\n"
            ;

    }
    else
    {
        $tempView[$viewLevel] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Course access').'</a>';

    }
    echo '</p>'."\n\n";
    /***************************************************************************
     *
     *        Tools
     *
     ***************************************************************************/
    $tempView = $view;
    $viewLevel++;
    echo '<p>'."\n";
    if($view[$viewLevel] == '1')
    {
        $tempView[$viewLevel] = '0';
        echo '-&nbsp;&nbsp;<b>'.get_lang('Access to tools').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        $sql = "SELECT `access_tid`,
                COUNT(DISTINCT `access_user_id`) AS `nbr_distinct_users_access`,
                COUNT( `access_tid` )            AS `nbr_access`,
                        `access_tlabel`
                    FROM `" . $tbl_track_e_access . "`
                    WHERE `access_tid` IS NOT NULL
                      AND `access_tid` <> ''
                    GROUP BY `access_tid`";

        $results = claro_sql_query_fetch_all($sql);
        echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>&nbsp;'.get_lang('Name of the tool').'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.get_lang('Users\' Clicks').'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.get_lang('Total Clicks').'&nbsp;</th>'."\n"
            .'</tr>'."\n"
            .'<tbody>'."\n"
            ;
        $toolNameList= claro_get_tool_name_list();

        if( !empty($results) && is_array($results))
        {
            foreach( $results as $result )
            {
                echo '<tr>' . "\n"
                .    '<td>'
                .    '<a href="toolaccess_details.php?toolId='.$result['access_tid'].'">'
                .    $toolNameList[trim($result['access_tlabel'],'_')] . '</a></td>' . "\n"
                .    '<td align="right"><a href="user_access_details.php?cmd=tool&amp;id='.$result['access_tid'].'">'.$result['nbr_distinct_users_access'] . '</a></td>' . "\n"
                .    '<td align="right">' . $result['nbr_access'] . '</td>' . "\n"
                .    '</tr>'
                .    "\n\n"
                ;
            }

        }
        else
        {
            echo '<tr>'."\n"
                .'<td colspan="3"><div align="center">'.get_lang('No result').'</div></td>'."\n"
                .'</tr>'."\n"
                ;
        }
        echo '</tbody>'
        .    '</table>'."\n"
        ;
    }
    else
    {
        $tempView[$viewLevel] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Access to tools').'</a>';
    }
    echo '</p>'."\n\n";
    /***************************************************************************
     *
     *        Documents
     *
     ***************************************************************************/
    $tempView = $view;
    $viewLevel++;
    echo '<p>'."\n";
    if($view[$viewLevel] == '1')
    {
        $tempView[$viewLevel] = '0';
        echo '-&nbsp;&nbsp;<b>'.get_lang('Documents').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        $sql = "SELECT `down_doc_path` AS `path`,
                        COUNT(DISTINCT `down_user_id`) AS `nbr_distinct_user_downloads`,
                        COUNT(`down_doc_path`) AS `nbr_total_downloads`
                    FROM `".$tbl_track_e_downloads."`
                    GROUP BY `down_doc_path`";

        $results = claro_sql_query_fetch_all($sql);

        echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>&nbsp;'.get_lang('Document').'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.get_lang('Users Downloads').'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.get_lang('Total Downloads').'&nbsp;</th>'."\n"
            .'</tr>'."\n"
            .'<tbody>'."\n"
            ;
        if( !empty($results) && is_array($results) )
        {
            foreach( $results as $result )
            {
                    echo '<tr>'."\n"
                        .'<td>'.$result['path'].'</td>'."\n"
                        .'<td align="right"><a href="user_access_details.php?cmd=doc&amp;path='.urlencode($result['path']).'">'.$result['nbr_distinct_user_downloads'].'</a></td>'."\n"
                        .'<td align="right">'.$result['nbr_total_downloads'].'</td>'."\n"
                        .'</tr>'."\n\n"
                        ;
            }

        }
        else
        {
            echo '<tr>'."\n"
                .'<td colspan="3"><div align="center">'.get_lang('No result').'</div></td>'."\n"
                .'</tr>'."\n"
                ;
        }
        echo '</tbody>'
            .'</table>'."\n"
            ;
    }
    else
    {
        $tempView[$viewLevel] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Documents').'</a>';
    }
    echo '</p>'."\n\n";

    /***************************************************************************
     *        Exercises
     ***************************************************************************/
    $tempView = $view;
    $viewLevel++;
    echo '<p>'."\n";
    if($view[$viewLevel] == '1')
    {
        $tempView[$viewLevel] = '0';
        echo '-&nbsp;&nbsp;<b>'.get_lang('Exercises').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'" >'.get_lang('Close').'</a>]</small><br />'."\n";

        $sql = "SELECT TEX.`exe_exo_id`,
                        COUNT(DISTINCT TEX.`exe_user_id`) AS `nbr_distinct_user_attempts`,
                        COUNT(TEX.`exe_exo_id`) AS `nbr_total_attempts`,
                        EX.`title`
                    FROM `".$tbl_track_e_exercises."` AS TEX, `".$tbl_qwz_exercise."` AS EX
                    WHERE TEX.`exe_exo_id` = EX.`id`
                    GROUP BY TEX.`exe_exo_id`";

        $results = claro_sql_query_fetch_all($sql);
        echo '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>&nbsp;'.get_lang('Exercises').'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.get_lang('User attempts').'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.get_lang('Total attempts').'&nbsp;</th>'."\n"
            .'</tr>'."\n"
            .'<tbody>'."\n"
            ;

        if( !empty($results) && is_array($results) )
        {
            foreach( $results as $result )
            {
                    echo '<tr>'."\n"
                        .'<td><a href="exercises_details.php?exId='.$result['exe_exo_id'].'">'.$result['title'].'</a></td>'."\n"
                        .'<td align="right">'.$result['nbr_distinct_user_attempts'].'</td>'."\n"
                        .'<td align="right">'.$result['nbr_total_attempts'].'</td>'."\n"
                        .'</tr>'."\n\n"
                        ;
            }
        }
        else
        {
            echo '<tr>' . "\n"
            .    '<td colspan="3">'
            .    '<div align="center">' . get_lang('No result') . '</div>'
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;
        }
        echo '</tbody>'."\n"
            .'</table>'."\n"
            ;
    }
    else
    {
        $tempView[$viewLevel] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Exercises').'</a>';
    }
    echo '</p>'."\n\n";

    /***************************************************************************
     *
     *        Forum posts
     *
     ***************************************************************************/
    $tempView = $view;
    $viewLevel++;
    echo '<p>'."\n";
    if($view[$viewLevel] == '1')
    {
        $tempView[$viewLevel] = '0';

        echo '-&nbsp;&nbsp;<b>'.get_lang('Forum usage').'</b>&nbsp;&nbsp;&nbsp;<small>[<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Close').'</a>]</small><br />'."\n";

        // total number of posts
        $sql = "SELECT count(`post_id`)
                        FROM `".$tbl_bb_posts."`";
        $totalPosts = claro_sql_query_get_single_value($sql);

        // total number of threads
        $sql = "SELECT count(`topic_title`)
                        FROM `".$tbl_bb_topics."`";
        $totalTopics = claro_sql_query_get_single_value($sql);

        // display total of posts and threads
        echo '<ul>'."\n"
            .'<li>'.get_lang('Messages posted').' : '.$totalPosts.'</li>'."\n"
            .'<li>'.get_lang('Topics started').' : '.$totalTopics.'</li>'."\n";
        // top 10 topics more active (more responses)
        $sql = "SELECT `topic_id`, `topic_title`, `topic_replies`
                    FROM `".$tbl_bb_topics."`
                    ORDER BY `topic_replies` DESC
                    LIMIT 10
                    ";
        $results = claro_sql_query_fetch_all($sql);
        echo '<li>'.get_lang('More replied topics').'<br />'
            .'<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>'.get_lang('Topic').'</th>'."\n"
            .'<th>'.get_lang('Replies').'</th>'."\n"
            .'</tr>'."\n";
        if( !empty($results) && is_array($results) )
        {
            echo '<tbody>'."\n";
            foreach( $results as $result )
            {
                echo '<tr>'."\n"
                    .'<td><a href="../phpbb/viewtopic.php?topic='.$result['topic_id'].'">'.$result['topic_title'].'</a></td>'."\n"
                    .'<td>'.$result['topic_replies'].'</td>'."\n"
                    .'</tr>'."\n";
            }
            echo '</tbody>'."\n";

        }
        else
        {
            echo '<tfoot>'."\n".'<tr>'."\n"
                .'<td align="center">'.get_lang('No result').'</td>'."\n"
                .'</tr>'."\n".'</tfoot>'."\n";
        }
        echo '</table>'."\n"
            .'</li>'."\n";


        // top 10 topics more seen
        $sql = "SELECT `topic_id`, `topic_title`, `topic_views`
                    FROM `".$tbl_bb_topics."`
                    ORDER BY `topic_views` DESC
                    LIMIT 10
                    ";
        $results = claro_sql_query_fetch_all($sql);

        echo '<li>'.get_lang('More seen topics').'<br />'
            .'<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>'.get_lang('Topic').'</th>'."\n"
            .'<th>'.get_lang('Seen').'</th>'."\n"
            .'</tr>'."\n";
        if( !empty($results) && is_array($results) )
        {
            echo '<tbody>'."\n";
            foreach( $results as $result )
            {
                echo '<tr>'."\n"
                    .'<td><a href="../phpbb/viewtopic.php?topic='.$result['topic_id'].'">'.$result['topic_title'].'</a></td>'."\n"
                    .'<td>'.$result['topic_views'].'</td>'."\n"
                    .'</tr>'."\n";
            }
            echo '</tbody>'."\n";

        }
        else
        {
            echo '<tfoot>'."\n".'<tr>'."\n"
                .'<td align="center">'.get_lang('No result').'</td>'."\n"
                .'</tr>'."\n".'</tfoot>'."\n";
        }
        echo '</table>'."\n"
            .'</li>'."\n";

        // last 10 distinct messages posted
        $sql = "SELECT `bb_t`.`topic_id`, `bb_t`.`topic_title`, max(`bb_t`.`topic_time`) as `last_message`
                FROM `".$tbl_bb_posts."` as `bb_p`, `".$tbl_bb_topics."` as `bb_t`
                WHERE `bb_t`.`topic_id` = `bb_p`.`topic_id`
                GROUP BY `bb_t`.`topic_title`
                ORDER BY `bb_p`.`post_time` DESC
                LIMIT 10";

        $results = claro_sql_query_fetch_all($sql);

        echo '<li>'.get_lang('Last active topics').'<br />'
            .'<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
                .'<tr class="headerX">'."\n"
                .'<th>'.get_lang('Topic').'</th>'."\n"
                .'<th>'.get_lang('Last message').'</th>'."\n"
                .'</tr>'."\n";
        if (is_array($results))
        {
            echo '<tbody>'."\n";
            foreach( $results as $result )
            {
                    echo '<tr>'."\n"
                            .'<td><a href="../phpbb/viewtopic.php?topic='.$result['topic_id'].'">'.$result['topic_title'].'</a></td>'."\n"
                            .'<td>'.$result['last_message'].'</td>'."\n"
                            .'</tr>'."\n";
            }
            echo '</tbody>'."\n";

        }
        else
        {
            echo '<tfoot>'."\n".'<tr>'."\n"
                    .'<td align="center">'.get_lang('No result').'</td>'."\n"
                    .'</tr>'."\n".'</tfoot>'."\n";
        }
        echo '</table>'."\n"
            .'</li>';

        echo '</ul>';
    }
    else
    {
        $tempView[$viewLevel] = '1';
        echo '+&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?view='.$tempView.'">'.get_lang('Forum usage').'</a>';
    }
    echo '<br /></p>'."\n\n";

    // display link to delete all course stats
    echo '<hr />'."\n"
        .'<a class="claroButton" href="delete_course_stats.php">'
        .'<img src="'.$imgRepositoryWeb.'delete.gif" alt="">'.get_lang('Delete all course statistics')
        .'</a>'."\n";
}
// not allowed
else
{
    echo get_lang('Tracking has been disabled by system administrator.');
}

include($includePath."/claro_init_footer.inc.php");
?>
