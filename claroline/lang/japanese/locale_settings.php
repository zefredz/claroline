<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
$englishLangName = "Japanese";
$localLangName = "Japanese";

$iso639_1_code = "ja";
$iso639_2_code = "jpn";

$langNameOfLang['brazilian']="brazilian";
$langNameOfLang['english']="english";
$langNameOfLang['finnish']="finnish";
$langNameOfLang['french']="french";
$langNameOfLang['german']="german";
$langNameOfLang['italian']="italian";
$langNameOfLang['japanese']="japanese";
$langNameOfLang['polish']="polish";
$langNameOfLang['simpl_chinese']="simplified chinese";
$langNameOfLang['spanish']="spanish";
$langNameOfLang['swedish']="swedish";
$langNameOfLang['thai']="thai";


$charset = 'EUC-JP';
$text_dir = 'ltr';
$left_font_family = 'sans-serif';
$right_font_family = 'sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('バイト', 'KB', 'MB', 'GB');

$day_of_week = array('日', '月', '火', '水', '木', '金', '土');
$month = array('1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月');
// See http://www.php.net/manual/en/function.strftime.php to define the
// variable below

$langDay_of_weekNames['init'] = array('日', '月', '火', '水', '木', '金', '土'); // 1 letter
$langDay_of_weekNames['short'] = array('日', '月', '火', '水', '木', '金', '土');
$langDay_of_weekNames['long'] = array('日', '月', '火', '水', '木', '金', '土'); // complete word

$langMonthNames['init']  = array('1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月');
$langMonthNames['short'] = array('1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月');
$langMonthNames['long'] = array('1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%Y年%b%e日";
$dateFormatLong  = '%Y年%B%e日';
$dateTimeFormatLong  = '%Y年%B%e日 %H:%M';
$timeNoSecFormat = '%H:%M';

?>
