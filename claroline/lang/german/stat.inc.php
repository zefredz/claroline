<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$              |
      |   German translation                                                 |
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
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator:                                                          |
      +----------------------------------------------------------------------+
 */
 $langStats="Statistiken";
 $msgAdminPanel = "Administratorformular";
 $msgStats = "Statistiken";
 $msgStatsBy = "Statistiken von";
 $msgHours = "Stunden";
 $msgDay = "Tag";
 $msgWeek = "Woche";
 $msgMonth = "Monat";
 $msgYear = "Jahr";
 $msgFrom = "von ";
 $msgTo = "bis ";
 $msgPreviousDay = "vorheriger Tag";
 $msgNextDay = "nächster Tag";
 $msgPreviousWeek = "vorherige Woche";
 $msgNextWeek = "nächste Woche";
 $msgCalendar = "Kalender";
 $msgShowRowLogs = "Logzeilen anzeigen";
 $msgRowLogs = "Logzeilen";
 $msgRecords = "Datensätze";
 $msgDaySort = "Tagessortierung";
 $msgMonthSort = "Monatssortierung";
 $msgCountrySort = "Ländersortierung";
 $msgOsSort = "Betriebssystemsortierung";
 $msgBrowserSort = "Browsersortierung";
 $msgProviderSort = "Providersortierung";
 $msgTotal = "Gesamt";
 $msgBaseConnectImpossible = "SQL Quelle nicht erreichbar";
 $msgSqlConnectImpossible = "SQL Server Verbindung nicht möglich";
 $msgSqlQuerryError = "SQL Query nicht möglich";
 $msgBaseCreateError = "Beim Versuch ezboo Base zu erstellen ist ein Fehler aufgetreten";
 $msgMonthsArray = array("Januar","Februar","März","April","Mai","Junie","Juli","August","September","Oktober","November","Dezember");
 $msgDaysArray = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");
 $msgDaysShortArray=array("S","M","D","M","D","F","S");
 $msgToday = "Heute";
 $msgOther = "Anderer";
 $msgUnknown = "Unbekannt";
 $msgServerInfo = "php Server info";
 $msgStatBy = "Statistiken von";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Administrator:</b> Ein Cookie wurde auf Ihrem Computer angelegt,<BR>
     Sie werden in Ihren Logs nicht mehr geführt.<br><br><br><br>";
 $msgCreateCookError = "<b>Administrator:</b> Der Cookie konnte nicht auf Ihrem Computer gespeichert werden.<br>
     Überprüfen Sie die Einstellungen Ihres Browsers und aktualisieren Sie die Seite.<br><br><br><br>";
 $msgInstalComments = "<p>Die automatische Installationsprozedur wird versuchen</p>
       <ul>
         <li>eine Tabelle namens <b>liste_domaines</b> in Ihrer SQL Datenbank anzulegen.<br>
           </b>Diese Tabelle wird automatisch mit Ländernamen und den entsprechenden InterNIC codes gefüllt</li>
         <li>Eine Tabelle namens <b>logezboo</b> anlegen<br>
           Diese Tabelle wird Ihre Logdaten speichern</li>
       </ul>
       <font color=\"#FF3333\">Sie müssen die folgenden Punkte von Hand anpassen:<ul><li><b>config_sql.php3</b> Datei mit Ihrem <b>Login</b>, <b>Passwort</b> und <b>Datenbankname</b> für die SQL-Sever Verbindung.</li><br><li>Die Datei <b>config.inc.php3</b> muss angepasst werden, um die entsprechende Sprache einzustellen.</font></li></ul><br>Um das umzusetzen können sie einen beliebigen Texteditor verwenden (z.B. Notepad).";
 $msgInstallAbort = "SETUP ABGEBROCHEN";
 $msgInstall1 = "Erscheint keine Fehlermeldung, war die Installation erfolgreich.";
 $msgInstall2 = "2 Tabellen wurden in Ihrer SQL Datenbank angelegt";
 $msgInstall3 = "Sie können nun das Hauptinterface aufrufen";
 $msgInstall4 = "Um Ihre Tabelle zu füllen, wenn Seiten geladen werden, müssen Sie einen Tag in die zu überwachenden Seiten einfügen.";

 $msgUpgradeComments ="Diese neue Version von ezBOO WebStats benutzt die gleiche Tabelle <b>logezboo</b> wie vorhergehende Versionen.<br>
                                                  Wenn Länder nicht in Englisch geschrieben sind, müssen sie die Tabelle <b>liste_domaine</b>
                                                  löschen und die Installation neu durchführen.<br>
                                                  Das wird keine Auswirkungen auf die Tabelle <b>logezboo</b> haben.<br>
                                                  Die Fehlermeldung ist normal. :-)";

?>