<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
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
 * 5. The script initializes the user permission status and permission for the 
 * course level
 * 
 * 6. If needed, the script retrieves group informations an store them in 
 * session.
 * 
 * 7. The script initializes the user status and permission for the group level.
 * 
 ******************************************************************************/

if ($HTTP_SESSION_VARS['_uid'] && ! ($login || $logout))
{
    // uid is in session => login already done, continue with this value
    $_uid = $HTTP_SESSION_VARS['_uid'];

}
else
{
    unset($_uid); // uid not in session ? prevent any hacking

    if ($login && $password) // $login && $password are given to log in
    {
        //lookup the user in the Claroline database

        $sql = "SELECT `user_id`, `username`, `password`, `authSource`
                FROM `".$mainDbName."`.`user`
                WHERE username = \"".trim($login)."\"";

        $result = mysql_query($sql) or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

        if (mysql_num_rows($result) > 0)
        {
            $uData = mysql_fetch_array($result);
            
            if ($uData['authSource'] == 'claroline')
            {
                //the authentification of this user is managed by claroline itself

                $password = stripslashes( trim($password) );
                $login    = stripslashes( trim($login)    );

                // determine if the password needs to be crypted before checkin
                // $userPasswordCrypted is set in an external configuration file

                if ($userPasswordCrypted) $password = md5($password);

                // check the user's password

                if ($password == $uData['password'] && (trim($login) == $uData['username']))
                {
                    $_uid = $uData['user_id'];
                    session_register('_uid');
                }
                else // abnormal login -> login failed
                {
                    $loginFailed = true;
                    unset($_uid);
                    session_unregister('_uid');
                }

                if ($_uid != $uData['creatorId'])
                {
                    //first login for a not self registred
                    //e.g. registered by a teacher
                    //do nothing (code may be added later)
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
    
                /* >>>>>>>>>>>>>>>>>>>>>>>>> LDAP <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
                include_once($extAuthSource[$key]['login']);
                /* >>>>>>>>>>>>>>>> end of LDAP <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
                
            }
        }
        else // login failed, mysql_num_rows($result) <= 0
        {
            $loginFailed = true;  // Default initialisation. It could
                                  // change after the external authentication
            
            /*
             * In this section:
             * there is no entry for the $login user in the claroline
             * database. This also means there is no authSource for the user.
             * We let all external procedures attempt to add him/her
             * to the system.
             *
             * Process external login on the basis
             * of the authentication source list
             * provided by the configuration settings.
             * If the login succeeds, for going further,
             * Claroline needs the $_uid variable to be
             * set and registered in the session. It's the
             * responsability of the external login script
             * to provide this $_uid.
             */

            if (is_array($extAuthSource))
            {
                foreach($extAuthSource as $thisAuthSource)
                {
                    ( @include_once($thisAuthSource['newUser']) )
                    or die ("WARNING !! EXTERNAL AUTHENTICATION CONFIGURATION PROBLEM !".__LINE__);;
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

if ($cidReq && $cidReq != $HTTP_SESSION_VARS['_cid'])
{
    $cidReset = true;
    $gidReset = true;    // As groups depend from courses, group id is reset
}

                      // if the requested group is different from the group in session

if ($gidReq && $gidReq != $HTTP_SESSION_VARS['_gid'])
{
    $gidReset = true;
}

// if the requested tool is different from the current tool in session
// (special request can come from the tool id, or the tool label)

if (    (  $tidReq    && $tidReq    != $HTTP_SESSION_VARS['_tid']             ) 
    ||  ( $tlabelReq && $tlabelReq  != $HTTP_SESSION_VARS['_courseTool']['label'] )
   )
{
    $tidReset = true;
}


//////////////////////////////////////////////////////////////////////////////
// USER INIT
//////////////////////////////////////////////////////////////////////////////

if ($uidReset) // session data refresh requested
{
    if ($_uid) // a uid is given (log in succeeded)
    {

        if ($is_trackingEnabled)
        {
            $sql = "SELECT `user`.*, `a`.`idUser` `is_admin`,
                            UNIX_TIMESTAMP(`login`.`login_date`) `login_date`
                     FROM `".$mainDbName."`.`user`
                     LEFT JOIN `".$mainDbName."`.`admin` `a`
                     ON `user`.`user_id` = `a`.`idUser`
                     LEFT JOIN `".$statsDbName."`.`track_e_login` `login`
                     ON `user`.`user_id`  = `login`.`login_user_id`
                     WHERE `user`.`user_id` = '".$_uid."'
                     ORDER BY `login`.`login_date` DESC LIMIT 1";
        }
        else
        {
            $sql = "SELECT `user`.*, `a`.`idUser` `is_admin`
                    FROM `".$mainDbName."`.`user`
                    LEFT JOIN `".$mainDbName."`.`admin` `a`
                    ON `user`.`user_id` = `a`.`idUser`
                    WHERE `user`.`user_id` = '".$_uid."'";
        }

        $result = mysql_query($sql) or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

        if (mysql_num_rows($result) > 0)
        {
			// Extracting the user data

            $uData = mysql_fetch_array($result);

            $_user ['firstName'] = $uData ['prenom'    ];
            $_user ['lastName' ] = $uData ['nom'       ];
            $_user ['mail'     ] = $uData ['email'     ];
            $_user ['lastLogin'] = $uData ['login_date'];

            $is_platformAdmin        = (bool) (! is_null( $uData['is_admin']));
            $is_allowedCreateCourse  = (bool) ($uData ['statut'] == 1);

            session_register('_user');
        }
        else
        {
            exit("WARNING UNDEFINED UID !! ");
        }
    }
    else // no uid => logout or Anonymous
    {
        unset($_user);
        session_unregister('_user');

        unset($_uid);
        session_unregister('_uid');

        $is_platformAdmin        = false;
        $is_allowedCreateCourse  = false;
    }

    session_register('is_platformAdmin','is_allowedCreateCourse');
}
else // continue with the previous values
{
    $_user = $HTTP_SESSION_VARS['_user'];
}

//////////////////////////////////////////////////////////////////////////////
// COURSE INIT
//////////////////////////////////////////////////////////////////////////////

if ($cidReset) // course session data refresh requested
{
    if ($cidReq)
    {
        $sql =    "SELECT `cours`.*, `faculte`.`code` `faCode`, `faculte`.`name` `faName`
                 FROM `".$mainDbName."`.`cours`
                 LEFT JOIN `".$mainDbName."`.`faculte`
                 ON `cours`.`faculte` =  `faculte`.`code`
                 WHERE `cours`.`code` = '$cidReq'";

        $result = mysql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

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


            // read of group tools config related to this course

            $sql = "SELECT * FROM `".$_course['dbNameGlu']."group_property`";
            
            $result = mysql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! $sql ".__LINE__." ".mysql_errno());
            
            $gpData = mysql_fetch_array($result);
            
            $_groupProperties ['registrationAllowed'] = (bool) ($gpData['self_registration'] == 1);
            $_groupProperties ['private'            ] = (bool) ($gpData['private']           == 1);
            $_groupProperties ['nbGroupPerUser'     ] = $gpData['nbGroupPerUser'];
            $_groupProperties ['tools'] ['forum'    ] = (bool) ($gpData['forum']             == 1);
            $_groupProperties ['tools'] ['document' ] = (bool) ($gpData['document']          == 1);
            $_groupProperties ['tools'] ['wiki'     ] = (bool) ($gpData['wiki']              == 1);
            $_groupProperties ['tools'] ['chat'   ] = (bool) ($gpData['chat']            == 1);

        }
        else
        {
            exit("WARNING UNDEFINED CID !! ");
        }
    }
    else
    {
        unset($_cid);
        unset($_course);
        session_unregister('_cid');
        session_unregister('_course');
        //// all groups of these course
        ///  ( theses properies  are from the link  between  course and  group,
        //// but a group  can be only in one course)

        $_groupProperties ['registrationAllowed'] = false;
        $_groupProperties ['tools'] ['forum'    ] = false;
        $_groupProperties ['tools'] ['document' ] = false;
        $_groupProperties ['tools'] ['wiki'     ] = false;
        $_groupProperties ['tools'] ['chat'   ] = false;
        $_groupProperties ['private'            ] = true;
    }

    //save states
    session_register('_groupProperties');
}
else // continue with the previous values
{
    $_cid             = $HTTP_SESSION_VARS['_cid'   ];
    $_course          = $HTTP_SESSION_VARS['_course'];
    $_groupProperties = $HTTP_SESSION_VARS['_groupProperties'];
}

//////////////////////////////////////////////////////////////////////////////
// COURSE / USER REL. INIT
//////////////////////////////////////////////////////////////////////////////

if ($uidReset || $cidReset) // session data refresh requested
{
    if ($_uid && $_cid) // have keys to search data
    {
        $sql = "SELECT * FROM `".$mainDbName."`.`cours_user`
               WHERE `user_id`  = '$_uid'
               AND `code_cours` = '$cidReq'";

        $result = mysql_query($sql) or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

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

        unset($_courseUser);
        session_unregister('_courseUser');
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
    $_courseUser      = $HTTP_SESSION_VARS ['_courseUser'     ];

    $is_courseMember  = $HTTP_SESSION_VARS ['is_courseMember' ];
    $is_courseAdmin   = $HTTP_SESSION_VARS ['is_courseAdmin'  ];
    $is_courseAllowed = $HTTP_SESSION_VARS ['is_courseAllowed'];
    $is_courseTutor   = $HTTP_SESSION_VARS ['is_courseTutor'  ];
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
                    `".$mainDbName."`.`course_tool`  pct

               WHERE `ctl`.`tool_id` = `pct`.`id`
			   			AND
					(
							  `ctl`.`id`      = '".$tidReq."'
               OR   (".(int) is_null($tidReq)." AND pct.claro_label = '".$tlabelReq."')
			   )
";
		// Note : 'ctl' stands for  'course tool list' and  'pct' for 'platform course tool'
        $result = mysql_query($sql) or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

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
 		echo "<pre>mr : ".mysql_num_rows($result)."</pre>";

            exit('WARNING UNDEFINED TID !!');
        }
    }
    else // keys missing => not anymore in the course - tool relation
    {
        //// course
        unset($_tid);
        unset($_courseTool);
        session_unregister('_tid');
        session_unregister('_courseTool');
    }

}
else // continue with the previous values
{
    $_tid        = $HTTP_SESSION_VARS['_tid'       ] ;
    $_courseTool = $HTTP_SESSION_VARS['_courseTool'];
}


//////////////////////////////////////////////////////////////////////////////
// GROUP INIT
//////////////////////////////////////////////////////////////////////////////


if ($gidReset || $cidReset) // session data refresh requested
{
    if ($gidReq && $_cid ) // have keys to search data
    {
        $sql = "SELECT * FROM `".$_course['dbNameGlu']."group_team`
                WHERE `id` = '$gidReq'";

        $result = mysql_query($sql) or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

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
            exit("WARNING UNDEFINED GID !! ");
        }
    }
    else  // Keys missing => not anymore in the group - course relation
    {
            unset($_gid);
            unset($_group);
            session_unregister('_gid');
            session_unregister('_group');
    }
}
else // continue with the previous values
{
    $_gid             = $HTTP_SESSION_VARS ['_gid'            ];
    $_group           = $HTTP_SESSION_VARS ['_group'          ];
}

//////////////////////////////////////////////////////////////////////////////
// GROUP / USER REL. INIT
//////////////////////////////////////////////////////////////////////////////

if ($uidReset || $cidReset || $gidReset) // session data refresh requested
{
    if ($_uid && $_cid && $_gid) // have keys to search data
    {
        $sql = "SELECT * FROM `".$_course['dbNameGlu']."group_rel_team_user`
                WHERE `user` = '$_uid'
                AND `team` = '$gidReq'";

        $result = mysql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

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
            unset($_groupUser);
            session_unregister('_groupUser');
        }

        $is_groupTutor = ($_group['tutorId'] == $_uid);

        session_register('_groupUser');
    }
    else  // Keys missing => not anymore in the user - group (of this course) relation
    {
        $is_groupMember = false;
        $is_groupTutor  = false;

        unset($_groupUser);
        session_unregister('_groupUser');
    }

    // user group access is allowed or user is group member or user is admin
    $is_groupAllowed = (bool) (!$_groupProperties['private'] || $is_groupMember || $is_courseAdmin || $is_groupTutor  || $is_platformAdmin) ;

    session_register('is_groupMember', 'is_groupTutor', 'is_groupAllowed');
}
else // continue with the previous values
{
    $_groupUser      = $HTTP_SESSION_VARS ['_groupUser'     ];
    $is_groupMember  = $HTTP_SESSION_VARS ['is_groupMember' ];
    $is_groupTutor   = $HTTP_SESSION_VARS ['is_groupTutor'  ];
    $is_groupAllowed = $HTTP_SESSION_VARS ['is_groupAllowed'];
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

	       // Developper notes. And what about these following cases ?
	       // case "COURSE_TUTOR"     : $is_toolAllowed = $is_courseTutor; break;
	       // case "PLATFORM_MEMBER"  : $is_toolAllowed = (bool) $_uid;    break;
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
    $is_toolAllowed = $HTTP_SESSION_VARS ['is_toolAllowed'];
}


////
////
////
if ($uidReset || $cidReset)
{
	if ($_cid) // have keys to search data
	{
	
	    $reqAccessList = array('ALL');
	    if ($is_platformAdmin) $reqAccessList [] = 'PLATFORM_ADMIN';
	    if ($is_courseAdmin  ) $reqAccessList [] = 'COURSE_ADMIN';
	    if ($is_courseTutor  ) $reqAccessList [] = 'COURSE_TUTOR';
	    if ($is_groupTutor   ) $reqAccessList [] = 'GROUP_TUTOR';
	    if ($is_groupMember  ) $reqAccessList [] = 'GROUP_MEMBER';
	    if ($is_courseMember ) $reqAccessList [] = 'COURSE_MEMBER';
	    if ($_uid)             $reqAccessList [] = 'PLATFORM_MEMBER';
	
	      $sql ="SELECT ctl.id        id,
	               pct.claro_label    label,
	               ctl.script_name    name,
	               ctl.access         access,
	               pct.icon           icon,
	               pct.access_manager access_manager,
				  IF(pct.script_url IS NULL ,ctl.script_url,CONCAT('".$clarolineRepositoryWeb."', pct.script_url)) url
	           FROM `".$_course['dbNameGlu']."tool_list` ctl
	           LEFT JOIN `".$mainDbName."`.`course_tool` pct
	
	            ON       pct.id = ctl.tool_id
	
	            WHERE
	
	            ctl.access IN (\"".implode("\", \"", $reqAccessList)."\")";
	
		$result = mysql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);
		
		$_courseToolList = array();
		
		while( $tlistData = mysql_fetch_array($result))	
		{
			$_courseToolList[] = $tlistData;
		}
		session_register('_courseToolList');
	}
	else
	{
		unset($_courseToolList);
		session_unregister('_courseToolList');
	}
}
else // continue with the previous values
{
    $_courseToolList      = $HTTP_SESSION_VARS ['_courseToolList'];
}
?>
