<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                               |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$           |
	  |   Tha๏ Translation                                                   |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesch้ <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Sutas Jitchuen		<dtsjc@mucc.mahidol.ac.th>    			 |
      +----------------------------------------------------------------------+
 */
 $msgAdminPanel = "Administrator Panel";
 $msgStats = "สถิติ";
 $msgStatsBy = "สถิติ โดย";
 $msgHours = "ชั่วโมง";
 $msgDay = "วัน";
 $msgWeek = "สัปดาห์";
 $msgMonth = "เดือน";
 $msgYear = "ปี";
 $msgFrom = "จาก ";
 $msgTo = "ถึง ";
 $msgPreviousDay = "วันก่อนหน้า";
 $msgNextDay = "วันต่อไป";
 $msgPreviousWeek = "สัปดาห์ก่อนหน้า";
 $msgNextWeek = "สัปดาห์ถัดไป";
 $msgCalendar = "ปฏิทิน";
 $msgShowRowLogs = "แสดงปูมบันทึก";
 $msgRowLogs = "ปูมบันทึก";
 $msgRecords = "records";
 $msgDaySort = "เรียงตามวัน";
 $msgMonthSort = "เรียงตามเดือน";
 $msgCountrySort = "เรียงตามประเทศ";
 $msgOsSort = " เรียงตามระบบปฏิบัติการ";
 $msgBrowserSort = "เรียงตาม Browser";
 $msgProviderSort = "เรียงตาม Provider";
 $msgTotal = "รวมทั้งสิ้น";
 $msgBaseConnectImpossible = "ไม่สามารถเลือกฐานข้อมูล SQL ได้";
 $msgSqlConnectImpossible = "SQL server connection impossible";
 $msgSqlQuerryError = "SQL querry impossible";
 $msgBaseCreateError = "An error occure when attempting to create ezboo base";
 $msgMonthsArray = array("january","february","march","april","may","june","july","august","september","october","november","december");
 $msgDaysArray = array("Sunday","Monday","Tuesday","Wenesday","Thursday","Friday","Saturday");
 $msgDaysShortArray=array("S","M","T","W","T","F","S");
 $msgToday = "วันนี้";
 $msgOther = "Other";
 $msgUnknown = "Unknown";
 $msgServerInfo = "php Server info";
 $msgStatBy = "Statistics by";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Administrator:</b> A cookie has been created on your computer,<BR>
     You will not appear anymore in your logs.<br><br><br><br>";
 $msgCreateCookError = "<b>Administrator:</b> cookie could not be saved on your computer.<br>
     Check your browser settings and refresh page.<br><br><br><br>";
 $msgInstalComments = "<p>The automatic install procedure will attempt to:</p>
       <ul>
         <li>create a table named <b>liste_domaines</b> in your SQL base<br>
           </b>This table will be automatically filled with country names with InterNIC
           codes</li>
         <li>create a table named <b>logezboo</b><br>
           This table will store your logs</li>
       </ul>
       <font color=\"#FF3333\">You must have modified manually:<ul><li><b>config_sql.php3</b> file with your <b>login</b>, <b>password</b> and <b>base name</b> for SQL sever connexion.</li><br><li>The file <b>config.inc.php3</b> must have been modified to select apropriate language.</font></li></ul><br>To do so, you can you anykind of text editor (such as Notepad).";
 $msgInstallAbort = "SETUP ABORTED";
 $msgInstall1 = "If there is no error message above, installation is successfull.";
 $msgInstall2 = "2 tables have been created in your SQL base";
 $msgInstall3 = "You can now open the main interface";
 $msgInstall4 = "In order to fill your table when pages are loaded, you must put a tag in monitored pages.";

 $msgUpgradeComments ="This new version of ezBOO WebStats uses the same table <b>logezboo</b> as previous 
  						versions.<br>
  						If countries are not written in english, you must erase table <b>liste_domaine</b> 
  						et launch setup.<br>
  						This will have no effect on the table <b>logezboo</b> .<br>
  						Error message is normal. :-)";


$langStats="สถิติ";
?>