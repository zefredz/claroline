<?
 $msgAdminPanel = "Okno administratorja";
 $msgStats = "Statistika";
 $msgStatsBy = "Statistika po";
 $msgHours = "urah";
 $msgDay = "dnevih";
 $msgWeek = "tednih";
 $msgMonth = "mesecih";
 $msgYear = "letih";
 $msgFrom = "od ";
 $msgTo = "do ";
 $msgPreviousDay = "prejsnji dan";
 $msgNextDay = "naslednji dan";
 $msgPreviousWeek = "prejsnji teden";
 $msgNextWeek = "naslednji teden";
 $msgCalendar = "koledar";
 $msgShowRowLogs = "show row logs";
 $msgRowLogs = "row logs";
 $msgRecords = "zapisi";
 $msgDaySort = "Razvrscanje po dnevih";
 $msgMonthSort = "MRazvrscanje po mesecih";
 $msgCountrySort = "Razvrscanje po pokrajini";
 $msgOsSort = "OS sort";
 $msgBrowserSort = "Razvrscanje po brkljalniku";
 $msgProviderSort = "Razvrscanje po ponudniku";
 $msgTotal = "Skupaj";
 $msgBaseConnectImpossible = "Ne znam select SQL base";
 $msgSqlConnectImpossible = "Povezava s streznikom SQL ni mozna";
 $msgSqlQuerryError = "SQL poizvedba ni mozna";
 $msgBaseCreateError = "Napaka pri poskusu tvorbe ezboo base";
 $msgMonthsArray = array("januar","februar","marec","april","maj","junij","julij","avgust","september","oktober","november","december");
 $msgDaysArray = array("Nedelja","Ponedeljek","Torek","Sreda","Cetrtek","Petek","Sobota");
 $msgDaysShortArray=array("N","P","T","S","C","P","S");
 $msgToday = "Danes";
 $msgOther = "Drugo";
 $msgUnknown = "Neznano";
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

$langStats="Statistika";
?>