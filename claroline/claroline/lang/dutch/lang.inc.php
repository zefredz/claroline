<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
 */

/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language

*****************************************************************/

//echo "<p>dutch/lang.inc included.</p>";

// header
$langMyCourses="Mijn cursussen";
$langModifyProfile="Mijn profiel";
$langLogout="Logout";
$langManager="Platformbeheerder";
$langPlatform= $institution["name"] . " gebruikt het platform";
// end header

// GENERIC

$langModify="Wijzigen";
$langDelete="Verwijderen";
$langTitle="Titel";
$langHelp="Help";
$langOk="OK";
$langAddIntro="INLEIDENDE TEKST TOEVOEGEN";
$langBackList="Terug naar de lijst";





// index.php CAMPUS HOME PAGE

$langInvalidId="Ongeldige log-in. Indien u nog niet ingeschreven bent, gelieve het <a href='claroline/auth/inscription.php'>registratieformulier</a> in te vullen.";
$langMyCourses="Mijn cursussen";
$langCourseCreate="Cursussite creëren";
$langModifyProfile="Mijn profiel";
$langTodo="Todo";
$langWelcome="cursussite(s) hieronder hebben vrije toegang. De andere cursussen vragen een gebruikersnaam en een wachtwoord. Die kunt u krijgen door een klik op 'Registratie'. Het is mogelijk voor de docenten en assistenten om een nieuwe cursus te creëren door een klik op 'Registratie'."; 
$langUserName="Gebruikersnaam";
$langPass="Wachtwoord";
$langEnter="Enter";
$langHelp="Help";
$langManager="Platformbeheerder";
$langPlatform= $institution["name"] . " gebruikt het platform";



// REGISTRATION - AUTH - INSCRIPTION
$langRegistration="Registratie";
$langName="Naam";
$langSurname="Voornaam";

// COURSE HOME PAGE

$langAnnouncements="Ad Valvas";
$langLinks="Links";
$langWorks="Studenten Box";
$langUsers="Gebruikers";
$langStatistics="Statistieken";
$langCourseProgram="Cursusprogramma";
$langAddPageHome="Pagina toevoegen en linken aan Homepage";
$langLinkSite="Link aan Homepage toevoegen";
$langModifyInfo="Cursusinfo wijzigen";
$langDeactivate="Desactiveren";
$langActivate="Activeren";
$langInactiveLinks="Inactieve Links";
$langAdminOnly="Voorbehouden voor Cursusbeheerders";





// AGENDA

$langAddEvent="Item toevoegen";
$langDetail="Detail";
$langHour="Uur";
$langLasting="Duur";
$month_default="Maand";
$january="Januari";
$february="Februari";
$march="Maart";
$april="April";
$may="Mei";
$june="Juni";
$july="Juli";
$august="Augustus";
$september="September";
$october="Oktober";
$november="November";
$december="December";
$year_default="Jaar";
$year1="2001";
$year2="2002";
$year3="2003";
$hour_default="Uur";
$hour1="08u30";
$hour2="09u30";
$hour3="10u45";
$hour4="11u45";
$hour5="12u30";
$hour6="12u45";
$hour7="13u00";
$hour8="14u00";
$hour9="15u00";
$hour10="16u15";
$hour11="17u15";
$hour12="18u15";
$lasting_default="duur";
$lasting1="30min";
$lasting2="45min";
$lasting3="1u";
$lasting4="1u30";
$lasting5="2u";
$lasting6="4u";





// DOCUMENT

$langDownloadFile= "Upload het bestand op de server";
$langDownload="Upload";
$langCreateDir="Nieuwe Folder";
$langName="Naam";
$langNameDir="Naam nieuwe folder";
$langSize="Grootte";
$langDate="Datum";
$langMove="Verplaatsen";
$langRename="Nieuwe naam geven";
$langComment="Commentaar";
$langVisible="Zichtbaar/onzichtbaar";
$langCopy="Copiëren";
$langTo="naar";
$langNoSpace="Upload is niet geslaagd. Er is geen plaats genoeg in uw Folder";
$langDownloadEnd="Upload is geslaagd";
$langFileExists="Onmogelijk.<br>Er bestaat al een bestand met dezelfde naam.";
$langIn="in";
$langNewDir="Naam van de nieuwe Folder";
$langImpossible="Onmogelijk";
$langAddComment="Commentaar toevoegen/wijzigen";
$langUp="Hoger";



// WORKS

$langTooBig="U hebt geen bestand gekozen om op te sturen of het bestand is te groot.";
$langListDeleted="Volledige lijst is verwijderd.";
$langDocModif="Document is gewijzigd.";
$langDocAdd="Document is toegevoegd.";
$langDocDel="Document is verwijderd.";
$langTitleWork="Titel document";
$langAuthors="Auteurs";
$langDescription="Beschrijving";
$langDelList="Volledige lijst verwijderen.";



// ANNOUCEMENTS
$langAnnEmpty="Alle mededelingen zijn verwijderd.";
$langAnnModify="Mededeling is gewijzigd";
$langAnnAdd="Mededeling is toegevoegd";
$langAnnDel="Mededeling verwijderen";
$langPubl="Gepubliceerd op";
$langAddAnn="Mededeling toevoegen";
$langContent="Inhoud";
$langEmptyAnn="Alle mededelingen verwijderen";




// OLD
$langAddPage="Pagina toevoegen";
$langPageAdded="Pagina is toegevoegd";
$langPageTitleModified="Titel pagina is gewijzigd";
$langAddPage="Pagina toevoegen";
$langSendPage="Pagina opsturen";
$langCouldNotSendPage="Dit bestand is niet in HTML formaat en kon 
bijgevolg niet opgestuurd worden. Als u documenten naar de server wilt 
sturen die niet in HTML zijn (PDF, Word, Power Point, Video, etc.) dan 
moet u <a href=../document/document.php>Documenten</a> gebruiken.";
$langAddPageToSite="Pagina aan cursussite toevoegen";
$langNotAllowed="U bent geen cursusbeheerder. U kunt deze actie niet uitvoeren.";
$langExercices="Oefeningen";

?>