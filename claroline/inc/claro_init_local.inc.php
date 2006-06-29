<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*******************************************************************************
 *
 *                             SCRIPT PURPOSE
 *
 * This script initializes and manages main Claroline session informations. It
 * keeps available session informations always up to date.
 *
 * You can request a course id. It will check if the course Id requested is the
 * same as the current one. If it isn't it will update session information from
 * the database. You can also force the course reset if you want ($cidReset).
 *
 * All the course informations are store in the $_course array.
 *
 * You can request a group id. It will check if the group Id requested is the
 * same as the current one. If it isn't it will update session information from
 * the database. You can also force the course reset if you want ($gidReset).
 *
 * All the current group information are stored in the $_group array
 *
 * The course id is stored in $_cid session variable.
 * The group  id is stored in $_gid session variable.
 *
 *
 *                    VARIABLES AFFECTING THE SCRIPT BEHAVIOR
 *
 * string  $login
 * string  $password
 * boolean $logout
 *
 * string  $cidReq   : course Id requested
 * boolean $cidReset : ask for a course Reset, if no $cidReq is provided in the
 *                     same time, all course informations is removed from the
 *                     current session
 *
 * int     $gidReq   : group Id requested
 * boolean $gidReset : ask for a group Reset, if no $gidReq is provided in the
 *                     same time, all group informations is removed from the
 *                     current session
 *
 * int     $tidReq   : tool Id requested
 * boolean $tidReset : ask for a tool reset, if no $tidReq or $tlabelReq is
 *                     provided  in the same time, all information concerning
 *                     the current tool is removed from the current sesssion
 *
 * $tlabelReq        : more generic call to a tool. Each tool are identified by
 *                     a unique id into the course. But tools which are part of
 *                     the claroline release have also an generic label.
 *                     Tool label and tool id are decoupled. It means that one
 *                     can have several token of the same tool with different
 *                     settings in the same course.
 *
 *                   VARIABLES SET AND RETURNED BY THE SCRIPT
 *
 * Here is resumed below all the variables set and returned by this script.
 *
 * USER VARIABLES
 *
 * int $_uid (the user id)
 *
 * string  $_user ['firstName']
 * string  $_user ['lastName' ]
 * string  $_user ['mail'     ]
 * string  $_user ['officialEmail'     ]
 * string  $_user ['lastLogin']
 *
 * boolean $is_platformAdmin
 * boolean $is_allowedCreateCourse
 *
 * COURSE VARIABLES
 *
 * string  $_cid (the course id)
 *
 * string  $_course['name'        ]
 * string  $_course['officialCode']
 * string  $_course['sysCode'     ]
 * string  $_course['path'        ]
 * string  $_course['dbName'      ]
 * string  $_course['dbNameGlu'   ]
 * string  $_course['titular'     ]
 * string  $_course['language'    ]
 * string  $_course['extLinkUrl'  ]
 * string  $_course['extLinkName' ]
 * string  $_course['categoryCode']
 * string  $_course['categoryName']
 *
 * PROPERTIES IN ALL GROUPS OF THE COURSE
 *
 * boolean $_groupProperties ['registrationAllowed']
 * boolean $_groupProperties ['private'            ]
 * int     $_groupProperties ['nbGroupPerUser'     ]
 * boolean $_groupProperties ['tools'] ['forum'    ]
 * boolean $_groupProperties ['tools'] ['document' ]
 * boolean $_groupProperties ['tools'] ['wiki'     ]
 * boolean $_groupProperties ['tools'] ['chat'     ]
 *
 * REL COURSE USER VARIABLES
 * int     $profileId
 * string  $_courseUser['role']
 * boolean $iscourseMember
 * boolean $is_courseTutor
 * boolean $is_courseAdmin
 *
 * REL COURSE GROUP VARIABLES
 *
 * int     $_gid (the group id)
 *
 * string  $_group ['name'       ]
 * string  $_group ['description']
 * int     $_group ['tutorId'    ]
 * int     $_group ['forumId'    ]
 * string  $_group ['directory'  ]
 * int     $_group ['maxMember'  ]
 *
 * boolean $is_groupMember
 * boolean $is_groupTutor
 * boolean $is_groupAllowed
 *
 * TOOL VARIABLES
 *
 * int $_tid
 *
 * string $_courseTool['label'         ]
 * string $_courseTool['name'          ]
 * string $_courseTool['access'        ]
 * string $_courseTool['url'           ]
 * string $_courseTool['icon'          ]
 * string $_courseTool['access_manager']
 *
 * REL USER TOOL COURSE VARIABLES
 * boolean $is_toolAllowed
 *
 * LIST OF THE TOOLS AVAILABLE FOR THE CURRENT USER
 *
 * int     $_courseToolList[]['id'            ]
 * string  $_courseToolList[]['label'         ]
 * string  $_courseToolList[]['name'          ]
 * string  $_courseToolList[]['access'        ]
 * string  $_courseToolList[]['icon'          ]
 * string  $_courseToolList[]['access_manager']
 * string  $_courseToolList[]['url'           ]
 *
 *
 *                       IMPORTANT ADVICE FOR DEVELOPERS
 *
 * We strongly encourage developers to use a connection layer at the top of
 * their scripts rather than use these variables, as they are, inside the core
 * of their scripts. It will make Claroline code maintenance much easier.
 *
 * For example, a common practice is to connect the user status with action
 * permission flag at the top of the script like this :
 *
 *     $is_allowedToEdit = $is_courseAdmin
 *
 *
 *                               SCRIPT STRUCTURE
 *
 * 1. The script determines if there is an authentication attempt. This part
 * only chek if the login name and password are valid. Afterwards, it set the
 * $_uid (user id) and the $uidReset flag. Other user informations are retrieved
 * later. It's also in this section that optional external authentication
 * devices step in.
 *
 * 2. The script determines what other session informations have to be set or
 * reset, setting correctly $cidReset (for course) and $gidReset (for group).
 *
 * 3. If needed, the script retrieves the other user informations (first name,
 * last name, ...) and stores them in session.
 *
 * 4. If needed, the script retrieves the course information and stores them
 * in session
 *
 * 5. The script initializes the user status and permission for current course
 *
 * 6. If needed, the script retrieves group informations an store them in
 * session.
 *
 * 7. The script initializes the user status and permission for the current group.
 *
 * 8. The script initializes the user status and permission for the current tool
 *
 * 9. The script get the list of all the tool available into the current course
 *    for the current user.
 ******************************************************************************/

require_once dirname(__FILE__).'/conf/auth.conf.php'; // load the platform authentication settings

/*===========================================================================
  Set claro_init_local.inc.php variables coming from HTTP request into the
  global name space.
 ===========================================================================*/

$AllowedPhpRequestList = array('logout', 'uidReset',
                               'cidReset', 'cidReq',
                               'gidReset', 'gidReq',
                               'tidReset', 'tidReq', 'tlabelReq');

foreach($AllowedPhpRequestList as $thisPhpRequestName)
{
    // some claroline scripts set these variables before calling
    // the claro init process. Avoid variable setting if it is the case.

    if ( isset($GLOBALS[$thisPhpRequestName]) ) continue;

    if ( isset($_REQUEST[$thisPhpRequestName] ) )
    {
        $GLOBALS[$thisPhpRequestName] = $_REQUEST[$thisPhpRequestName];
    }
    else
    {
        $GLOBALS[$thisPhpRequestName] = null;
    }
}

$login    = isset($_REQUEST['login'   ]) ? trim( $_REQUEST['login'   ] ) : null;
$password = isset($_REQUEST['password']) ? trim( $_REQUEST['password'] ) : null;

/*===========================================================================
  Get table name
 ===========================================================================*/

$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_user            = $tbl_mdb_names['user'           ];
$tbl_track_e_login   = $tbl_mdb_names['track_e_login'  ];
$tbl_course          = $tbl_mdb_names['course'         ];
$tbl_category        = $tbl_mdb_names['category'       ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
$tbl_tool            = $tbl_mdb_names['tool'           ];
$tbl_sso             = $tbl_mdb_names['sso'            ];

/*---------------------------------------------------------------------------
  Check authentification
 ---------------------------------------------------------------------------*/

// default variables initialization
$claro_loginRequested = false;
$claro_loginSucceeded = false;

if ($logout && !empty($_SESSION['_uid']))
{
    // needed to notify that a user has just loggued out
    $logout_uid = $_SESSION['_uid'];
}

if ( ! empty($_SESSION['_uid']) && ! ($login || $logout) )
{
    // uid is in session => login already done, continue with this value
    $_uid = $_SESSION['_uid'];

    if ( !empty($_SESSION['is_platformAdmin']) )    $is_platformAdmin = $_SESSION['is_platformAdmin'];
    else                                            $is_platformAdmin = false;

    if ( !empty($_SESSION['is_allowedCreateCourse']) )  $is_allowedCreateCourse = $_SESSION['is_allowedCreateCourse'];
    else                                                $is_allowedCreateCourse = false;
}
else
{
    $_uid     = null;   // uid not in session ? prevent any hacking
    $uidReset = false;

    if ( isset($claro_CasEnabled) && $claro_CasEnabled ) // CAS is a special case of external authentication
    {
        require($claro_CasProcessPath);
    }

    if ( $login && $password ) // $login && $password are given to log in
    {
        $claro_loginRequested = true;

        // lookup the user in the Claroline database

        $sql = 'SELECT user_id, username, password, authSource
                FROM `' . $tbl_user . '` `user`
                WHERE '
             . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY' : '')
             . ' username = "'. addslashes($login) .'"'
             ;

        $result = claro_sql_query($sql);

        if ( mysql_num_rows($result) > 0)
        {
            while ( ( $uData = mysql_fetch_array($result) ) && ! $claro_loginSucceeded )
            {
                if ( $uData['authSource'] == 'claroline' )
                {
                    // the authentification of this user is managed by claroline itself

                    // determine first if the password needs to be crypted before checkin
                    // $userPasswordCrypted is set in main configuration file

                    if ( $userPasswordCrypted ) $password = md5($password);

                    // check the user's password

                    if ( $password == $uData['password'] )
                    {
                        $_uid                 = $uData['user_id'];
                        $uidReset             = true;
                        $claro_loginSucceeded = true;
                    }
                    else // abnormal login -> login failed
                    {
                        $_uid                 = null;
                        $claro_loginSucceeded = false;
                    }
                }
                else // no standard claroline login - try external authentification
                {
                    /*
                     * Process external authentication
                     * on the basis of the given login name
                     */

                    $key = $uData['authSource'];

                    $_uid = include_once($extAuthSource[$key]['login']);

                    if ( $_uid > 0 )
                    {
                        $uidReset             = true;
                        $claro_loginSucceeded = true;
                    }
                    else
                    {
                        $_uid                 = null;
                        $claro_loginSucceeded = false;
                    }
                } // end try external authentication
            } // end while
        }
        else // login failed, mysql_num_rows($result) <= 0
        {
            $claro_loginSucceeded = false;

            /*
             * In this section:
             * there is no entry for the $login user in the claroline database.
             * This also means there is no authSource for the user. We let all
             * external procedures attempt to add him/her to the system.
             *
             * Process external login on the basis of the authentication sources
             * list provided by the Claroline configuration settings.
             * If the login succeeds, for going further, Claroline needs the
             * $_uid variable to be set and registered in the session. It's the
             * responsability of the external login script to provide this
             * $_uid.
             */

            if (isset($extAuthSource) && is_array($extAuthSource))
            {
                foreach($extAuthSource as $thisAuthSource)
                {
                    $_uid = include_once($thisAuthSource['newUser']);

                    if ( $_uid > 0 )
                    {
                        $uidReset             = true;
                        $claro_loginSucceeded = true;
                        break;
                    }
                    else
                    {
                        $_uid                 = null;
                        $claro_loginSucceeded = false;
                    }
                }
            } //end if is_array($extAuthSource)

        } //end else login failed
    } // end if $login & password
    else
    {
        $claro_loginRequested = false;
    }
}

/*---------------------------------------------------------------------------
  User initialisation
 ---------------------------------------------------------------------------*/

if ( $uidReset && $claro_loginSucceeded ) // session data refresh requested
{
    // Update the current session id with a newly generated one ( PHP >= 4.3.2 )
    // This function is vital in preventing session fixation attackselle
    function_exists('session_regenerate_id') && session_regenerate_id();
    $cidReset = true;
    $gidReset = true;

    if ( !empty($_uid) ) // a uid is given (log in succeeded)
    {
            $sql = "SELECT `user`.`prenom`          AS firstName             ,
                           `user`.`nom`             AS lastName              ,
                           `user`.`email`           AS `mail`                ,
                           `user`.`officialEmail`   AS `officialEmail`       ,
                           `user`.`language`                                 ,
                           `user`.`isCourseCreator`   AS is_courseCreator    ,
                           `user`.`isPlatformAdmin`  AS is_platformAdmin    ,
                           `user`.`creatorId`       AS creatorId             , "

                  .       (get_conf('is_trackingEnabled')
                           ? "UNIX_TIMESTAMP(`login`.`login_date`)"
                           : "DATE_SUB(CURDATE(), INTERVAL 1 DAY)") . " AS lastLogin

                    FROM `".$tbl_user."` `user` "

                 . (get_conf('is_trackingEnabled')
                    ? "LEFT JOIN `". $tbl_track_e_login ."` `login`
                              ON `user`.`user_id`  = `login`.`login_user_id` "
                    : '')

                 .   "WHERE `user`.`user_id` = ". (int) $_uid

                 .  (get_conf('is_trackingEnabled')
                     ? " ORDER BY `login`.`login_date` DESC LIMIT 1"
                     : '')
                 ;

        $_user = claro_sql_query_get_single_row($sql);

        if ( is_array($_user) )
        {
            // Extracting the user data

            $is_platformAdmin        = (bool) ($_user['is_platformAdmin'       ] );
            $is_allowedCreateCourse  = (bool) ($_user['is_courseCreator'] || $is_platformAdmin);

            if ( $_uid != $_user['creatorId'] )
            {
                // first login for a not self registred (e.g. registered by a teacher)
                // do nothing (code may be added later)
                $sql = "UPDATE `".$tbl_user."`
                        SET   creatorId = user_id
                        WHERE user_id='" . (int)$_uid . "'";

                claro_sql_query($sql);

                $_SESSION['firstLogin'] = true;
            }
            else
            {
                $_SESSION['firstLogin'] = false;
            }

            // RECORD SSO COOKIE
            // $ssoEnabled set in claroline/conf/auth.conf.php

            if ( $ssoEnabled )
            {
               $ssoCookieExpireTime = time() + $ssoCookiePeriodValidity;
               $ssoCookieValue      = md5( mktime() . rand(100, 1000000) );

                $sql = "UPDATE `".$tbl_sso."`
                        SET cookie    = '".$ssoCookieValue."',
                            rec_time  = NOW()
                        WHERE user_id = ". (int) $_uid;

                $affectedRowCount = claro_sql_query_affected_rows($sql);

                if ($affectedRowCount < 1)
                {
                    $sql = "INSERT INTO `".$tbl_sso."`
                            SET cookie    = '".$ssoCookieValue."',
                                rec_time  = NOW(),
                                user_id   = ". (int) $_uid;

                    claro_sql_query($sql);
                }

               $boolCookie = setcookie($ssoCookieName, $ssoCookieValue,
                                       $ssoCookieExpireTime,
                                       $ssoCookiePath, $ssoCookieDomain);

               // Note. $ssoCookieName, $ssoCookieValussoCookieExpireTime,
               //       $soCookiePath and $ssoCookieDomain are coming from
               //       claroline/inc/conf/auth.conf.php

            } // end if ssoEnabled
        }
        else
        {
            exit('WARNING UNDEFINED UID !! ');
        }
    }
}
elseif ( !empty($_uid) ) // elseif of if($uidReset) continue with the previous values
{
    if ( !empty($_SESSION['_user']) )   $_user = $_SESSION['_user'];
    else                                $_user = null;
}
else
{
    // Anonymous, logout or login failed
    $_user = null;
    $_uid  = null;
    $is_platformAdmin        = false;
    $is_allowedCreateCourse  = false;
}


if( $claro_loginRequested && isset($claro_loginSucceeded) && $claro_loginSucceeded )
{
    // needs to be AFTER the initialisation of $_user ['lastLogin']
    event_login();
}

/*---------------------------------------------------------------------------
  Course initialisation
 ---------------------------------------------------------------------------*/

// if the requested course is different from the course in session

if ( $cidReq && ( !isset($_SESSION['_cid']) || $cidReq != $_SESSION['_cid'] ) )
{
    $cidReset = true;
    $gidReset = true;    // As groups depend from courses, group id is reset
}

if ( $cidReset ) // course session data refresh requested
{
    if ( $cidReq )
    {
        $_course = claro_get_course_data($cidReq, true);

        if ($_course == false) die('WARNING !! INIT FAILED ! '.__LINE__);

        $_cid    = $_course['sysCode'];

        $_groupProperties = claro_get_main_group_properties($_cid);

        if ($_groupProperties == false) die('WARNING !! INIT FAILED ! '.__LINE__);
    }
    else
    {
        $_cid    = null;
        $_course = null;

        $_groupProperties ['registrationAllowed'] = false;
        $_groupProperties ['tools'] ['forum'    ] = false;
        $_groupProperties ['tools'] ['document' ] = false;
        $_groupProperties ['tools'] ['wiki'     ] = false;
        $_groupProperties ['tools'] ['chat'     ] = false;
        $_groupProperties ['private'            ] = true;
    }

}
else // else of if($cidReset) - continue with the previous values
{
    if ( !empty($_SESSION['_cid']) ) $_cid = $_SESSION['_cid'];
    else                             $_cid = null;

    if ( !empty($_SESSION['_course']) ) $_course = $_SESSION['_course'];
    else                                $_course = null;

    if ( !empty($_SESSION['_groupProperties']) ) $_groupProperties = $_SESSION['_groupProperties'];
    else                                         $_groupProperties = null;
}

/*---------------------------------------------------------------------------
  Course / user relation initialisation
 ---------------------------------------------------------------------------*/

if ( $uidReset || $cidReset ) // session data refresh requested
{
    if ( $_uid && $_cid ) // have keys to search data
    {
        $sql = "SELECT profile_id as profileId,
                       isCourseManager,
                       tutor,
                       role
                FROM `".$tbl_rel_course_user."` `cours_user`
                WHERE `user_id`  = '". (int) $_uid."'
                AND `code_cours` = '". addslashes($cidReq) ."'";

        $result = claro_sql_query($sql) or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        if ( mysql_num_rows($result) > 0 ) // this  user have a recorded state for this course
        {
            $cuData = mysql_fetch_array($result);

            $_profileId      = $cuData['profileId'];
            $is_courseMember = true;
            $is_courseTutor  = (bool) ($cuData['tutor' ] == 1 );
            $is_courseAdmin  = (bool) ($cuData['isCourseManager'] == 1 );

            $_courseUser['role'] = $cuData['role'  ]; // not used

        }
        else // this user has no status related to this course
        {
            $_profileId      = claro_get_profile_id('guest');
            $is_courseMember = false;
            $is_courseAdmin  = false;
            $is_courseTutor  = false;

            $_courseUser     = null; // not used
        }

        $is_courseAdmin = (bool) ($is_courseAdmin || $is_platformAdmin);

    }
    else // keys missing => not anymore in the course - user relation
    {
        // course
        $_profileId      = claro_get_profile_id('anonymous');
        $is_courseMember = false;
        $is_courseAdmin  = false;
        $is_courseTutor  = false;

        $_courseUser = null; // not used
    }

    $is_courseAllowed = (bool) ($_course['visibility'] || $is_courseMember || $is_platformAdmin); // here because it's a right and not a state

}
else // else of if ($uidReset || $cidReset) - continue with the previous values
{
    if ( !empty($_SESSION['_profileId']) )       $_profileId       = $_SESSION['_profileId'];
    else                                         $_profileId       = false;
    if ( !empty($_SESSION['is_courseMember']) )  $is_courseMember  = $_SESSION['is_courseMember' ];
    else                                         $is_courseMember  = false;
    if ( !empty($_SESSION['is_courseAdmin']) )   $is_courseAdmin   = $_SESSION['is_courseAdmin' ];
    else                                         $is_courseAdmin   = false;
    if ( !empty($_SESSION['is_courseAllowed']) ) $is_courseAllowed = $_SESSION['is_courseAllowed' ];
    else                                         $is_courseAllowed = false;
    if ( !empty($_SESSION['is_courseTutor']) )   $is_courseTutor   = $_SESSION['is_courseTutor'];
    else                                         $is_courseTutor   = false;

    // not used
    if ( !empty($_SESSION['_courseUser']) )  $_courseUser      = $_SESSION['_courseUser'     ];
    else                                     $_courseUser      = null;
}

/*---------------------------------------------------------------------------
  Course / tool relation initialisation
 ---------------------------------------------------------------------------*/

// if the requested tool is different from the current tool in session
// (special request can come from the tool id, or the tool label)

if (   ( $tidReq    && $tidReq    != $_SESSION['_tid']                 )
    || ( $tlabelReq && ( ! isset($_SESSION['_courseTool']['label'])
                         || $tlabelReq != $_SESSION['_courseTool']['label']) )
   )
{
    $tidReset = true;
}

if ( $tidReset || $cidReset ) // session data refresh requested
{
    if ( ( $tidReq || $tlabelReq) && $_cid ) // have keys to search data
    {
        $sql = " SELECT ctl.id                  AS id            ,
                      pct.claro_label           AS label         ,
                      ctl.script_name           AS name          ,
                      ctl.access                AS access        ,
                      pct.icon                  AS icon          ,
                      pct.access_manager        AS access_manager,
                      CONCAT('".$clarolineRepositoryWeb."', pct.script_url)
                                                AS url

                   FROM `".$_course['dbNameGlu']."tool_list` ctl,
                    `".$tbl_tool."`  pct

               WHERE `ctl`.`tool_id` = `pct`.`id`
                 AND (`ctl`.`id`      = '". (int) $tidReq."'
                       OR   (".(int) is_null($tidReq)." AND pct.claro_label = '". addslashes($tlabelReq) ."')
                     )";

        // Note : 'ctl' stands for  'course tool list' and  'pct' for 'platform course tool'
        $_courseTool = claro_sql_query_get_single_row($sql);

        if ( is_array($_courseTool) ) // this tool have a recorded state for this course
        {
            $_tid                          = $_courseTool['id'];
        }
        else // this tool has no status related to this course
        {
            exit('WARNING UNDEFINED TLABEL OR TID !!');
        }
    }
    else // keys missing => not anymore in the course - tool relation
    {
        // course
        $_tid        = null;
        $_courseTool = null;
    }

}
else // continue with the previous values
{
    if ( !empty($_SESSION['_tid']) ) $_tid = $_SESSION['_tid'] ;
    else                             $_tid = null;

    if ( !empty( $_SESSION['_courseTool']) ) $_courseTool = $_SESSION['_courseTool'];
    else                                     $_courseTool = null;
}

/*---------------------------------------------------------------------------
  Group initialisation
 ---------------------------------------------------------------------------*/

// if the requested group is different from the group in session

if ( $gidReq && ( !isset($_SESSION['_gid']) || $gidReq != $_SESSION['_gid']) )
{
    $gidReset = true;
}

if ( $gidReset || $cidReset ) // session data refresh requested
{
    if ( $gidReq && $_cid ) // have keys to search data
    {
        $sql = "SELECT g.id               AS id          ,
                       g.name             AS name        ,
                       g.description      AS description ,
                       g.tutor            AS tutorId     ,
                       f.forum_id         AS forumId     ,
                       g.secretDirectory  AS directory   ,
                       g.maxStudent       AS maxMember

                FROM `".$_course['dbNameGlu']."group_team`      g
                LEFT JOIN `".$_course['dbNameGlu']."bb_forums`   f

                   ON    g.id = f.group_id
                WHERE    `id` = '". (int) $gidReq."'";

        $_group = claro_sql_query_get_single_row($sql);

        if ( is_array($_group) ) // This group has recorded status related to this course
        {
            $_gid = $_group ['id'];
        }
        else
        {
            exit('WARNING UNDEFINED GID !! ');
        }
    }
    else  // Keys missing => not anymore in the group - course relation
    {
        $_gid   = null;
        $_group = null;
    }
}
else // continue with the previous values
{
    if ( !empty($_SESSION ['_gid']) )   $_gid = $_SESSION ['_gid'];
    else                                $_gid = null;

    if ( !empty($_SESSION ['_group']) ) $_group = $_SESSION ['_group'];
    else                                $_group = null;
}

/*---------------------------------------------------------------------------
  Group / User relation initialisation
 ---------------------------------------------------------------------------*/

if ($uidReset || $cidReset || $gidReset) // session data refresh requested
{
    if ($_uid && $_cid && $_gid) // have keys to search data
    {
        $sql = "SELECT status,
                       role
                FROM `" . $_course['dbNameGlu'] . "group_rel_team_user`
                WHERE `user` = '". (int) $_uid . "'
                AND `team`   = '". (int) $gidReq . "'";

        $result = claro_sql_query($sql)  or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        if (mysql_num_rows($result) > 0) // This user has a recorded status related to this course group
        {
            $gpuData = mysql_fetch_array($result);

            $_groupUser ['status'] = $gpuData ['status'];
            $_groupUser ['role'  ] = $gpuData ['role'  ];

            $is_groupMember = true;
        }
        else
        {
            $is_groupMember = false;
            $_groupUser     = null;
        }

        $is_groupTutor = ($_group['tutorId'] == $_uid);

    }
    else  // Keys missing => not anymore in the user - group (of this course) relation
    {
        $is_groupMember = false;
        $is_groupTutor  = false;

        $_groupUser = null;
    }

    // user group access is allowed or user is group member or user is admin
    $is_groupAllowed = (bool) (!$_groupProperties['private'] || $is_groupMember || $is_courseAdmin || $is_groupTutor  || $is_platformAdmin) ;

}
else // continue with the previous values
{
    if ( !empty($_SESSION['_groupUser']) )      $_groupUser      = $_SESSION['_groupUser'     ];
    else                                        $_groupUser      = null;

    if ( !empty($_SESSION['is_groupMember']) )  $is_groupMember  = $_SESSION['is_groupMember' ];
    else                                        $is_groupMember  = null;

    if ( !empty($_SESSION['is_groupTutor']) )   $is_groupTutor   = $_SESSION['is_groupTutor'  ];
    else                                        $is_groupTutor   = null;

    if ( !empty($_SESSION['is_groupAllowed']) ) $is_groupAllowed = $_SESSION['is_groupAllowed'];
    else                                        $is_groupAllowed = null;
}

/*---------------------------------------------------------------------------
  COURSE TOOL / USER / GROUP REL. INIT
 ---------------------------------------------------------------------------*/

if ( $uidReset || $cidReset || $gidReset || $tidReset ) // session data refresh requested
{
    if ( $_tid && $_gid )
    {
        //echo 'passed here';

        $group_tool_label = str_replace( '_', '', $_courseTool['label'] );

        switch ( $group_tool_label )
        {
            case 'CLWIKI': $is_toolAllowed = $_groupProperties ['tools'] ['wiki'    ]; break;
            case 'CLDOC' : $is_toolAllowed = $_groupProperties ['tools'] ['document']; break;
            case 'CLCHT' : $is_toolAllowed = $_groupProperties ['tools'] ['chat'    ]; break;
            case 'CLFRM' : $is_toolAllowed = $_groupProperties ['tools'] ['forum'   ]; break;
            default      : $is_toolAllowed = false;
        }

        if ( $_groupProperties ['private'] )
        {
            $is_toolAllowed = $is_toolAllowed
                && ( $is_groupMember || $is_groupTutor );
        }

        $is_toolAllowed = $is_toolAllowed || ( $is_courseAdmin || $is_platformAdmin );
    }
    elseif ( $_tid )
    {
        switch($_courseTool['access'])
        {
            case 'PLATFORM_ADMIN'   : $is_toolAllowed = $is_platformAdmin; break;
            case 'COURSE_ADMIN'     : $is_toolAllowed = $is_courseAdmin;   break;
            case 'COURSE_TUTOR'     : $is_toolAllowed = $is_courseTutor;   break;
            case 'GROUP_TUTOR'      : $is_toolAllowed = $is_groupTutor;    break;
            case 'GROUP_MEMBER'     : $is_toolAllowed = $is_groupMember;   break;
            case 'COURSE_MEMBER'    : $is_toolAllowed = $is_courseMember;  break;
            case 'PLATFORM_MEMBER'  : $is_toolAllowed = (bool) $_uid;      break;
            case 'ALL'              : $is_toolAllowed = true;              break;
            default                 : $is_toolAllowed = false;
        }
    }
    else
    {
        $is_toolAllowed = false;
    }

}
else // continue with the previous values
{
    if ( !empty( $_SESSION['is_toolAllowed']) ) $is_toolAllowed = $_SESSION['is_toolAllowed'];
    else                                        $is_toolAllowed = null;
}

/*---------------------------------------------------------------------------
  Course tool list initialisation for current user
 ---------------------------------------------------------------------------*/

if ($uidReset || $cidReset)
{
    if ($_cid) // have course keys to search data
    {
        if     ($is_platformAdmin) $courseReqAccessLevel = 'PLATFORM_ADMIN' ;
        elseif ($is_courseAdmin  ) $courseReqAccessLevel = 'COURSE_ADMIN'   ;
        elseif ($is_courseTutor  ) $courseReqAccessLevel = 'COURSE_TUTOR'   ;
        elseif ($is_groupTutor   ) $courseReqAccessLevel = 'GROUP_TUTOR'    ;
        elseif ($is_groupMember  ) $courseReqAccessLevel = 'GROUP_MEMBER'   ;
        elseif ($is_courseMember ) $courseReqAccessLevel = 'COURSE_MEMBER'  ;
        elseif ($_uid            ) $courseReqAccessLevel = 'PLATFORM_MEMBER';
        else                       $courseReqAccessLevel = 'ALL';

        $_courseToolList = claro_get_course_tool_list($_cid, $courseReqAccessLevel, true, true);
    }
    else
    {
        $_courseToolList = null;
    }
}
else // continue with the previous values
{
    if ( !empty($_SESSION['_courseToolList']) ) $_courseToolList = $_SESSION['_courseToolList'] ;
    else                                        $_courseToolList = null;
}

/*===========================================================================
  Save all variables in session
 ===========================================================================*/

/*---------------------------------------------------------------------------
  User info in the platform
 ---------------------------------------------------------------------------*/
$_SESSION['_uid'                  ] = $_uid;
$_SESSION['_user'                 ] = $_user;
$_SESSION['is_allowedCreateCourse'] = $is_allowedCreateCourse;
$_SESSION['is_platformAdmin'      ] = $is_platformAdmin;

/*---------------------------------------------------------------------------
  Course info of $_cid course
 ---------------------------------------------------------------------------*/

$_SESSION['_cid'            ] = $_cid;
$_SESSION['_course'         ] = $_course;
$_SESSION['_groupProperties'] = $_groupProperties;

/*---------------------------------------------------------------------------
  User rights of $_uid in $_cid course
 ---------------------------------------------------------------------------*/

$_SESSION['_profileId'      ] = $_profileId;
$_SESSION['is_courseAdmin'  ] = $is_courseAdmin;
$_SESSION['is_courseAllowed'] = $is_courseAllowed;
$_SESSION['is_courseMember' ] = $is_courseMember;
$_SESSION['is_courseTutor'  ] = $is_courseTutor;

if ( isset($_courseUser) ) $_SESSION['_courseUser'] = $_courseUser; // not used

/*---------------------------------------------------------------------------
  Tool info of $_tid in $_cid course
 ---------------------------------------------------------------------------*/

$_SESSION['_tid'       ] = $_tid;
$_SESSION['_courseTool'] = $_courseTool;

/*---------------------------------------------------------------------------
  Group info of $_gid in $_cid course
 ---------------------------------------------------------------------------*/

$_SESSION['_gid'           ] = $_gid;
$_SESSION['_group'         ] = $_group;
$_SESSION['is_groupAllowed'] = $is_groupAllowed;
$_SESSION['is_groupMember' ] = $is_groupMember;
$_SESSION['is_groupTutor'  ] = $is_groupTutor;

/*---------------------------------------------------------------------------
 Tool in $_cid course allowed to $_uid user
 ---------------------------------------------------------------------------*/

$_SESSION['is_toolAllowed'] = $is_toolAllowed;

/*---------------------------------------------------------------------------
  List of available tools in $_cid course
 ---------------------------------------------------------------------------*/

$_SESSION['_courseToolList'] = $_courseToolList;

/*===========================================================================
  Set config for course ---> to move in claro_init_global
 ===========================================================================*/

if (isset($_cid) && $_courseTool['label'])
{
    $config_code = rtrim($_courseTool['label'],'_');

    if (file_exists( claro_get_conf_dir($config_code) . $config_code . '.conf.php'))
       require claro_get_conf_dir($config_code) . $config_code . '.conf.php';
    elseif (file_exists($includePath . '/conf/' . $config_code . '.conf.php'))
        require $includePath . '/conf/' . $config_code . '.conf.php';
    if (isset($_cid) && file_exists($coursesRepositorySys . $_course['path'] . '/conf/' . $config_code . '.conf.php'))
        require $coursesRepositorySys . $_course['path'] . '/conf/' . $config_code . '.conf.php';
}

?>
