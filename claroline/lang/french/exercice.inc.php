<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$     |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |



      |          Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language

*****************************************************************/

// general

$langExercice="Exercice";
$langExercices="Exercices";
$langQuestion="Question";
$langQuestions="Questions";
$langAnswer="Réponse";
$langAnswers="Réponses";
$langActivate="Activer";
$langDeactivate="Désactiver";
$langComment="Commentaire";


// exercice.php

$langNoEx="Il n'y a aucun exercice actuellement";
$langNoResult="Il n'y a pas encore de résultats";
$langNewEx="Nouvel exercice";
$langUsedInSeveralPath = "Cet exercice est utilisé dans un ou plusieurs parcours pédagogiques.  Si vous le supprimez il ne sera plus disponible au sein de ce ou ces parcours.";
$langConfirmDeleteExercise = "Etes-vous sûr de vouloir supprimer cet exercice ?"; // JCC 

// exercise_admin.inc.php

$langExerciseType="Type d'exercice";
$langExerciseName="Intitulé de l'exercice";
$langExerciseDescription="Description de l'exercice";
$langSimpleExercise="Questions sur une seule page";
$langSequentialExercise="Une question par page (séquentiel)";
$langRandomQuestions="Questions aléatoires";
$langGiveExerciseName="Veuillez introduire l'intitulé de l'exercice";


// question_admin.inc.php

$langNoAnswer="Il n'y a aucune réponse actuellement";
$langGoBackToQuestionPool="Retour à la banque de questions";
$langGoBackToQuestionList="Retour à la liste des questions";
$langQuestionAnswers="Réponses à la question";
$langUsedInSeveralExercises="Attention ! Cette question et ses réponses sont utilisées dans plusieurs exercices. Souhaitez-vous les modifier";
$langModifyInAllExercises="pour l'ensemble des exercices";
$langModifyInThisExercise="uniquement pour l'exercice courant";


// statement_admin.inc.php

$langAnswerType="Type de réponse";
$langUniqueSelect="Choix multiple (Réponse unique)";
$langMultipleSelect="Choix multiple (Réponses multiples)";
$langFillBlanks="Remplissage de blancs";
$langMatching="Correspondance";
$langAddPicture="Ajouter une image";
$langReplacePicture="Remplacer l'image";
$langDeletePicture="Supprimer l'image";
$langQuestionDescription="Commentaire facultatif";
$langGiveQuestion="Veuillez introduire la question";


// answer_admin.inc.php

$langWeightingForEachBlank="Veuillez donner une pondération à chacun des blancs";
$langUseTagForBlank="utilisez des crochets [...] pour créer un ou des blancs";
$langQuestionWeighting="Pondération";
$langTrue="Vrai";
$langMoreAnswers="+rép";
$langLessAnswers="-rép";
$langMoreElements="+élem";
$langLessElements="-élem";
$langTypeTextBelow="Veuillez introduire votre texte ci-dessous";
$langDefaultTextInBlanks="Les [anglais] vivent en [Angleterre].";
$langDefaultMatchingOptA="Royaume Uni";
$langDefaultMatchingOptB="Japon";
$langDefaultMakeCorrespond1="Les anglais vivent au";
$langDefaultMakeCorrespond2="Les japonais vivent au";
$langDefineOptions="Définissez la liste des options";
$langMakeCorrespond="Faites correspondre";
$langFillLists="Veuillez remplir les deux listes ci-dessous";
$langGiveText="Veuillez introduire le texte";
$langDefineBlanks="Veuillez définir au moins un blanc en utilisant les crochets [...]";
$langGiveAnswers="Veuillez fournir les réponses de cette question";
$langChooseGoodAnswer="Veuillez choisir une bonne réponse";
$langChooseGoodAnswers="Veuillez choisir une ou plusieurs bonnes réponses";


// question_list_admin.inc.php

$langNewQu="Nouvelle question";
$langQuestionList="Liste des questions de l'exercice";
$langMoveUp="Déplacer vers le haut";
$langMoveDown="Déplacer vers le bas";
$langGetExistingQuestion="Récupérer une question d'un autre exercice";


// question_pool.php

$langQuestionPool="Banque de questions";
$langOrphanQuestions="Questions orphelines";
$langNoQuestion="Il n'y a aucune question actuellement";
$langAllExercises="Tous les exercices";
$langFilter="Filtre";
$langGoBackToEx="Retour à l'exercice";
$langReuse="Récupérer";
$langConfirmDeleteQuestion = "Etes-vous sûr de vouloir totalement supprimer cette question ?";  // JCC 


// admin.php

$langExerciseManagement="Administration d'un exercice";
$langQuestionManagement="Administration des questions / réponses";
$langQuestionNotFound="Question introuvable";


// exercice_submit.php

$langExerciseNotFound="Exercice introuvable";
$langAlreadyAnswered="Vous avez déjà répondu à la question";


// exercise_result.php

$langElementList="Liste des éléments";
$langResult="Résultat";
$langScore="Points";
$langCorrespondsTo="Correspond à";
$langExpectedChoice="Choix attendu";
$langYourTotalScore="Vous avez obtenu un total de";

$langTracking = "Statistiques";
?>