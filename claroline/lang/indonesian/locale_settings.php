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

$iso639_1_code = "id";
$iso639_2_code = "ind";

$langNameOfLang[arabic]="arabian";
$langNameOfLang[brazilian]="brazilian";
$langNameOfLang[bulgarian]="bulgarian";
$langNameOfLang[croatian]="croatian";
$langNameOfLang[dutch]="dutch";
$langNameOfLang[english]="english";
$langNameOfLang[finnish]="finnish";
$langNameOfLang[french]="french";
$langNameOfLang[german]="german";
$langNameOfLang[greek]="greek";
$langNameOfLang[italian]="italian";
$langNameOfLang[japanese]="japanese";
$langNameOfLang[polish]="polish";
$langNameOfLang[simpl_chinese]="simplified chinese";
$langNameOfLang[spanish]="spanish";
$langNameOfLang[swedish]="swedish";
$langNameOfLang[thai]="thai";
$langNameOfLang[turkish]="turkish";
$langNameOfLang[indonesian]="indonesian";

$charset = 'iso-8859-1';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('M', 'S', 'S', 'R', 'K', 'J', 'S');
$langDay_of_weekNames['short'] = array('Ming', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab');
$langDay_of_weekNames['long'] = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
$langMonthNames['long'] = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A, %d %B %Y';
$dateTimeFormatLong  = '%d %B, %Y pada %I:%M %p';
$timeNoSecFormat = '%I:%M %p';

?>
