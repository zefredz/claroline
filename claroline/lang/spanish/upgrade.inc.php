<?php


/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.1                                              |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
     
      +----------------------------------------------------------------------+
      | Translation to Spanish v.1.5.1                                       |
      | Rodrigo Alejandro Parra Soto , Ing. (e) En Computación eInformatica  |
      | Concepción, Chile  <raparra@gmail.com>                               |
      +----------------------------------------------------------------------|



*/



/* Página de Bienvenida */

$langTitleUpgrade = "<h2>Herramienta de Actualización de Claroline<br />\n
                     de 1.4.* a 1.5</h2>\n";

$langDone = "Paso realizado (echo)";
$langTodo = "Pasos por hacer (Steps todo)";
$langAchieved = "Actualizar el proceso de archivado";

/* Step 0 */

$langStep0 = "Confirmar el Respaldo";
$langMakeABackupBefore = "<p>La <em>Herramienta de Actualización de Claroline</em> respaldará los datos de la versión anterior de la instalación de Claroline
y los hará compatibles con la nueva versión de Claroline. Esta actualización se realizará en 3 pasos:</p>\n
<ol>\n
<li>Obtendrá su configuración previa de Claroline y las pondrá en los nuevos archivos de configuración</li>\n
<li>Hará que las tablas de claroline (usuario, cursos categorías, listas de cursos, ...) para hacerlos compatible con la nueva estructura de datos.</li>\n
<li>Actualizará uno por uno cada datos del curso (directorios, tablas de la base de datos, ...)</li>\n
</ol>\n
<p>Antes de proceder con la actualización:</p>\n
<table>
<tbody>
<tr valign=\"top\"><td>-</td><td>Respalde Todos sus datos de toda su plataforma (Archivos y bases de datos)</td><td>%s</td></tr>\n
</tbody>
</table>
<p>A usted no se le permitirá comenzar con el proceso de actualización mientras no haya marcado este punto como 'Echo'.</p>
";
$langConfirm = "Echo";

/* Step 1 */

$langStep1 = "Paso 1 de 3: Configuración de la plataforma principal";
$langIntroStep1 = "<p>La <em>herramienta de actualización de Claroline</em> está a punto de actualizar la configuración principal. 
                Esta configuración será guardada dentro de claroline/include/config.inc.php dentro de la versión anterior de su plataforma.</p>";
$langLaunchStep1 = "<p><button onclick=\"document.location='%s';\">Actualizar la configuración de la plataforma principal</button></p>";

/* Step 2 */

$langStep2 = "Paso 2 de 3: Actualización de las tablas de la plataforma principal.";
$langIntroStep2 = "<p>Ahora, la <em>herramienta de actualización de Claroline</em> está a punto de actualizar los datos guardados dentro de las tablas principales de Claroline  
                    (usuarios, categorías de cursos, lista de herramienta, ...) y las hará compatibles con la nueva versión de Claroline.</p>
                   <p class=\"help\">Nota: Dependiendo de la velocidad del servidor esta tarea podría tomar algún tiempo  
                   en ser ejecutada.</p>";
$langLaunchStep2 = "<p><button onclick=\"document.location='%s';\">Actualizar las tablas de la plataforma principal.</button></p>";
$langNextStep = "<p><button onclick=\"document.location='%s';\">Siguiente ></button></p>";

/* Step 3 */

$langStep3 = "Paso 3 de 3: Actualización de los cursos";
$langIntroStep3 = "<p>Ahora, la <em>herramienta de actualización de Claroline </em> está a punto de actualización los datos de los cursos(directorios y tablas de la base de dato) uno por uno.
                   <p class=\"help\">Nota: Dependiendo de la velocidad del servidor esta tarea podría tomar algún tiempo
                    en ser ejecutada.</p>";
$langLaunchStep3 = "<p><button onclick=\"document.location='%s';\">Actualizar los datos de cursos</button></p>";
$langIntroStep3Run = "<p>La <em>herramienta de actualización de Claroline </em> procederá con la actualización de los datos de los cursos</p>" ;
$langNbCoursesUpgraded = "<p style=\"text-align: center\"><strong>%s cursos de %s han sido actualizados.</strong><br /></p>";

/* stuff for all */

$langYes="si";
$langNo="no";
$langSucceed="Exito!";
$langFailed="<span style=\"color: red\">Fallo!</span>";
$langNextStep = "<p><button onclick=\"document.location='%s';\">Siguiente ></button></p>";

?>