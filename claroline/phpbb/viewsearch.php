<?php # -$Id$

$tlabelReq = 'CLFRM___';

require '../inc/claro_init_global.inc.php';
require $includePath .'/lib/forum.lib.php';

$nameTools         = $langForums;
$last_visit        = $_user['lastLogin'];
$is_groupPrivate   = $_groupProperties ['private'];
$is_allowedToEdit  = claro_is_allowed_to_edit();

if (  !isset($_cid) || !isset($is_courseAllowed) || $is_courseAllowed == FALSE ) claro_disp_auth_form(true);

if (isset($_REQUEST['searchPattern']))
{
    $searchPatternString = trim($_REQUEST['searchPattern']);

    if ($searchPatternString != '')
    {
        $searchPatternList = explode(' ', $searchPatternString);
        $sqlClauseList = '';

        foreach($searchPatternList as $thisSearchPattern)
        {
            $thisSearchPattern = str_replace('_', '\\_', $thisSearchPattern);
            $thisSearchPattern = str_replace('%', '\\%', $thisSearchPattern);
            $thisSearchPattern = str_replace('?', '_' , $thisSearchPattern);
            $thisSearchPattern = str_replace('*', '%' , $thisSearchPattern);

            $sqlClauseList[] = 
            "   pt.post_text  LIKE '%".addslashes($thisSearchPattern)."%'
             OR p.nom           LIKE '%".addslashes($thisSearchPattern)."%'
             OR p.prenom        LIKE '%".addslashes($thisSearchPattern)."%'
             OR t.topic_title   LIKE '%".addslashes($thisSearchPattern)."%'";
        }

        $sqlClauseString = implode("\n OR \n", $sqlClauseList);

        $tbl_cdb_names  = claro_sql_get_course_tbl();
        $tbl_posts_text = $tbl_cdb_names['bb_posts_text'];
        $tbl_posts      = $tbl_cdb_names['bb_posts'     ];
        $tbl_topics     = $tbl_cdb_names['bb_topics'    ];
        $tbl_forums     = $tbl_cdb_names['bb_forums'    ];

        $sql = "SELECT pt.post_id, pt.post_text, 
                       p.nom lastname, p.prenom firstname, p.post_time,
                       t.topic_id, t.topic_title,
                       f.forum_id, f.forum_name, f.group_id
               FROM   ".$tbl_posts_text." pt, 
                      ".$tbl_posts."     p, 
                      ".$tbl_topics."    t, 
                      ".$tbl_forums."    f
               WHERE ( ". $sqlClauseString . ")
                 AND pt.post_id = p.post_id
                 AND p.topic_id = t.topic_id
                 AND t.forum_id = f.forum_id
               ORDER BY p.post_time DESC, t.topic_id";

        $searchResultList = claro_sql_query_fetch_all($sql);

        $userGroupList  = get_user_group_list($_uid);
        $tutorGroupList = get_tutor_group_list($_uid);
    }
}

$pagetype= 'viewsearch';

include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title($langForums, 
                           $is_allowedToEdit ? 'help_forum.php' : false);

disp_forum_toolbar($pagetype, null);

disp_forum_breadcrumb($pagetype, null, null, null);


echo '<table class="claroTable" width="100%">'                          . "\n"
.    '<tr align="left">'                                                . "\n"
.    '<th class="superHeader">'                                         . "\n"
.    $langSearchResult. ' : '.htmlspecialchars($_REQUEST['searchPattern']) . "\n"
.    '</th>'                                                            . "\n"
.    '</tr>'                                                            . "\n";

    if (count($searchResultList) < 1 )
    {
        echo '<tr><td align="center">' . $langNoResult . '</td></tr>';
    }
    else foreach ( $searchResultList as $thisPost )
    {
        // PREVENT USER TO CONSULT POST FROM A GROUP THEY ARE NOT ALLOWED
        if (    ! is_null($thisPost['group_id'])
            &&  $is_groupPrivate
            && ! (    in_array($thisPost['group_id'], $userGroupList )
                   || in_array($thisPost['group_id'], $tutorGroupList)
                   || $is_courseAdmin
                 )
           )
        {
           continue;
        }
        else
        {
            // Check if the forum post is after the last login
            // and choose the image according this state
            $post_time = datetime_to_timestamp($thisPost['post_time']);

            if($post_time < $last_visit) $postImg = 'post.gif';
            else                         $postImg = 'post_hot.gif';

            echo '<tr>'                                                   . "\n"

            .   '<th class="headerX">'                                    . "\n"
            .   '<img src="' . $imgRepositoryWeb . 'topic.gif" alt="">'
            .   '<a href="viewtopic.php?topic='.$thisPost['topic_id'].'">'
            .   $thisPost['topic_title'] 
            .   '</a><br />'                                              . "\n"
            .   '<img src="' . $imgRepositoryWeb . $postImg . '" alt="">'
            .   $l_author . ' : <b>' . $thisPost['firstname'] . ' ' . $thisPost['lastname'] . '</b> '
            .   '<small>' . $l_posted . ' : ' . $thisPost['post_time'] . '</small>' . "\n"
            .   '</th>'                                                  . "\n"

            .   '</tr>'                                                  . "\n"

            .   '<tr>'                                                   . "\n"
        
            .   '<td>'                                                   . "\n"
            .   claro_parse_user_text($thisPost['post_text'])            . "\n"
            .   '</td>'                                                  . "\n"
            .   '</tr>'                                                  . "\n";
        } // end else if ( ! is_null($thisPost['group_id'])
	
	} // end for each
	
	echo '</table>' . "\n";
	
/*-----------------------------------------------------------------
  Display Forum Footer
 -----------------------------------------------------------------*/

include $includePath.'/claro_init_footer.inc.php';

?>
