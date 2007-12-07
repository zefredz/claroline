<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');
$includePath = dirname(__FILE__);

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
 * string  $_course['extLink'     ]['url' ]
 * string  $_course['extLink'     ]['name']
 * string  $_course['categoryCode']
 * string  $_course['categoryName']
 *
 * boolean $_groupProperties ['registrationAllowed']
 * boolean $_groupProperties ['private'            ]
 * int     $_groupProperties ['nbGroupPerUser'     ]
 * boolean $_groupProperties ['tools'] ['forum'    ]
 * boolean $_groupProperties ['tools'] ['document' ]
 * boolean $_groupProperties ['tools'] ['wiki'     ]
 * boolean $_groupProperties ['tools'] ['chat'   ]
 *
 * string  $_courseUser['role']
 * boolean $is_courseMember
 * boolean $is_courseTutor
 * boolean $is_courseAdmin
 *
 *
 * GROUP VARIABLES
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
 * boolean $is_toolAllowed
 *
 * LIST OF THE TOOLS AVAILABLE FOR THE CURRENT USER
 *
 * int     $_courseToolList[]['id'            ]
 * string  $_courseToolList[]['label'         ]
 * string  $_courseToolList[]['name'          ]
 * string  $_courseToolList[]['access'        ]
 * sting   $_courseToolList[]['icon'          ]
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


// Set claro_init_local.inc.php variables coming from HTTP request into the
// global name space.

$AllowedPhpRequestList = array('login', 'password', 'logout', 'uidReset',
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

// Get table name

$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_user            = $tbl_mdb_names['user'           ];
$tbl_admin           = $tbl_mdb_names['admin'          ];
$tbl_track_e_login   = $tbl_mdb_names['track_e_login'  ];
$tbl_course          = $tbl_mdb_names['course'         ];
$tbl_category        = $tbl_mdb_names['category'       ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
$tbl_tool            = $tbl_mdb_names['tool'           ];
$tbl_sso             = $tbl_mdb_names['sso'];

/*---------------------------------------------------------------------------*/



// check authentification

if ( ! empty($_SESSION['_uid']) && ! ($login || $logout) )
{
    // uid is in session => login already done, continue with this value
    $_uid                   = $_SESSION['_uid'                  ];
    $is_platformAdmin       = $_SESSION['is_platformAdmin'      ];
    $is_allowedCreateCourse = $_SESSION['is_allowedCreateCourse'];
}
else
{
    if ( file_exists($includePath.'/conf/auth.conf.php') )
    {
        require_once $includePath.'/conf/auth.conf.php'; // load the platform authentication settings
    }

    $_uid = null; // uid not in session ? prevent any hacking

    if ($login && $password) // $login && $password are given to log in
    {
        //lookup the user in the Claroline database

        $sql = "SELECT user_id, username, password, authSource, creatorId
                FROM `".$tbl_user."` `user`
                WHERE BINARY username = \"". claro_addslashes($login) ."\"";

        $result = claro_sql_query($sql) or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        if ( mysql_num_rows($result) > 0)
        {
            $uData = mysql_fetch_array($result);

            if ($uData['authSource'] == 'claroline')
            {
                //the authentification of this user is managed by claroline itself

                $password = stripslashes( $password );
                $login    = stripslashes( $login    );

                // determine if the password needs to be crypted before checkin
                // $userPasswordCrypted is set in an external configuration file

                if ($userPasswordCrypted) $password = md5($password);

                // check the user's password

                if ($password == $uData['password'] && ( $login == $uData['username']))
                {
                    $_uid = $uData['user_id'];
                    session_register('_uid');
                }
                else // abnormal login -> login failed
                {
                    $loginFailed = true;
                    $_uid        = null;
                }

                if ($_uid != $uData['creatorId'])
                {
                    //first login for a not self registred (e.g. registered by a teacher)
                    //do nothing (code may be added later)
                    $sql = "UPDATE `".$tbl_user."`
                            SET   creatorId = user_id
                            WHERE user_id='".$_uid."'";

                    claro_sql_query($sql);
                }
            }
            else // no standard claroline login - try external authentification
            {
                /*
                 * Process external authentication
                 * on the basis of the given login name
                 */

                $loginFailed = true;  // Default initialisation. It could
                                      // change after the external authentication
                $key = $uData['authSource'];

                $_uid = include_once($extAuthSource[$key]['login']);

                if ( $_uid > 0 )
                {
                    $uidReset    = true;
                    $loginFailed = false;
                    session_register('_uid');
                }
                else
                {
                    $_uid        = null;
                    $uidReset    = false;
                    $loginFailed = true;
                }
            } // end try external authentication
        }
        else // login failed, mysql_num_rows($result) <= 0
        {
            $loginFailed = true;  // Default initialisation. It could
                                  // change after the external authentication

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
                        session_register('_uid');
                        $uidReset     = true;
                        $loginFailed  = false;
                        break;
                    }
                    else
                    {
                        $_uid = null;
                    }
                }
            } //end if is_array($extAuthSource)

        } //end else login failed
    }

    //    else {} => continue as anonymous user
    $uidReset = true;

//    $cidReset = true;
//    $gidReset = true;
}

// if the requested course is different from the course in session

if ($cidReq && $cidReq != $_SESSION['_cid'])
{
    $cidReset = true;
    $gidReset = true;    // As groups depend from courses, group id is reset
}

// if the requested group is different from the group in session

if ($gidReq && $gidReq != $_SESSION['_gid'])
{
    $gidReset = true;
}

// if the requested tool is different from the current tool in session
// (special request can come from the tool id, or the tool label)

if (   ( $tidReq    && $tidReq    != $_SESSION['_tid']                 )
    || ( $tlabelReq && $tlabelReq != $_SESSION['_courseTool']['label'] )
   )
{
    $tidReset = true;
}


//////////////////////////////////////////////////////////////////////////////
// USER INIT
//////////////////////////////////////////////////////////////////////////////

if ($uidReset) // session data refresh requested
{
    if ( ! empty($_uid)) // a uid is given (log in succeeded)
    {

        if ($is_trackingEnabled)
        {
            $sql = "SELECT `user`.`prenom`                       `firstname`,
                           `user`.`nom`                          `lastname` ,
                           `user`.`email`                        `email`    ,
                           `user`.`statut`,
                           `a`.`idUser`                          `is_admin`,
                            UNIX_TIMESTAMP(`login`.`login_date`) `lastLogin`
                     FROM `".$tbl_user."` `user`
                     LEFT JOIN `". $tbl_admin  ."` `a`
                     ON `user`.`user_id` = `a`.`idUser`
                     LEFT JOIN `". $tbl_track_e_login ."` `login`
                     ON `user`.`user_id`  = `login`.`login_user_id`
                     WHERE `user`.`user_id` = '".$_uid."'
                     ORDER BY `login`.`login_date` DESC LIMIT 1";
        }
        else
        {
            $sql = "SELECT
                        `user`.`prenom`     `firstname`,
                        `user`.`nom`        `lastname` ,
                        `user`.`email`                 ,
                        DATE_SUB(CURDATE(), INTERVAL 1 DAY) `lastLogin`,
                        `user`.`statut`,
                        `a`.`idUser`        `is_admin`
                    FROM `". $tbl_user ."` `user`
                    LEFT JOIN `". $tbl_admin  ."` `a`
                    ON `user`.`user_id` = `a`.`idUser`
                    WHERE `user`.`user_id` = '".$_uid."'";
        }

        $result = claro_sql_query($sql);

        if (mysql_num_rows($result) > 0)
        {
            // Extracting the user data

            $uData = mysql_fetch_array($result);

            $_user ['firstName'] = $uData ['firstname'    ];
            $_user ['lastName' ] = $uData ['lastname'       ];
            $_user ['mail'     ] = $uData ['email'     ];
            $_user ['lastLogin'] = $uData ['lastLogin'];

            $is_platformAdmin        = (bool) (! is_null( $uData['is_admin']));
            $is_allowedCreateCourse  = (bool) ($uData ['statut'] == 1);

            session_register('_user');

            // RECORD SSO COOKIE

            if ( $ssoEnabled )
            {
               $ssoCookieExpireTime = time() + $ssoCookiePeriodValidity;
               $ssoCookieValue      = md5( mktime() );

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
    else // no uid => logout or Anonymous
    {
        $_user = null;
        $_uid  = null;

        $is_platformAdmin        = false;
        $is_allowedCreateCourse  = false;
    }

    session_register('is_platformAdmin','is_allowedCreateCourse');
}
else // continue with the previous values
{
    $_user = $_SESSION['_user'];
}

//////////////////////////////////////////////////////////////////////////////
// COURSE INIT
//////////////////////////////////////////////////////////////////////////////

if ($cidReset) // course session data refresh requested
{
    if ($cidReq)
    {
        $sql =  "SELECT `c`.`code`,
                        `c`.`intitule`,
                        `c`.`fake_code`,
                        `c`.`directory`,
                        `c`.`dbName`,
                        `c`.`titulaires`,
                        `c`.`email`,
                        `c`.`languageCourse`,
                        `c`.`departmentUrl`,
                        `c`.`departmentUrlName`,
                        `c`.`visible`,
                        `cat`.`code` `faCode`,
                        `cat`.`name` `faName`
                 FROM     `".$tbl_course."`    `c`
                 LEFT JOIN `".$tbl_category."` `cat`
                 ON `c`.`faculte` =  `cat`.`code`
                 WHERE `c`.`code` = '".$cidReq."'";

        $result = claro_sql_query($sql)  or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        if (mysql_num_rows($result)>0)
        {
            $cData = mysql_fetch_array($result);

            $_cid                            = $cData['code'             ];

            $_course['name'        ]         = $cData['intitule'         ];
            $_course['officialCode']         = $cData['fake_code'        ]; // use in echo
            $_course['sysCode'     ]         = $cData['code'             ]; // use as key in db
            $_course['path'        ]         = $cData['directory'        ]; // use as key in path
            $_course['dbName'      ]         = $cData['dbName'           ]; // use as key in db list
            $_course['dbNameGlu'   ]         = $courseTablePrefix . $cData['dbName'] . $dbGlu; // use in all queries
            $_course['titular'     ]         = $cData['titulaires'       ];
            $_course['email'       ]         = $cData['email'            ];
            $_course['language'    ]         = $cData['languageCourse'   ];
            $_course['extLink'     ]['url' ] = $cData['departmentUrl'    ];
            $_course['extLink'     ]['name'] = $cData['departmentUrlName'];
            $_course['categoryCode']         = $cData['faCode'           ];
            $_course['categoryName']         = $cData['faName'           ];
            $_course['email'        ]        = $cData['email'            ];

            $_course['visibility'  ]         = (bool) ($cData['visible'] == 2 || $cData['visible'] == 3);
            $_course['registrationAllowed']  = (bool) ($cData['visible'] == 1 || $cData['visible'] == 2);

            session_register('_cid', '_course');

            // GET COURSE TABLE

            // read of group tools config related to this course

            $sql = "SELECT self_registration,
                           private,
                           nbGroupPerUser,
                           forum, document,
                           wiki,
                           chat
                    FROM `".$_course['dbNameGlu']."group_property`";

            $result = claro_sql_query($sql)  or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

            $gpData = mysql_fetch_array($result);

            $_groupProperties ['registrationAllowed'] = (bool) ($gpData['self_registration'] == 1);
            $_groupProperties ['private'            ] = (bool) ($gpData['private'          ] == 1);
            $_groupProperties ['nbGroupPerUser'     ] = $gpData['nbGroupPerUser'];
            $_groupProperties ['tools'] ['forum'    ] = (bool) ($gpData['forum'            ] == 1);
            $_groupProperties ['tools'] ['document' ] = (bool) ($gpData['document'         ] == 1);
            $_groupProperties ['tools'] ['wiki'     ] = (bool) ($gpData['wiki'             ] == 1);
            $_groupProperties ['tools'] ['chat'     ] = (bool) ($gpData['chat'             ] == 1);
        }
        else
        {
            exit('WARNING UNDEFINED CID !! ');
        }
    }
    else
    {
        $_cid    = null;
        $_course = null;
        //// all groups of these course
        ///  ( theses properies  are from the link  between  course and  group,
        //// but a group  can be only in one course)

        $_groupProperties ['registrationAllowed'] = false;
        $_groupProperties ['tools'] ['forum'    ] = false;
        $_groupProperties ['tools'] ['document' ] = false;
        $_groupProperties ['tools'] ['wiki'     ] = false;
        $_groupProperties ['tools'] ['chat'     ] = false;
        $_groupProperties ['private'            ] = true;
    }

    //save states
    session_register('_groupProperties');
}
else // continue with the previous values
{
    if ( !empty($_SESSION['_cid']) ) $_cid = $_SESSION['_cid'];
    else                             $_cid = null;

    if ( !empty($_SESSION['_course']) ) $_course = $_SESSION['_course'];
    else                                $_course = null;

    if ( !empty($_SESSION['_groupProperties']) ) $_groupProperties = $_SESSION['_groupProperties'];
    else                                         $_groupProperties = null;

}

//////////////////////////////////////////////////////////////////////////////
// COURSE / USER REL. INIT
//////////////////////////////////////////////////////////////////////////////

if ($uidReset || $cidReset) // session data refresh requested
{
    if ($_uid && $_cid) // have keys to search data
    {
        $sql = "SELECT statut,
                       tutor,
                       role
                FROM `".$tbl_rel_course_user."` `cours_user`
                WHERE `user_id`  = '".$_uid."'
                AND `code_cours` = '".$cidReq."'";

        $result = claro_sql_query($sql) or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        if (mysql_num_rows($result) > 0) // this  user have a recorded state for this course
        {
            $cuData = mysql_fetch_array($result);

            $_courseUser['role'] = $cuData['role'  ];
            $is_courseMember     = true;
            $is_courseTutor      = (bool) ($cuData['tutor' ] == 1 );
            $is_courseAdmin      = (bool) ($cuData['statut'] == 1 );

            session_register('_courseUser');

        }
        else // this user has no status related to this course
        {
            $is_courseMember = false;
            $is_courseAdmin  = false;
            $is_courseTutor  = false;
        }

        $is_courseAdmin = (bool) ($is_courseAdmin || $is_platformAdmin);
    }
    else // keys missing => not anymore in the course - user relation
    {
        //// course
        $is_courseMember = false;
        $is_courseAdmin  = false;
        $is_courseTutor  = false;

        $_courseUser = null;
    }

    $is_courseAllowed = (bool) ($_course['visibility'] || $is_courseMember || $is_platformAdmin); // here because it's a right and not a state

    // save the states

    session_register('is_courseMember');
    session_register('is_courseAdmin');
    session_register('is_courseAllowed');
    session_register('is_courseTutor');
}
else // continue with the previous values
{
    $_courseUser      = $_SESSION ['_courseUser'     ];

    $is_courseMember  = $_SESSION ['is_courseMember' ];
    $is_courseAdmin   = $_SESSION ['is_courseAdmin'  ];
    $is_courseAllowed = $_SESSION ['is_courseAllowed'];
    $is_courseTutor   = $_SESSION ['is_courseTutor'  ];
}

//////////////////////////////////////////////////////////////////////////////
// COURSE / TOOL REL. INIT
//////////////////////////////////////////////////////////////////////////////

if ($tidReset || $cidReset) // session data refresh requested
{
    if ( ( $tidReq || $tlabelReq) && $_cid) // have keys to search data
    {
        $sql ="SELECT ctl.id             id,
                      pct.claro_label    label,
                      ctl.script_name    name,
                      ctl.access         access,
                      pct.icon           icon,
                      pct.access_manager access_manager,
                      CONCAT('".$clarolineRepositoryWeb."', pct.script_url) url

               FROM `".$_course['dbNameGlu']."tool_list` ctl,
                    `".$tbl_tool."`  pct

               WHERE `ctl`.`tool_id` = `pct`.`id`
                 AND (`ctl`.`id`      = '".$tidReq."'
                       OR   (".(int) is_null($tidReq)." AND pct.claro_label = '".$tlabelReq."')
                     )";

        // Note : 'ctl' stands for  'course tool list' and  'pct' for 'platform course tool'
        $result = claro_sql_query($sql) or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        if (mysql_num_rows($result) == 1) // this tool have a recorded state for this course
        {
            $ctData = mysql_fetch_array($result);

            $_tid                          = $ctData['id'             ];

            $_courseTool['label'         ] = $ctData['label'          ];
            $_courseTool['name'          ] = $ctData['name'           ];
            $_courseTool['access'        ] = $ctData['access'         ];
            $_courseTool['url'           ] = $ctData['url'            ];
            $_courseTool['icon'          ] = $ctData['icon'           ];
            $_courseTool['access_manager'] = $ctData['access_manager' ];

            session_register('_tid');
            session_register('_courseTool');

        }
        else // this tool has no status related to this course
        {
            exit('WARNING UNDEFINED TID !!');
        }
    }
    else // keys missing => not anymore in the course - tool relation
    {
        //// course
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


//////////////////////////////////////////////////////////////////////////////
// GROUP INIT
//////////////////////////////////////////////////////////////////////////////


if ($gidReset || $cidReset) // session data refresh requested
{
    if ($gidReq && $_cid ) // have keys to search data
    {
        $sql = "SELECT id,
                       name,
                       description,
                       tutor,
                       forumId,
                       secretDirectory,
                       maxStudent
                FROM `".$_course['dbNameGlu']."group_team`
                WHERE `id` = '".$gidReq."'";

        $result = claro_sql_query($sql) or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        if (mysql_num_rows($result) > 0) // This group has recorded status related to this course
        {
            $gpData = mysql_fetch_array($result);

            $_gid                   = $gpData ['id'             ];
            $_group ['name'       ] = $gpData ['name'           ];
            $_group ['description'] = $gpData ['description'    ];
            $_group ['tutorId'    ] = $gpData ['tutor'          ];
            $_group ['forumId'    ] = $gpData ['forumId'        ];
            $_group ['directory'  ] = $gpData ['secretDirectory'];
            $_group ['maxMember'  ] = $gpData ['maxStudent'     ];

            session_register('_gid', '_group');
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

//////////////////////////////////////////////////////////////////////////////
// GROUP / USER REL. INIT
//////////////////////////////////////////////////////////////////////////////

if ($uidReset || $cidReset || $gidReset) // session data refresh requested
{
    if ($_uid && $_cid && $_gid) // have keys to search data
    {
        $sql = "SELECT status,
                       role
                FROM `".$_course['dbNameGlu']."group_rel_team_user`
                WHERE `user` = '".$_uid."'
                AND `team`   = '".$gidReq."'";

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

    session_register('is_groupMember', 'is_groupTutor', 'is_groupAllowed');
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

//////////////////////////////////////////////////////////////////////////////
// COURSE TOOL / USER / GROUP REL. INIT
//////////////////////////////////////////////////////////////////////////////

if ($uidReset || $cidReset || $gidReset || $tidReset) // session data refresh requested
{
    if ($_tid)
    {
        switch($_courseTool['access'])
        {
            case 'PLATFORM_ADMIN'   : $is_toolAllowed = $is_platformAdmin; break;
            case 'COURSE_ADMIN'     : $is_toolAllowed = $is_courseAdmin;   break;
            case 'COURSE_TUTOR'     : $is_toolAllowed = $is_courseTutor;   break;
            case 'GROUP_TUTOR'      : $is_toolAllowed = $is_groupTutor;    break;
            case 'GROUP_MEMBER'     : $is_toolAllowed = $is_groupMember;   break;
            case 'COURSE_MEMBER'    : $is_toolAllowed = $is_courseMember;  break;
            case "PLATFORM_MEMBER"  : $is_toolAllowed = (bool) $_uid;      break;
            case 'ALL'              : $is_toolAllowed = true;              break;
            default                 : $is_toolAllowed = false;
        }
    }
    else
    {
        $is_toolAllowed = false;
    }

    session_register('is_toolAllowed');
}
else // continue with the previous values
{
    $is_toolAllowed = $_SESSION ['is_toolAllowed'];
}


//////////////////////////////////////////////////////////////////////////////
// COURSE TOOL LIST INIT FOR CURRENT USER
//////////////////////////////////////////////////////////////////////////////


if ($uidReset || $cidReset)
{
    if ($_cid) // have course keys to search data
    {
        $reqAccessList = array('ALL');
        if ($is_platformAdmin) $reqAccessList [] = 'PLATFORM_ADMIN';
        if ($is_courseAdmin  ) $reqAccessList [] = 'COURSE_ADMIN';
        if ($is_courseTutor  ) $reqAccessList [] = 'COURSE_TUTOR';
        if ($is_groupTutor   ) $reqAccessList [] = 'GROUP_TUTOR';
        if ($is_groupMember  ) $reqAccessList [] = 'GROUP_MEMBER';
        if ($is_courseMember ) $reqAccessList [] = 'COURSE_MEMBER';
        if ($_uid            ) $reqAccessList [] = 'PLATFORM_MEMBER';

          $sql ="SELECT ctl.id             id,
                        pct.claro_label    label,
                        ctl.script_name    name,
                        ctl.access         access,
                        pct.icon           icon,
                        pct.access_manager access_manager,

                        IF(pct.script_url IS NULL ,
                           ctl.script_url,CONCAT('".$clarolineRepositoryWeb."',
                           pct.script_url)) url

               FROM `".$_course['dbNameGlu']."tool_list` ctl

               LEFT JOIN `".$tbl_tool."` pct
               ON       pct.id = ctl.tool_id

               WHERE ctl.access IN (\"".implode("\", \"", $reqAccessList)."\")
               ORDER BY ctl.rank";

        $result = claro_sql_query($sql)  or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        $_courseToolList = array();

        while( $tlistData = mysql_fetch_array($result))
        {
            $_courseToolList[] = $tlistData;
        }

        session_register('_courseToolList');
    }
    else
    {
        $_courseToolList = null;
    }
}
else // continue with the previous values
{
    $_courseToolList      = $_SESSION ['_courseToolList'];
}

if (isset($_cid) && $_courseTool['label'])
{
    $config_code = rtrim($_courseTool['label'],'_');
    if (file_exists($includePath.'/conf/'.$config_code.'.conf.php'))
        require $includePath.'/conf/'.$config_code.'.conf.php';
    if (isset($_cid) && file_exists($coursesRepositorySys.$_course['path'].'/conf/'.$config_code.'.conf.php'))
        require $coursesRepositorySys.$_course['path'].'/conf/'.$config_code.'.conf.php';
}

?>
