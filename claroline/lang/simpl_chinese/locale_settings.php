<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
$englishLangName = "Chinese";
$localLangName = "zho";

$iso639_1_code = "zh";
$iso639_2_code = "zho";

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

$charset = 'gb2312';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
$langDay_of_weekNames['short'] = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
$langDay_of_weekNames['long'] = array('.$(AV\HU.(B','.$(AV\R;.(B', '.$(AV\6~.(B', '.$(AV\H}.(B', '.$(AV\KD.(B', '.$(AV\Ne.(B', '.$(AV\Ay.(B');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$langMonthNames['long'] = array('.$(AR;TB.(B', '.$(A6~TB.(B', '.$(AH}TB.(B', '.$(AKDTB.(B', '.$(ANeTB.(B', '.$(AAyTB.(B', '.$(AF_TB.(B', '.$(A0KTB.(B', '.$(A>ETB.(B', '.$(AJ.TB.(B', '.$(AJ.R;TB.(B', '.$(AJ.6~TB.(B');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A %B %d, %Y';
$dateTimeFormatLong  = '%B %d, %Y at %I:%M %p';
$timeNoSecFormat = '%I:%M %p';

?>
