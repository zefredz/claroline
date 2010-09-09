<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}


/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 * @copyright (c) 2001-2010 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package CLCOURSELIST
 * @author Claro Team <cvs@claroline.net>
 */

require_once dirname(__FILE__) . '/clarocategory.class.php';

class category_browser
{
    // Identifier of the selected category
    public $categoryId;
    
    // Identifier of the current user
    public $userId;
    
    // Current category
    public $curentCategory;
    
    // List of categories
    public $categoriesList;
    
    // List of courses
    public $coursesList;
    
    
    /**
     * Constructor
     *
     * @param mixed $categoryId null or valid category identifier
     * @param mixed $userId null or valid user identifier
     * @return category_browser object
     */
    function category_browser( $categoryId = null, $userId = null )
    {
        $this->categoryId   = $categoryId;
        $this->userId       = $userId;
        
        $this->currentCategory  = new claroCategory();
        $this->currentCategory->load($categoryId);
        $this->categoriesList   = claroCategory::getCategories($categoryId, 1);
        $this->coursesList      = claroCourse::getRestrictedCourses($categoryId, $userId);
    }


    /**
     * @since 1.8
     * @return object claroCategory
     */
    function get_current_category_settings()
    {
        if (!is_null($this->currentCategory))
            return $this->currentCategory;
        else
            return null;
    }


    /**
     * @since 1.8
     * @return iterator     list of sub category of the current category
     */
    function get_sub_category_list()
    {
        if (!empty($this->categoriesList))
            return $this->categoriesList;
        else
            return array();
    }


    /**
     * Fetch list of courses of the current category.
     *
     * This list include main data about the user but also
     * registration status.
     *
     * @since 1.8
     * @return array    list of courses of the current category
     */
    function get_course_list()
    {
        if (!empty($this->coursesList))
            return $this->coursesList;
        else
            return array();
    }
    
    
    /**
     * Fetch list of courses of the current category without
     * the session courses.
     *
     * This list include main data about
     * the user but also registration status
     *
     * @return array    list of courses of the current category
     *                  without session courses
     * @since 1.10
     */
    function getCoursesWithoutSessionCourses()
    {
        if (!empty($this->coursesList))
        {
            $coursesList = array();
            foreach ($this->coursesList as $course)
            {
                if (is_null($course['sourceCourseId']) || (isset($course['isCourseManager']) && $course['isCourseManager'] == 1))
                {
                    $coursesList[] = $course;
                }
            }
            
            return $coursesList;
        }
        else
            return array();
    }
    
    
    /**
     * Fetch list of courses of the current category without
     * the source courses (i.e. courses having session courses).
     *
     * This list include main data about the user but also
     * registration status.
     *
     * @return array    list of courses of the current category
     *                  without source courses
     * @since 1.10
     */
    function getCoursesWithoutSourceCourses()
    {
        if (!empty($this->coursesList))
        {
            // Find the source courses identifiers
            $sourceCoursesIds = array();
            foreach ($this->coursesList as $course)
            {
                if (!is_null($course['sourceCourseId'])
                    && !in_array($course['sourceCourseId'], $sourceCoursesIds))
                {
                    $sourceCoursesIds[] = $course['sourceCourseId'];
                }
            }
            
            $coursesList = array();
            foreach ($this->coursesList as $course)
            {
                if (!in_array($course['id'], $sourceCoursesIds))
                    $coursesList[] = $course;
            }
            
            return $coursesList;
        }
        else
            return array();
    }
}


/**
 * Search a specific course based on his course code.  If the user isn't
 * a platform admin, this function will not return source courses having
 * session courses.
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  string       $keyword course code from the cours table
 * @param  mixed        $userId  null or valid id of a user (default:null)
 *
 * @return array        course parameters
 */
function search_course($keyword, $userId = null)
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_course           = $tbl_mdb_names['course'         ];
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'];
    
    $keyword = trim($keyword);
    
    if ( empty($keyword) ) return array();
    
    $upperKeyword = addslashes(strtoupper($keyword));
    
    $curdate = date('Y-m-d H:i:s', time());
    
    $sql = "SELECT c.cours_id             AS id,
                   c.intitule             AS title,
                   c.titulaires           AS titular,
                   c.code                 AS sysCode,
                   c.sourceCourseId       AS souceCourseId,
                   c.administrativeNumber AS officialCode,
                   c.directory            AS directory,
                   c.code                 AS code,
                   c.language             AS language,
                   c.email                AS email,
                   c.sourceCourseId,
                   c.visibility,
                   c.access,
                   c.registration,
                   c.status,
                   c.creationDate,
                   c.expirationDate"
        
         .  ($userId ? ",
                   cu.user_id AS enroled,
                   cu.isCourseManager" : "")
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
    
    $coursesList = claro_sql_query_fetch_all($sql);

    if (count($coursesList) > 0)
    {
        //If not platform admin, remove source courses
        if (!claro_is_platform_admin())
        {
            // Find the source courses identifiers
            $sourceCoursesIds = array();
            foreach ($coursesList as $course)
            {
                if (!is_null($course['sourceCourseId'])
                    && !in_array($course['sourceCourseId'], $sourceCoursesIds))
                {
                    $sourceCoursesIds[] = $course['sourceCourseId'];
                }
            }
            
            $filteredCoursesList = array();
            foreach ($coursesList as $course)
            {
                if (!in_array($course['id'], $sourceCoursesIds))
                    $filteredCoursesList[] = $course;
            }
            
            return $filteredCoursesList;
        }
        else
        {
            return $coursesList;
        }
    }
    else
    {
        return array() ;
    }
}


/**
 * Return course list of a user.
 *
 * @param int $userId valid id of a user
 * @param boolean $renew whether true, force to read databaseingoring an existing cache (default: false)
 * @param boolean $categories wheter true, get categories informations (default: false)
 * @return array (list of course) of array (course settings) of the given user
 * @todo search and merge other instance of this functionality
 */
function get_user_course_list($userId, $renew = false, $categories = false)
{
    static $cached_uid = null, $userCourseList = null;

    if ($cached_uid != $userId || is_null($userCourseList) || $renew)
    {
        $cached_uid = $userId;

        $tbl_mdb_names              = claro_sql_get_main_tbl();
        $tbl_courses                = $tbl_mdb_names['course'         ];
        $tbl_rel_user_courses       = $tbl_mdb_names['rel_course_user'];
        $tbl_rel_course_category    = $tbl_mdb_names['rel_course_category'];

        $curdate = claro_mktime();
        
        $sql = "SELECT course.cours_id,
                       course.code                  AS `sysCode`,
                       course.directory             AS `directory`,
                       course.administrativeNumber  AS `officialCode`,
                       course.dbName                AS `db`,
                       course.intitule              AS `title`,
                       course.titulaires            AS `titular`,
                       course.language              AS `language`,
                       course.access                AS `access`,
                       course.status,
                       course.sourceCourseId,
                       UNIX_TIMESTAMP(course.expirationDate) AS expirationDate,
                       UNIX_TIMESTAMP(course.creationDate)   AS creationDate,
                       rcu.isCourseManager";
        
        if ($categories)
            $sql .= ",
                       rcc.categoryId               AS `categoryId`,
                       rcc.rootCourse";
        
        $sql .= "
                
                FROM `" . $tbl_courses . "` AS course
                
                LEFT JOIN `" . $tbl_rel_user_courses . "` AS rcu
                ON rcu.user_id = " . (int) $userId . " ";
        
        if ($categories)
            $sql .= "
                
                LEFT JOIN `" . $tbl_rel_course_category . "` AS rcc
                ON course.cours_id = rcc.courseId";
        
        $sql .= "
                
                WHERE course.code = rcu.code_cours
                AND (course.`status`='enable'
                      OR (course.`status` = 'date'
                           AND (UNIX_TIMESTAMP(`creationDate`) < '". $curdate ."'
                                 OR `creationDate` IS NULL OR UNIX_TIMESTAMP(`creationDate`)=0
                               )
                           AND ('". $curdate ."' < UNIX_TIMESTAMP(`expirationDate`) OR `expirationDate` IS NULL)
                         )
                    )";
        
        if ($categories)
            $sql .= "
                AND rcc.rootCourse != 1";
        
        if ( !get_conf('userCourseListGroupByCategories') )
        {
            $sql .= " GROUP BY course.code";
        }

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

        $sql = "SELECT course.cours_id,
                       course.code                 AS `sysCode`,
                       course.directory            AS `directory`,
                       course.administrativeNumber AS `officialCode`,
                       course.dbName               AS `db`,
                       course.intitule             AS `title`,
                       course.titulaires           AS `titular`,
                       course.language             AS `language`,
                       course.access               AS `access`,
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
 * Return the editable textzone for a course where subscript are denied.
 *
 * @param string        $course_id
 * @return string       html content
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
 * Return the editable textzone for a course where subscript are locked.
 *
 * @param string        $course_id
 * @return string       html content
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
 * Return trail (path) for a given category.
 *
 * @param array         list of categories
 * @param int           id of the category for which we want the trail
 * @return string       trail
 */
function build_category_trail($categoriesList, $requiredId)
{
    $trail = array();
    
    if( is_array($categoriesList) && !empty($categoriesList) )
    {
        foreach( $categoriesList as $category )
        {
            if( $category['id'] == $requiredId )
            {
                if( !is_null($category['idParent']) && ($category['idParent']) )
                {
                    $trail[] = build_category_trail($categoriesList, $category['idParent']);
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


function render_course_dt_in_dd_list($course, $hot = false, $iconAccess = true)
{
    if( $hot ) $classItem = ' hot';
    else       $classItem = '';
    
    $langNameOfLang = get_locale('langNameOfLang');
    $out = '';
    
    if ( isset( $course['isCourseManager'] ) && $course['isCourseManager'] == 1 )
    {
        $userStatusImg = '&nbsp;&nbsp;<img src="' . get_icon_url('manager') . '" alt="'.get_lang('Course manager').'" />';
    }
    else
    {
        $userStatusImg = '';
    }

    // show course language if not the same of the platform
    if ( (get_conf('platformLanguage') != $course['language']) || get_conf('showAlwaysLanguageInCourseList',false) )
    {
        if ( !empty($langNameOfLang[$course['language']])  )
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

    if ( $iconAccess )
    {
        $iconUrl = get_course_access_icon( $course['access'] );
    }
    else $iconUrl = get_icon_url('course') ;
    
    $managerString = htmlspecialchars( $course['titular'] . $course_language_txt );
    if( isset( $course['email'] ) && claro_is_user_authenticated() )
    {
        $managerString = '<a href="mailto:' . $course['email'] . '">' . $managerString . '</a>';
    }
    
    // Don't give a link to the course if the user is in pending state
    $isUserPending = ($course['access'] == 'private' && isset($course['isPending']) && $course['isPending'] == 1) ?
                     (true) :
                     (false);
    
    if ( $isUserPending )
    {
        $courseLink = '<a>'
                    . htmlspecialchars($courseTitle)
                    . $userStatusImg
                    . '</a> ['.get_lang('Pending registration').']' . "\n";
    }
    else
    {
        $courseLink = '<a href="' . htmlspecialchars( $url ) . '">'
                    . htmlspecialchars($courseTitle)
                    . $userStatusImg
                    . '</a>' . "\n";
    }
    
    
    $out .= '<dt '.(!empty($classItem) ? 'class="'.$classItem.'"' : '').'" >' . "\n"
          . '<img class="iconDefinitionList" src="' . $iconUrl . '" alt="Icon URL" />'
          . $courseLink . "\n"
          . '</dt>' . "\n"
          . '<dd>'
          . $managerString
          . '</dd>' . "\n"
          ;
    return $out;
}


function render_user_course_list_desactivated()
{
    $personnalCourseList = get_user_course_list_desactivated(claro_get_current_user_id());
    
    $out = '';

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
                  . '<img class="iconDefinitionList" src="' . get_icon_url('course') . '" alt="" />';
            
            if ($course['status']=='pending')
            {
                $out .=  '<a href="' . htmlspecialchars( $url ) . '">'
                      . htmlspecialchars($courseTitle)
                      . '</a>' . "\n"
                      . '<a href="'.$urlSettings.'">'
                      . '<img src="'.get_icon_url('manager').'" alt="" /> '.get_lang('Reactivate it ').'</a>';
            }
            
            if ($course['status']=='disable')
            {
                if (claro_is_platform_admin())
                {
                    $out .= '<a href="' . htmlspecialchars( $url ) . '">'
                          . htmlspecialchars($courseTitle)
                          . '</a> '
                          . '<img src="'.get_icon_url('platformadmin').'" alt="" /> '
                          . '<a href="'.$urlSettings.'">'.get_lang('Reactivate it ').'</a>'
                          . "\n";
                }
                else
                {
                    $out .= htmlspecialchars($courseTitle)
                          . ' '.get_lang('Contact your administrator to reactivate it. ');
                }
                
            }
            
            if ($course['status']=='date')
            {
                if ($course['creationDate'] > claro_mktime())
                {
                    $out .= '<a href="' . htmlspecialchars( $url ) . '">'
                          . htmlspecialchars($courseTitle)
                          . '</a>' . "\n"
                          . ' '.get_lang('Will be published on ').date('d-m-Y',$course['creationDate']);
                }
                
                if (isset($course['expirationDate']) AND ($course['expirationDate'] < claro_mktime()))
                {
                    $out .= '<a href="' . htmlspecialchars( $url ) . '">'
                          . htmlspecialchars($courseTitle)
                          . '</a>' . "\n"
                          . ' '.get_lang('Expired since ').date('d-m-Y',$course['expirationDate']) ;
                }
            
            }
            
            $out .= '</dt>' . "\n";
            
            $out .= '<dd>'
                  . '<small>' . "\n"
                  . htmlspecialchars( $course['titular'] )
                  . '</small>' . "\n"
                  . '</dd>' . "\n" ;
            
        }
        
        $out .= '</dl>' . "\n";
    }
    
    return $out;
}


/**
 * Returns a courses list for the current user.
 *
 * @return string       list of courses (HTML format)
 */
function render_user_course_list()
{
    // Get the list of personnal courses marked as contening new events
    $date            = Claroline::getInstance()->notification->get_notification_date(claro_get_current_user_id());
    $modified_course = Claroline::getInstance()->notification->get_notified_courses($date,claro_get_current_user_id());
    
    // Get courses, categories and sort it for display
    $categoryList       = ClaroCategory::getAllCategories(0, 0, 1);
    $userCourseList     = claro_get_user_course_list();
    $userCategoryList   = ClaroCategory::getCoursesCategories($userCourseList);
    
    // Use the course id as array index and flag hot courses
    $reorganizedUserCourseList = array();
    foreach ($userCourseList as $course)
    {
        // Flag hot courses
        $course['hot'] = (bool) in_array($course['sysCode'], $modified_course);
        
        if (!isset($reorganizedUserCourseList[$course['courseId']]))
        {
            $reorganizedUserCourseList[$course['courseId']] = $course;
        }
    }
    unset($userCourseList);
    
    // Use the category id as array index
    $reorganizedUserCategoryList = array();
    foreach ($userCategoryList as $category)
    {
        // Flag root courses and put it aside
        $reorganizedUserCourseList[$category['courseId']]['rootCourse'] = null;
        if ($category['rootCourse'])
        {
            $reorganizedUserCourseList[$category['courseId']]['rootCourse'] = 1;
        }
        
        if (!isset($reorganizedUserCategoryList[$category['categoryId']]))
        {
            $reorganizedUserCategoryList[$category['categoryId']] =
                $category;
            
            //We won't need that key anymore
            unset($reorganizedUserCategoryList[$category['categoryId']]['courseId']);
        }
    }
    
    // Place courses in the right categories and build categories' trails
    $currentCategoryId = null;
    foreach ($userCategoryList as $category)
    {
        // Build the full trail for each category (excepted root category)
        if ($category['categoryId'] == 0)
        {
            $trail = $category['name'];
        }
        else
        {
            $trail = build_category_trail($categoryList, $category['categoryId']);
        }
        $reorganizedUserCategoryList[$category['categoryId']]['trail'] = $trail;
        
        // Put root courses aside
        if ($reorganizedUserCourseList[$category['courseId']]['rootCourse'])
        {
            $reorganizedUserCategoryList[$category['categoryId']]['rootCourse'] =
                $reorganizedUserCourseList[$category['courseId']];
        }
        else
        {
            // Do not include source courses (only display session courses)
            if (!($reorganizedUserCourseList[$category['courseId']]['isSourceCourse']))
            {
                $reorganizedUserCategoryList[$category['categoryId']]['courseList'][] =
                    $reorganizedUserCourseList[$category['courseId']];
            }
        }
    }
    unset($userCategoryList);
    
    $out = '';
    
    // Display
    if( get_conf('userCourseListGroupByCategories') )
    {
        foreach ($reorganizedUserCategoryList as $category)
        {
            if (!empty($category['courseList']))
            {
                $out .= '<h4 id="'.$category['categoryId'].'">'
                      . $category['trail']
                      . (!empty($category['rootCourse']) ?
                      ' [<a href="'
                      . get_path('url') . '/claroline/course/index.php?cid='
                      . htmlspecialchars($course['sysCode'])
                      .'">Infos</a>]' :
                      '')
                      . '</h4>'."\n"
                      . '<ul class="courses">';
                
                foreach ($category['courseList'] as $course)
                {
                    $out .= '<li '.($course['hot'] ? 'class="hot"' : '').'>'
                          . '<dl>'
                          . render_course_dt_in_dd_list($course, $course['hot']).'</li>'
                          . '</dl>' . "\n";
                }
            }
            $out .= '</ul>';
        }
    }
    else
    {
        // Display list
        if (count($reorganizedUserCourseList))
        {
            $out .= '<dl class="userCourseList">'."\n";
            
            foreach($reorganizedUserCourseList as $course)
            {
                if ($course['rootCourse'] != 1 && $course['isSourceCourse'] != 1)
                {
                    $iconAccess = false;
                    if ($course['isCourseManager'])
                        $iconAccess  =  true;
                    $out .= render_course_dt_in_dd_list($course, $course['hot'], $iconAccess);
                }
            }
            
            $out .= '</dl>' . "\n";
        }
    }
    
    return $out;
}


/**
 * Get an icon url according to a course access mode ('public', 'private' or 'platform')
 *
 * @param string $accessMode : label of the access mode for which an icon is asked for
 * @return string : the url to the icon
 */
function get_course_access_icon( $accessMode )
{
    switch( $accessMode )
    {
        case 'private' :
            $iconUrl = get_icon_url( 'access_locked' );
            break;
        case 'platform' :
            $iconUrl = get_icon_url( 'access_platform' );
            break;
        case 'public' :
            $iconUrl = get_icon_url( 'access_open' );
            break;
        default :
            $iconUrl = get_icon_url( 'course' );
    }
    return $iconUrl;
}

function render_access_mode_caption_block()
{
    $block = '<fieldset class="captionBlock">' . "\n"
           . '<legend>' . get_lang( 'Caption' ) . '</legend>' . "\n"
           . '<img class="iconDefinitionList" src="' . get_icon_url( 'access_open' ) . '" alt="public" />' . get_lang( 'Access allowed to anybody (even without login)' ) . '<br />' . "\n"
           . '<img class="iconDefinitionList" src="' . get_icon_url( 'access_platform' ) . '" alt="restricted" />' . get_lang( 'Access allowed only to platform members (user registered to the platform)' ) . '<br />' . "\n"
           . '<img class="iconDefinitionList" src="' . get_icon_url( 'access_locked' ) . '"  alt="locked" />' . get_lang( 'Access allowed only to course members (people on the course user list)' ) . "\n"
           . '</fieldset>';
    return $block;
}