<?php
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: Muret Benoît <muret_ben@hotmail.com>
//----------------------------------------------------------------------

/**
  * search content of a file
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param string $file the path a the file
  *
  * @return string content of the file
  *
  * @desc The function search content of a file
  */
function contentFile($file)
{
	if(file_exists($file))
	{
		if($fp = fopen($file,"r"))
		{
			while(!feof($fp))
			{
				$line=fgets($fp,255);
				$AllLine.=$line;
			}

			fclose($fp);
		}

		return $AllLine;
	}
	else
		return FALSE;
}

?>
