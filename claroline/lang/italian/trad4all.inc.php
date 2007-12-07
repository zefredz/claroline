<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                              |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$        |
      |   Italian translation                                                |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator:                                                          |
      +----------------------------------------------------------------------+
 */

/* Original was : Pietro Danesi <danone@aruba.it>  07.09.2001 init version  in PHPMyAdmin */

$englishLangName = "italian";
$localLangName = "italiano";

$iso639_2_code = "it";
$iso639_1_code = "ita";

$langNameOfLang['arabic']="arabo";
$langNameOfLang['brazilian']="brasiliano";
$langNameOfLang['english']="inglese";
$langNameOfLang['finnish']="finlandese";
$langNameOfLang['french']="francese";
$langNameOfLang['german']="tedesco";
$langNameOfLang['italian']="italiano";
$langNameOfLang['japanese']="giapponese";
$langNameOfLang['polish']="polacco";
$langNameOfLang['greek']="greco";
$langNameOfLang['simpl_chinese']="cinese semplificato";
$langNameOfLang['spanish']="spagnolo";
$langNameOfLang['swedish']="svedese";
$langNameOfLang['thai']="tailandese";
$langNameOfLang['turkish']="turco";

$charset = 'iso-8859-1';
$text_dir = 'ltr';
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$number_thousands_separator = '.';
$number_decimal_separator = ',';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('D', 'L', 'M', 'M', 'G', 'V', 'S'); //italian days
$langDay_of_weekNames['short'] = array('Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'); //italian days
$langDay_of_weekNames['long'] = array('Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'); //italian days
$day_of_weekNames = $langDay_of_weekNames;

$langMonthNames['init'] = array('G', 'F', 'M', 'A', 'M', 'G', 'L', 'A', 'S', 'O', 'N', 'D'); //italian months
$langMonthNames['short'] = array('Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'); //italian months
$langMonthNames['long'] = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'); //italian months
$monthNames = $langMonthNames;
// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous


$dateFormatShort =  "%a %d %b %y";
$dateFormatLong  = '%A %d %B %Y';
$dateTimeFormatLong  = '%A %d %B %Y ore %H:%M';
$timeNoSecFormat = '%H:%M';


// GENERIC

$langYes="Si";
$langNo="No";
$langBack="Indietro";
$langNext="Avanti";
$langAllowed="Permesso";
$langDenied="Negato";
$langBackHome="Ritorna all'inizio";
$langPropositions="Proponi un miglioramento";
$langMaj="Aggiorna";
$langModify="Modifica";
$langDelete="Elimina";
$langMove="Sposta";
$langTitle="Titolo";
$langHelp="Aiuto";
$langOk="Conferma";
$langAdd="Aggiungi";
$langAddIntro="Aggiungi un testo di introduzione";
$langBackList="Ritorna all'elenco";
$langText="Testo";
$langEmpty="Vuoto";
$langConfirmYourChoice="Conferma la tua scelta";
$langAnd="e";
$langChoice="La tua scelta";
$langFinish="Fine";
$langCancel="Cancella";
$langNotAllowed="Non sei autorizzato qui";
$langManager="Manager";
$lang_footer_CourseManager = "Manager(s) del Corso";
$langPlatform="Supportato da";
$langOptional="Optional";
$langNextPage="Pag. Avanti";
$langPreviousPage="Pag. Indietro";
$langUse="Usato";
$langTotal="Totale";
$langTake="prendi";
$langOne="Uno";
$langSeveral="Qualche";
$langNotice="Notazione";
$langDate="Data";
$langAmong="tra";

// banner

$langMyCourses="I miei corsi";
$langModifyProfile="Modifica il mio profilo";
$langMyAgenda = "La mia agenda";
$langPlatformAdministration="Amministra Claroline";
$langLogout="Uscire";

//needed for student view
$langCourseManagerview = "Vista Manager";
$langStudentView = "Vista Studente";
$lang_this_course_is_protected = 'Questo corso è protetto';
$lang_enter_your_user_name_and_password = 'Inserisci Nome e Password';
$lang_if_you_dont_have_a_user_account_profile_on = 'If you don\'t have a user account on';
$lang_click_here = 'Premi Qui';
$lang_your_user_profile_doesnt_seem_to_be_enrolled_to_this_course = "You're user profile doesn't seem to be enrolled to this course";
$lang_if_you_wish_to_enroll_to_this_course = "Se vuoi iscriverti a questo corso";
$lang_username = "User Name";
$lang_password = "Password";

// TOOLNAMES
$langCourseHome = "Home del Corso";
$langAgenda = "Agenda";
$langLink="Links";
$langDocument="Documenti e Links";
$langWork="Assegnazioni";
$langAnnouncement="Annunci";
$langUser="Utenti";
$langForum="Forum";
$langExercise="Esercizi";
$langGroups ="Gruppi";
$langChat ="Chat";
$langLearnPath="Learning Path";
$langDescriptionCours  = "Descrizione del Corso";
$langPlatformAdministration = "Amministrazione Claroline";

?>