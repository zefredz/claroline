<?php
//require '../inc/claro_init_global.inc.php';
//define('FPDF_font_path',"../admin/mysql/libraries/fpdf/font/");

GLOBAL $siteName,$administrator,$administrator_email, $currentCourseCode, $_REQUEST;


$screenCodeCourse 	= $currentCourseCode;
$nameCourse 		= $_REQUEST["intitule"];
$titu				= $_REQUEST["titulaires"];
$fac				= $_REQUEST["faculte"];
$courseLang			= $_REQUEST["languageCourse"];

$FPDF_font_path = "../admin/mysql/libraries/fpdf/font/";
//$FPDF_font_path = "font/";
require("../admin/mysql/libraries/fpdf/fpdf.php");
$pdf=new FPDF();
$pdf->Open();
$pdf->AddPage();
$pdf->SetFont('times','B',16);
$pdf->Cell(40,10,$siteName,'B',1);
$pdf->SetFont('times','B',12);
$pdf->Cell(40,10,'Hello World !',0,1);
$pdf->SetFont('times','',12);
$pdf->Cell(40,10,"Cours ".$screenCodeCourse." ",'',1);
$pdf->SetFont('times','I',12);
$pdf->Cell(40,10,$nameCourse,'',1);
$pdf->SetFont('times','',11);
$pdf->Cell(40,10,'Hello World !',0,1);
$pdf->Cell(40,10,'Titulaire : 	'.$titu,0,1);
$pdf->Cell(40,10,'Faculté : 	'.$fac,0,1);
$pdf->Cell(40,10,'Langue : 	'.$courseLang,0,1);
$pdf->SetFont('times','',10);
$pdf->Cell(180,8,$administrator_name." ".$administrator_email,'T');
$fichierTemp =  tempnam ( ".", "samplePDF");
$pdf->Output($fichierTemp);

?>
