<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 * @copyright (c) 2001-2008 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package CLCOURSELIST
 * @author Claro Team <cvs@claroline.net>
 */

class category_browser
{
    /**
     * constructor
     *
     * @param mixed $categoryCode null or valid category_code
     * @param mixed $userId null or valid user_id
     * @return category_browser object
     */
    function category_browser($categoryCode = null, $userId = null)
    {
        $this->categoryCode = $categoryCode;
        $this->userId       = $userId;

        $tbl_mdb_names         = claro_sql_get_main_tbl();
        $tbl_courses           = $tbl_mdb_names['course'  ];
        $tbl_courses_nodes     = $tbl_mdb_names['category'];
        
        $curdate = date('Y-m-d H:i:s', time());

        $sql = "SELECT `faculte`.`code`  , `faculte`.`name`,
                       `faculte`.`code_P`, `faculte`.`nb_childs`,
                       COUNT( `cours`.`cours_id` ) AS `nbCourse`
                FROM `" . $tbl_courses_nodes . "` AS `faculte`

                LEFT JOIN `" . $tbl_courses_nodes . "` AS `subCat`
                       ON (`subCat`.`treePos` >= `faculte`.`treePos`
                      AND `subCat`.`treePos` <= (`faculte`.`treePos`+`faculte`.`nb_childs`) )

                LEFT JOIN `" . $tbl_courses . "` AS `cours`
                       ON `cours`.`faculte` = `subCat`.`code`
                       AND `cours`.visibility = 'VISIBLE'
                       ";

        if ($categoryCode)
        {
            $sql .= "WHERE UPPER(`faculte`.`code_P`) = UPPER('" . claro_sql_escape($categoryCode) . "')
                        OR UPPER(`faculte`.`code`)   = UPPER('" . claro_sql_escape($categoryCode) . "') \n";
        }
        else
        {
            $sql .= "WHERE `faculte`.`code`   IS NULL
                        OR `faculte`.`code_P` IS NULL \n";
        }

         $sql .= "AND (`cours`.`status` = 'enable'
                       OR (`cours`.`status` = 'date'
                          AND (`cours`.`creationDate` < '". $curdate ."' OR `cours`.`creationDate` IS NULL OR UNIX_TIMESTAMP(`cours`.`creationDate`)=0)
                          AND ('". $curdate ."'<`cours`.`expirationDate`  OR `cours`.`expirationDate` IS NULL)))
                  GROUP  BY `faculte`.`code`
                  ORDER BY  `faculte`.`treePos`";
            

        $this->categoryList = claro_sql_query_fetch_all($sql);
    }

    /**
     * @since 1.8
     * @return array list of setting of the current category
     */
    function get_current_category_settings()
    {
        if ($this->categoryCode) return $this->categoryList[0];
        else                     return null;
    }

    /**
     * @since 1.8
     * @return array list of sub category of the current category
     */
    function get_sub_category_list()
    {
        if ($this->categoryCode) return array_slice($this->categoryList, 1);
        else                     return $this->categoryList;
    }

    /**
     * Fetch list of courses of the current category
     *
     * This list include main data about
     * the user but also registration status
     *
     * @since 1.8
     * @return array list of courses of the current category
     */
    function get_course_list()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_courses   = $tbl_mdb_names['course'];
        $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
        
        $curdate = date('Y-m-d H:i:s', time());

        $sql = "SELECT intitule             AS title,
                       titulaires           AS titular,
                       code                 AS sysCode,
                       administrativeNumber AS officialCode,
                                              `language`,
                                               directory,
                                               visibility,
                                               access,
                                               registration,
                                               email,
                                               status,
                       "
              . ( $this->userId ? 'isCourseManager, ' : '')."
                       "
              . ( $this->userId ? "cu.user_id" : "NULL") . " AS enroled "

              . " FROM `" . $tbl_courses . "` AS c
                "
              . ($this->userId
                 ? "LEFT JOIN `" . $tbl_rel_course_user . "` AS `cu`
                           ON  `c`.`code`    = `cu`.`code_cours`
                          AND `cu`.`user_id` = " . (int) $this->userId . "
                   "
                 : " ")
                 
              . "WHERE c.`faculte` = '" . addslashes($this->categoryCode) . "'
                 AND visibility = 'VISIBLE' 
                 AND (`status` = 'enable' 
                     OR (`status` = 'date'
                         AND (`creationDate` < '". $curdate ."' OR `creationDate` IS NULL OR UNIX_TIMESTAMP(`creationDate`)=0)
                         AND ('". $curdate ."'<`expirationDate`  OR `expirationDate` IS NULL)))"
                 . ($this->userId ? "OR NOT (cu.user_id IS NULL)" :"") .
                 " ORDER BY UPPER(c.administrativeNumber)";

        return claro_sql_query_fetch_all($sql);
    }
}

/**
 * Search a specific course based on his course code
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  string  $keyword course code from the cours table
 * @param  mixed   $userId  null or valid id of a user (default:null)
 *
 * @return array    course parameters
 */

function search_course($keyword, $userId = null)
{
   $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_course           = $tbl_mdb_names['course'         ];
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'];

    $keyword = trim($keyword);

    if (empty($keyword) ) return array();

    $upperKeyword = addslashes(strtoupper($keyword));
    
    $curdate = date('Y-m-d H:i:s', time());

    $sql = "SELECT c.intitule             AS title,
                   c.titulaires           AS titular,
                   c.code                 AS sysCode,
                   c.administrativeNumber AS officialCode,
                   c.directory            AS directory,
                   c.code                 AS code,
                   c.email                AS email,
                   c.visibility,
                   c.access,
                   c.registration,
                   c.status,
                   c.creationDate,
                   c.expirationDate"

         .  ($userId ? ", cu.user_id AS enroled" : "")
         . " \n "
         .  "FROM `" . $tbl_course . "` c "
         . " \n "
         .  ($userId ? "LEFT JOIN `" . $tbl_rel_course_user . "` AS cu
                        ON  c.code = cu.code_cours
                        AND cu.user_id = " . (int) $userId
                     :  "")
         . " \n "
         
         . "WHERE ( "
         . (claro_is_platform_admin() ? '' : 
            "(visibility = 'VISIBLE'
                AND ( `status`='enable'
                        OR ( `status` = 'date'
                            AND ( `creationDate` < '" . $curdate . "'
                                OR `creationDate` IS NULL
                                OR UNIX_TIMESTAMP(`creationDate`) = 0
                                )
                            AND ( '" . $curdate . "' < `expirationDate`
                                OR `expirationDate` IS NULL
                                )
                            )
                    )
            "
            . ( $userId ? " OR cu.user_id " : "")
            . " ) AND "
            )
             . "
            ( UPPER(administrativeNumber)   LIKE '%" . $upperKeyword . "%'
                OR UPPER(intitule)              LIKE '%" . $upperKeyword . "%'
                OR UPPER(titulaires)            LIKE '%" . $upperKeyword . "%'
                )"
            . "
            )
            ORDER BY officialCode";

    $courseList = claro_sql_query_fetch_all($sql);

    if (count($courseList) > 0) return $courseList;
    else                        return array() ;
}

/**
 * Return the list of course of a user.
 *
 * @param int $userId valid id of a user
 * @param boolean $renew whether true, force to read databaseingoring an existing cache.
 * @return array (list of course) of array (course settings) of the given user.
 * @todo search and merge other instance of this functionality
 */

function get_user_course_list($userId, $renew = false)
{
    static $cached_uid = null, $userCourseList = null;

    if ($cached_uid != $userId || is_null($userCourseList) || $renew)
    {
        $cached_uid = $userId;

        $tbl_mdb_names         = claro_sql_get_main_tbl();
        $tbl_courses           = $tbl_mdb_names['course'         ];
        $tbl_link_user_courses = $tbl_mdb_names['rel_course_user'];

        $curdate = claro_mktime();
        
        $sql = "SELECT course.code                 AS `sysCode`,
                       course.directory            AS `directory`,
                       course.administrativeNumber AS `officialCode`,
                       course.dbName               AS `db`,
                       course.intitule             AS `title`,
                       course.titulaires           AS `titular`,
                       course.language             AS `language`,
                       course.faculte              AS `categoryCode`,
                       course_user.isCourseManager,
                       course.status,
                       UNIX_TIMESTAMP(course.expirationDate) AS expirationDate,
                       UNIX_TIMESTAMP(course.creationDate)     AS creationDate

                       FROM `" . $tbl_courses . "`           AS course,
                            `" . $tbl_link_user_courses . "` AS course_user

                       WHERE course.code         = course_user.code_cours
                         AND course_user.user_id = " . (int) $userId . " 
                         AND (course.`status`='enable'
                             OR (course.`status` = 'date'
                                  AND (UNIX_TIMESTAMP(`creationDate`) < '". $curdate ."' 
                                  OR `creationDate` IS NULL OR UNIX_TIMESTAMP(`creationDate`)=0)
                                  AND ('". $curdate ."' < UNIX_TIMESTAMP(`expirationDate`) OR `expirationDate` IS NULL)
                                  )
                              ) \n " ;

        if ( get_conf('course_order_by') == 'official_code' )
        {
            $sql .= " ORDER BY UPPER(`administrativeNumber`), `title`";
        }
        else
        {
            $sql .= " ORDER BY `title`, UPPER(`administrativeNumber`)";
        }

        $userCourseList = claro_sql_query_fetch_all($sql);
    }

    return $userCourseList;
}

/**
 * Return the list of disabled or unpublished course of a user.
 *
 * @param int $userId valid id of a user
 * @param boolean $renew whether true, force to read databaseingoring an existing cache.
 * @return array (list of course) of array (course settings) of the given user.
 * @todo search and merge other instance of this functionality
 */

function get_user_course_list_desactivated($userId, $renew = false)
{
    static $cached_uid = null, $userCourseList = null;
    
    $curdate = claro_mktime();

    if ($cached_uid != $userId || is_null($userCourseList) || $renew)
    {
        $cached_uid = $userId;

        $tbl_mdb_names         = claro_sql_get_main_tbl();
        $tbl_courses           = $tbl_mdb_names['course'         ];
        $tbl_link_user_courses = $tbl_mdb_names['rel_course_user'];

        $sql = "SELECT course.code                 AS `sysCode`,
                       course.directory            AS `directory`,
                       course.administrativeNumber AS `officialCode`,
                       course.dbName               AS `db`,
                       course.intitule             AS `title`,
                       course.titulaires           AS `titular`,
                       course.language             AS `language`,
                       course.faculte              AS `categoryCode`,
                       course_user.isCourseManager,
                       course.status,
                       UNIX_TIMESTAMP(course.expirationDate) AS expirationDate,
                       UNIX_TIMESTAMP(course.creationDate)     AS creationDate

                       FROM `" . $tbl_courses . "`           AS course,
                            `" . $tbl_link_user_courses . "` AS course_user

                       WHERE course.code         = course_user.code_cours
                         AND course_user.user_id = " . (int) $userId . " 
                         AND (course.`status` = 'disable' 
                              OR course.`status` = 'pending'
                              OR (course.`status` = 'date' 
                                  AND (UNIX_TIMESTAMP(`creationDate`) > '". $curdate ."' 
                                       OR '". $curdate ."'> UNIX_TIMESTAMP(`expirationDate`)
                                       )
                                  )
                              ) 
                         AND course_user.isCourseManager = 1 " ;

        if ( get_conf('course_order_by') == 'official_code' )
        {
            $sql .= " ORDER BY UPPER(`administrativeNumber`), `title`";
        }
        else
        {
            $sql .= " ORDER BY `title`, UPPER(`administrativeNumber`)";
        }

        $userCourseListDesactivated = claro_sql_query_fetch_all($sql);
    }

    return $userCourseListDesactivated;
}

/**
 * return the editable textzone for a course where subscript are denied
 *
 * @param string $course_id
 * @return string : html content
 */

function get_locked_course_explanation($course_id=null)
{
    $courseExplanation = claro_text_zone::get_content('course_subscription_locked', array(CLARO_CONTEXT_COURSE => $course_id));
    
    if( ! empty($courseExplanation) )
    {
        return $courseExplanation;
    }
    else
    {
        $globalExplanation = claro_text_zone::get_content('course_subscription_locked');
        
        if( ! empty( $globalExplanation ) )
        {
            return $globalExplanation;
        }
        else
        {
            return get_lang('Subscription not allowed');
        }
    }
}

/**
 * Return the editable textzone for a course where subscript are locked
 *
 * @param string $course_id
 *
 * @return string : html content
 */

function get_locked_course_by_key_explanation($course_id=null)
{
    $courseExplanation = claro_text_zone::get_content('course_subscription_locked_by_key', array(CLARO_CONTEXT_COURSE => $course_id));
    
    if( ! empty($courseExplanation) )
    {
        return $courseExplanation;
    }
    else
    {
        $globalExplanation = claro_text_zone::get_content('course_subscription_locked_by_key');
        
        if( ! empty( $globalExplanation ) )
        {
            return $globalExplanation;
        }
        else
        {
            return get_lang('Subscription not allowed');
        }
    }
}


/**
 * 
 */
function build_category_trail($categoryList, $requiredCode)
{
    $trail = array();
    if( is_array($categoryList) && !empty($categoryList) )
    {
        foreach( $categoryList as $category )
        {
            if( $category['code'] == $requiredCode )
            {
                if( !empty($category['parentCode']) && !is_null($category['parentCode']) )
                {
                    $trail[] = build_category_trail($categoryList, $category['parentCode']);
                    $trail[] = $category['name'];
                }
                else
                {
                    
                    return $category['name'];
                }
            }
        }
    }
    
    return implode(' &gt; ', $trail);
}

function render_course_dt_in_dd_list($course, $hot = false)
{
    if( $hot ) $classItem = ' hot';
    else       $classItem = '';
    
    $langNameOfLang = get_locale('langNameOfLang');
    $out = '';
    
    if ($course['isCourseManager'] == 1)
    {
        $userStatusImg = '&nbsp;&nbsp;<img src="' . get_icon_url('manager') . '" alt="'.get_lang('Course manager').'" />';
    }
    else
    {
        $userStatusImg = '';
    }

    // show course language if not the same of the platform
    if ( get_conf('platformLanguage') != $course['language'] )
    {
        if ( !empty($langNameOfLang[$course['language']]) )
        {
            $course_language_txt = ' - ' . ucfirst($langNameOfLang[$course['language']]);
        }
        else
        {
            $course_language_txt = ' - ' . ucfirst($course['language']);
        }
    }
    else
    {
        $course_language_txt = '';
    }

    if ( get_conf('course_order_by') == 'official_code' )
    {
        $courseTitle = $course['officialCode'] . ' - ' . $course['title'];
    }
    else
    {
        $courseTitle = $course['title'] . ' (' . $course['officialCode'] . ')';
    }

    $url = get_path('url') . '/claroline/course/index.php?cid='
    .    htmlspecialchars($course['sysCode'])
    ;

    $out .= '<dt class="' . $classItem . '" >' . "\n"
    .    '<img class="iconDefinitionList" src="' . get_icon_url('course') . '" alt="" />'
    .    '<a href="' . htmlspecialchars( $url ) . '">'
    .    htmlspecialchars($courseTitle)
    .    $userStatusImg
    .    '</a>' . "\n"
    .    '</dt>' . "\n"
    .    '<dd>'
    .    '<small>' . "\n"
    .    htmlspecialchars( $course['titular'] . $course_language_txt )
    .    '</small>' . "\n"
    .    '</dd>' . "\n"
    ;
    return $out;
}

function render_user_course_list_desactivated()
{
        $personnalCourseList = get_user_course_list_desactivated(claro_get_current_user_id());
        
        $out='';    
         //display list
         if (!empty($personnalCourseList) && is_array($personnalCourseList))
         {
             $out .= '<dl class="userCourseList">'."\n";
             
             foreach($personnalCourseList as $course)
             {
                  if ( get_conf('course_order_by') == 'official_code' )
                  {
                      $courseTitle = $course['officialCode'] . ' - ' . $course['title'];
                  }
                  else
                  {
                      $courseTitle = $course['title'] . ' (' . $course['officialCode'] . ')';
                  }
                
                  $url = get_path('url') . '/claroline/course/index.php?cid='
                  .    htmlspecialchars($course['sysCode']) ;
                  
                  $urlSettings = Url::Contextualize( get_path('url') . '/claroline/course/settings.php?cidReq='
                  . htmlspecialchars($course['sysCode']. '&cmd=exEnable') ) ;
                
                  $out .= '<dt>' . "\n"
                  .    '<img class="iconDefinitionList" src="' . get_icon_url('course') . '" alt="" />';
                   
                    if ($course['status']=='pending')
                    {
                        $out.=  '<a href="' . htmlspecialchars( $url ) . '">'
                            .   htmlspecialchars($courseTitle)
                            .   '</a>' . "\n"
                            .   '<a href="'.$urlSettings.'">'
                            .   '<img src="'.get_icon_url('manager').'" alt="" /> '.get_lang('Reactivate it ').'</a>';
                    }
                    
                    if ($course['status']=='disable')
                    {
                        if (claro_is_platform_admin())
                        {
                            $out.=  '<a href="' . htmlspecialchars( $url ) . '">'
                            .   htmlspecialchars($courseTitle)
                            .   '</a> ' 
                            .   '<img src="'.get_icon_url('platformadmin').'" alt="" /> '
                            .   '<a href="'.$urlSettings.'">'.get_lang('Reactivate it ').'</a>'
                            .   "\n";
                        }
                        else 
                        {
                            $out.=  htmlspecialchars($courseTitle)
                             .' '.get_lang('Contact your administrator to reactivate it. ');
                        }
                                
                    }
                    
                    if ($course['status']=='date')
                    {
                        if ($course['creationDate'] > claro_mktime())
                        {
                            $out.=  '<a href="' . htmlspecialchars( $url ) . '">'
                                .    htmlspecialchars($courseTitle)
                                .    '</a>' . "\n"
                                .     ' '.get_lang('Will be published on ').date('d-m-Y',$course['creationDate']);
                        }
                        
                        if (isset($course['expirationDate']) AND ($course['expirationDate'] < claro_mktime()))
                        {
                            $out.=  '<a href="' . htmlspecialchars( $url ) . '">'
                                .    htmlspecialchars($courseTitle)
                                .    '</a>' . "\n"
                                .     ' '.get_lang('Expired since ').date('d-m-Y',$course['expirationDate']) ;
                        }
                    
                    }
                    
                    $out .= '</dt>' . "\n";
                    
                    $out .=     '<dd>'
                          .    '<small>' . "\n"
                          .    htmlspecialchars( $course['titular'] )
                          .    '</small>' . "\n"
                          .    '</dd>' . "\n" ;
                 
             }
                        
                
                    $out .= '</dl>' . "\n";
        }
         return $out;

}

function render_user_course_list()
{
    // get the list of personnal courses marked as contening new events
    $date            = Claroline::getInstance()->notification->get_notification_date(claro_get_current_user_id());
    $modified_course = Claroline::getInstance()->notification->get_notified_courses($date,claro_get_current_user_id());

    $out = '';
    
    if( get_conf('userCourseListGroupByCategories', false) )
    {
        // get category list
        $tbl_mdb_names   = claro_sql_get_main_tbl();
        $tbl_category    = $tbl_mdb_names['category'];
        
        $sql = "SELECT `code`,
                       `name`,
                       `code_P` as `parentCode`,
                       `nb_childs` as `nbChildren`
                FROM `" . $tbl_category . "`";
        $categoryList = claro_sql_query_fetch_all_rows($sql);
    
        // categories have to be ordered alphabetically using full trail so handle it here
        if( is_array($categoryList) && !empty($categoryList) )
        {
            foreach( $categoryList as $category )
            {
                $trail = build_category_trail($categoryList,$category['code']);
                $sortedCategoryList[$category['code']] = $trail;
            }
            // order by trail and keep key-value associated
            asort($sortedCategoryList);
        }
        else
        {
            $sortedCategoryList = array();
        }
        
        // get courseList
        $userCourseList = get_user_course_list(claro_get_current_user_id());
        // group courses by category code for better perf in main loop
        if( is_array($userCourseList) && !empty($userCourseList) )
        {
            foreach($userCourseList as $userCourse)
            {
                $sortedUserCourseList[$userCourse['categoryCode']][] = $userCourse;
            }
        }
        else
        {
            $sortedUserCourseList = array();
        }
        
        // so now we have ordered course list and ordered category list we can use them to display the user course list
        $out .= '<div id="courseListByCat">' . "\n";
        // traverse category list, on each node check if some course the user is subscribed in is of this category
        foreach($sortedCategoryList as $categoryCode => $trail )
        {
            if( array_key_exists($categoryCode, $sortedUserCourseList) && !empty($sortedUserCourseList[$categoryCode]) )
            {
                // display category header
                $out .= '<h4>' 
                    . '<strong>'
                    . '<a name="'.$categoryCode.'"></a>'
                    . $trail
                    . '</strong>'
                    . '</h4>';
    
                $out .= '<dl class="userCourseList">'."\n";
                // display category courses
                foreach( $sortedUserCourseList[$categoryCode] as $thisCourse )
                {
                    // If the course contains new things to see since last user login,
                    // The course name will be displayed with the 'hot' class style in the list.
                    // Otherwise it will name normally be displayed
                    $hot = (bool) in_array ($thisCourse['sysCode'], $modified_course);
                
                    $out .= render_course_dt_in_dd_list($thisCourse, $hot);
                }
                $out .= '</dl>' . "\n";
            }
        }
        $out .= '</div>' . "\n";
    }
    else
    {
        $personnalCourseList = get_user_course_list(claro_get_current_user_id());
        
        //display list
        if (count($personnalCourseList))
        {
            $out .= '<dl class="userCourseList">'."\n";
            
            foreach($personnalCourseList as $thisCourse)
            {
                // If the course contains new things to see since last user login,
                // The course name will be displayed with the 'hot' class style in the list.
                // Otherwise it will name normally be displayed
                $hot = (bool) in_array ($thisCourse['sysCode'], $modified_course);
            
                $out .= render_course_dt_in_dd_list($thisCourse, $hot);
            }
        
            $out .= '</dl>' . "\n";
        }
    }
    
    return $out;
}