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
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Geschй <gesche@ipm.ucl.ac.be>                    |
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

$langExercice="Тест";
$langExercices="Тесты";
$langQuestion="Вопрос";
$langQuestions="Вопросы";
$langAnswer="Ответ";
$langAnswers="Ответы";
$langActivate="Назначить";
$langDeactivate="Сделать неактивным";
$langComment="Комментарий";


// exercice.php

$langNoEx="В данный момент тестов нет";
$langNoResult="Результатов пока нет";
$langNewEx="Новый тест";


// exercise_admin.inc.php

$langExerciseType="Тип теста";
$langExerciseName="Название теста";
$langExerciseDescription="Описание теста";
$langSimpleExercise="Вопросы на одной странице";
$langSequentialExercise="Один вопрос на страницу (разбивка)";
$langRandomQuestions="Случайные вопросы";
$langGiveExerciseName="Введите название теста";


// question_admin.inc.php

$langNoAnswer="В настоящий момент ответов нет";
$langGoBackToQuestionPool="Назад к банку вопросов";
$langGoBackToQuestionList="Назад к списку вопросов";
$langQuestionAnswers="Ответы на вопрос";
$langUsedInSeveralExercises="Внимание! Этот вопрос и ответы к нему используются в нескольких тестах. Вы хотите их изменить?";
$langModifyInAllExercises="для всех тестов";
$langModifyInThisExercise="только для текущего теста";


// statement_admin.inc.php

$langAnswerType="Тип ответа";
$langUniqueSelect="Множественный выбор (один правильный ответ)";
$langMultipleSelect="Множественный выбор (несколько правильных ответов)";
$langFillBlanks="Заполнение пропусков";
$langMatching="Соответствия";
$langAddPicture="Добавить изображение";
$langReplacePicture="Заменить изображение";
$langDeletePicture="Удалить изображение";
$langQuestionDescription="Комментарий к вопросу (по желанию)";
$langGiveQuestion="Введите вопрос";


// answer_admin.inc.php

$langWeightingForEachBlank="Дайте вес каждому вопросу";
$langUseTagForBlank="используйте квадратные скобки [...], чтобы создать пропуск(и)";
$langQuestionWeighting="Вес вопроса";
$langTrue="Верно";
$langMoreAnswers="+отв";
$langLessAnswers="-отв";
$langMoreElements="+элем";
$langLessElements="-элем";
$langTypeTextBelow="Введите ваш текст ниже";
$langDefaultTextInBlanks="[Англичане] живут в [Англии].";
$langDefaultMatchingOptA="Великобритании";
$langDefaultMatchingOptB="Японии";
$langDefaultMakeCorrespond1="Англичане живут в ";
$langDefaultMakeCorrespond2="Японцы живут в ";
$langDefineOptions="выберите вариант";
$langMakeCorrespond="найдите соответствия";
$langFillLists="Заполните два списка ниже";
$langGiveText="Введите текст";
$langDefineBlanks="Создайте минимум один пропуск, используя квадратные скобки [...]";
$langGiveAnswers="Дайте ответы на этот вопрос";
$langChooseGoodAnswer="Выберите правильный ответ";
$langChooseGoodAnswers="Выберите один или несколько правильных ответов";


// question_list_admin.inc.php

$langNewQu="Новый вопрос";
$langQuestionList="Список вопросов теста";
$langMoveUp="Переместить вверх";
$langMoveDown="Переместить вниз";
$langGetExistingQuestion="Взять вопрос из другого теста";


// question_pool.php

$langQuestionPool="Банк вопросов";
$langOrphanQuestions="Одиночные вопросы";
$langNoQuestion="Сецчас нет вопросов";
$langAllExercises="Все тесты";
$langFilter="Фильтр";
$langGoBackToEx="Назад к тесту";
$langReuse="восстановить";


// admin.php

$langExerciseManagement="администрирование теста";
$langQuestionManagement="администрирование вопросов и ответов";
$langQuestionNotFound="Вопрос не найден";


// exercice_submit.php

$langExerciseNotFound="Тест не найден";
$langAlreadyAnswered="Вы уже ответили на вопрос";


// exercise_result.php

$langElementList="Список элементов";
$langResult="Результат";
$langScore="баллы";
$langCorrespondsTo="соответствует";
$langExpectedChoice="ожидаемый выбор";
$langYourTotalScore="Общее количество набранных вами баллов";
?>
