<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$                |
      |   Swedish translation                                                |
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
      | Translator: Jan Olsson <jano@artedi.nordmaling.se>                   |
      +----------------------------------------------------------------------+
 */
$langStats="Statistik";
$msgAdminPanel="Administratörspanel";
$msgStats="Statistik";
$msgStatsBy="Statistik över";
$msgHours="timmar";
$msgDay="dag";
$msgWeek="vecka";
$msgMonth="månad";
$msgYear="år";
$msgFrom="från ";
$msgTo="till ";
$msgPreviousDay="föregående dag";
$msgNextDay="nästa dag";
$msgPreviousWeek="föregående vecka";
$msgNextWeek="nästa vecka";
$msgCalendar="kalender";
$msgShowRowLogs="visa radlogg";
$msgRowLogs="radloggar";
$msgRecords="poster";
$msgDaySort="Dagsorterad";
$msgMonthSort="Månadssorterad";
$msgCountrySort="Landssorterad";
$msgOsSort="OS-sorterad";
$msgBrowserSort="Webbläsarsorterad";
$msgProviderSort="ISP-sorterad";
$msgTotal="Totalt";
$msgBaseConnectImpossible="omöjligt att välja SQL-databas";
$msgSqlConnectImpossible="SQL-serveranslutning omöjlig";
$msgSqlQuerryError="SQL-fråga omöjlig";
$msgBaseCreateError="Ett fel uppstod vid försök att skapa ezboo-databas";
$msgMonthsArray=array("januari","februari","mars","april","maj","juni","juli","augusti","september","oktober","november","december");
$msgDaysArray=array("Söndag","Måndag","Tisdag","Onsdag","Torsdag","Fredag","Lördag");
$msgDaysShortArray=array("S","M","T","O","T","F","L");
$msgToday="Idag";
$msgOther="Annan";
$msgUnknown="Okänd";
$msgServerInfo="php-serverinformation";
$msgStatBy="Statistik över";
$msgVersion="Webstats 1.30";
$msgCreateCook="<b>Administratör:</b> En cookie har skapats på din dator,<BR>
    Du kommer inte att dyka upp i loggarna längre.<br><br><br><br>";
$msgCreateCookError = "<b>Administratör:</b> cookie kunde inte sparas på din dator.<br>
    Kontrollera dina webbläsarinställningar och fräscha upp sidan.<br><br><br><br>";
$msgInstalComments = "<p>Den automatiska installationsproceduren kommer att försöka att:/p>
       <ul>
         <li>skapa en tabell med namnet <b>liste_domaines</b> i din SQL-databas<br>
           </b>Denna tabell kommer automatiskt att fyllas med landsnamn med InterNIC
           koder</li>
         <li>skapa en tabell med namnet <b>logezboo</b><br>
           Denna tabell kommer att spara dina loggar</li>
       </ul>
       <font color=\"#FF3333\">Du måste ha modifierat filen:<ul><li><b>config_sql.php3</b> manuellt med ditt <b>login</b>, <b>lösenord</b> och <b>databasnamne</b> för SQL-serveranslutning.</li><br><li>Filen <b>config.inc.php3</b> måste modifieras för att kunna välja rätt språk.</font></li></ul><br>För att göra det kan du välja valfri textredigerare (t.ex. Notepad).";
$msgInstallAbort = "Installation avbruten";
$msgInstall1 = "Om det inte finns något felmeddelande ovan, så var installationen lyckad.";
$msgInstall2 = "2 tabeller har skapats i din databas";
$msgInstall3 = "Du kan nu öppna huvudgränssnittet";
$msgInstall4 = "För att kunna fylla dina tabeller när sidor öppnas, så måste du lägga till en tagg i 'monitored pages'.";

$msgUpgradeComments ="Denna nya version av ezBOO WebStats använder samma tabell <b>logezboo</b> som tidigare versioner.<br>
  						Om länder inte skrivs på engelska måste du radera tabellen <b>liste_domaine</b> 
  						och starta installationen.<br>
  						Detta kommer inte att påverka tabellen <b>logezboo</b> .<br>
  						Error meddelandet är normalt. :-)";


?>
