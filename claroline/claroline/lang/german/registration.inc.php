<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.0 $Revision$                             |
      +----------------------------------------------------------------------+
      |   German translation                                                 |



      +----------------------------------------------------------------------+
      | Translator:                                                          |
      +----------------------------------------------------------------------+
 */

// GENERIC

$langModify="ändern";
$langDelete="löschen";
$langTitle="Titel";
$langHelp="Hilfe";
$langOk="Ok";
$langAddIntro="Einführungstext hinzufügen";
$langBackList="Zurück zur Liste";

// userManagement
$langAdminOfCourse  = "Administrator";  //
$langSimpleUserOfCourse = "normal"; // strings for synopsis
$langIsTutor  = "Trainer/in"; //

$langCourseCode = "Seminar";        // strings for list Mode
$langParamInTheCourse = "Status"; //

$langAddNewUser = "Einen neuen Teilnehmer einfügen";
$langMember ="registriert";

$langDelete        ="gelöscht";
$langLock        = "geschlossen";
$langUnlock        = "offen";
// $langOk

$langHaveNoCourse = "kein Seminar";


$langFirstname = "Nachname"; // by moosh
$langLastname = "Vorname"; // by moosh
$langEmail = "Email";// by moosh
$langRetrieve ="Erinnerungsinformation";// by moosh
$langMailSentToAdmin = "Eine E-Mail wurde an den Adminstrator geschickt.";// by moosh
$langAccountNotExist = "Anmeldedaten wurden nicht gefunden.found.<BR>".$langMailSentToAdmin." Er/Sie wird die Daten ermitteln.<BR>";
$langAccountExist = "Anmeldedaten liegen vor.<BR>".$langMailSentToAdmin."<BR>";// by moosh
$langWaitAMailOn = "Eine E-Mail kann gesendet werden an ";// by moosh
$langCaseSensitiveCaution = "Beachten Sie Groß- und Kleinschreibung";// by moosh
$langDataFromUser = "Informationen an Benutzer geschickt";// by moosh
$langDataFromDb = "Daten in der Datenbank";// by moosh
$langLoginRequest = "Login Anfrage";// by moosh
$langExplainFormLostPass = "Geben Sie ein, was Sie beim Anmeldevorgang eingegeben haben.";// by moosh
$langTotalEntryFound = "Eintrag gefunden";// by moosh
$langEmailNotSent = "Etwas hat nicht funktioniert. Senden Sie eine E-Mail an ";// by moosh
$langYourAccountParam = "Dies ist Ihr Anmelde Passewort";// by moosh
$langTryWith ="Versuchen Sie es mit";// by moosh
$langInPlaceOf ="und  nicht mit ";// by moosh
$langParamSentTo = "Identifikationsdaten gesendet an ";// by moosh

// REGISTRATION - AUTH - inscription.php
$langRegistration="Registrierung";
$langName="Nachname";
$langSurname="Vorname";
$langUsername="Benutzername";
$langPass="Passwort";
$langConfirmation="Bestätigung";
$langEmail="Email";
$langStatus="Status";
$langRegStudent="Zu den Seminaren(Teilnehmer/innen)";
$langRegAdmin="Seminarwebsite erstellen (Trainer/innen)";

// inscription_second.php

$langPassTwice="Sie haben zwei verschiedene Passwörter eingetragen. Gehen Sie zurück und versuchen Sie es noch einmal.";

$langEmptyFields="Sie haben einige Felder leer gelassen. Gehen Sie zurück und versuchen Sie es noch einmal.";

$langUserFree="Dieser Benutzername ist bereits vergeben. Gehen Sie zurück und wählen einen anderen.";

$langYourReg="Ihre Registrierung am";
$langDear="Guten Tag";
$langYouAreReg="Sie wurden registriert am";
$langSettings="mit den folgenden Einstellungen:\nBenutzername:";
$langAddress="Die Adresse von ";
$langIs="ist";
$langProblem="Falls Probleme auftreten sollten, treten Sie bitte mit uns in Kontakt.";
$langFormula="Mit freundlichen Grüßen";
$langManager="Verantwortlicher";
$langPersonalSettings="Ihre persönlichen Einstellungen wurden gespeichert und eine E-Mail mit Benutzernamen und Passwort wurde zur Erinnerung an Sie gesendet.</p>Wählen Sie nun in der Liste der Kurse die Kurse aus, die Sie belegen möchten.";
$langYourRegTo="Ihre Anmeldung zu den Seminaren";
$langIsReg="wurde gespeichert";
$langCanEnter="Sie können das Seminar jetzt <a href=../../index.php>besuchen</a>";

// profile.php

$langModifProfile="Mein Profil verändern";
$langPassTwo="Sie haben zwei unterschiedliche Passworte eingetragen";
$langAgain="Versuchen Sie es noch einmal!";
$langFields="Sie haben einige Felder leer gelassen";
$langUserTaken="Dieser Benutzername ist bereits belegt";
$langEmailWrong="Die E-Mail Adresse ist nicht komplett oder enthält ungültige Buchstaben";
$langProfileReg="Ihr neues Profil wurde gespeichert";
$langHome="Zurück zur Homepage";
$langMyStats = "Meine Statistik anzeigen";

// user.php

$langUsers="Benutzer";
$langModRight="Ändern der Administrationsrechte von";
$langNone="keine";
$langAll="alle";
$langNoAdmin="hat jetzt <b>keine Administrationsrechte auf dieser Seite</b>";
$langAllAdmin="hat jetzt <b>alle Administrationsrechte auf dieser Seite</b>";
$langModRole="Ändern der Rolle von";
$langRole="Rolle";
$langIsNow="ist jetzt";
$langInC="in diesem Kurs";
$langFilled="Sie haben einige Felder leer gelassen.";
$langUserNo="Der Benutzername  ";
$langTaken="ist bereits belegt. Bitte wählen Sie einen anderen.";
$langOneResp="Eine/r der Seminarleiter/innen";
$langRegYou="hat Sie in diesen Kurs eingetragen";
$langTheU="Der Benutzer";
$langAddedU="wurde hinzugefügt. Es wurde eine E-Mail versandt, um den Usernamen mitzuteilen";
$langAndP="und das Passwort";
$langDereg="wurde aus dem Kurs ausgetragen";
$langAddAU="Benutzer hinzufügen";
$langStudent="Teilnehmer/in";
$langBegin="Anfang.";
$langPreced50="50 vorherige.";
$langFollow50="50 weitere";
$langEnd="Ende";
$langAdmR="Admin. Rechte";
$langUnreg="Austragen";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Seminarliste bearbeiten</big><br><br>
Prüfen Sie die Seminare, die Sie besuchen wollen.<br>
Prüfen Sie die Seminare, die Sie nicht mehr besuchen wollen.<br> Klicken Sie dann auf den OK Button auf der Liste.";
$langTitular = "Autor/in";
$langCanNotUnsubscribeYourSelf = "Das Austragen aus der Teilnahmeliste können lediglich die Seminarleitungen und die Administratoren.";

$langGroup="Gruppe";
$langUserNoneMasc="-";
$langTutor="Trainer/in";
$langTutorDefinition="Trainer/in (Rechte, um Gruppen einzurichten)";
$langAdminDefinition="Admin (Recht, um den Inhalt der Seminarseiten zu verändern)";
$langDeleteUserDefinition="Austragen (Von der Teilnehmerliste löschen)";
$langNoTutor = "ist kein/e Trainer/in für dieses Seminar";
$langYesTutor = "ist Trainer/in für dieses Seminar";
$langUserRights="Teilnehmer Rechte";
$langNow="jetzt";
$langOneByOne="Teilnehmer manuell eintragen";
$langUserMany="Teilnehmerliste durch Textdatei einfügen";
$langNo="nein";
$langYes="ja";
$langUserAddExplanation="Jede Zeile der Datei darf nur genau 5 Felder enthalten: <b>Nachname&nbsp;&nbsp;&nbsp;Vorname&nbsp;&nbsp;&nbsp;
                Login&nbsp;&nbsp;&nbsp;Passwort&nbsp;
                &nbsp;&nbsp;Email</b> getrennt durch Tabulatoren und in dieser Reihenfolge.
                Die Teilnehmer/innen erhalten eine Bestätigung durch E-Mail mit Login-Daten und Passwort.";
$langSend="Senden";
$langDownloadUserList="Upload der Liste";
$langUserNumber="Nummer";
$langGiveAdmin="Erstelle admin";
$langRemoveRight="Entferne diese Berechtigung";
$langGiveTutor="Erstelle Trainer/in";
$langUserOneByOneExplanation="Er/Sie erhält eine Bestätigung durch E-Mail mit Login-Daten und Passwort.";
$langBackUser="Zurück zur Teilnehmerliste";
$langUserAlreadyRegistered="Ein Teilnemer mit dem gleiche Vor- und Nachnamen ist für dieses Semianr bereits angemeldet.";

$langAddedToCourse="wurde für Ihr Seminar registriert";
$langGroupUserManagement="Gruppenverwaltung";
$langIsReg="Ihre Änderungen wurden registriert";
$langPassTooEasy ="dieses Passwort ist zu einfach. Benutzen Sie ein Passwort wie dieses ";

$langIfYouWantToAddManyUsers="Wenn Sie eine Liste von Teilnehmer/innen hinzufügen wollen,
                        nehmen Sie bitte mit dem Administrator Kontakt auf.";

$langCourses="Seminare.";

$langLastVisits="Meine letzten Besuche";
$langSee                = "Gehe&nbsp;zu";
$langSubscribe        = "Eintragen";
$langCourseName        = "Name&nbsp;des&nbsp;Seminars";
$langLanguage        = "Sprache";

$langConfirmUnsubscribe = "Bestätigung der Abmeldung";
$langAdded = "hinzugefügt";
$langDeleted = "gelöscht";
$langPreserved = "vorgemerkt";



$langDate = "Datum";
$langAction = "Aktion";
$langLogin = "Anmeldung";
$langModify = "Veränderung";

$langUserName = "Benutzername";

$langEdit = "Bearbeiten";
$langCourseManager = "Trainer/in";
$langManage                     = "Verwaltung der Lernplattform";
$langAdministrationTools = "Verwaltungs-Tools";
$langModifProfile        = "Profil bearbeiten";
$langUserProfileReg        = "aktualisiert";

$lang_lost_password = "Passwort vergessen";
$lang_enter_email_and_well_send_you_password ="Geben Sie Ihre E-Mail Adresse ein mit der Sie sich angemeldet haben und wir senden Ihnen Ihr Passwort zu.";
$lang_your_password_has_been_emailed_to_you = "Ihr Passwort wurde Ihnen per E-mail zugesandt.";
$lang_no_user_account_with_this_email_address = "Kein Benutzer wurde mit dieser E-Mail Adresse angemeldet.";
$langCourses4User = "Seminare dieses Benutzers";
$langCoursesByUser = "Seminare nach Benutzern";

?>