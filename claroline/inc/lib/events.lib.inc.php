<?php # $Id$

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

/*============================================================================
                                 EVENTS LIBRARY
  ============================================================================*/

/*
 * Functions of this library are used to record informations when some kind
 * of event occur. Each event has his own types of informations then each event
 * use its own function.
 */


// REGROUP TABLE NAMES FOR MAINTENANCE PURPOSE

// stats db
$TABLETRACK_LOGIN         = $statsDbName."`.`track_e_login";
$TABLETRACK_OPEN          = $statsDbName."`.`track_e_open";
$TABLETRACK_SUBSCRIPTIONS = $statsDbName."`.`track_e_subscriptions";
$TABLETRACK_DEFAULT       = $statsDbName."`.`track_e_default";
// course db
$TABLETRACK_ACCESS        = $_course['dbNameGlu']."track_e_access";
$TABLETRACK_DOWNLOADS     = $_course['dbNameGlu']."track_e_downloads";
$TABLETRACK_UPLOADS       = $_course['dbNameGlu']."track_e_uploads";
$TABLETRACK_LINKS         = $_course['dbNameGlu']."track_e_links";
$TABLETRACK_EXERCICES     = $_course['dbNameGlu']."track_e_exercices";




define("CONFVAL_LOG_DIRECT_IN_TABLE",true); //unstable with false
define("CONFVAL_INSERT_IS_DELAYED", true);
/**
 * DEPRECATED : all references to this function must be replaced by claro_sql_query
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc
 */
function myMySqlQuery($sql)
{
    @mysql_query($sql);
    if (mysql_errno())
    {
        // uncomment following line to debug
        //echo "Mysql error :  ".mysql_errno().": ".mysql_error()." In : $sql ";
    }
    // else : nothing to do
    return $val;
}

/**
 * Function found on php.net to replace the html_entity_decode (that only works in php 4.3.0 and upper)
 *
 */
function unhtmlentities ($string) 
{
   $trans_tbl = get_html_translation_table (HTML_ENTITIES);
   $trans_tbl = array_flip ($trans_tbl);
   return strtr ($string, $trans_tbl);
}
  
  
/**
 *
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record information for open event (when homepage is opened)
 */

function event_open()
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $rootWeb ;
    global $TABLETRACK_OPEN;
    // @getHostByAddr($REMOTE_ADDR) : will provide host and country information
    // $HTTP_USER_AGENT :  will provide browser and os information
    // $HTTP_REFERER : provide information about refering url
    $referer = $_SERVER['HTTP_REFERER'];
    // record informations only if user comes from another site
    //if(!eregi($rootWeb,$referer))
    $pos = strpos($referer,$rootWeb);
    if( $pos === false )
    {
        //$remhost = @getHostByAddr($_SERVER['REMOTE_ADDR']);
    	  //if($remhost == $_SERVER['REMOTE_ADDR'] ) $remhost = "Unknown"; // don't change this
        
        $reallyNow = time();

        $sql = "INSERT INTO `".$TABLETRACK_OPEN."`
                        (`open_date`)
                VALUES
                        (FROM_UNIXTIME($reallyNow))";

        $res = claro_sql_query($sql);
        //$mysql_query($sql);
    }
    return 1;
}


/**

 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record information for login event
     (when an user identifies himself with username & password)
 */

function event_login()
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $_uid;
    global $TABLETRACK_LOGIN;

    $reallyNow = time();
    $sql = "INSERT INTO `".$TABLETRACK_LOGIN."`
            (`login_user_id`, 
             `login_ip`, 
             `login_date`)

             VALUES
                ('".$_uid."', 
                '".$_SERVER['REMOTE_ADDR']."',
                FROM_UNIXTIME(".$reallyNow."))";

    $res = claro_sql_query($sql);
    //$mysql_query($sql);
    //return 0;

}


/**

 * @param tool name of the tool (rubrique in mainDb.accueil table)
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record information for access event for courses
 */
function event_access_course()
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $_uid;
    global $TABLETRACK_ACCESS;

    $reallyNow = time();
    if($_uid)
    {
        $user_id = "'".$_uid."'";
    }
    else // anonymous
    {
        $user_id = "NULL";
    }
    $sql = "INSERT INTO `".$TABLETRACK_ACCESS."`
            (`access_user_id`,  
             `access_date`)
            VALUES
            (".$user_id.", 
            FROM_UNIXTIME(".$reallyNow."))";

        $res = claro_sql_query($sql);

    return 1;

}

/**
 * @param tid id of the tool user access (tid is a unique identifier of a tool occurence)
 * @param tlabel label of the tool the user access (tlabel is a unique identifier for a type of tool
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record information for access event for tools
 */
function event_access_tool($tid, $tlabel)
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $_uid;
    global $TABLETRACK_ACCESS;
    global $rootWeb;
    global $_course;

    $reallyNow = time();
    // record information only if user doesn't come fromthe tool itself
    if( $_SESSION['tracking']['lastUsedTool'] != $tlabel )
    {
        if($_uid)
        {
            $user_id = "'".$_uid."'";
        }
        else // anonymous
        {
            $user_id = "NULL";
        }

        $sql = "INSERT INTO `".$TABLETRACK_ACCESS."`
                (`access_user_id`,
                 `access_tid`,
                 `access_tlabel`,
                 `access_date`)

             VALUES

             (".$user_id.",
              ".$tid.",
              '".$tlabel."',
              FROM_UNIXTIME(".$reallyNow."))";
              
        $res = claro_sql_query($sql);
        $_SESSION['tracking']['lastUsedTool'] = $tlabel;
    }
    return 1;
}

/**

 * @param doc_id id of document (id in mainDb.document table)
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record information for download event
     (when an user click to d/l a document)
     it will be used in a redirection page
 */
function event_download($doc_url)
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $_uid;

    global $TABLETRACK_DOWNLOADS;

    $reallyNow = time();
    if($_uid)
    {
        $user_id = "'".$_uid."'";
    }
    else // anonymous
    {
        $user_id = "NULL";
    }

    $sql = "INSERT INTO `".$TABLETRACK_DOWNLOADS."`
            (
             `down_user_id`,
             `down_doc_path`,
             `down_date`
            )

            VALUES
            (
             ".$user_id.",
             '".htmlspecialchars($doc_url,ENT_QUOTES)."', 
             FROM_UNIXTIME(".$reallyNow.")
            )";
                
    $res = claro_sql_query($sql);
    //$mysql_query($sql);
    return 1;
}

/**

 * @param doc_id id of document (id in mainDb.document table)
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record information for upload event
     used in the works tool to record informations when
     an user upload 1 work
 */
function event_upload($doc_id)
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $_uid;

    global $TABLETRACK_UPLOADS;

    $reallyNow = time();
    if($_uid)
    {
        $user_id = "'".$_uid."'";
    }
    else // anonymous
    {
        $user_id = "NULL";
    }
    
    $sql = "INSERT INTO `".$TABLETRACK_UPLOADS."`
            (
             `upload_user_id`,
             `upload_work_id`, 
             `upload_date`
            )

            VALUES
            (
             ".$user_id.", 
             '".$doc_id."', 
             FROM_UNIXTIME(".$reallyNow.")
            )";
                
    $res = claro_sql_query($sql);
    //$mysql_query($sql);
    return 1;
}
/**

 * @param link_id (id in coursDb liens table)
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record information for link event (when an user click on an added link)
    it will be used in a redirection page
*/
function event_link($link_id)
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $_uid;
    global $TABLETRACK_LINKS;

    $reallyNow = time();
    if($_uid)
    {
        $user_id = "'".$_uid."'";
    }
    else // anonymous
    {
        $user_id = "NULL";
    }
    $sql = "INSERT INTO `".$TABLETRACK_LINKS."`
            (
             `links_user_id`, 
             `links_link_id`, 
             `links_date`
            )
            
            VALUES
            (
             ".$user_id.", 
             '".$link_id."', 
             FROM_UNIXTIME(".$reallyNow.")
            )";
                
    $res = claro_sql_query($sql);
    //$mysql_query($sql);
    return 1;
}

/**

 * @param exo_id ( id in courseDb exercices table )
 * @param result ( score @ exercice )
 * @param weighting ( higher score )
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record result of user when an exercice was done
*/
function event_exercice($exo_id,$score,$weighting,$time, $uid = "")
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $TABLETRACK_EXERCICES;

    $reallyNow = time();
    if($uid && $uid != "")
    {
        $user_id = "'".$uid."'";
    }
    else // anonymous
    {
        $user_id = "NULL";
    }
    $sql="INSERT INTO `".$TABLETRACK_EXERCICES."`
          (
           `exe_user_id`,
           `exe_exo_id`,
           `exe_result`,
           `exe_weighting`,
           `exe_date`,
	   `exe_time`
          )
          
          VALUES
          (
          ".$user_id.",
           '".$exo_id."',
           '".$score."',
           '".$weighting."',
           FROM_UNIXTIME(".$reallyNow."),
	   $time
          )";

    $res = claro_sql_query($sql);
    //$mysql_query($sql);
    //return 0;
}

/**

 * @param cours_code (cours.code in maindb))

 * @param action ( enum of strings : "sub" or "unsub" )
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Record information for subscription and unsubscription to courses
*/
function event_subscription($cours_id,$action)
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $_uid;
    global $TABLETRACK_SUBSCRIPTIONS;
    
    $sql="INSERT INTO `$TABLETRACK_SUBSCRIPTIONS`
          (`sub_user_id`,
           `sub_cours_id`,
           `sub_action`)
          VALUES
          ('".$_uid."', 
           '".$cours_id."', 
           '".$action."')";
                
    $res = claro_sql_query($sql);

    //$mysql_query($sql);
    return 1;

}

/**

 * @param type_event type of event to record
 * @param values indexed array of values (keys are the type of values, values are the event_values)
 * @author Sebastien Piraux <pir@cerdecam.be>
 * @desc Standard function for all users who wants to add an event recording in their pages
         e.g. : event_default("Exercice Result",array ("ex_id"=>"1", "result"=> "5", "weighting" => "20"));
*/
function event_default($type_event,$values)
{
    global $is_trackingEnabled ;
    // if tracking is disabled record nothing
    if( ! $is_trackingEnabled ) return 0;

    global $_uid;
    global $_cid;
    global $TABLETRACK_DEFAULT;

    $reallyNow = time();

    if($_uid)
    {
        $user_id = "\"".$_uid."\"";
    }
    else // anonymous
    {
        $user_id = "NULL";
    }
    if($_uid)
    {
        $cours_id = "\"".$_cid."\"";
    }
    else // anonymous
    {
        $cours_id = "NULL";
    }
    $sqlValues = "";

    foreach($values as $type_value => $event_value)
    {
        if($sqlValues == "")
        {
            $sqlValues .= "('',$user_id,$cours_id,$reallyNow,'$type_event','$type_value','$event_value')";
        }
        else
        {
            $sqlValues .= ",('',$user_id,$cours_id,$reallyNow,\"$type_event\",\"$type_value\",\"$event_value\")";
        }
    }
    $sql = "INSERT INTO `".$TABLETRACK_DEFAULT."`
            VALUES ".$sqlValues;


    $res = claro_sql_query($sql);
    return 1;

}
?>
