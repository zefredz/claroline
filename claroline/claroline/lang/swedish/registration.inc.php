<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$        |
      |   Swedish translation                                                |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator: Jan Olsson <jano@artedi.nordmaling.se>                   |
      +----------------------------------------------------------------------+
 */


// userMAnagement
$langAdminOfCourse="admin";
$langSimpleUserOfCourse="normal";
$langIsTutor="lärare";

$langCourseCode="Kurskod";
$langParamInTheCourse="Status"; 

$langAddNewUser="lägg till användare i systemet";
$langMember="registrerad";

$langDelete="radera";
$langLock="lås";
$langUnlock="lås upp";

$langHaveNoCourse="ingen kurs";


$langFirstname="Förnamn"; 
$langLastname="Efternamn";
$langEmail="Emejl";
$langRetrieve="Återfå identifikationsinformation";
$langMailSentToAdmin="Ett mejl har skickats till administratören.";
$langAccountNotExist="Kontot finns inte.<BR>".$langMailSentToAdmin." Han/hon kommer att söka vidare manuellt.<BR>";
$langAccountExist="Detta konto existerar.<BR>".$langMailSentToAdmin."<BR>";
$langWaitAMailOn="Ett brev kan sändas till ";
$langCaseSensitiveCaution="Detta system är skiftkänsligt.";
$langDataFromUser="Data sänt av användaren";
$langDataFromDb="Data i databasen";
$langLoginRequest="Inloggningsförfrågan";
$langExplainFormLostPass="Skriv in vad du tror att du skrev in under registreringsprocessen.";
$langTotalEntryFound="Anmälan funnen";
$langEmailNotSent="Något fungerar inte, mejla detta till ";
$langYourAccountParam = "Detta är inte ditt Login-Lösen";
$langTryWith ="Försök med";
$langInPlaceOf ="och inte med ";
$langParamSentTo = "Identifikationsinformation sänd till ";


// REGISTRATION - AUTH - inscription.php
$langRegistration="Registrering";
$langName="Efternamn";
$langSurname="Förnamn";
$langUsername="Användarnamn";
$langPass="Lösenord";
$langConfirmation="bekräftelse";
$langEmail="Emejl";
$langStatus="Status";
$langRegStudent="Följ kurser (elev)";
$langRegAdmin="Skapa kurswebbsajter (lärare)";


// inscription_second.php


$langPassTwice="Du skrev in två skilda lösenord. Använda webbläsaren tillbakaknapp och försök igen.";

$langEmptyFields="Du har lämnat några fält tomma. Använda webbläsaren tillbakaknapp och fyll i dem och försök igen.";

$langUserFree="Ditt användarnamn är redan upptaget. Använda webbläsaren tillbakaknapp och försök igen med ett annat.";

$langYourReg="Din registrering vid";
$langDear="Kära";
$langYouAreReg="Du registrerades vid";
$langSettings="med följande inställningar:\nAnvändarnamn:";
$langAddress="Adressen till ";
$langIs="är";
$langProblem="I händelse av problem, kontakta oss.";
$langFormula="Vänliga hälsningar";
$langManager="Ansvarig";
$langPersonalSettings="Dina personliga inställningar har registrerats och emejlats till dig för att hjälpa dig komma ihåg användarnamn och lösenord.</p>Välj i listan vilka kurser som du vill deltaga i.";
$langYourRegTo="Din registrering till";
$langIsReg="kurser är registrerade";
$langCanEnter="Du kan nu <a href=../../index.php>stiga in i studiemiljön</a>";

// profile.php

$langModifProfile="Modifiera min profil";
$langPassTwo="Du har skrivit in två olika lösenord";
$langAgain="Försök igen!";
$langFields="Du har lämnat några fält tomma";
$langUserTaken="Användarnamnet är upptaget";
$langEmailWrong="Emejl address är inte komplett eller så innehåller den otillåtna tecken";
$langProfileReg="Din nya profil har sparats";
$langHome="Tillbaka till hemsidan";


// user.php

$langUsers="Användare";
$langModRight="Modifiera administratörsrättigheterna på";
$langNone="ingen";
$langAll="alla";
$langNoAdmin="har nu <b>inga administratörsrättigheter på denna sajt";
$langAllAdmin="har nu <b>alla administratörsrättigheter på denna sajt";
$langModRole="Modifiera rollen av";
$langRole="Roll";
$langIsNow="är nu";
$langInC="i denna kurs";
$langFilled="Du har lämnat några fält tomma.";
$langUserNo="Användarnamnet som du valt ";
$langTaken="är redan upptaget. Välj ett annat.";
$langOneResp="En av kursadministratörerna";
$langRegYou="har registrerat dig på denna kurs";
$langTheU="Användaren";
$langAddedU="har lagts till. Ett emejl har skickats ut för att ge personen ett användarnamn ";
$langAndP="och ett lösenord";
$langDereg="har avregistrerats från denna kurs";
$langAddAU="Lägg till en användare";
$langStudent="elev";
$langBegin="starta.";
$langPreced50="50 föregående.";
$langFollow50="50 nästa";
$langEnd="slut";
$langAdmR="Admin. rättig.";
$langUnreg="Avregistrera";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Modifiera kurslistan</big><br><br>
Markera de kurser som du önskar följa.
Avmarkera de kurser som du inte önska följa längre. Klicka därefter på Ok längst ner i kurslistan";
$langCanNotUnsubscribeYourSelf="Du kan inte avregistrera dig från kursen som du administrerar, endast andra kursadministratörer kan göra detta. ";
$langTitular="Titel";

$langAddedToCourse="är redan registrerad till campus men inte i denna kurs. Ordnar detta nu.";
$langAdminDefinition="Admin (rätt att modifiera kurswebbsajtsinnehåll)";
$langBackUser="Tillbaka till användarlistan";
$langCourses="kurser.";
$langDeleteUserDefinition="Avregistrera (radera från användarlistan i <b>denna</b> kurs)";
$langDownloadUserList="Ladda ner användarlistan";
$langGiveAdmin="Gör till admin";
$langGiveTutor="Gör till lärare";
$langGroup="grupp";
$langGroupUserManagement="Grupphantering";
$langIfYouWantToAddManyUsers="Om du vill lägga till en lista med användare, kontakta din webbadministratör.";
$langLastVisits="Mina senaste besök";
$langNo="nej";
$langYes="ja";
$langNoTutor="är inte lärare för denna kurs";
$langNow="nu";
$langOneByOne="Lägg till användare manuellt";
$langPassTooEasy="detta lösenord är för enkelt. Använd ett som liknar detta:";
$langRemoveRight="Radera rättighet";
$langSend="Sänd";
$langTutor="Lärare";
$langTutorDefinition="rätt att hantera grupper";
$langUserAddExplanation="varje rad i filen behöver 5 fält: <b>Efternamn&nbsp;&nbsp;&nbsp;Förnamn&nbsp;&nbsp;&nbsp;Användarnamn&nbsp;&nbsp;&nbsp;Lösenord&nbsp;&nbsp;&nbsp;Emejl</b> separerade med tabbar och i ovanstående ordning. Användarna får bekräftelse via emejl med användarnamn och lösenord.";
$langUserAlreadyRegistered="En användare med samma namn/förnamn är redan registrerad i denna kurs. Du kan inte registrera honom (henne) dubbelt.";
$langUserMany="Importera användarlista via textfil";
$langUserNoneMasc="-";
$langUserNumber="nummer";
$langUserOneByOneExplanation="Han (hon) kommer att erhålla en bekräftelse med användarnamn och lösenord via emejl.";
$langUserRights="Användarrättigheter";
$langYesTutor="är lärare för denna kurs";
$langAccountExist="Detta konto existerar, ett brev skickades till administratören";
$langAccountNotExist="Kontot kan inte hittas, ett brev skickades till administratören för manuell sökning";
$langAction="Utför";
$langAdded="Lagts till";
$langInPlaceOf="och inte med";
$langLanguage="Språk";
$langLastname="Efternamn";
$langLogin="Logga in";
$langLoginRequest="Inloggningsförfrågan";
$langLogout="Logga ut";
$langCaseSensitiveCaution="Systemet är skiftkänsligt och gör då skillnad mellan stora och små bokstäver";
$langConfirmUnsubscribe="Bekräfta avregistrering";
$langCourseName="Kursnamn";
$langDataFromDb="Data i databasen";
$langDataFromUser="Data från användaren";
$langDate="Datum";
$langDeleted="Raderad";
$langEmailNotSent="Ett problem har uppstått, mejla detta till";
$langExplainFormLostPass="Skriv in det som tror du hade vid tiden för registrering";
$langFirstname="Förnamn";
$langMailSentToAdmin="Ett brev har sänts till administratören";
$langNowGoChooseYourCourses="Du kan nu välja, i listan, de kurser du vill deltaga i.";
$langNowGoCreateYourCourse="Du kan nu skapa din kurs";
$langParamSentTo="Identifikationsuppgifter har sänts till";
$langPreserved="Bevarad";
$langRetrieve="Hämta identifikationsuppgifter";
$langSee="Gå till";
$langTotalEntryFound="Posten har hittats";
$langTryWith="Försök med";
$langWaitAMailOn="Ett mejlkan skickas till";
$langYourAccountParam="Detta är lösenord och inloggningsnamn för ditt konto";
$langSubscribe="Prenumerera";
$langRetrieve="Återfå identifikationinformation";
$langEdit="Redigera";
$langModify="Modifiera";
$langMyStats="Visa min statistik";
$langUserName="Användarens namn"; 

$langUsers = "Användare";
$langCourseManager= "Kursansvarig";
$langGroup="Grupp";
$langManager="Ansvarig";
$langTutor="Lärare";
$langRole="Roll";
?>
