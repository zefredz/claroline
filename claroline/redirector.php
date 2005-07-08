<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.7
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

if ( isset($_REQUEST['url']) )
{
    header('location:' . $_REQUEST['url']);
}
else
{
    header('Location:../');
}

?>