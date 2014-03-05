<?php // $Id$

/**
 * Claroline extension modules docks management functions
 *
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html
 *      GNU GENERAL PUBLIC LICENSE version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.12
 */

/**
 * Set the dock in which the module displays its content
 * @param integer $moduleId id of the module to rename
 * @param string $newDockName new name  for the doc
 * @return boolean
 */
function add_module_in_dock( $moduleId, $newDockName )
{
    $tbl = claro_sql_get_main_tbl();

    //find info about this module occurence in this dock in the DB

    $sql = "SELECT D.`name`      AS dockname,
                   D.`rank`      AS oldRank
            FROM `" . $tbl['module'] . "` AS M
               , `" . $tbl['dock']   . "` AS D
            WHERE M.`id` = D.`module_id`
            AND M.`id` = " . (int) $moduleId . "
            AND D.`name` = '" . $newDockName . "'";
    $module = claro_sql_query_get_single_row($sql);

    //if the module is already in the dock ,we just do nothing and return true.

    if (isset($module['dockname']) && $module['dockname'] == $newDockName)
    {
        return true;
    }
    else
    {
        //find the highest rank already used in the new dock
        $max_rank = get_max_rank_in_dock($newDockName);
        // the module is not already in this dock, we just insert it into this in the DB

        $sql = "INSERT INTO `" . $tbl['dock'] . "`
                SET module_id = " . (int) $moduleId . ",
                    name    = '" . claro_sql_escape($newDockName) . "',
                    rank    = " . ((int) $max_rank + 1) ;
        $result = claro_sql_query($sql);

        // TODO FIXME handle failure
        generate_module_cache();

        return $result;
    }
}

/**
 * Remove a module from a dock in which the module displays
 * @param integer $moduleId
 * @param string  $dockName
 */

function remove_module_dock($moduleId, $dockName)
{
    $tbl = claro_sql_get_main_tbl();

    // call of this function to remove ALL occurence of the module in any dock

    if ('ALL' == $dockName)
    {
        //1- find all dock in which the dock displays

        $sql="SELECT `name` AS dockName
              FROM   `" . $tbl['dock'] . "`
              WHERE  `module_id` = " . (int) $moduleId;

        $dockList = claro_sql_query_fetch_all($sql);

        //2- re-call of this function which each dock concerned

        foreach($dockList as $dock)
        {
            remove_module_dock($moduleId,$dock['dockName']);
        }
    }
    else //call of this function to remove ONE SPECIFIC occurence of the module in the dock
    {
        //find the rank of the module in this dock :

        $sql = "SELECT `rank` AS oldRank
                FROM   `" . $tbl['dock'] . "`
                WHERE  `module_id` = " . (int) $moduleId . "
                AND    `name` = '" .$dockName . "'";
        $module = claro_sql_query_get_single_row($sql);

        //move up all modules displayed in this dock

        $sql = "UPDATE `" . $tbl['dock'] . "`
                SET `rank` = `rank` - 1
                WHERE `name` = '" . $dockName . "'
                AND `rank` > " . (int) $module['oldRank'];
        claro_sql_query($sql);

        //delete the module line in the dock table

        $sql = "DELETE FROM `" . $tbl['dock'] . "`
                WHERE `module_id` = " . (int) $moduleId. "
                AND   `name` = '" . $dockName . "'";
        claro_sql_query($sql);

        generate_module_cache();
    }
}

/**
 * Move a module inside its dock (change its position in the display
 * @param integer $moduleId
 * @param string $dockName
 * @param string $direction 'up' or 'down'
 */

function move_module_in_dock($moduleId, $dockName, $direction)
{
    $tbl = claro_sql_get_main_tbl();

    switch ($direction)
    {
        case 'up' :
        {
            //1-find value of current module rank in the dock
            $sql = "SELECT `rank`
                    FROM `" . $tbl['dock'] . "`
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" . claro_sql_escape($dockName) . "'";
            $result=claro_sql_query_get_single_value($sql);

            //2-move down above module
            $sql = "UPDATE `" . $tbl['dock'] . "`
                    SET `rank` = `rank`+1
                    WHERE `module_id` != " . (int) $moduleId . "
                    AND `name`       = '" . claro_sql_escape($dockName) . "'
                    AND `rank`       = " . (int) $result['rank'] . " -1 ";

            claro_sql_query($sql);

            //3-move up current module
            $sql = "UPDATE `" . $tbl['dock'] . "`
                    SET `rank` = `rank`-1
                    WHERE `module_id` = " . (int) $moduleId . "
                    AND `name`      = '" .  claro_sql_escape($dockName) . "'
                    AND `rank` > 1"; // this last condition is to avoid wrong update due to a page refreshment
            claro_sql_query($sql);

            break;
        }
        case 'down' :
        {
            //1-find value of current module rank in the dock
            $sql = "SELECT `rank`
                    FROM `" . $tbl['dock'] . "`
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" . claro_sql_escape($dockName) . "'";
            $result=claro_sql_query_get_single_value($sql);

            //this second query is to avoid a page refreshment wrong update

            $sqlmax= "SELECT MAX(`rank`) AS `max_rank`
                      FROM `" . $tbl['dock'] . "`
                      WHERE `name`='" .  claro_sql_escape($dockName) . "'";
            $resultmax=claro_sql_query_get_single_value($sqlmax);

            if ($resultmax['max_rank'] == $result['rank']) break;

            //2-move up above module
            $sql = "UPDATE `" . $tbl['dock'] . "`
                    SET `rank` = `rank` - 1
                    WHERE `module_id` != " . $moduleId . "
                    AND `name` = '" . claro_sql_escape($dockName) . "'
                    AND `rank` = " . (int) $result['rank'] . " + 1
                    AND `rank` > 1";
            claro_sql_query($sql);

            //3-move down current module
            $sql = "UPDATE `" . $tbl['dock'] . "`
                    SET `rank` = `rank` + 1
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" .  claro_sql_escape($dockName) . "'";
            claro_sql_query($sql);

            break;
        }
    }
    // TODO FIXME handle failure
    generate_module_cache();
}

/**
 * Function used by the SAX xml parser when the parser meets a opening tag
 * @param tring $dockName the dock from which we want this info
 * @return int the max rank used for this dock
 *
 */
function get_max_rank_in_dock($dockName)
{
    $tbl = claro_sql_get_main_tbl();


    $sql = "SELECT MAX(rank) AS mrank
            FROM `" . $tbl['dock'] . "` AS D
            WHERE D . `name` = '" . claro_sql_escape($dockName) . "'";
    $max_rank = claro_sql_query_get_single_value($sql);
    return (int) $max_rank;
}

/**
 * Return list of dock where a module is docked
 * @param integer $moduleId
 * @return array of array ( id, name)
 */
function get_module_dock_list($moduleId)
{
    static $dockListByModule = array();

    if(!array_key_exists($moduleId,$dockListByModule))
    {
        $tbl_name        = claro_sql_get_main_tbl();
        $sql = "SELECT `id`    AS dock_id,
                       `name`  AS dockname
            FROM `" . $tbl_name['dock'] . "`
            WHERE `module_id`=" . (int) $moduleId;
        $dockListByModule[$moduleId] = claro_sql_query_fetch_all($sql);

    }
    return $dockListByModule[$moduleId];
}

/**
 * Return list of dock aivailable for a given type
 * @param string $moduleType
 * @param string $context
 * @return array
 */
function get_dock_list($moduleType)
{
    $dockList   = array();
    switch($moduleType)
    {
        case 'applet' :
        {
            $dockList['campusBannerLeft'] = get_lang('Campus banner - left');
            $dockList['campusBannerRight'] = get_lang('Campus banner - right');
            $dockList['userBannerLeft'] = get_lang('User banner - left');
            $dockList['userBannerRight'] = get_lang('User banner - right');
            $dockList['courseBannerLeft'] = get_lang('Course banner - left');
            $dockList['courseBannerRight'] = get_lang('Course banner - right');
            $dockList['campusHomePageTop'] = get_lang('Campus homepage - top');
            $dockList['campusHomePageBottom'] = get_lang('Campus homepage - bottom');
            $dockList['campusHomePageRightMenu'] = get_lang('Campus homepage - right menu');
            $dockList['campusFooterCenter'] = get_lang('Campus footer - center');
            $dockList['campusFooterLeft'] = get_lang('Campus footer - left');
            $dockList['campusFooterRight'] = get_lang('Campus footer - right');
            $dockList['userProfileBox'] = get_lang('User profile box');
            break;
        }
        case 'tool' :
        {
            $dockList['commonToolList'] = get_lang('Tool list');
        }
    }
    return $dockList;
}

