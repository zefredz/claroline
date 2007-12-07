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
      +----------------------------------------------------------------------|
      | Translation to European Portuguese (pt_PT):                          |
      | Dionisio Martínez Soler  <dmsoler@edu.xunta.es >                     |
      | 	(Escola Oficial de Idiomas de Vigo, Spain)                   |
      +----------------------------------------------------------------------|
*/


// GENERIC

$langModify   = "alterar";
$langDelete   = "apagar";
$langTitle    = "T&iacute;tulo";
$langHelp     = "ajuda";
$langOk       = "validar";
$langBackList = "Voltar &agrave; lista";


// page_header.php

$langNewTopic     = "Come&ccedil;ar um tema novo";
$langAnswer       = "Responder";
$langHelp         = "ajuda";
$langAdm          = "administrar";
$langQuote        = "citar";
$langEditDel      = "editar/apagar";
$langSeen         = "Visto";
$langLastMsg      = "&Uacute;ltima mensagem";

$langGroupSpaceLink="&Aacute;rea do grupo";
$langGroupForumLink="Foro do grupo";
$langGroupDocumentsLink="Documentos do grupo";
$langMyGroup="o meu grupo";
$langOneMyGroups="sob a minha supervis&atilde;o";

$langLoginBeforePost1 = "Enviar mensagens para o foro, ";
$langLoginBeforePost2 = "antes, deves ";
$langLoginBeforePost3 = "entrar no Curso";

$l_forum 	= "Foro";
$l_forums	= "Foros";
$l_topic	= "T&oacute;pico";
$l_topics 	= "T&oacute;picos";
$l_replies	= "Respostas";
$l_poster	= "Iniciador";
$l_author	= "Autor";
$l_views	= "Vistas";
$l_post 	= "Enviar mensagem";
$l_posts 	= "Mensagens enviadas";
$l_message	= "Mensagem";
$l_messages	= "Mensagens";
$l_subject	= "Tema";

$l_body		= "$l_message"; //no cambiar al español, es una variable
$l_from		= "De";   // Mensaje de
$l_moderator 	= "Moderador";
$l_username 	= "Nome de Utilizador";
$l_password 	= "Senha";
$l_email 	= "Correio electr&oacute;nico";
$l_emailaddress	= "Morada de correio electr&oacute;nico";
$l_preferences	= "Prefer&ecirc;ncias";

$l_anonymous	= "An&oacute;nimo";  // Post
$l_guest	= "Convidado"; // Whosonline
$l_noposts	= "N&atilde; h&aacute; $l_posts";
$l_joined	= "Inscrito";
$l_gotopage	= "Ir &agrave; p&aacute;gina";
$l_nextpage 	= "P&aacute;gina Seguinte";
$l_prevpage     = "P&aacute;gina Anterior";
$l_go		= "Ir a";
$l_selectforum	= "Escolha um $l_forum";

$l_date		= "Data";
$l_number	= "N&uacute;mero";
$l_name		= "Nome";
$l_options 	= "Op&ccedil;&otilde;es";
$l_submit	= "Enviar";
$l_confirm 	= "Confirmar";
$l_enter 	= "Entrar";
$l_by		= "por"; // Enviado por
$l_ondate	= "em"; // Este mensaje ha sido editado por: $username el $date
$l_new          = "Novo";

$l_html		= "HTML";
$l_bbcode	= "BBcode";
$l_smilies	= "Smilies";
$l_on		= "Ligado";
$l_off		= "Desligado";
$l_yes		= "Sim";
$l_no		= "N&atilde;o";

$l_click 	= "Carregar";
$l_here 	= "aqui";
$l_toreturn	= "para voltar";
$l_returnindex	= "$l_toreturn ao &iacute;ndice do foro";
$l_returntopic	= "$l_toreturn &agrave; lista de temas do foro.";

$l_error	= "Erro";
$l_tryagain	= "Por favor, volte atr&aacute; e tente de novo.";
$l_mismatch 	= "As senhas n&atilde;o se correspondem.";
$l_userremoved 	= "O utilizador com esta senha foi eliminado da base de dados.";
$l_wrongpass	= "Senha errada.";
$l_userpass	= "Por favor, escreva o seu nome de utilizador e a sua senha.";
$l_banned 	= "Foi proibido de aceder a este foro. Se tem d&uacute;vidas, por favor, contacte o administrador do sistema.";
$l_enterpassword= "Deve escrever a sua senha.";

$l_nopost	= "N&atilde;o est&aacute; autorizado a enviar mensagens para este foro.";
$l_noread	= "N&atilde;o est&aacute; autorizado a ler as mensagens deste foro.";

#--- $l_lastpost 	= "&Uacute;ltima $l_post";
$l_lastpost 	= "&Uacute;ltima mensagem";


$l_sincelast	= "desde a sua &uacute;ltima visita";
$l_newposts 	= "Novas $l_posts $l_sincelast";
$l_nonewposts 	= "N&atilde;o h&aacute; novas $l_posts $l_sincelast";

// Index page
$l_indextitle	= "&Iacute;ndice do foro";

// Members and profile
$l_profile	= "Perfil";
$l_register	= "Inscrever-se";
$l_onlyreq 	= "Exigido apenas se houve alguma mudança";
$l_location 	= "De";
$l_viewpostuser	= "Ver as mensagens enviadas por este utilizador";
$l_perday       = "$l_messages por dia";
$l_oftotal      = "do total";
$l_url 		= "URL";
$l_icq 		= "ICQ";
$l_icqnumber	= "N&uacute;mero ICQ";
$l_icqadd	= "Acrescentar";
$l_icqpager	= "Pager";
$l_aim 		= "AIM";
$l_yim 		= "YIM";
$l_yahoo 	= "Yahoo Messenger";
$l_msn 		= "MSN";
$l_messenger 	= "MSN Messenger";
$l_website 	= "Morada da p&aacute;gina Web";
$l_occupation 	= "Profiss&atilde;o";
$l_interests 	= "Interesses";
$l_signature 	= "Assinatura";
$l_sigexplain 	= "Texto que pode ser acrescentado &agrave;s mensagens que enviar para o foro.<BR>255 caracteres no m&aacute;ximo!";
$l_usertaken	= "O $l_username que escolheu j&aacute; est&aacute; a ser usado por outro utilizador.";
$l_userdisallowed = "O $l_username que escolheu n&atilde;o pode ser autorizado pelo administrador. $l_tryagain";
$l_infoupdated	= "A sua informa&ccedil;&atilde;o foi actualizada";
$l_publicmail	= "Permitir que os outros utilizadores vejam a minha $l_emailaddress";
$l_itemsreq	= "Os campos marcados com * s&atilde;o obrigat&oacute;rios";

// Viewforum
$l_viewforum	= "Ver Foro";
$l_notopics	= "N&atilde;o h&aacute; t&oacute;picos para este foro. Pode propor um.";
$l_hotthres	= "Mais de $hot_threshold $l_posts";
$l_islocked	= "O $l_topic foi fechado (N&atilde;o podem ser enviadas novas  $l_posts )";
$l_moderatedby	= "Moderado por";

// Private forums
$l_privateforum	= "&Eacute; um <b>Foro Privado</b>.";
$l_private 	= "$l_privateforum<br>Nota: deve autorizar as 'cookies' para utilizar os foros privados.";
$l_noprivatepost = "$l_privateforum N&atilde;o pode enviar mensagens para este foro.";

// Viewtopic
$l_topictitle	= "Ver $l_topic";
$l_unregistered	= "Utilizador n&atilde;o registado";
$l_posted	= "Enviado em";
$l_profileof	= "Ver o perfil de";
$l_viewsite	= "Ver a p&aacute;gina web de";
$l_icqstatus	= "Estado $l_icq";  // Estado ICQ
$l_editdelete	= "Editar/Apagar esta $l_post";
$l_replyquote	= "Responder citando";   // ****************
$l_viewip	= "Ver os IP (s&oacute; Moderadores/Administradores)";
$l_locktopic	= "Fechar este $l_topic";
$l_unlocktopic	= "Abrir este $l_topic";
$l_movetopic	= "Mover este $l_topic";
$l_deletetopic	= "Apagar este $l_topic";

// Functions
$l_loggedinas	= "Ligado como";  //****************
$l_notloggedin	= "Desligado";
$l_logout	= "Desligar";
$l_login	= "Aceder com um nome de utilizador";

// Page_header
$l_separator	= "» »";  // Included here because some languages have
		          // problems with high ASCII (Big-5 and the like).
$l_editprofile	= "Editar Perfil";
$l_editprefs	= "Editar $l_preferences";
$l_search	= "Procurar";
$l_memberslist	= "Lista de utilizadores";
$l_faq		= "Perguntas frequentes";
$l_privmsgs	= "$l_messages Privadas";
$l_sendpmsg	= "Enviar uma mensagem privada";
$l_statsblock   = '$statsblock = "Os nossos utilizadores enviaram um total de -$total_posts- $l_messages.<br>
H&aacute; -$total_users- Utilizadores Registados.<br>
O utilizador registado mais recente &eacute; -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"utilizador &eacute;":"utilizadores s&aacute;o") ." <a href=\"$online_url\">actualmente</a> nos foros.<br>";';
$l_privnotify   = '$privnotify = "<br>Tem $new_message <a href=\"$privmsg_url\"> ".($new_message>1?"novas mensagens privadas":"nova mensagem privada")."</a>.";';

// Page_tail
$l_adminpanel	= "Painel de administra&ccedil;&atilde;o";
$l_poweredby	= "Baseado em";
$l_version	= "Vers&atilde;o";

// Auth

// Register
$l_notfilledin	= "Erro - n&atilde;o preencheu todos os campos obrigat&oacute;rios.";
$l_invalidname	= "O nome de utilizador escolhido \"$username\" j&aacute; est&aacute; a ser usado por outro utilizador.";
$l_disallowname	= "O nome de utilizador escolhido \"$username\" n&atilde;o pode ser autorizado pelo administrador.";

$l_welcomesubj	= "Bem-vindo aos Foros de $sitename";
$l_welcomemail	=
"
$l_welcomesubj,

Por favor, guarde cuidadosamente esta mensagem.

A informa&ccedil;&atilde;o da sua conta &eacute; a seguinte:

----------------------------
Nome de Utilizador: $username
Senha: $password
----------------------------

Por favor, n&atilde;o esque&ccedil;a a sua senha, pois foi guardada encriptada na nossa base de dados e n&atilde;o &eacute; poss&iacute;vel recuper&aacute;-la.
Em caso de necessidade, se esquecer a senha, podemos fornecer-lhe um pequeno script f&aacute;cil de usar, que permite gerar e enviar uma nova senha.

Obrigado por se registar.

$email_sig
";
$l_beenadded	= "Foi acrescentado &agrave; base de dados.";
$l_thankregister= "Obrigado por se registar!";
$l_useruniq	= "Deve ser &uacute;nico. Dois utilizadores n&atilde;o podem ter o mesmo nome de utilizador.";
$l_storecookie	= "Guarde o meu nome de utilizador numa 'cookie' durante um ano.";

// Prefs
$l_prefupdated	= "$l_preferences actualizadas. $l_click <a href=\"index.$phpEx\">$l_here</a> $l_returnindex";
$l_editprefs	= "Edite as suas $l_preferences";
$l_themecookie	= "NOTA: Para utilizar os temas DEVE ter activadas as 'cookies'.";
$l_alwayssig	= "Acrescentar sempre a minha assinatura";
$l_alwaysdisable= "Desactivar sempre"; // Utilisé pour les 3 phrases suivantes
$l_alwayssmile	= "$l_alwaysdisable $l_smilies";
$l_alwayshtml	= "$l_alwaysdisable $l_html";
$l_alwaysbbcode	= "$l_alwaysdisable $l_bbcode";
$l_boardtheme	= "Tema";
$l_boardlang    = "L&iacute;ngua";
$l_nothemes	= "N&atilde;o h&aacute; temas na base de dados";
$l_saveprefs	= "Guardar as minhas $l_preferences";

// Search
$l_searchterms	= "Palavras chave";
$l_searchany	= "Procurar CADA palavra (Por omiss&atilde;o)";
$l_searchall	= "Procurar TODAS as palavras";
$l_searchallfrm	= "Procurar em todos os foros";
$l_sortby	= "Ordenar por";
$l_searchin	= "Procurar em";
$l_titletext	= "T&iacute;tulo &amp; Texto";
$l_search	= "Procurar";
$l_nomatches	= "N&atilde;o h&aacute; registos que preencham as condi&ccedil;&otilde;es que definiu. Por favor, volte a definir a sua pesquisa.";

// Whosonline
$l_whosonline	= "Quem est&aacute; ligado?";
$l_nousers	= "Por enquanto, n&atilde;o h&aacute; utilizadores nos foros";


// Editpost
$l_notedit	= "N&atilde;o pode editar uma mensagem que n&atilde;o lhe pertence.";
$l_permdeny	= "N&atilde;o forneceu a $l_password correcta ou n&atilde;o tem autoriza&ccedil;&atilde;o para editar a mensagem. $l_tryagain";
$l_editedby	= "Esta $l_message foi editada por:";
$l_stored	= "A sua $l_message foi arquivada.";
$l_viewmsg	= "para ver a sua $l_message.";
$l_deleted	= "A sua $l_post foi apagada.";
$l_nouser	= "O $l_username n&atilde;o existe.";
$l_passwdlost	= "Esqueci a minha senha!";
$l_delete	= "Apagar a mensagem";

$l_disable	= "Desactivar";
$l_onthispost	= "nesta mensagem";

$l_htmlis	= "$l_html est";
$l_bbcodeis	= "$l_bbcode est";

$l_notify	= "Avisar por correio electr&oacute;nico quando forem recebidas mensagens de resposta";

// Newtopic
$l_emptymsg	= "Deve escrever uma $l_message para a sua publica&ccedil;&atilde;o no foro. N&atilde;o pode enviar uma $l_message vazia.";
$l_aboutpost	= "Sobre o envio de mensagens";
$l_regusers	= "Todos os utilizadores <b>Registados</b>";
$l_anonusers	= "Utilizadores <b>An&oacute;nimos</b>";
$l_modusers	= "S&oacute; <B>Moderadores e Administradores</b>";
$l_anonhint	= "<br>(Para enviar uma mensagem an&oacute;nima, n&atilde;o deve introduzir nem o nome de utilizador nem a senha)";
$l_inthisforum	= "pode enviar novos temas e respostas para este foro";
$l_attachsig	= "Mostrar a assinatura <font size=-2>(a assinatura pode ser modificada ou acrescentada ao seu perfil)</font>";
$l_cancelpost	= "Anular a mensagem";

// Reply
$l_nopostlock	= "N&atilde;o pode responder a este tema, este tema est&aacute; fechado.";
$l_topicreview  = "Revis&atilde;o do t&oacute;pico";
$l_notifysubj	= "Foi enviada uma mensagem de resposta sobre o seu tema.";
$l_notifybody	= 'Caro $m[username]\r\n Esta mensagem de correio electr&oacute;nico informa-o do envio de uma resposta &agrave; mensagem que enviou para o foro $sitename, pois escolheu ser informado das respostas recebidas.

Pode ver o tema em:

http://$SERVER_NAME$url_phpbb/viewtopic.$phpEx?topic=$topic&forum=$forum

Ou ver o &iacute;ndice do foro de $sitename em

http://$SERVER_NAME$url_phpbb

Obrigado por utilizar os foros de $sitename.

Com os melhores cumprimentos,

$email_sig';


$l_quotemsg	= '[quote]\nEm $m[post_time], $m[username] escreveu:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient	= "Deve escrever o nome do utilizador a quem deseja enviar a $l_message.";
$l_sendothermsg	= "Enviar outro Mensagem Privada";
$l_cansend	= "pode enviar $l_privmsgs";  // Tous les utilisateurs enregistrés peuvent envoyer des MPs
$l_yourname	= "O seu $l_username";
$l_recptname	= "$l_username do Destinat&aacute;rio";

// Replypmsg
$l_pmposted	= "Resposta enviada, pode carregar <a href=\"viewpmsg.$phpEx\">aqui</a> para ver as suas $l_privmsgs";

// Viewpmsg
$l_nopmsgs	= "N&atilde;o tem $l_privmsgs.";
$l_reply	= "Responder";

// Delpmsg
$l_deletesucces	= "Foi apagada com sucesso.";

// Smilies
$l_smilesym	= "Que escrever";
$l_smileemotion	= "Emo&ccedil;&atilde;o";
$l_smilepict	= "Imagem";

// Sendpasswd
$l_wrongactiv	= "A senha de activa&ccedil;&atilde;o &eacute; incorrecta. Verifique a $l_message de correio electr&oacute;nico que recebeu e certifique-se de ter copiado a senha correctamente.";
$l_passchange	= "A sua senha foi alterada com sucesso. A qualquer momento pode ir ao seu <a href=\"bb_profile.$phpEx?modo=editar\">perfil</a> e alterar de novo a sua senha.";
$l_wrongmail	= "A morada de correio electr&oacute;nico n&atilde;o &eacute; a que foi registada na nossa base de dados.";

$l_passsubj	= "Foros $sitename - Altera&ccedil;&atilde;o da senha";

$l_pwdmessage	= 'Caro $checkinfo[username],
Vimos por este meio inform&aacute;-lo do pedido de altera&ccedil;&atilde;o da senha dos foros de $sitename realizado por si (ou por algu&eacute;m que est&aacute; a usar o seu nome de utilizador). Se pensa que n&atilde;o devia ter recebido esta mensagem, ignore-a e a sua senha permanecer&aacute; inalterada.

A nova senha que foi gerada para si &eacute;: $newpw

Para a mudan&ccedil;a ter efeito deve visitar a p&aacute;gina:

   http://$SERVER_NAME$PHP_SELF?actkey=$key

Depois de visitar esta p&aacute;gina, a sua senha ser&aacute; alterada na nossa base de dados e poder&aacute; alter&aacute;-la de novo no seu perfil a qualquer momento.

Obrigado por utilizar os foros de $sitename

$email_sig';

$l_passsent	= "A sua senha foi mudada por uma nova, gerada aleatoriamente. Verifique o seu correio electr&oacute;nico para saber como finalizar o processo de mudan&ccedil;a da senha.";
$l_emailpass	= "Senha esquecida";
$l_passexplain	= "Por favor, preencha este formul&aacute;rio: enviar-lhe-emos uma nova senha atrav&eacute;s do seu correio electr&oacute;nico";
$l_sendpass	= "Enviar senha";

$langHelp	="Ajuda";

?>
