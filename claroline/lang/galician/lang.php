<?php

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                          |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Copyright (c) 2002, High Sierra Networks, Inc.                       |
      | This module was modifyed 2002-02-21 by                               |
      |          Mayra Angeles     <mayra.angeles@eduservers.com>            |
      |          Jorge Gonzalez    <jgonzalez@eduservers.com>                |
      +----------------------------------------------------------------------|
      | Translation to Galician                                              |
      | e-learning dept CESGA <teleensino@cesga.es >                         |
      |                                                                      |
      +----------------------------------------------------------------------|

 */


/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language

*****************************************************************/



// GENERIC

$langModify="modificar";
$langDelete="borrar";
$langTitle="T&iacute;tulo";
$langHelp="axuda";
$langOk="aceptar";
$langAddIntro="ENGADIR UN TEXTO DE INTRODUCCI&Oacute;N";
$langBackList="Engadir &aacute; lista";





// index.php CAMPUS HOME PAGE

$langInvalidId     = "Login inv&aacute;lido. Se non est&aacute; inscrito,
cubra o <a href='claroline/auth/inscription.php'>formulario de inscripci&oacute;n</a></font color>";
$langMyCourses     = "Os meus cursos";
$langCourseCreate  = "Crear o sitio dun curso";
$langModifyProfile = "Modificar o meu perfil";
$langTodo          = "Suxerencias";
$langWelcome       = "cursos listados abaixo son de acceso libre. Os outros
cursos requiren un nome de usuario e unha clave de acceso, que poden ser obtidas mediante
unha 'inscripci&oacute;n'. Os profesores e asistentes
poden crear cursos mediante 'inscripci&oacute;n'.";
$langUserName      = "Nome de usuario";
$langPass          = "Clave de acceso";
$langEnter         = "Entrar";
$langHelp          = "Axuda";
$langManager       = "Responsable";
$langPlatform      = "Emprega ";



// REGISTRATION - AUTH - INSCRIPTION
$langRegistration   = "Inscripci&oacute;n";
$langName           = "Apelido";
$langSurname        = "Nome";

// COURSE HOME PAGE

$langAnnouncements  = "Anuncios";
$langLinks          = "Ligaz&oacute;ns";
$langWorks          = "Traballos";
$langUsers          = "Usuarios";
$langStatistics     = "Estad&iacute;sticas";
$langCourseProgram  = "Programa do curso";
$langAddPageHome    = "Subir unha p&aacute;xina e enlazala  dende a p&aacute;xina principal";
$langLinkSite       = "Xerar un enlace a unha web dende a p&aacute;xina principal";
$langModifyInfo     = "Modificar a informaci&oacute;n do curso";
$langDeactivate     = "desactivar";
$langActivate       = "activar";
$langInactiveLinks  = "Ligaz&oacute;ns inactivas";
$langAdminOnly      = "S&oacute; administradores";




// AGENDA

$langAddEvent    = "Engadir un evento";
$langDetail      = "Detalles";
$langHour        = "Hora";
$langLasting     = "Duraci&oacute;n";
$month_default   = "mes";
$january         = "xaneiro";
$february        = "febreiro";
$march           = "marzo";
$april           = "abril";
$may             = "maio";
$june            = "xuño";
$july            = "xullo";
$august          = "agosto";
$september       = "setembro";
$october         = "outubro";
$november        = "novembro";
$december        = "decembro";
$year_default    = "ano";
$year1           = "2003";
$year2           = "2004";
$year3           = "2005";
$hour_default    = "hora";
$hour1="08h30";
$hour2="09h30";
$hour3="10h45";
$hour4="11h45";
$hour5="12h30";
$hour6="12h45";
$hour7="13h00";
$hour8="14h00";
$hour9="15h00";
$hour10="16h15";
$hour11="17h15";
$hour12="18h15";
$lasting_default="duraci&oacute;n";
$lasting1="30min";
$lasting2="45min";
$lasting3="1h";
$lasting4="1h30";
$lasting5="2h";
$lasting6="4h";





// DOCUMENT

$langDownloadFile  = "Subir o arquivo &oacute; servidor";
$langDownload      = "subir";
$langCreateDir     = "Crear un directorio";
$langName          = "Nome";
$langNameDir       = "Nome do novo directorio";
$langSize          = "Tama&ntilde;o";
$langDate          = "Data";
$langMove          = "Mover";
$langRename        = "Cambiar o nome";
$langComment       = "Comentario";
$langVisible       = "Visible/invisible";
$langCopy          = "Copiar";
$langTo            = "a";
$langNoSpace       = "O env&iacute;o fallou. Non hai espacio suficiente no seu directorio";
$langDownloadEnd   = "A carga rematou";
$langFileExists    = "Imposible efectuar esta operaci&oacute;n.<br>Existe un archivo co mesmo nome.";
$langIn            = "en";
$langNewDir        = "nome do novo directorio";
$langImpossible    = "Imposible efectuar esta operaci&oacute;n";
$langAddComment    = "engadir/modificar un comentario a";
$langUp            = "volver a cargar";



// WORKS
$langTooBig        = "Non elixiu o arquivo a enviar ou o arquivo &eacute; demasiado grande.";
$langListDeleted   = "A lista foi borrada por completo";
$langDocModif      = "O documento foi modificado";
$langDocAdd        = "O documento foi engadido";
$langDocDel        = "O traballo foi borrado";
$langTitleWork     = "T&iacute;tulo completo";
$langAuthors       = "Autores";
$langDescription   = "Descripci&oacute;n";
$langDelList       = "Borrar completamente a lista";


// ANNOUCEMENTS
$langAnnEmpty      = "Os anuncios foron valeirados completamente";
$langAnnModify     = "O anuncio foi modificado";
$langAnnAdd        = "O anuncio foi engadido";
$langAnnDel        = "O anuncio foi borrado";
$langPubl          = "Publicalo o";
$langAddAnn        = "Engadir un anuncio";
$langContent       = "Contido";
$langEmptyAnn      = "Valeirar completamente os anuncios";



// OLD
$langAddPage           = "Engadir unha p&aacute;xina";
$langPageAdded         = "A p&aacute;xina foi engadida";
$langPageTitleModified = "O t&iacute;tulo da p&aacute;xina foi modificado";
$langAddPage           = "Engadir una p&aacute;xina";
$langSendPage          = "P&aacute;xina a enviar";
$langCouldNotSendPage  = "Este arquivo non est&aacute; en formato HTML e non puido enviarse.
Se desexa subir documentos non HTML &oacute; servidor (PDF, Word, Power Point, Video, etc.) empregue <a href=../document/document.php>Documentos</a>";
$langAddPageToSite     = "Engadir unha p&aacute;xina &oacute; sitio";
$langNotAllowed        = "Vostede non foi identificado como responsable deste curso";
$langExercices         = "Exercicios";


?>
