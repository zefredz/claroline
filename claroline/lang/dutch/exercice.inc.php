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
// header
$langMyCourses="Mijn cursussen";
$langModifyProfile="Mijn profiel";
$langLogout="Logout";
$langManager="Platformbeheerder";
$langPlatform= $institution["name"] . " gebruikt het platform";
// end header

// exercice.php

$langExercices="Oefeningen";
$langEvalSet="Evaluatieparameters";
$langExercice="Oefening";
$langActive="actief";
$langInactive="inactief";
$langActivate="activeren";
$langDeactivate="inactiveren";
$langNoEx="Er zijn momenteel geen oefeningen";
$langNewEx="Nieuwe oefening";

// exercice_admin.php

$langQuestCreate="Aanmaak van vragen";
$langExRecord="Uw oefening werd opgeslaan";
$langBackModif="Terug naar de wijziging van vragen";
$langDoEx="Oefening maken";
$langDefScor="Evaluatieparameters omschrijven";
$langCreateModif="Aanmaak/wijzigingen van vragen";
$langSub="Ondertitel";
$langNewQu="Nieuwe vraag";
$langQuestion="Vraag";
$langQuestions="Vragen";
$langDescription="Beschrijving";
$langAnswers="Antwoorden";
$langTrue="Juist";
$langAnswer="Antwoord";
$langComment="Commentaar";
$langMorA="+antwoord";
$langLesA="-antwoord";
$langRecEx="Oefening opslaan";
$langRecQu="Vraag opslaan";
$langRecAns="Antwoorden opslaan";
$langIntroduction="Inleiding";
$langTitleAssistant="Assistent voor de aanmaak van oefeningen";
$langQuesList="Vragenlijst";
$langSaveEx="Oefening opslaan";
$langClose="Sluiten";
$langFinish="Eindigen";
$langCancel="Annuleren";
$langQImage="Beeldvraag";
$langNext="Volgende";
$langPrevious="Vorige";
$langStep1="Welkom bij de assistent voor de aanmaak van meerkeuzevragen.
            U wordt verder geleid.
						Druk op volgende om door te gaan.";
$langStep2="Titel van de oefening en beschrijving toevoegen/wijzingen.
						Druk op volgende wanneer U klaar bent.";
$langAddQ="Vraag toevoegen";

// exercice_submit.php

$langDoAnEx="Oefening maken";
$langGenerator="Oefeningenlijst";
$langResult="Resultaat";
$langChoice="Uw keuze";
$langCorrect="Juist";

// scoring.php & scoring_student.php

$langPossAnsw="Aantal juiste antwoorden voor eenzelfde vraag";
$langStudAnsw="aantal fouten door de student gemaakt";
$langDetermine="Kies zelf de waarde van de evaluatie door de tabel hieronder te wijzigen. Klik dan op \"valideren\"";
$langNonNumber="Een niet-numerieke waarde";
$langAnd="en"; 
$langReplaced="werd ingegeven. Werd vervangen door 0";
$langSuperior="Een waarde boven 20";
$langRep20="werd ingetikt, vervangen door 20";
$langDefault="Standaardwaarden *";
$langDefComment="* Door klikken op knop \"Standaardwaarden\", zullen de vorige waarden vervangen worden";
$langScoreGet="De cijfers in het zwart = aantal punten gescoord";

//Milgrom deactivate scoring
$langShowScor="Tonen evaluatie aan student : "; 

// general

$langExercice="Oefening";
$langExercices="Oefeningen";
$langQuestion="Vraag";
$langQuestions="Vragen";
$langAnswer="Antwoord";
$langAnswers="Antwoorden";
$langActivate="Activeren";
$langDeactivate="Inactiveren";
$langComment="Commentaar";

// exercice.php

$langNoEx="Er is momenteel geen oefening.";
$langNoResult="Er is nog geen resultaat";
$langNewEx="Nieuwe oefening";

// exercise_admin.inc.php

$langExerciseType="Oefening type";
$langExerciseName="Oefening naam";
$langExerciseDescription="Oefening beschrijving";
$langSimpleExercise="Op 1 pagina";
$langSequentialExercise="Eén vraag per pagina (sequentieel)";
$langRandomQuestions="Willekeurige vragen";
$langGiveExerciseName="Geef a.u.b. een naam aan de oefening.";

// question_admin.inc.php

$langNoAnswer="Er is momentel geen antwoord.";
$langGoBackToQuestionPool="Terug naar de lijst van alle vragen";
$langGoBackToQuestionList="Terug naar de vragenlijst van de oefening";
$langQuestionAnswers="Antwoorden op de vraag";
$langUsedInSeveralExercises="Waarschuwing: Deze vraag en zijn antwoorden worden in verschillende oefeningen gebruikt. Wilt u de vraag wijzigen";
$langModifyInAllExercises="in alle oefeningen";
$langModifyInThisExercise="enkel in de huidige oefening";

// statement_admin.inc.php

$langAnswerType="Antwoord type";
$langUniqueSelect="Meerkeuze (uniek antwoord)";
$langMultipleSelect="Meerkeuze (meerdere antwoorden)";
$langFillBlanks="Invullen";
$langMatching="Koppelen ('matching')";
$langAddPicture="Voeg tekening toe";
$langReplacePicture="Wijzig tekening";
$langDeletePicture="Verwijder tekening";
$langQuestionDescription="Commentaar (optioneel)";
$langGiveQuestion="Stel a.u.b. een vraag";

// answer_admin.inc.php

$langWeightingForEachBlank="Geef aub een gewicht aan elke invulruimte";
$langUseTagForBlank="gebruik vierkante haken [...] om een of meerdere invulruimtes te definiëren";
$langQuestionWeighting="Gewichten";
$langTrue="Waar";
$langMoreAnswers="+ antwoord";
$langLessAnswers="- antwoord";
$langMoreElements="+ element";
$langLessElements="- element";
$langTypeTextBelow="Tik hieronder uw tekst";
$langDefaultTextInBlanks="Van alle [Galliërs] zijn de [Belgen] het dapperst.";
$langDefaultMatchingOptA="rijk";
$langDefaultMatchingOptB="aantrekkelijk";
$langDefaultMakeCorrespond1="Uw vader is";
$langDefaultMakeCorrespond2="Uw moeder is";
$langDefineOptions="Definieer de keuzes";
$langMakeCorrespond="Laat overeenstemmen";
$langFillLists="Please fill the two lists below";
$langGiveText="Tik aub een tekst";
$langDefineBlanks="Please define at least one blank with brackets [...]";
$langGiveAnswers="Geef aub de antwoorden op de vraag";
$langChooseGoodAnswer="Kies een goed antwoord";
$langChooseGoodAnswers="Kies één of meerdere goede antwoorden.";

// question_list_admin.inc.php

$langNewQu="Nieuwe vraag";
$langQuestionList="Vragenlijst van de oefening";
$langMoveUp="Omhoog";
$langMoveDown="Omlaag";
$langGetExistingQuestion="Gebruik een vraag van een andere oefening.";

// question_pool.php

$langQuestionPool="Lijst met alle vragen";
$langOrphanQuestions="Vragen niet in een oefening ('wezen')";
$langNoQuestion="Er zijn momenteel geen vragen (in deze categorie)";
$langAllExercises="Alle vragen";
$langFilter="Filter";
$langGoBackToEx="Terug naar de oefening";
$langReuse="Hergebruik";

// admin.php

$langExerciseManagement="Oefeningenbeheer";
$langQuestionManagement="Vragen / Antwoorden beheer";
$langQuestionNotFound="Vraag niet gevonden";

// exercice_submit.php

$langExerciseNotFound="Oefening niet gevonden";
$langAlreadyAnswered="U hebt deze vraag al beantwoord.";

// exercise_result.php

$langElementList="Elementen lijst";
$langResult="Resultaat";
$langScore="Score";
$langCorrespondsTo="Corresponds to";
$langExpectedChoice="Correcte antwoord";
$langYourTotalScore="Uw totale score is";

?>