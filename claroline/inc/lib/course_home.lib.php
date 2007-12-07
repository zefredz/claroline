<?php # $Id

$tbl_tool_list = $_course['dbNameGlu']."tool_list";

/**
 * insert a new claroline standart course tool into the course
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  string $tool_label
 * @return void
 */


function insert_course_tool($tool_label)
{
    global $tbl_tool_list, $mainDbName;

    /*
     * Get the necessary tool setting 
     * from the central claroline tool table
     */

    $sql = "SELECT  id , claro_label, script_url, 
                    icon, def_access, def_rank, 
                    add_in_course, access_manager  

            FROM `".$mainDbName."`.`course_tool`
            WHERE claro_label = \"".$tool_label."\"";

    list($defaultToolSettingList) = claro_sql_query_fetch_all($sql);

    if (count($defaultToolSettingList) < 1) return false;

    /*
     * Insert the tool into the course table
     */

    $defaultToolSettingList['rank'] = get_next_course_tool_rank();


    $sql = "INSERT INTO `".$tbl_tool_list."`
            SET tool_id  = \"".$defaultToolSettingList['id'        ]."\",
                access = \""  .$defaultToolSettingList['def_access']."\",
                rank     = \"".$defaultToolSettingList['rank'      ]."\"";

    claro_sql_query($sql);
}

//////////////////////////////////////////////////////////////////////////////

/**
 * 
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $reqAccessLevel should be in 'ALL', 'COURSE_MEMBER', 
 *                                'GROUP_MEMBER', 'COURSE_TUTOR', 
 *                                'COURSE_MANAGER', 'PLATFORM_ADMIN'
 * @return array
 */


function get_course_tool_list($reqAccessLevel = 'ALL')
{
    global $tbl_tool_list, $mainDbName;

    /*
     * Build a list containing all the necessary access level
     */

    $standartAccessList = array('ALL',           'PLATFORM_MEMBER',
                                'COURSE_MEMBER', 'COURSE_TUTOR',
                                'GROUP_MEMBER',  'GROUP_TUTOR',
                                'COURSE_ADMIN',  'PLATFORM_ADMIN');

    foreach($standartAccessList as $thisAccessLevel)
    {
        $reqAccessList[] = $thisAccessLevel;

        if ($thisAccessLevel == $reqAccessLevel) break;
    }

    /*
     * Search all the tool corresponding to these Access
     */

    $sql = "SELECT tl.id                               id,
                   tl.script_name                      name,
                   tl.access                         access,
                   tl.rank                             rank,
                   IFNULL(ct.script_url,tl.script_url) url,
                   ct.claro_label                      label,
                   ct.icon                            icon,
                   ct.access_manager                  access_manager

            FROM      `".$tbl_tool_list."`             tl

            LEFT JOIN `".$mainDbName."`.`course_tool` ct

            ON        ct.id = tl.tool_id

            WHERE tl.access IN (\"".implode("\", \"", $reqAccessList)."\")";

     return claro_sql_query_fetch_all($sql);
}

//////////////////////////////////////////////////////////////////////////////

/**
 * Get all the settings from a specific tool
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param int $toolId id of the tool
 * @return array containing 'id', 'name', 'access', 'rank', 'url', 'label',
 *                          'icon', 'access_manager'
 */


function get_course_tool_settings ($toolId)
{
    global $tbl_tool_list, $mainDbName;

    $sql = "SELECT tl.id                               id, 
                   tl.script_name                      name, 
                   tl.access                         access,
                   tl.rank                             rank,
                   IFNULL(ct.script_url,tl.script_url) url,
                   ct.claro_label                      label, 
                   ct.icon                            icon,
                   ct.access_manager                  access_manager

            FROM      `".$tbl_tool_list."`             tl

            LEFT JOIN `".$mainDbName."`.`course_tool` ct

            ON        ct.id = tl.tool_id

            WHERE tl.id = \"" . $toolId . "\"";
            
    $toolList = claro_sql_query_fetch_all($sql);
       
    if (count($toolList) > 0)
    {
        // this function is supposed to return only one tool
        // That's why we extract it immediately from the result array
        
        list ($toolSetting) = claro_sql_query_fetch_all($sql);
    }
    
    return ($toolSetting);
}

//////////////////////////////////////////////////////////////////////////////


function enable_course_tool($toolIdList, $accessLevel = 'ALL')
{
	set_course_tool_access_level($toolIdList, $accessLevel);
}

//////////////////////////////////////////////////////////////////////////////


function disable_course_tool($toolIdList, $disablingLevel = 'COURSE_ADMIN')
{
	set_course_tool_access_level($toolIdList, $disablingLevel);
}

//////////////////////////////////////////////////////////////////////////////

/**
 * 
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param int $toolId
 * @param string $level should be in 'ALL', 'COURSE_MEMBER', 'GROUP_MEMBER', 
 *                      'COURSE_TUTOR', 'COURSE_MANAGER', 'PLATFORM_ADMIN'
 * @return
 */


function set_course_tool_access_level($toolIdList, $level)
{
    global $tbl_tool_list;
    
    if (! is_array ($toolIdList) ) $toolIdList = array($toolId);

    $sql = "UPDATE `".$tbl_tool_list."`
            SET   access = \"".$level."\"
            WHERE id IN (\"". implode('", "',$toolIdList) ."\")";

    return claro_sql_query($sql);
}

//////////////////////////////////////////////////////////////////////////////

/**
 * 
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param int $toolId
 * @param string $name
 * @param string $url
 * @return bool true if it suceeds, false otherwise
 */


function set_local_course_tool($toolId, $name, $url, $accessLevel = 'ALL')
{
    global $tbl_tool_list;

    // check for "http://", if the user forgot "http://" or "ftp://" or ...
    // the link will not be correct
    if( !ereg( "://",$url ) )
    {
         // add "http://" as default protocol for url
         $url = "http://".$url;
    }
    
    if ( (int)$toolId != 0 )
    {

        $sql = "UPDATE `".$tbl_tool_list."`
                SET script_name = \"".$name."\",
                    script_url  = \"".$url."\",
                    access = \"".$accessLevel . "\" 
                WHERE id        = \"".intval($toolId)."\"
                AND   tool_id IS NULL";
                
        if (claro_sql_query_affected_rows($sql) > 0)
        {
            return true;
        }
    } 
    
    return false;
}

//////////////////////////////////////////////////////////////////////////////


/**
 * 
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $name
 * @param string $url
 * @param string $accessLevel (optionnal) should be in 'ALL', 'COURSE_MEMBER',
 *                             'GROUP_MEMBER', 'COURSE_TUTOR', 'COURSE_MANAGER', 
 *                             'PLATFORM_ADMIN'
 * @return bool true if it succeeds, false otherwise
 */


function insert_local_course_tool($name, $url, $accessLevel = 'ALL')
{
    global $tbl_tool_list;

    // check for "http://", if the user forgot "http://" or "ftp://" or ...
    // the link will not be correct
    if( !ereg( "://",$url ) )
    {
         // add "http://" as default protocol for url
         $url = "http://".$url;
    }

    $nextRank = get_next_course_tool_rank();

        $sql = "INSERT INTO `".$tbl_tool_list."`
                SET 
                script_name = \"".$name."\",
                script_url  = \"".$url."\",
                access    = \"".$accessLevel."\",
                rank        = \"".$nextRank."\"";

    return claro_sql_query($sql);    
}

//////////////////////////////////////////////////////////////////////////////


/**
 * 
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @author Christophe Gesche
 * @param int $toolId
 * @return bool true if it succeeds, false otherwise
 */


function delete_course_tool($toolId)
{
    global $tbl_tool_list;

    $sql = "DELETE FROM `$tbl_tool_list`
            WHERE id = \"".$toolId."\"";

    return claro_sql_query($sql);
}

//////////////////////////////////////////////////////////////////////////////


/**
 * 
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @return int
 */


function get_next_course_tool_rank()
{
    global $tbl_tool_list;

    $sql = "SELECT (MAX(rank)+1) next_rank 
            FROM `".$tbl_tool_list."`";

    list($rank) = claro_sql_query_fetch_all($sql);

    return $rank['next_rank'];
}

//////////////////////////////////////////////////////////////////////////////

/**
 * offset the tools rank from a start rank until a certain number of rank 
 * leaving free ranks between both
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param int  $startRank
 * @param inti $offset (optional)
 * @return boolean true if succeeds, false otherwise
 */

function offset_course_tool_rank_from($startRank, $offset = 1)
{
    global $tbl_tool_list;

    if ($offset < 1) return false;

    $sql = "UPDATE `".$tbl_tool_list."`
            SET   rank = rank + ".int($offset)."
            WHERE rank >= ".int($startRank)."
            ORDER BY rank DESC";

    return claro_sql_query($sql);
}

//////////////////////////////////////////////////////////////////////////////

function move_up_course_tool($toolId)
{
	return move_course_tool($toolId, 'UP');
}

function move_down_course_tool($toolId)
{
	return move_course_tool($toolId, 'DOWN');
}


/**
 * move a tool up or down
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param int $reqToolId - the tool to move
 * @param string $moveDirection - should be 'UP' or 'DOWN'
 * @return
 */


function move_course_tool($reqToolId, $moveDirection)
{
	global $tbl_tool_list;

	if ( strtoupper($moveDirection)     == 'DOWN' ) $sortDirection   = 'DESC';
	elseif ( strtoupper($moveDirection) == 'UP'   ) $sortDirection   = 'ASC';

	if ($sortDirection)
	{
		$sql = "SELECT id, rank
                FROM `".$tbl_tool_list."`
		        ORDER BY rank ".$sortDirection;

        $toolList = claro_sql_query_fetch_all($sql);

        $reqToolFound = false; // init reqToolFound with default value

        foreach($toolList as $thisTool)
        {
            // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
			//          COMMIT ORDER SWAP ON THE DB

			if ($reqToolFound)
			{
				$nextToolId   = $thisTool['id'  ];
				$nextToolRank = $thisTool['rank'];

                $sql = "UPDATE `".$tbl_tool_list."`
				        SET rank = \"".$nextToolRank."\"
				        WHERE id = \"".$reqToolId."\"";

				claro_sql_query($sql);

                $sql = "UPDATE `".$tbl_tool_list."`
				        SET rank = \"".$reqToolRank."\"
						WHERE id = \"".$nextToolId."\"";

				claro_sql_query($sql);

				return true;
			}

            // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT

			if ($thisTool['id'] == $reqToolId)
			{
                $reqToolRank  = $thisTool['rank'];
				$reqToolFound = true;
			}

        } // end foreach toolList as thisTool
    } // end if sortDirection
}

//////////////////////////////////////////////////////////////////////////////

/*
dans cours
CREATE TABLE `tool_list` (
  `id` int(11) NOT NULL auto_increment,
  `tool_id` int(10) unsigned default NULL,
  `rank` int(10) unsigned NOT NULL default '1',
  `access` enum('ALL','COURSE_MEMBER','GROUP_MEMBER','COURSE_TUTOR','COURSE_ADMIN','PLATFORM_ADMIN') NOT NULL default 'ALL',
  `script_url` varchar(255) default NULL,
  `script_name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=12 ;

#
# Dumping data for table `tool_list`
#

INSERT INTO `tool_list` VALUES (1, 1, 1, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (2, 2, 2, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (3, 3, 3, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (4, 4, 4, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (5, 5, 5, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (6, 6, 6, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (7, 7, 7, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (8, 8, 8, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (9, 9, 9, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (10, 10, 10, 'ALL', NULL, NULL);
INSERT INTO `tool_list` VALUES (11, 11, 11, 'ALL', NULL, NULL);

#
# Table structure for table `course_tool`
#

CREATE TABLE `course_tool` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `claro_label` varchar(8) NOT NULL default '',
  `script_url` varchar(255) NOT NULL default '',
  `icon` varchar(255) default NULL,
  `def_access` enum('ALL','COURSE_MEMBER','GROUP_MEMBER','GROUP_TUTOR','COURSE_ADMIN','PLATFORM_ADMIN') NOT NULL default 'ALL',
  `def_rank` int(10) unsigned default NULL,
  `add_in_course` enum('MANUAL','AUTOMATIC') NOT NULL default 'AUTOMATIC',
  `access_manager` enum('PLATFORM_ADMIN','COURSE_ADMIN') NOT NULL default 'COURSE_ADMIN',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `claro_label` (`claro_label`)
) TYPE=MyISAM COMMENT='based definiton of the claroline tool used in each course' AUTO_INCREMENT=12 ;

#
# Dumping data for table `course_tool`
#

INSERT INTO `course_tool` VALUES (1, 'CLCAL___', '../claroline/calendar/agenda.php', '../claroline/img/agenda.gif', 'ALL', 1, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (2, 'CLDOC___', '../claroline/document/document.php', '../claroline/img/documents.gif', 'ALL', 2, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (3, 'CLWRK___', '../claroline/work/work.php', '../claroline/img/works.gif', 'ALL', 3, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (4, 'CLANN___', '../claroline/announcements/announcements.php', '../claroline/img/valves.gif', 'ALL', 4, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (5, 'CLUSR___', '../claroline/user/user.php', '../claroline/img/membres.gif', 'ALL', 5, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (6, 'CLFRM___', '../claroline/phpbb/index.php', '../claroline/img/forum.gif', 'ALL', 6, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (7, 'CLQWZ___', '../claroline/exercice/exercice.php', '../claroline/img/quiz.gif', 'ALL', 7, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (8, 'CLGRP___', '../claroline/group/group.php', '../claroline/img/group.gif', 'ALL', 8, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (9, 'CLDSC___', '../claroline/course_description/index.php', '../claroline/img/info.gif', 'ALL', 9, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (10, 'CLCHT___', '../claroline/chat/chat.php', '../claroline/img/forum.gif', 'ALL', 10, 'AUTOMATIC', 'COURSE_ADMIN');
INSERT INTO `course_tool` VALUES (11, 'CLLNP___', '../claroline/learnPath/learningPathList.php', '../claroline/img/step.gif', 'ALL', 11, 'AUTOMATIC', 'COURSE_ADMIN');

*/




//insert_course_tool('CLDOC___');
//insert_course_tool('CLDOC___');
//insert_course_tool('CLXXX___');
//echo  get_next_course_tool_rank();
//set_course_tool_access_level(2, 'ALL');
//enable_course_tool(2);
//insert_local_course_tool('yahoo', 'http://www.yahoo.com');
//delete_course_tool(10);
//set_local_course_tool(9, 'IPM', 'http://www.ipm/ucl.ac.be');
//move_down_course_tool(9);

//$toolList = get_course_tool_list('ALL');
//
//// DEBUG START ------------------------------
//echo "\n<pre style='color:red;font-weight:bold'>";
//var_dump($toolList);
//echo "</pre>";
//// DEBUG END ------------------------------
//
?>
