<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004, 2003 Universite catholique de Louvain (UCL)|
      +----------------------------------------------------------------------+
      |   Este programa es software libre; usted puede redistribuirlo y/o    | 
      |   modificarlo bajo los términos de la Licencia Pública General (GNU) | 
      |   como fué publicada por la Fundación de Sofware Libre; desde la     |
      |   versión 2 de esta Licencia o (a su opción) cualquier versión       |
      |   posterior.                                                         |
      |   Este programa es distribuído con la esperanza de que sea útil,     |
      |   pero SIN NINGUNA GARANTIA; sin ninguna garantía implícita de       |
      |   MERCATIBILILIDAD o ADECUACIÓN PARA PROPOSITOS PARTICULARES.        |
      |   Vea la Licencia Pública General GNU por más detalles.              |
      |   Usted pudo haber recibido una copia de la Licencia Pública         |
      |   General GNU junto con este programa; sino, escriba a la Fundación  |
      |   de Sofware Libre : Free Software Foundation, Inc., 59 Temple Place |
      |   - Suite 330, Boston, MA 02111-1307, USA. La licencia GNU GPL       |
      |   también está disponible a través de la world-wide-web en la        |
      |   dirección  http://www.gnu.org/copyleft/gpl.html                    |
      +----------------------------------------------------------------------+
      | Autores: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
      | Traducción :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      |          Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
      | Basado en la traducción al castellano de                             |
      |          Xavier Casassas Canals <xcc@ics.co.at>                      |
      | Adaptado al español latinoamericano en Agosto-2003 por               |
      |          Carlos Brys       <brys@fce.unam.edu.ar>                    |
      +----------------------------------------------------------------------+
 */


// general

$langExercice="Ejercicio";
$langExercices="Exercicios";
$langQuestion="Pregunta";
$langQuestions="Preguntas";
$langAnswer="Respuesta";
$langAnswers="Respuestas";
$langActivate="Activado";
$langDeactivate="Desactivado";
$langComment="Comentario";


// exercice.php

$langNoEx="Por el momento, no hay ejercicios";
$langNoResult="Todavía ho hay resultados";

// question_pool.php

$langQuestionPool="Depósito de preguntas";
$langOrphanQuestions="preguntas huéfanas";
$langNoQuestion="Por el momento, no hay preguntas";
$langAllExercises="Todos los ejercicios";
$langFilter="Filtro";
$langUnknownExercise="Ejercicio deconocido";
$langGoBackToEx="Volver al ejercicio";
$langReuse="Reusar";
$langReuseQuestion="Reusar una pregunta existente";


// [exercice/question/answer]_admin.php

$langElementList="Lista de elementos";
$langWeightingForEachBlank="Por favor, otorgue un peso a cada blanco";
$langUseTagForBlank="use corchetes  [...] para definir uno o más blancos";
$langExerciseType="Tipo de ejercicio";
$langAnswerType="Tipo de respuesta";
$langUniqueSelect="Múltiple choice (respuesta única)";
$langMultipleSelect="Múltiple choice (varias respuestas)";
$langFillBlanks="Completar en los espacios";
$langMatching="Hacer parejas";
$langAddPicture="Agregar una imagen";
$langReplacePicture="Reemplazar la imagen";
$langDeletePicture="Borrar la imagen";
$langQuestionWeighting="Peso";
$langExerciseName="Nombre del ejercicio";
$langCreateExercise="Crear un ejercicio";
$langCreateQuestion="Crear una pregunta";
$langCreateAnswers="Crear respuestas";
$langModifyExercise="Modificar un ejercicio";
$langModifyQuestion="Modificar una pregunta";
$langModifyAnswers="Modificar respuestas";
$langNewEx="Nuevo ejercicio";
$langNewQu="Nueva pregunta";
$langExerciseDescription="Descripción del ejercicio";
$langQuestionDescription="Descripción de la pregunta";
$langTrue="Verdadero";
$langMoreAnswers="+resp";
$langLessAnswers="-resp";
$langMoreElements="+elem";
$langLessElements="-elem";
$langTypeTextBelow="Por favor, escriba su texto a continuación";
$langExerciseNotFound="No se encontró el ejercicio";
$langQuestionNotFound="No se encontró la pregunta";
$langQuestionList="Lista de preguntas";
$langForExercise="para el ejercicio";
$langMoveUp="Mover hacia arriba";
$langMoveDown="Mover hacia abajo";
$langSimpleExercise="En una única página";
$langSequentialExercise="Una pregunta por página (secuencial)";
$langRandomQuestions="Preguntas al azar";
$langDefaultTextInBlanks="[Los británicos] viven en  [Inglaterra].";
$langDefaultMatchingOptA="rico";
$langDefaultMatchingOptB="buena vista";
$langDefaultMakeCorrespond1="Su padre es ";
$langDefaultMakeCorrespond2="Su madre es ";
$langUseExistantQuestion="Usar una pregunta existente";
$langUsedInSeveralExercises="AAtención ! Esta pregunta y sus respuestas se usaron en varios ejercicios. Desea modificarla";
$langModifyInAllExercises="en todos los ejercicios";
$langModifyInThisExercise="solo en el ejercicio actual";
$langDefineOptions="Por favor, defina las opciones";
$langMakeCorrespond="Hacer corresponder";
$langAmong="a traves";
$langGiveExerciseName="Por favor, dé el nombre del ejercicio";
$langFillLists="Por favor, complete las dos listas que siguen";
$langGiveText="Por favor, escriba el texto";
$langDefineBlanks="POr favor, defina al menos un blanco con corchetes [...]";
$langGiveQuestion="Por favor, dé la pregunta ";
$langGiveWeighting="Por favor, dé el peso de la pregunta";
$langGiveAnswers="Por favor, dé las respuestas de la pregunta";
$langChooseGoodAnswer="Por favor, elija una respuesta correcta";
$langChooseGoodAnswers="Por favor, dé elija un o más respuestas correctas";
$langTotalWeightingMultipleChoice="La suma de las respuestas seleccionadas debe ser igual al peso de la pregunta";
$langTotalWeightingFillInBlanks="La suma de los pesos de los blancos debe ser igual al peso de la pregunta";
$langTotalWeightingMatching="La suma los pesos de las parejas debe ser igual al peso de la pregunta";


// exercice_submit.php / exercise_result.php

$langResult="Puntaje";
$langCorrect="Correcto";
$langCorrespondsTo="Corresponde a";
$langAlreadyAnswered="Ud. ya respondió la pregunta";
$langShowQuestion="Mostrar una pregunta";
$langScore="Puntaje";
$langExpectedChoice="Elección esperada";
?>
