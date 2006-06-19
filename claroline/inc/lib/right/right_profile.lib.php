<?php // $Id$

/**
 * CLAROLINE
 *
 * Library profile
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package RIGHT
 *
 * @author Claro Team <cvs@claroline.net>
 */

require_once 'constants.inc.php';

/**
 * Get all names of profile in an array where key are profileId
 */

function get_all_profile_name_list ()
{
    $profileList = null;

    static $cachedProfileList = null ;    

    if ( $cachedProfileList )
    {
        $profileList = $cachedProfileList;
    }
    else
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_profile = $tbl_mdb_names['right_profile'];

        $sql = "SELECT profile_id, name
                FROM `" . $tbl_profile . "`
                ORDER BY profile_id ";

        $result = claro_sql_query_fetch_all($sql);

        foreach ( $result as $profile)
        {
            $profile_id = $profile['profile_id'];
            $profile_name = $profile['name'];
            $profileList[$profile_id] = $profile_name; 
        }

        $cachedProfileList = $profileList ; // cache for the next time ...
    }
    
    return $profileList ;
}

/**
 * Get profileId
 */

function get_profile_id ($profileName)
{
    $profileList = get_all_profile_name_list();

    $profileList = array_flip($profileList);

    if ( isset($profileList[$profileName]) )
    {
        return $profileList[$profileName];
    }
    else
    {
        return false;
    }
}

/**
 * Get profileName
 */

function get_profile_name ($profileId)
{
    $profileList = get_all_profile_name_list();

    if ( isset($profileList[$profileId]) )
    {
        return $profileList[$profileId];
    }
    else
    {
        return false;
    }
}

?>
