<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$        |
      |   Japanese translation                                               |
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
      | Translator:                                                          |
	  |          yoshii akira      <yoshii@cc.hokyodai.ac.jp>                |                                                      |
      +----------------------------------------------------------------------+
 */
$englishLangName = "japanese";
$localLangName = "japanese";

$iso639_2_code = "ja";
$iso639_1_code = "jpn";

$langNameOfLang[brazilian]="brazilian";
$langNameOfLang[english]="english";
$langNameOfLang[finnish]="finnish";
$langNameOfLang[french]="french";
$langNameOfLang[german]="german";
$langNameOfLang[italian]="italian";
$langNameOfLang[japanese]="japanese";
$langNameOfLang[polish]="polish";
$langNameOfLang[simpl_chinese]="simplified chinese";
$langNameOfLang[spanish]="spanish";
$langNameOfLang[swedish]="swedish";
$langNameOfLang[thai]="thai";


$charset = 'EUC-JP';
$text_dir = 'ltr';
$left_font_family = 'sans-serif';
$right_font_family = 'sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('¥Ð¥¤¥È', 'KB', 'MB', 'GB');

$day_of_week = array('Æü', '·î', '²Ð', '¿å', 'ÌÚ', '¶â', 'ÅÚ');
$month = array('1·î','2·î','3·î','4·î','5·î','6·î','7·î','8·î','9·î','10·î','11·î','12·î');
// See http://www.php.net/manual/en/function.strftime.php to define the
// variable below

$langDay_of_weekNames['init'] = array('Æü', '·î', '²Ð', '¿å', 'ÌÚ', '¶â', 'ÅÚ'); // 1 letter
$langDay_of_weekNames['short'] = array('Æü', '·î', '²Ð', '¿å', 'ÌÚ', '¶â', 'ÅÚ');
$langDay_of_weekNames['long'] = array('Æü', '·î', '²Ð', '¿å', 'ÌÚ', '¶â', 'ÅÚ'); // complete word

$langMonthNames['init']  = array('1·î','2·î','3·î','4·î','5·î','6·î','7·î','8·î','9·î','10·î','11·î','12·î');
$langMonthNames['short'] = array('1·î','2·î','3·î','4·î','5·î','6·î','7·î','8·î','9·î','10·î','11·î','12·î');
$langMonthNames['long'] = array('1·î','2·î','3·î','4·î','5·î','6·î','7·î','8·î','9·î','10·î','11·î','12·î');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%YÇ¯%b%eÆü";
$dateFormatLong  = '%YÇ¯%B%eÆü';
$dateTimeFormatLong  = '%YÇ¯%B%eÆü %H:%M';
$timeNoSecFormat = '%H:%M';

// GENERIC
$langModify="½¤Àµ";
$langDelete="ºï½ü";
$langTitle="¥¿¥¤¥È¥ë";
$langHelp="¤Ø¥ë¥×";
$langOk="¥ª¥Ã¥±¡¼";
$langAddIntro="Add introduction text";
$langBackList="¥ê¥¹¥È¤ËÌá¤ë";


// banner

$langMyCourses="¼«Ê¬¤Î¥³¡¼¥¹";
$langModifyProfile="¼«Ê¬¤Î¥×¥í¥Õ¥¡¥¤¥ë¤ò½¤Àµ";
$langLogout="¥í¥°¥¢¥¦¥È";
?>