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
      +----------------------------------------------------------------------+
      | Traducción :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
      | Basado en la traducción al castellano de                             |
      |          Xavier Casassas Canals <xcc@ics.co.at>                      |
      | Adaptado al español latinoamericano en Agosto-2003 por               |
      |          Carlos Brys       <brys@fce.unam.edu.ar>                    |
      +----------------------------------------------------------------------+
*/

unset($titreBloc);
unset($titreBlocNotEditable);
unset($questionPlan);
unset($info2Say);

$titreBloc[] = "Descripción";  
$titreBlocNotEditable[] = FALSE;  
$questionPlan[] = "¿ Cual es el lugar específico del curso en el programa o carrera ?  ¿ Existen cursos previos requeridos ? ¿ Cuales on los vinculos con otros cursos ?";  
$info2Say[] = "Información que permite identificar el curso (iniciales, título, cantidad de horas, ayudas ...) y docentes  (apellido, nombre, teléfono, oficina, e-mail, disponibilidad).  Presentación general del curso en el programa.";  
$titreBloc[] = "Calificación y Objetivos";  
$titreBlocNotEditable[] = TRUE;  
$info2Say[] = "Presentación de los objetivos generales y específicos del curso, de las calificaciones requeridas para alcanzar los objetivos.";  
$questionPlan[] = "Cuales son las habilidades pretendidas por la asignatura ? Al finalizar el curso, ¿ que calificaciones, capacidades y conocimientos tendrán los estudiantes ?";  
$titreBloc[] = "Contenido del Curso";  
$titreBlocNotEditable[] = TRUE;  
$questionPlan[] = "¿ Cual es la importancia de los distintos contenidos a ser tratados en el marco del curso? ¿ Cual es el nivel de dificultad de esos contenidos ?   ¿ Como está estructurada la materia ? ¿ Cual será la secuencia de los contenidos ?  ¿ Cual será la progresión de los contenidos ? ";  
$info2Say[] = "Presentación de los contenidos del curso, la estructura de los contenidos, la pregresión y el calendario";  
$titreBloc[] = "Actividades de entrenamiento";  
$titreBlocNotEditable[] = TRUE;  
$questionPlan[] = "¿ Qué métodos y qué actividades se usarán para lograr los objetivos del curso?  ¿ Cual es el calendario de esas actividades ?";  
$info2Say[] = "Presentación de las actividades con corrección  (revisiones, participación esperada de los estudiantes, trabajos prácticos, reuniones de laboratorio, visitas, recolección de información de campo...)."; 
$titreBloc[] =" Soportes ";  
$titreBlocNotEditable[] = TRUE;  
$questionPlan[] = "¿ Existe un soporte en el curso ? Que tipo se sopote se vá a privilegiar ? Abierto? Cerrado ? ";  
$info2Say[] = "Presentación del soporte del curso.  Presentación de la bibliografía, el conjunto de documentos o bibliografía complementaria.";  
$titreBloc[] = "Recursos Físicos y Humanos";  
$titreBlocNotEditable[] = TRUE;  
$questionPlan[] = "¿ Cuales son los recursos físicos y humanos disponibles ?   ¿ Cual será la naturaleza de la infraestructura ?  ¿ Que pueden esperar los estudiantes del equipo o la infraestructura del docente ? ";  
$info2Say[] = "Presentación de los otros docentes que componen el curso  (asistentes, investigadotes, tutores ...), de la disponibilidad de personal, de los recursos, aulas, equipamiento, computadoras disponibles.";  
$titreBloc[] = "Métodos de evaluación";  
$titreBlocNotEditable[] = TRUE;  
$questionPlan[] = "¿ Qué métodos de evaluación se eligieron para lograr los objetivos definidos al inicio del curso ? ¿ Cuales son las estrategias de realización de las evaluaciones a efectos que los estudiantes puedan identificar los posibles espacios de tiempo antes de los exámenes ?";  
$info2Say[] = "Detalles precisos acerca de las formas de evaluación  (exámenes escritos, orales, proyectos, entrega de trabajos ...).";  

?>
