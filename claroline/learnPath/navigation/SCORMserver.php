<?
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version  $Revision$                                 |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors: Piraux Sébastien <pir@cerdecam.be>                          |
  |          Lederer Guillaume <led@cerdecam.be>                         |
  +----------------------------------------------------------------------+

  DESCRIPTION:    This file must be included when the module browsed is SCORM conformant
                  This script supplies the SCORM API implementation in javascript for browsers like IE,NS and Mozilla
  ****

*/
 /**
  * Scormserver
  * This file implements the NusSoap PHPWebservice needed for the serverside SCORM API implémentation
  * see also :
  * @link http://www.adlnet.org
  * @package learningpath
  * @subpackage navigation
  * @author Piraux Sébastien <pir@cerdecam.be>
  * @author Lederer Guillaume <led@cerdecam.be>
  * @filesource
  * @copyright This source file is subject to the GENERAL PUBLIC LICENSE, available through the world-wide-web at {@link http://www.gnu.org/copyleft/gpl.html}
  */
  /**
   *    Needed for session check, config vars, ...
   *
   */
  $langFile = "learnPath";
  include ('../../inc/claro_init_global.inc.php');

  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

  $TABLEUSERS                    = $mainDbName."`.`user";




  // col names in user_module_progress (used to build update selects)
  // keys correspond to keys in javascript API
  // value is an empty string when there is no corresponding column in db
  $umpColNames[0]  = "";
  $umpColNames[1]  = "";
  $umpColNames[2]  = "";
  $umpColNames[3]  = "lesson_location";
  $umpColNames[4]  = "lesson_status";
  $umpColNames[5]  = "credit";
  $umpColNames[6]  = "entry";
  $umpColNames[7]  = "";
  $umpColNames[8]  = "raw";
  $umpColNames[9]  = "total_time";
  $umpColNames[10] = "";
  $umpColNames[11] = "session_time";
  $umpColNames[12] = "suspend_data";
  $umpColNames[13] = "";
  $umpColNames[14] = "scoreMin";
  $umpColNames[15] = "scoreMax";
/*======================================
       CLAROLINE MAIN
  ======================================*/

  // NuSOAP needed librairy-----------------------

  require_once("nusoap.php");

  $server = new soap_server;

  $server->configureWSDL('LMSScorm', $clarolineRepositoryWeb."/learnPath/navigation");
  $server->wsdl->schemaTargetNamespace = 'http://soapinterop.org/xsd/';

  $server->wsdl->addComplexType(
        'ArrayOfstring',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'string[]')),
        'xsd:string'
  );

  // Register functions available by the webservice


  $server->register('doLMSCommit',array('inputString'=>'si:ArrayOfstring','lpid'=>'xsd:integer'),array('return'=>'xsd:string'),'http://soapinterop.org/');
  $server->register('doLMSFinish',array('inputString'=>'si:ArrayOfstring','lpid'=>'xsd:integer'),array('return'=>'xsd:string'),'http://soapinterop.org/');

  function writeDebugInAFile($type, $txt,$line, $file)
  {
         $fp = @fopen("./DEBUG.html","a+");
         fputs($fp,"[<i>".date(" j/n/Y H:i:s ")."</i>] [<b>".$type."</b>] [".$_SESSION['navig']."] [ line ".$line." in file ".$file." ] <br />".$txt." <br /><br /> \n");
  }

  // WEBSERVICE SCORM API IMPlEMENTATION------------
  /**
   * doLMSCommit is called when the SCO execute a LMScommit() SCORM API function.
   *
   *
   */
  /*
       These are the correspondances
       ex : func_get_arg(6) will return the value of cmi.core.entry

        [0]  = "cmi.core._children";
        [1]  = "cmi.core.student_id";
        [2]  = "cmi.core.student_name";
        [3]  = "cmi.core.lesson_location";
        [4]  = "cmi.core.lesson_status";
        [5]  = "cmi.core.credit";
        [6]  = "cmi.core.entry";
        [7]  = "cmi.core.score._children";
        [8]  = "cmi.core.score.raw";
        [9]  = "cmi.core.total_time";
        [10] = "cmi.core.exit";
        [11] = "cmi.core.session_time";
        [12] = "cmi.suspend_data";
        [13] = "cmi.launch_data";
  */
  function doLMSCommit($inputString, $lpid)
  {
       global $umpColNames;
       global $TABLEUSERMODULEPROGRESS, $TABLELEARNPATHMODULE, $TABLEUSERS;

       // anonymous user or bug in module progress table , do not commit anything
       if ( !isset($lpid) || $lpid == '' ) 
       {
          //writeDebugInAFile(" lpid not set : ", $lpid,__LINE__, __FILE__);
          return "true";
        }

       //writeDebugInAFile("New LMSCommit", "<hr />", __LINE__, __FILE__);

       // build query
       $args = "";
       $i = 0;
       $creditToChange = false;

      
       foreach ($inputString as $value)
       {
             if ( $umpColNames[$i] == "lesson_status" || $umpColNames[$i] == "entry" || $umpColNames[$i] == "credit" )
             {

                // Set lesson status to COMPLETED if the SCO didn't change it itself.

                $value = strtoupper($value);
                if ( $umpColNames[$i] == "lesson_status" &&  $value == "NOT ATTEMPTED" )
                {
                    $value = "COMPLETED";
                    $creditToChange = true;
                }

                // see if cmi.core.credit must be set by the LMS

                if ( $umpColNames[$i] == "lesson_status" && ($value == "COMPLETED" || $value == "PASSED"))
                {
                    $creditToChange = true;
                }

                //set cmi.core.credit to "CREDIT" value if needed

                if (($umpColNames[$i] == "credit") && ( $creditToChange == true ))
                {
                   $value = "CREDIT";
                }
             }

             if ( $umpColNames[$i] == "session_time")
             {
               $session_time =  $value;
               //writeDebugInAFile(" session time : ", $session_time,__LINE__, __FILE__);
             }

             if ( ($umpColNames[$i]) != "" && ($umpColNames[$i] != "total_time"))
                  $args .= "`".$umpColNames[$i]."` = '".$value."',";
             $i++;
       }

       // remove last ","
       $setExpression = substr($args, 0, -1);
       if ( $i != 0 )
       {
           // udate writable info in the user module progression table

           $sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."` SET ".$setExpression." WHERE `user_module_progress_id` = ".$lpid;
           mysql_query($sql);

           if(mysql_errno()) return "false"; // query failed.
           //writeDebugInAFile("out","true", __LINE__, __FILE__);
           return "true";
       }
       else
       {
           //writeDebugInAFile("out","false",__LINE__, __FILE__);
           return "false";
       }
  }

 /**
   * doLMSFinish is called when the SCO execute a LMScommit() SCORM API function. This include also a doLMScommit execution
   * and an update of the total_time passed by the user in the SCO in user module progression table.
   *
   */
  function doLMSFinish($inputString, $lpid)
  {

       global $umpColNames;
       global $TABLEUSERMODULEPROGRESS, $TABLELEARNPATHMODULE, $TABLEUSERS;

       // anonymous user or bug in module progress table , do not change anything and finish properly
       if ( !isset($lpid) || $lpid == '' ) return "true";

       //update total_time for this user in the user module progression table

          // find session_time in the parameters array

       $i = 0;
       foreach ($inputString as $value)
       {

            if ( $umpColNames[$i] == "session_time")
             {
               $session_time =  $value;
               //writeDebugInAFile(" session time : ", $session_time,__LINE__, __FILE__);
             }
            $i++;
       }

         // find old total time in DB

       $sql = "SELECT `total_time` FROM `".$TABLEUSERMODULEPROGRESS."` WHERE `user_module_progress_id` = ".$lpid;
       $result =  mysql_query($sql);
       $list = mysql_fetch_array($result);

       //writeDebugInAFile(" sql3 ", $sql,__LINE__, __FILE__);

         // add session time to old total time

       if (isScormTime($session_time))
       {

           $total_time = addScormTime($list['total_time'], $session_time);

             // update DB with new total time

           $sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."` SET `total_time` = '".$total_time."' WHERE `user_module_progress_id` = ".$lpid;
           mysql_query($sql);
           //writeDebugInAFile(" sql3 ", $sql,__LINE__, __FILE__);
       }

            // call to commit function before end

       doLMSCommit($inputString, $lpid);
       return "true";
  }



 /**
  * This function allow to see if a time string is the SCORM requested format : hhhh:mm:ss.cc
  *
  */
 function isScormTime($time)
 {
    $mask = "/^[0-9]{2,4}:[0-9]{2}:[0-9]{2}.?[0-9]?[0-9]?$/";
    if (preg_match($mask,$time))
     {
       return true;
     }

    return false;
 }

 /**
  * This function allow to add times saved in the SCORM requested format : hhhh:mm:ss.cc
  *
  */
 function addScormTime($time1, $time2)
 {
   if (isScormTime($time2))
   {
      //extract hours, minutes, secondes, ... from time1 and time2

      $mask = "/^([0-9]{2,4}):([0-9]{2}):([0-9]{2}).?([0-9]?[0-9]?)$/";

      preg_match($mask,$time1, $matches);
      $hours1 = $matches[1];
      $minutes1 = $matches[2];
      $secondes1 = $matches[3];
      $primes1 = $matches[4];

      preg_match($mask,$time2, $matches);
      $hours2 = $matches[1];
      $minutes2 = $matches[2];
      $secondes2 = $matches[3];
      $primes2 = $matches[4];

      // calculate the resulting added hours, secondes, ... for result

      $primesReport = false;
      $secondesReport = false;
      $minutesReport = false;
      $hoursReport = false;

         //calculate primes

      if ($primes1 < 10) {$primes1 = $primes1*10;}
      if ($primes2 < 10) {$primes2 = $primes2*10;}
      $total_primes = $primes1 + $primes2;
      if ($total_primes >= 100)
      {
        $total_primes -= 100;
        $primesReport = true;
      }

         //calculate secondes

      $total_secondes = $secondes1 + $secondes2;
      if ($primesReport) {$total_secondes ++;}
      if ($total_secondes >= 60)
      {
        $total_secondes -= 60;
        $secondesReport = true;
      }

        //calculate minutes

      $total_minutes = $minutes1 + $minutes2;
      if ($secondesReport) {$total_minutes ++;}
      if ($total_minutes >= 60)
      {
        $total_minutes -= 60;
        $minutesReport = true;
      }

        //calculate hours

      $total_hours = $hours1 + $hours2;
      if ($minutesReport) {$total_hours ++;}
      if ($total_hours >= 10000)
      {
        $total_hours -= 10000;
        $hoursReport = true;
      }

         // construct and return result string

      if ($total_hours < 10) {$total_hours = "0".$total_hours;}
      if ($total_minutes < 10) {$total_minutes = "0".$total_minutes;}
      if ($total_secondes < 10) {$total_secondes = "0".$total_secondes;}

      $total_time = $total_hours.":".$total_minutes.":".$total_secondes.".".$total_primes;
      return $total_time;
   }
   else
   {
      return $time1;
   }
 }

 // End webservice

 $server->service($HTTP_RAW_POST_DATA);
?>
