<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
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
 */

 $msgAdminPanel = "Beheerpaneel";
 $msgStats = "Statistieken";
 $msgStatsBy = "Statistieken door";
 $msgHours = "uren";
 $msgDay = "dag";
 $msgWeek = "week";
 $msgMonth = "maand";
 $msgYear = "jaar";
 $msgFrom = "vanaf ";
 $msgTo = "tot ";
 $msgPreviousDay = "vorige dag";
 $msgNextDay = "volgende dag";
 $msgPreviousWeek = "vorige week";
 $msgNextWeek = "volgende week";
 $msgCalendar = "kalender";
 $msgShowRowLogs = "bruto logs zien";
 $msgRowLogs = "bruto logs";
 $msgRecords = "records";
 $msgDaySort = "Gesorteerd op dag";
 $msgMonthSort = "Gesorteerd op maand";
 $msgCountrySort = "Gesorteerd volgens land";
 $msgOsSort = "Gesorteerd volgens OS";
 $msgBrowserSort = "Gesorteerd volgens navigator";
 $msgProviderSort = "Gesorteerd volgens provider";
 $msgTotal = "Totaal";
 $msgBaseConnectImpossible = "Onmogelijk SQL-bank te selecteren";
 $msgSqlConnectImpossible = "Verbinding met SQL-server onmogelijk";
 $msgSqlQuerryError = "SQL-verzoek onmogelijk";
 $msgBaseCreateError = "Fout bij de aanmaak van databank";
 $msgMonthsArray = array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
 $msgDaysArray=array("Zondag","Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag","Zaterdag");
 $msgDaysShortArray=array("Zo","M","Di","W","Do","V","Za");
 $msgToday = "Vandaag";
 $msgOther = "Ander";
 $msgUnknown = "Onbekend";
 $msgServerInfo = "php Server info";
 $msgStatBy = "Statistics by";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Beheerder:</b> Een cookie werd opgeslagen in uw computer,<BR>
     U zal niet meer in de logs staan.<BR><BR><BR><BR>";
 $msgCreateCookError = "<b>Beheerder:</b> de cookie werd niet in uw computer opgeslagen.<br>
     Controleer of Uw navigator deze aanvaardt, en refresh de pagina.<br><br><br><br>";
 $msgInstalComments = "<p>De automatische installatieprocedure zal proberen:</p>
       <ul>
         <li>een tabel aan te maken met de titel <b>gebieden_lijst</b> in Uw SQL databank<br>
           </b>Deze tabel zal automatisch ingevuld worden met de namen van de landen en de InterNIC code
</li>
         <li>een tabel aan te maken met de naam <b>logezboo</b><br>
           Deze tabel zal Uw logs inhouden</li>
       </ul>
       <font color=\"#FF3333\">U moet daarvoor manueel wijzigen:
	<ul><li>het bestand <b>config_sql.php3</b> met uw <b>login</b>, <b>wachtwoord</b> en <b>databanknaam</b> voor de verbinding met de SQL server.</li><br>
	<li>Het bestand <b>config.inc.php3</b> moet gewijzigd worden om de taal te selecteren.</font></li></ul><br>Daarvoor kan u een teksteditor gebruiken zoals Notepad.";
 $msgInstallAbort = "INSTALLATIE GESTOPT";
 $msgInstall1 = "Als er geen fout hierboven verschijnt, dan is de installatie geslaagd.";
 $msgInstall2 = "2 tabellen werden aangemaakt in Uw SQL databank";
 $msgInstall3 = "Nu mag u de voornaamste interface openen";
 $msgInstall4 = "Om de tabel van logs in te vullen, moet u een tab in uw paginas zetten.";

 $msgUpgradeComments ="De nieuwe versie gebruikt dezelfde tabel <b>logezboo</b> 
					   	als de vorige versie.<br>
  						Als de landen niet in het Nederlands verschijnen, dan moet U de tabel 
  						<b>gebieden_lijst</b> uitwissen en de installatie opnieuw starten.<br>
  						Dit zal geen effect op de tabel <b>logezboo</b> hebben.<br>
  						Het bericht van fout is normaal :-)";


$langStats="Statistieken";

?>
