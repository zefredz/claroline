<?php // $Id$
/***************************************************************************
 *                           lang_english.php  -  description
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *	    copyright            : (C) 2001 The phpBB Group
 *  	 email                : support@phpbb.com
 *
 *     $Id$
 *
 *  ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
/*
 +----------------------------------------------------------------------+
 | Copyright (c) 2002, High Sierra Networks, Inc.                       |
 | This module was modifyed 2002-02-21 by                               |
 |          Mayra Angeles     <mayra.angeles@eduservers.com>            |
 |          Jorge Gonzalez    <jgonzalez@eduservers.com>                |
 | Description:                                                         |
 | Translation to Spanish.                                              |
 +----------------------------------------------------------------------+
 | adaptation spanisch (Spain) Xavier Casassas Canals <xcc@ics.co.at>
 +----------------------------------------------------------------------+
 | Translation to Spanish v.1.4                                         |
 | e-learning dept CESGA <teleensino@cesga.es >                         |
 +----------------------------------------------------------------------|
 | Translation to Spanish v.1.5.1                                       |
 | Rodrigo Alejandro Parra Soto , Ing. (e) En Computación eInformatica  |
 | Concepción, Chile  <raparra@gmail.com>                               |
 +----------------------------------------------------------------------|
*/


// GENERIC

$langModify="modificar";
$langDelete="borrar";
$langTitle="T&iacute;tulo";
$langHelp="ayuda";
$langOk="aceptar";
$langBackList="Regresar a la lista";


// page_header.php

$langNewTopic     = "Comenzar un tema nuevo";
$langAnswer       = "Responder";
$langHelp         = "ayuda";
$langAdm          = "administrar";
$langQuote        = "citar";
$langEditDel      = "editar/borrar";
$langSeen         = "Visto";
$langLastMsg      = "&Uacute;ltimo mensaje";

$langGroupSpaceLink="&Aacute;rea del grupo";
$langGroupForumLink="Foro del grupo";
$langGroupDocumentsLink="Documentos del grupo";
$langMyGroup="mi grupo";
$langOneMyGroups="mi supervisi&oacute;n";

$langLoginBeforePost1 = "Poner mensajes en el foro, ";
$langLoginBeforePost2 = "antes, debes ";
$langLoginBeforePost3 = "entrar en el Curso";


$l_forum 	= "Foro";
$l_forums	= "Foros";
$l_topic	= "T&oacute;pico";
$l_topics 	= "T&oacute;picos";
$l_replies	= "Respuestas";
$l_poster	= "Iniciador";
$l_author	= "Autor";
$l_views	= "Vistas";
$l_post 	= "Enviar mensaje";
$l_posts 	= "Enviar mensajes";
$l_message	= "Mensaje";
$l_messages	= "Mensajes";
$l_subject	= "Tema";

$l_body		= "$l_message"; //no cambiar al español, es una variable
$l_from		= "De";   // Mensaje de
$l_moderator 	= "Moderador";
$l_username 	= "Nombre de Usuario";
$l_password 	= "Clave de Acceso";
$l_email 	= "Correo electr&oacute;nico";
$l_emailaddress	= "Direcci&oacute;n de correo electr&oacute;nico";
$l_preferences	= "Preferencias";

$l_anonymous	= "An&oacute;nimo";  // Post
$l_guest	= "Invitado"; // Whosonline
$l_noposts	= "No hay $l_posts";
$l_joined	= "Inscrito";
$l_gotopage	= "Ir a la p&aacute;gina";
$l_nextpage 	= "P&aacute;gina Siguiente";
$l_prevpage     = "P&aacute;gina Anterior";
$l_go		= "Ir a";
$l_selectforum	= "Seleccione un $l_forum";

$l_date		= "Fecha";
$l_number	= "N&uacute;mero";
$l_name		= "Nombre";
$l_options 	= "Opciones";
$l_submit	= "Enviar";
$l_confirm 	= "Confirmar";
$l_enter 	= "Entrar";
$l_by		= "por"; // Enviado por
$l_ondate	= "el"; // Este mensaje ha sido editado por: $username el $date
$l_new          = "Nuevo";

$l_html		= "HTML";
$l_bbcode	= "BBcode";
$l_smilies	= "Smilies";
$l_on		= "Encendido";
$l_off		= "Apagado";
$l_yes		= "Si";
$l_no		= "No";

$l_click 	= "Hacer click";
$l_here 	= "aqu&iacute;";
$l_toreturn	= "para regresar";
$l_returnindex	= "$l_toreturn al &iacute;ndice del foro";
$l_returntopic	= "$l_toreturn a la lista de temas del foro.";

$l_error	= "Error";
$l_tryagain	= "Por favor regrese y vuelva a intentarlo.";
$l_mismatch 	= "Las claves de acceso no corresponden.";
$l_userremoved 	= "El usuario con esa clave ha sido dado de baja de la base de datos.";
$l_wrongpass	= "Clave de acceso equivocada.";
$l_userpass	= "Por favor escriba su nombre de usuario y su clave de acceso.";
$l_banned 	= "Ha sido dado de baja de este foro. Si tiene preguntas, por favor contacte con el administrador del sistema.";
$l_enterpassword= "Debe escribir su clave de acceso.";

$l_nopost	= "Usted no puede enviar mensajes a este foro.";
$l_noread	= "Usted no puede leer este foro.";

#--- $l_lastpost 	= "&Uacute;ltimo $l_post";
$l_lastpost 	= "&Uacute;ltimo mensaje";


$l_sincelast	= "desde su &uacute;ltima visita";
$l_newposts 	= "Nuevos $l_posts $l_sincelast";
$l_nonewposts 	= "No hay nuevos $l_posts $l_sincelast";

// Index page
$l_indextitle	= "&Iacute;ndice del foro";

// Members and profile
$l_profile	= "Perfil";
$l_register	= "Registrarse";
$l_onlyreq 	= "Requerido solamente si cambi&oacute;";
$l_location 	= "De";
$l_viewpostuser	= "Ver los mensajes enviados por este usuario";
$l_perday       = "$l_messages por d&iacute;a";
$l_oftotal      = "del total";
$l_url 		= "URL";
$l_icq 		= "ICQ";
$l_icqnumber	= "N&uacute;mero ICQ";
$l_icqadd	= "A&ntilde;adir";
$l_icqpager	= "Pager";
$l_aim 		= "AIM";
$l_yim 		= "YIM";
$l_yahoo 	= "Yahoo Messenger";
$l_msn 		= "MSN";
$l_messenger 	= "MSN Messenger";
$l_website 	= "Direcci&oacute;n del sitio Web";
$l_occupation 	= "Ocupaci&oacute;n";
$l_interests 	= "Intereses";
$l_signature 	= "Firma";
$l_sigexplain 	= "Texto que puede ser a&ntilde;adido a los mensajes que env&iacute;a al foro.<BR>255 caracteres como m&aacute;ximo!";
$l_usertaken	= "El $l_username que ha elegido, ya existe.";
$l_userdisallowed = "El $l_username elegido no puede ser autorizado por el administrador. $l_tryagain";
$l_infoupdated	= "Su informaci&oacute;n ha sido actualizada";
$l_publicmail	= "Permitir a otros usuarios ver mi $l_emailaddress";
$l_itemsreq	= "Los campos marcados con * son obligatorios";

// Viewforum
$l_viewforum	= "Ver Foro";
$l_notopics	= "No hay temas para este foro. Usted puede proponer uno.";
$l_hotthres	= "M&aacute;s de $hot_threshold $l_posts";
$l_islocked	= "$l_topic esta Cerrado (No pueden enviarse nuevos $l_posts )";
$l_moderatedby	= "Moderado por";

// Private forums
$l_privateforum	= "Es un <b>Foro Privado</b>.";
$l_private 	= "$l_privateforum<br>Nota: debe autorizar los 'cookies' para utilizar los foros privados.";
$l_noprivatepost = "$l_privateforum No puede enviar mensajes a este foro.";

// Viewtopic
$l_topictitle	= "Ver $l_topic";
$l_unregistered	= "Usuario No-registrado";
$l_posted	= "Enviado el";
$l_profileof	= "Ver el perfil de";
$l_viewsite	= "Ver el sitio web de";
$l_icqstatus	= "Estado $l_icq";  // Estado ICQ
$l_editdelete	= "Editar/Borrar este $l_post";
$l_replyquote	= "Responder citando";   // ****************
$l_viewip	= "Ver los IP (Moderadores/Administradores Solamente)";
$l_locktopic	= "Cerrar este $l_topic";
$l_unlocktopic	= "Abrir este $l_topic";
$l_movetopic	= "Mover este $l_topic";
$l_deletetopic	= "Borrar este $l_topic";

// Functions
$l_loggedinas	= "Conectado como";  //****************
$l_notloggedin	= "No conectado";
$l_logout	= "Desconexi&oacute;n";
$l_login	= "Conexi&oacute;n";

// Page_header
$l_separator	= "» »";  // Included here because some languages have
		          // problems with high ASCII (Big-5 and the like).
$l_editprofile	= "Editar Perfil";
$l_editprefs	= "Editar $l_preferences";
$l_search	= "Buscar";
$l_memberslist	= "Lista de usuarios";
$l_faq		= "FAQ";
$l_privmsgs	= "$l_messages Privados";
$l_sendpmsg	= "Enviar un mensaje privado";
$l_statsblock   = '$statsblock = "Nuestros usuarios han enviado un total de -$total_posts- $l_messages.<br>
Hay -$total_users- Usuarios Registrados.<br>
El usuario registrado m&aacute;s recientemente es -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"usuario es":"usuarios son") ." <a href=\"$online_url\">actualmente</a> en los foros.<br>";';
$l_privnotify   = '$privnotify = "<br>Tiene $new_message <a href=\"$privmsg_url\"> ".($new_message>1?"nuevos mensajes privados":"nuevo mensaje privado")."</a>.";';

// Page_tail
$l_adminpanel	= "Panel de administraci&oacute;n";
$l_poweredby	= "Basado en";
$l_version	= "Versi&oacute;n";

// Auth

// Register
$l_notfilledin	= "Error - no ha llenado todos los campos requeridos.";
$l_invalidname	= "El nombre de usuario elegido \"$username\" ya existe.";
$l_disallowname	= "El nombre de usuario elegido \"$username\" no puede ser autorizado por el administrador.";

$l_welcomesubj	= "Bienvenido a los Foro de $sitename";
$l_welcomemail	=
"
$l_welcomesubj,

Por favor conserve este email.


La informaci&oacute;n de su cuenta es la siguiente:

----------------------------
Nombre de Usuario: $username
Clave de acceso: $password
----------------------------

Por favor no olvide su clave de acceso, &eacute;sta ha sido encriptada en nuestra base de datos y no ser&aacute; posible d&aacute;rsela posteriormente.
En caso necesario, si la olvida, podemos proporcionarle un peque&ntilde;o script f&aacute;cil de usar, que le permitir&aacute; generar y enviar una nueva clave de acceso.

Gracias por registrarse.

$email_sig
";
$l_beenadded	= "Usted ha sido a&ntilde;adido a la base de datos.";
$l_thankregister= "Gracias por registrarse!";
$l_useruniq	= "Debe ser &uacute;nica. Dos usuarios no pueden tener la misma clave.";
$l_storecookie	= "Guarde mi nombre de usuario en un 'cookie' durante un a&ntilde;o.";

// Prefs
$l_prefupdated	= "$l_preferences actualizadas. $l_click <a href=\"index.$phpEx\">$l_here</a> $l_returnindex";
$l_editprefs	= "Edite sus $l_preferences";
$l_themecookie	= "NOTA: Para utilizar los temas DEBE tener activados los 'cookies'.";
$l_alwayssig	= "Siempre a&ntilde;adir mi firma";
$l_alwaysdisable= "Siempre desactivar"; // Utilisé pour les 3 phrases suivantes
$l_alwayssmile	= "$l_alwaysdisable $l_smilies";
$l_alwayshtml	= "$l_alwaysdisable $l_html";
$l_alwaysbbcode	= "$l_alwaysdisable $l_bbcode";
$l_boardtheme	= "Tema";
$l_boardlang    = "Idioma";
$l_nothemes	= "No hay temas en la base de datos";
$l_saveprefs	= "Guardar mis $l_preferences";

// Search
$l_searchterms	= "Palabras claves";
$l_searchany	= "Buscar CADA una de las palabras (Por omisi&oacute;n)";
$l_searchall	= "Buscar TODAS las palabras";
$l_searchallfrm	= "Buscar en todos los foros";
$l_sortby	= "Ordenar por";
$l_searchin	= "Buscar en";
$l_titletext	= "T&iacute;tulo & Texto";
$l_search	= "Buscar";
$l_nomatches	= "No hay registros que correspondan a su solicitud. Por favor redefina su b&uacute;squeda.";

// Whosonline
$l_whosonline	= "&iquest;Qui&eacute;n est&aacute; en l&iacute;nea?";
$l_nousers	= "Por el momento no hay usuarios en los foros";


// Editpost
$l_notedit	= "No es posible editar un mensaje que no es suyo.";
$l_permdeny	= "No ha proporcionado el $l_password correcto o no tiene autorizaci&oacute;n de editar el mensaje. $l_tryagain";
$l_editedby	= "Este $l_message a sido editado por:";
$l_stored	= "Su $l_message ha sido archivado.";
$l_viewmsg	= "para ver su $l_message.";
$l_deleted	= "Su $l_post ha sido borrado.";
$l_nouser	= "El $l_username no existe.";
$l_passwdlost	= "He olvidado mi clave de acceso!";
$l_delete	= "Borrar el mensaje";

$l_disable	= "Desactivar";
$l_onthispost	= "en este mensaje";

$l_htmlis	= "$l_html est";
$l_bbcodeis	= "$l_bbcode est";

$l_notify	= "Avisar por correo electr&oacute;nico cuando hayan enviado mensajes de respuesta";

// Newtopic
$l_emptymsg	= "Usted debe escribir un $l_message a publicar. No puede enviar un $l_message en blanco.";
$l_aboutpost	= "Acerca de Publicar mensajes";
$l_regusers	= "Todos los usuarios <b>Registrados</b>";
$l_anonusers	= "Usuarios <b>An&oacute;nimos</b>";
$l_modusers	= "<B>Moderadores y Administradores </b> solamente";
$l_anonhint	= "<br>(Para enviar un mensaje an&oacute;nimo, no debe dar ni nombre de usuario ni clave de acceso)";
$l_inthisforum	= "puede enviar nuevos temas y respuestas en este foro";
$l_attachsig	= "Mostrar la firma <font size=-2>(&eacute;sta puede ser modificada o a&ntilde;adida a su perfil)</font>";
$l_cancelpost	= "Anular el mensaje";

// Reply
$l_nopostlock	= "No puede responder a este tema, este tema est&aacute; cerrado.";
$l_topicreview  = "Revisi&oacute;n de t&oacute;pico";
$l_notifysubj	= "Se ha enviado un mensaje de respuesta sobre su tema.";
$l_notifybody	= 'Estimado $m[username]\r\nUsted recibe este correo electr&oacute;nico como respuesta a un mensaje 
que ha enviado al foro $sitename, ya que usted ha elegido ser informado.

Usted puede ver el tema en:

$url_phpbb/viewtopic.$phpEx?topic=$topic&forum=$forum

O ver el &iacute;ndice del foro en $sitename en

$url_phpbb

Gracias por utilizar los foros de $sitename.

Que tenga un buen d&iacute;a.

$email_sig';


$l_quotemsg	= '[quote]\nEl $m[post_time], $m[username] ha escrito:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient	= "Debe escribir el nombre de usuario a quien desea enviar el $l_message.";
$l_sendothermsg	= "Enviar otro Mensaje Privado";
$l_cansend	= "puede enviar los $l_privmsgs";  // Tous les utilisateurs enregistrés peuvent envoyer des MPs
$l_yourname	= "Su $l_username";
$l_recptname	= "$l_username del Destinatario";

// Replypmsg
$l_pmposted	= "Respuesta enviada, puede hacer click <a href=\"viewpmsg.$phpEx\">aqu&iacute;</a> para ver sus $l_privmsgs";

// Viewpmsg
$l_nopmsgs	= "No tiene $l_privmsgs.";
$l_reply	= "Responder";

// Delpmsg
$l_deletesucces	= "Ha sido borrado.";

// Smilies
$l_smilesym	= "Que escribir";
$l_smileemotion	= "Emoci&oacute;n";
$l_smilepict	= "Imagen";

// Sendpasswd
$l_wrongactiv	= "La clave de activaci&oacute;n es incorrecta. Verifique el $l_message correo electr&oacute;nico que recibi&oacute; y aseg&uacute;rese de haber copiado la clave de activaci&oacute;n correctamente.";
$l_passchange	= "Su clave de acceso ha sido cambiada. En cualquier momento puede ir a su <a href=\"bb_profile.$phpEx?modo=editar\">perfil</a> y cambiar su clave de acceso.";
$l_wrongmail	= "La direcci&oacute;n de correo electr&oacute;nico no corresponde con la registrada en nuestra base de datos.";

$l_passsubj	= "Foros $sitename - Cambio de clave de acceso";

$l_pwdmessage	= 'Estimado $checkinfo[username],
Usted recibe este correo electr&oacute;nico porque usted (o alguien que pretende ser usted) ha solicitado
cambio de clave de acceso para los foros de $sitename. Si cree que ha recibido este
mensaje por error, ign&oacute;relo y su clave de acceso permanecer&aacute; igual.

Su nueva clave de acceso generada es: $newpw

Para que el cambio se lleva a cabo debe visitar la p&aacute;gina:

   http://$SERVER_NAME$PHP_SELF?actkey=$key

Una vez que visite esta p&aacute;gina, su clave de acceso ser&aacute; cambiada en nuestra base de datos,
y podr&aacute; modificarla en su perfil cuando lo desee.

Gracias por utilizar los foros de $sitename

$email_sig';

$l_passsent	= "Su clave de acceso ha sido cambiada por una nueva, generada al azar. Verifique su correo electr&oacute;nico para saber c&oacute;mo terminar el proceso de cambio de clave de acceso.";
$l_emailpass	= "Clave de acceso perdida";
$l_passexplain	= "Por favor llene este formulario, le enviaremos una nueva clave de acceso a su correo electr&oacute;nico";
$l_sendpass	= "Enviar clave de acceso";

$langHelp	="Ayuda";


////////////////////////////////////////////////////////////////////////
//agregado por Rodrigo Parra Soto
$langNoForumToShow = "No hay foros que mostrar";
$langNoPost = "No hay mensajes";
$langBackTo="Regresar a  :";

////////////////////////////////////////////////////////////////////////
?>