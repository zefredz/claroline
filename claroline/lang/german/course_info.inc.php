<?php

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                          |
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
      +----------------------------------------------------------------------+
 */



// GENERIC

$langModify="ändern";
$langDelete="löschen";
$langTitle="Titel";
$langHelp="Hilfe";
$langOk="Ok";
$langBack="Zurück zur Kursinformation";
$langBackH="Kurs Homepage";


// infocours.php

$langModifInfo="Kursinformation ändern";
$langModifDone="Die Information wurde geändert";
$langHome="Zurück zur Homepage";
$langCode="Kurs Code";
$langDelCourse="Diesen Kurs löschen";
$langProfessor="Trainer/in";
$langProfessors="Trainer/innen";
$langTitle="Kurstitel";
$langFaculty="Bereich";
$langDescription="Beschreibung";
$langConfidentiality="Vertraulichkeit";
$langPublic="Öffentlicher Zugriff auf die Veranstaltungsseiten auch ohne Anmeldung";
$langPrivOpen="Eingeschränkter Zugang, Registrierung offen";
$langPrivate="Eingeschränkter Zugang, Registrierung geschlossen (Die Seiten sind nur für angemeldete <a href=../user/user.php>Teilnehmer/innen</a> zugänglich.)";
$langForbidden="Nicht erlaubt";
$langLanguage="Sprache";
$langConfTip="Durch Grundeinstellung ist Ihr Kurs nur für Sie erreichbar, da Sie der einzige registrierte Benutzer sind. Wenn Sie den weiteren Zugriff einschränken möchten,
öffnen Sie die Registrierung für eine Woche und bitten Sie die Teilnehmer/innen sich anzumelden. Im Anschluss sperren Sie die Anmeldung und überprüfen Sie die Teilnehmerliste auf 'Trittbrettfahrer'.";
$langTipLang="Diese Sprache wird für alle Besucher Ihrer Webseite gültig sein.";


// Change Home Page
$langAgenda="Agenda";
$langLink="Links";
$langDocument="Dokumente";
$langVid="Video";
$langWork="Unterlagen für Teilnehmer/innen";
$langProgramMenu="Programm";
$langAnnouncement="Ankündigung";
$langUser="Benutzer";
$langForum="Foren";
$langExercise="Übungen";
$langStats="Statistiken";
$langUplPage="Upload Seite und Link zur Homepage";
$langLinkSite="Ein Link zu dieser Seite auf der Homepage einfügen";
$langModifGroups="Gruppen";
$langModifInfo="Seminarinformation ändern";


// delete_course.php

$langDelCourse="Die komplette Webseite zum Seminar löschen";
$langCourse="Die Website ";
$langHasDel="wurde gelöscht";
$langBackHome="Zurück zur Homepage von ";
$langByDel="Wenn die Website des Seminars gelöscht wird, werden auch alle enthaltenen Dokumente und gemeldeten Teilnehmer/innen gelöscht. (Teilnehmer/innen werden jedoch nicht aus anderen Kursen entfernt).<p>Wollen Sie wirklich das Seminar löschen?";
$langY="JA";
$langN="NEIN";

$langDepartmentUrl = "Bereichs-URL";
$langDepartmentUrlName = "Bereich";
$langDescriptionCours  = "Seminarbeschreibung";

$langArchive="Archiv";
$langArchiveCourse="Seminar Backup";
$langRestoreCourse = "Seminar erstellen";
$langRestore="Erstellen";
$langCreatedIn = "erstellt in";
$langCreateMissingDirectories ="Fehlendes Verzeichnis erstellen";
$langCopyDirectoryCourse = "Kopie von Seminardateien";
$langDisk_free_space = "freier Speicherplatz";
$langBuildTheCompressedFile ="Backup-Datei erstellen";
$langFileCopied = "Datei kopiert";
$langArchiveLocation="Archiv Ablage";
$langSizeOf ="Größe von";
$langArchiveName ="Archivname";
$langBackupSuccesfull = "Backup erfolgreich erstellt";
$langBUCourseDataOfMainBase = "Backup der Seminardaten in Datenbank für";
$langBUUsersInMainBase = "Backup der Nutzerdaten in Datenbank für";
$langBUAnnounceInMainBase="Backup der Ankündigungen in Datenbank für";
$langBackupOfDataBase="Backup der Datenbank";
$langBackupCourse="Archivieren des Seminars";

$langCreationDate = "Erstellt";
$langExpirationDate  = "Abschluß/Ende";
$langPostPone = "verlegt";
$langLastEdit = "Letzte Bearbeitung";
$langLastVisit = "Letzter Besuch";

$langSubscription="Zustimmung";
$langCourseAccess="Seminarzugang";

$langDownload="Download";
$langConfirmBackup="Wollen Sie wirklich dieses Seminar sichern?";

$langCreateSite="Ein neues Seminar anlegen";

$langRestoreDescription="Dies ist eine Archivdatei, die Sie unten auswählen können.<br><br>
Wenn Sie auf &quot;Wiederherstellen&quot; klicken, wird das Archiv geöffnet und das Seminar wieder erstellt.";
$langRestoreNotice="Dieses Script erlaubt nicht die automatische Wiederherstellung der Nutzerdaten, aber die Daten werden gesichert in der Datei &quot;users.csv&quot; sie können manuell vom Administrator bearbeitet werden.";
$langAvailableArchives="Verfügbare Archiv Liste";
$langNoArchive="Es wurde kein Archiv ausgewählt.";
$langArchiveNotFound="Das Archiv wurde nicht gefunden";
$langArchiveUncompressed="Das Archiv wurde entkomprimiert und installiert.";
$langCsvPutIntoDocTool="Die Datei &quot;users.csv&quot; wurde in das Documents tool gelegt.";
?>