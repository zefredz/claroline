<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$      |
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
// header
$langMyCourses="Mijn cursussen";
$langModifyProfile="Mijn profiel";
$langLogout="Logout";
$langManager="Platformbeheerder";
$langPlatform= $institution["name"] . " gebruikt het platform";
// end header


// GENERIC

$langModify="wijzigen";
$langDelete="verwijderen";
$langTitle="Titel";
$langHelp="Help";
$langOk="OK";
$langAddIntro="INLEIDENDE TEKST TOEVOEGEN";
$langBackList="Terug naar de lijst";




// REGISTRATION - AUTH - inscription.php
$langRegistration="Registratie";
$langName="Naam";
$langSurname="Voornaam";
$langUsername="Gebruikersnaam";
$langPass="Wachtwoord";
$langConfirmation="Bevestiging";
$langEmail="Email";
$langStatus="Actie";
$langRegStudent="Registreren voor cursussen";
$langRegAdmin="Cursussen aanmaken";
$langTitular = "Docenten";
// inscription_second.php


$langRegistration="Registratie";
$langPassTwice="U hebt twee verschillende wachtwoorden ingevuld. Gebruik de Back-toets van uw browser, en probeer opnieuw.";

$langEmptyFields="U hebt niet alle velden ingevuld. Gebruik de Back-toets van uw browser, en probeer opnieuw.";

$langPassTooEasy ="Dit wachtwoord is te eenvoudig. Kies een ander zoals bijvoorbeeld: ";

$langUserFree="De ingevoerde gebruikersnaam is al in gebruik. Gebruik de Back-toets van uw browser, en probeer opnieuw.";

$langYourReg="Uw registratie op";
$langDear="Beste";
$langYouAreReg="U bent geregistreerd voor";
$langSettings="met de volgende parameters:\nGebruikersnaam:";
$langAddress="Het adres van";
$langIs="is";
$langProblem="In geval van problemen kan u met ons contact opnemen.";
$langFormula="Hoogachtend";
$langManager="Platformbeheerder";

//standard:
//$langPersonalSettings="Uw persoonlijke gegevens werden opgeslagen en een e-mail werd naar U opgestuurd ter herinnering van uw gebruikersnaam en wachtwoord.</p>";
//for ldap: (no passwords emailed)
$langPersonalSettings="Uw persoonlijke gegevens werden opgeslagen en een e-mail werd naar U opgestuurd ter herinnering van uw gebruikersnaam.</p>";

$langNowGoChooseYourCourses ="U mag nu in de lijst de cursussen selecteren die U wenst te gebruiken.";
$langNowGoCreateYourCourse  ="U kunt nu verdergaan om uw cursus aan te maken.";

$langYourRegTo="Uw wijzigingen";
$langIsReg="Uw wijzigingen werden opgeslagen";
$langCanEnter="U krijgt nu toegang tot de <a href=../../index.php>cursussite</a>";

// profile.php

$langPassTwo="U hebt twee verschillende wachtwoorden ingevoerd";
$langAgain="Opnieuw!";
$langFields="U hebt niet alle velden ingevuld";
$langUserTaken="De gekozen gebruikersnaam is al in gebruik";
$langEmailWrong="Het e-mail adres is niet volledig of bevat ongeldige lettertekens";
$langProfileReg="Uw nieuw profiel werd opgeslagen";
$langHome="Terug naar startpagina";
$langDate="Datum";
$langAction="Actie";
$langLogin="Inloggen";
$langQuit="Uitloggen";
$langMyStats = "Toon mijn statistieken";

// user.php

$langUsers="Gebruikers";
$langModRight="Rechten wijzigen van : ";
$langNone="geen enkel";
$langAll="alle";
$langNoAdmin="heeft van nu af<b>geen beheerrecht op deze site</b>";
$langAllAdmin="heeft van nu af <b>alle beheerrechten op deze site</b>";
$langModRole="Rol wijzigen van";
$langRole="Rol (facultatief)";
$langIsNow="is vanaf nu ";
$langInC="in deze cursus ";
$langFilled="U heeft alle velden niet ingevuld.";
$langUserNo="De gekozen gebruikersnaam";
$langTaken="is al in gebruik. Kies een ander.";
$langOneResp="Eén van de verantwoordelijken voor de Claroline-cursus";
$langRegYou="heeft u ingeschreven op";
$langTheU="De gebruiker";
$langAddedU="werd toegevoegd. Indien u zijn adres ingetikt heeft, zal de student een bericht krijgen met zijn gebruikersnaam";
$langAndP="en zijn wachtwoord";
$langDereg="is niet meer voor deze cursus geregistreerd";
$langAddAU="Gebruikers toevoegen";
$langStudent="student";
$langBegin="begin";
$langPreced50="Vorige 50";
$langFollow50="Volgende 50";
$langEnd="einde";
$langAdmR="Beheerder";
$langUnreg="Registratie annuleren";
$langAddHereSomeCourses = "<font size=2 face='arial, helvetica'><big>mijn cursussen</big><br><br>
			Duid de cursussen aan die U wenst te volgen en degene die U niet meer wenst te volgen (niet mogelijk voor de cursussen waarvoor U verantwoordelijk bent). Klik dan op OK onder de lijst.";

$langCanNotUnsubscribeYourSelf = "Uw registratie voor een cursus waarvoor u beheerder bent kan u niet annuleren. Alleen een andere cursusbeheerder kan dit doen voor u.";

$langGroup="Groep";
$langUserNoneMasc="-";










$langTutor="Docent";
$langTutorDefinition="Docent (recht om groepen te beheren)";
$langAdminDefinition="Beheerder (recht om de inhoud van de site te wijzigen)";
$langDeleteUserDefinition="Registratie annuleren (gebruikerslijst van <b>deze</b> cursus verwijderen)";
$langNoTutor = "is geen docent van deze cursus";
$langYesTutor = "is docent van deze cursus";
$langUserRights="Rechten van de gebruikers";
$langNow="nu";
$langOneByOne="Gebruiker manueel toevoegen";
$langUserMany="Gebruikerslijst importeren via een tekstbestand";
$langNo="nee";
$langYes="ja";
$langConfirmUnsubscribe = "Bevestig annulering registratie";
$langUserAddExplanation="Elke lijn van het bestand dat opgestuurd moet worden, moet noodzakelijk en enkel de 5 velden inhouden <b>Naam&nbsp;&nbsp;&nbsp;Voornaam&nbsp;&nbsp;&nbsp;
		Gebruikersnaam&nbsp;&nbsp;&nbsp;Wachtwoord&nbsp;
		&nbsp;&nbsp;E-mail</b> door tabs gescheiden en in deze orde voorgesteld. De gebruikers zullen hun gebruikersnaam en wachtwoord per e-mail krijgen.";
$langSend="Opsturen";
$langDownloadUserList="Lijst opsturen";
$langUserNumber="aantal";
$langGiveAdmin="Tot beheerder benoemen";
$langRemoveRight="Dit recht annuleren";
$langGiveTutor="Tot docent benoemen";
$langUserOneByOneExplanation="De hier ingevulde gebruikersnaam en wachtwoord zal aan deze gebruiker per e-mail medegedeeld worden.";
$langBackUser="Terug naar gebruikerslijst";
$langUserAlreadyRegistered="Er is al een gebruiker ingeschreven voor de cursus, met dezelfde naam en voornaam. U kan deze gebruiker niet voor een tweede keer inschrijven";

$langAddedToCourse="is nu geregistreerd voor uw cursus";

$langGroupUserManagement="Groepenbeheer";

$langIfYouWantToAddManyUsers="Om een lijst van gebruikers aan Uw cursus toe te voegen moet u met een beheerder contact opnemen.";

$langCourses="cursussen.";
$langLastVisits="Mijn laatste bezoeken";


$langSee		= "Ga&nbsp;naar";
$langSubscribe	= "Registreer";
$langCourseName	= "Naam&nbsp;van&nbsp;de&nbsp;cursus";
$langLanguage	= "Taal";


$langAdded = "Toegevoegd";
$langDeleted = "Verwijderd";
$langPreserved = "Bewaard";

$lang_lost_password = "Wachtwoord vergeten";
$lang_enter_email_and_well_send_you_password ="Geef uw e-mail adres waarmee u registreerde en wij sturen u een nieuw wachtwoord.";
$lang_your_password_has_been_emailed_to_you = "Uw wachtwoord werd u doorgestuurd per e-mail.";
$lang_no_user_account_with_this_email_address = "Geen gebruiker gekend met dit e-mail adres.";
$langCourses4User = "Cursussen voor deze gebruiker";
$langCoursesByUser = "Cursussen van gebruiker";

?>