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


// HELP
// help_forums.php

$langHFor   = "Ayuda de los foros";
$langClose  = "Cerrar la ventana";



// help_forum.php

$langForContent="El foro es una herramienta de discusión escrita y asincrónica.
 A diferencia del email que permite un dialogo uno-a-uno, los foros permiten diálogos públicos o semi-públicos.</p><p>Hablando técnicamente, los estudiantes solo necesitan un prograama de navegación para usar los foros de Claroline.</P><p>Para organizar los foros, haga un clic en  'Administrar'. Las discusiones se organizan en conjuntos y subconjuntos como sigue:</p><p><b>Categoría > Foro > Tema > Respuestas</b></p>Para estructurar las discusiones de sus estudiantes, es necesario organizar categorías y los foros de antemano, dejando a ellos la creación de los temas y las respuestas. Por defecto, el foro de Claroline contiene solo la categoría  'Público', un foro y un tema de ejemploc.</p><p>La primera cosa que debe hacer es eliminar el foro de ejemplo y modificar el nombre del primero foro. Entonces, podrá crear, en la categoría  'publica', otros foros,por grupos o por temas, para colocar su requerimiento de escenario de aprendizaje.</p><p>No mezcle Categorías y foros, y no olvide que una categoría vacía (sin foros) no aparece a la vista de los estudiantes.</p><p>La descripción de un foro puede ser la lista de sus miembros, la definición de un objetivo, una tarea, un tema ...";



// help_home.php

$langHHome       = "Ayuda p&aacute;gina principal";

$langHomeContent = "La mayor&iacute;a de las rúbricas de iCampus est&aacute;n ya rellenadas con un corto texto o por un enlace dados por defecto, o por un ejemplo. A usted le corresponde el modificarlos.</p><p>As&iacute un corto texto aparece, bien a la vista, en la cabecera de vuestra web.  'Este es el texto de introducci&oacute;n de vuestra web...' Modifiquelo y aproveche para describir su curso, vuestros objectivos, vuestros dispositivos. Es importante para la correcta presentaci&oacute;n de vuestro trabajo.</p><p>En el momento de la creaci&oacute;n de vuestra web, numerosas herramientas (Agenda, documentos, ejercicios...) son activadas para usted por defecto. Es aconsejable desactivar aquellas que usted no utiliza con el fin de no hacer perder el tiempo a sus usuarios o a sus visitantes.</p><p>Usted puede tambi&eacute;n a&ntilde;adir p&aacute;ginas a la p&aacute;gina principal. Utilice la funci&oacute;n 'a&ntilde;adir p&aacute;gina' para a&ntilde;adir una p&aacute;gina y enviarla al servidor. Si por el contrario usted quiere enlarar con una p&aacute;gina o web ya existente, utilice la funci&oacute;n 'Enlace a la web'. Las p&aacute;ginas y enlaces que usted a&ntilde;ade a la p&aacute;gina principal pueden ser desactivadas y luego suprimidas, a diferencias de las herramientas existentes por defecto, las cuales pueden ser desactivadas, pero no suprimidas.</p><p>Tambi&eacute;n le corresponde a usted el decidir si su curso debe aparecer en la lista de los cursos. Es aconsejable que un curso en fase de pruebas o 'en obras' no aparezca en la lista (ver la funci&oacute;n 'Modificar informaci&oacute;n sobre el curso') y permanezca privado sin posibilidad de inscribirse en el durante el tiempo de su creaci&oacute;n.</p>";



// help_claroline.php

$langHClar        = "Ayuda: primeros pasos";

$langClarContent1 = "es el campous virtual de";

$langClarContent2 = "Aqu&iacute;, los profesores y los asitentes crean y administran las webs de los cursos, los estudiantes las consultan (documentos, agendas, informaciones diversos). Cuando proceda, los estudiantes realizan ejercicios, publican trabajos, toman parte en las discusiones via los foros...</p><b>Inscripci&oacute;n</b><p>Si usted es estudiante, s&oacute;lo necesita inscribirse eligiendo 'Incribirme a los cursos (estudiante)' y despu&eacute;s marcar los cursos que usted desea seguir.</p><p>Si usted es profesor o asistente, inscribase de la misma manera, pero escogiendo 'Crear webs de cursos (profesor)'. Usted tendr&aacute; que rellenar entonces un formulario precisando el c&oacute;digo de un primer curso y su t&iacute;tulo. Una vez qze usted habr&aacute; validado esta elecci&oacute;n, usted ser&aacute; conducido a la web que usted habr&aacute; creado y usted podr&aacute; modificar el contenido y la organisaci&oacute;n a vuestro modo.</P><p>Si este portal de creaci&oacute;n y de administraci&oacute;n de cursos no os satisface, no dude en comunicarnoslo via la r&uacute;brica 'Sugerencias' accesible desde la p&aacute;gina principal del campus (s&oacute; despu&eacute;s de haberse identificado).</p><p>El enlace a la inscripci&oacute;n s eencuentra en la p&aacute;gina principal de la web en la esquina superior derecha.</p><b>Identificaci&oacute;n personal (login)</b><p>En sus pr&oacute;ximas visitas, para acceder a sus cursos, introduzca su nombre de usuario y su password en los campos de introducci&oacute;n de datos situados en la esquina superior derecha de la pantalla. La direcci&oacute;n de la web es";

$langClarContent3 = "</p><p><b>Pedagog&iacute;a</b><p>Para los profesores, preparar un curso en internet es tambi&eacute;n un asunto  pedag&oacute;gico.";

$langClarContent4 = "est&aacute; a vuestra disposici&oacute;n para ayudaros en las diferentes fases de la creaci&oacute;n de vuestro proyecto: de la concepci&oacute;n de la herramienta en su integraci&oacute;n en un dispositivo coherente y a su evaluati&oacute;n en terminos del impacto sobre el aprendizaje.</p>";



// help_document.php

$langHDoc        = "Ayuda documentos";

$langDocContent  = "<p>El m&oacute;dulo de gesti&oacute;n de documentos funciona de manera semejante a la gesti&oacute;n de sus documentos en un ordenador. </p><p>Usted puede introducir documentos de todo tipo (HTML, Word, Powerpoint, Excel, Acrobat, Flash, Quicktime, etc.). Tenga en cuenta, sin embargo, el que los estudiantes dispongan de las herramientas apropiadas para poder consultarlos. Tenga tambi&eacute;n cuidado de no enviar 
  documentos infectados con virus. Es conveniente de comprobar primero con un programa antivirus que los documentos
no est&eacute;n infectados antes de colocarlos en iCampus.</p>
<p>Los documentos se presentan en pantalla por orden alfab&eacute;tico.<br>
  <b>Consejos:</b> si usted desea que los documentos sean ordenados de 
  manera diferente, usted puede hacerlos preceder de un n&uacute;mero, a partir de este momento se ordenar&aacute;n seg&uacute;n esta base. </p>
<p>Usted puede:</p>
<h4>Usted puede cargar a distancia un documento  en este m&oacute;dulo</h4>
<ul>
  <li>Seleccione el documento en su ordenador con la ayuda del  
	bot&oacute;n &quot;Buscar&quot; 
	<input type=submit value=Buscar name=submit2>
	a la derecha de su pantalla.</li>
  <li>Ejecute la carga a distancia con la ayuda del bot&oacute;n&quot; 
	cargar a distancia&quot; 
	<input type=submit value=cargar a distancia name=submit2>
	.</li>
</ul>
<h4>Cambiar el nombre de un documento (o de un directorio)</h4>
<ul>
  <li>Haga click en el bot&oacute;n <img src=../document/img/edit.gif width=20 height=20 align=baseline> 
	en la columna &quot;Cambiar el nombre.</li>
  <li>Introduzca el nuevo nombre en el lugar previsto para este efecto que aparece 
	arriba a la izquierda</li>
  <li>Validar haciendo click en &quot;OK&quot; 
	<input type=submit value=OK name=submit24>
	. 
</ul>
	<h4>Suprimir un documento (o un directorio)</h4>
	<ul>
	  
  <li>Haga click en el bot&oacute;n <img src=../document/img/delete.gif width=20 height=20> 
	en la columna &quot;Suprimir&quot;.</li>
	</ul>
	<h4>Hacer que un documento sea invisible para los estudiantes (o un directorio)</h4>
	<ul>
	  
  <li>Haga click en el bot&oacute;n <img src=../document/img/visible.gif width=20 height=20>
     en la columna &quot;Visible/invisible&quot;.</li>
	  <li>El documento (o el directorio) continua existiendo, pero ya no es visible para los estudiantes.</li>
	</ul>
	<ul>
	  
  <li>Si ustede desea que este elemento vuelva a ser visible, 
	haga click en el bot&oacute;n <img src=../document/../document/img/invisible.gif width=24 height=20> 
	en la columna &quot;Visible/invisible&quot;</li>
	</ul>
	<h4>A&ntilde;adir o modificar un comentario a un documento (o a un directorio)</h4>
	<ul>
	  
  <li>Haga click en el bot&oacute;n  <img src=../document/../document/img/comment.gif width=20 height=20> 
	en la columna &quot;Commentario&quot;</li>
	  <li>Introduzca el nuevo comentario en la zona prevista para tal efecto qui aparecer&aacute; arriba a la izquierda.</li>
	  <li>Valide haciendo click en &quot;OK&quot; 
		<input type=submit value=OK name=submit2>
		.</li>
	</ul>
	<p>Si ustede desea suprimir un comentario, haga click en el bot&oacute;n <img src=../document/../document/img/comment.gif width=20 height=20>, 
	  &quot;borrar el antiguo comentario de la zona&quot; y valide haciendo click en &quot;OK&quot; 
	  <input type=submit value=OK name=submit22>
	  . 
	<hr>
	<p>Usted puede tambi&eacute;n organizar el contenido del m&oacute;dulo de los documentos guardando 
	  los documentos en directorios. Para hacer esto usted debe :</p>
	<h4><b>Crear un directorio</b></h4>
	<ul>
	  <li>Hacer click en la funci&oacute;n &quot;<img src=../document/../document/img/dossier.gif width=20 height=20>crear 
		un directorio&quot; arriba a la izquierda de la pantalla</li>
	  <li>Introduzca el nombre de su nuevo repertorio en la zona prevista para tal efecto 
          arriba a la izquierda de la pantalla.</li>
	  <li>Valide haciendo click en &quot;OK&quot; 
		<input type=submit value=OK name=submit23>
		.</li>
	</ul>
	<h4>Desplazar un documento (o un directorio)</h4>
	<ul>
	  <li>Haga click sobre el bot&oacute;n <img src=../document/../document/img/deplacer.gif width=34 height=16> 
		en la columna &quot;desplazar&quot;</li>
	  <li>Escoja el repertorio al que usted quiere desplazar el documento
              o el directorio en el men&uacute; de selecci&oacute;n previsto
	     para tal efecto que aparecer&aacute; arriba a la izquierda.(nota: 
		la palabra &quot;raiz&quot; en dicho men&uacute; representa la raiz (base) de 
		su m&oacute;dulo de documentos).</li>
	  <li>Valide haciendo click sobre &quot;OK&quot; 
		<input type=submit value=OK name=submit232>
		.</li>
	</ul>
	<center>
	  <p>";



// Help_user.php

$langHUser        = "Ayuda usuarios";
$langUserContent  = "<b>Papeles (roles)</b><p>Los papeles (roles) no tienen ninguna funci&oacute;n inform&aacute;tica. No otorgan ning&uacute;n derecho sobre el sistema. Usted puede modificarlos haciendo click sobre 'modificar' debajo de  'rol/papel' y despu&eacute;s introduciendo todas las letras de la descripci&oacute;n de la funci&oacute;n conveniente: profesor, asistente, tutor, visitante, documentalista, experto, moderador... Est&oacute; servir&aacute; solamente para indicar p&uacute;blicamente que rol (papel) que desempe&ntilde;a en el curso.</P><hr>
<b>Derechos de administraci&oacute;n</b>
<p>Los derechos de administraci&oacute;n, por el contrario, se refieren al sistema inform&aacute;tico. Actualmente, se puede escoger entre <b>todos</b> (modificar todo, suprimir todo, a&ntilde;adir todo) y <b>ninguno</b> (a parte del derceho de visitar la web, de publicar mensajes en el foro y trabajos via la p&aacute;gina 'trabajos').</P>
<p>Para permitir a un co-titular, un asistente, un tutor o quien sea el co-administrar la web con usted, ustede debe inscribirlo previamente en su curso o asegurarse que ya est&aacute; inscrito y despu&eacute;s modificar los derechos que le corresponden haciendo click sobre 'modificar' debajo 'derechos de administraci&oacute;.' y luego marcando 'todos'.</P><hr>
<b>Co-titulares</b>
<p>Para hacer que figure el nombre de un co-titular en la cabecera de su curso, utilice la p&aacute;gina 'Modificar la informaci&oacute;n sobre el curso' (en las herramientas de color naranja). Esta modificaci&oacute;n de la cabercera del curso no inscribe autom&aacute;ticamente a este co-titular como usuario del curso. Se trata de dos acciones distintas.</p><hr>
<b>Ayudar a un usuario</b>
<p>Para a&ntilde;adir un usuario a su curso, comprobar primero si ya est&aacute; inscrito en iCampus utilizando el motor de b&uacute;squeda. Si ya est&aacute; inscrito, marque la casilla que aparece al lado de su nombre y valide. Si todav&iacute;a no est&aacute; inscrito a&ntilde;adale a mano. En los dos casos, la persona recibir&aacute; un email de confirmaci&oacute;n de su inscripci&oacute;n conteniendo su nombre de usuario y su password, excepto en el caso de que ustede no haya introducido su email.</p>";






?>
