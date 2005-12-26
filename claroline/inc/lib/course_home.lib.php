<?php // $Id$

/**
 * insert a new claroline standart course tool into the course
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  string $tool_label
 * @return void
 */


function insert_course_tool($tool_label)
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'  ];
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    /*
     * Get the necessary tool setting 
     * from the central claroline tool table
     */

    $sql = "SELECT  id , claro_label, script_url, 
                    icon, def_access, def_rank, 
                    add_in_course, access_manager  

            FROM `".$tbl_tool_list."` `course_tool`
            WHERE claro_label = \"". addslashes($tool_label) ."\"";

    list($defaultToolSettingList) = claro_sql_query_fetch_all($sql);

    if (count($defaultToolSettingList) < 1) return false;

    /*
     * Insert the tool into the course table
     */

    $defaultToolSettingList['rank'] = get_next_course_tool_rank();


    $sql = "INSERT INTO `".$tbl_course_tool_list."`
            SET tool_id  = \"".(int)$defaultToolSettingList['id'        ]."\",
                access = \""  .addslashes($defaultToolSettingList['def_access'])."\",
                rank     = \"".(int)$defaultToolSettingList['rank'      ]."\"";

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


function get_course_home_tool_list($reqAccessLevel = 'ALL')
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'  ];
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];


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
                   tl.access                           access,
                   tl.rank                             rank,
                   IFNULL(ct.script_url,tl.script_url) url,
                   ct.claro_label                      label,
                   ct.icon                             icon,
                   ct.access_manager                   access_manager,
                   ISNULL(tl.tool_id)                  external

            FROM `".$tbl_course_tool_list."` tl

            LEFT JOIN `".$tbl_tool_list."` ct

            ON        ct.id = tl.tool_id

            WHERE tl.access IN (\"".implode("\", \"", $reqAccessList)."\")

            ORDER BY external, tl.rank
            ";

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
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'  ];
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    $sql = "SELECT tl.id                               id, 
                   tl.script_name                      name, 
                   tl.access                         access,
                   tl.rank                             rank,
                   IFNULL(ct.script_url,tl.script_url) url,
                   ct.claro_label                      label, 
                   ct.icon                            icon,
                   ct.access_manager                  access_manager

            FROM      `".$tbl_course_tool_list."`             tl

            LEFT JOIN `".$tbl_tool_list."` ct

            ON        ct.id = tl.tool_id

            WHERE tl.id = \"" . (int)$toolId . "\"";
            
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
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    if (! is_array ($toolIdList) ) $toolIdList = array($toolId);

    $sql = "UPDATE `".$tbl_course_tool_list."`
            SET   access = \"". addslashes($level)."\"
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


function set_local_course_tool($toolId, $name, $url)
{
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    // check for "http://", if the user forgot "http://" or "ftp://" or ...
    // the link will not be correct
    if( !ereg( "://",$url ) )
    {
         // add "http://" as default protocol for url
         $url = "http://".$url;
    }
    
    if ( (int)$toolId != 0 )
    {

        $sql = "UPDATE `".$tbl_course_tool_list."`
                SET script_name = \"".addslashes($name)."\",
                    script_url  = \"".addslashes($url)."\"
                WHERE id        = \"".(int)$toolId."\"
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
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    // check for "http://", if the user forgot "http://" or "ftp://" or ...
    // the link will not be correct
    if( !ereg( "://",$url ) )
    {
         // add "http://" as default protocol for url
         $url = "http://".$url;
    }

    $nextRank = get_next_course_tool_rank();

        $sql = "INSERT INTO `".$tbl_course_tool_list."`
                SET 
                script_name = \"".addslashes($name)."\",
                script_url  = \"".addslashes($url)."\",
                access    = \"".addslashes($accessLevel)."\",
                rank        = \"".(int)$nextRank."\"";

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
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    $sql = "DELETE FROM `".$tbl_course_tool_list."`
            WHERE id = \"".(int)$toolId."\"";

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
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    $sql = "SELECT (MAX(rank)+1) next_rank 
            FROM `".$tbl_course_tool_list."`";

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
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    if ($offset < 1) return false;

    $sql = "UPDATE `".$tbl_course_tool_list."`
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
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_course_tool_list = $tbl_cdb_names['tool'];

    if ( strtoupper($moveDirection)     == 'DOWN' ) $sortDirection   = 'DESC';
    elseif ( strtoupper($moveDirection) == 'UP'   ) $sortDirection   = 'ASC';

    if ($sortDirection)
    {
        $sql = "SELECT id, rank
                FROM `".$tbl_tool_list."`
                ORDER BY rank ". $sortDirection;

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

                $sql = "UPDATE `".$tbl_course_tool_list."`
                        SET rank = \"".$nextToolRank."\"
                        WHERE id = \"".$reqToolId."\"";

                claro_sql_query($sql);

                $sql = "UPDATE `".$tbl_course_tool_list."`
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

?>
