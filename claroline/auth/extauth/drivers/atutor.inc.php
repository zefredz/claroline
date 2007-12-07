<?php # -$Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

$authSourceName = 'atutor';
$authSourceType = 'DB';

// Define the Auth driver options

$extAuthOptionList = array(

   // PUT HERE THE CORRECT DSN FOR YOUR DB SYSTEM
    'dsn'         => 'mysql://dbuser:dbpassword@domain/atutor',

    'table'       => 'AT_members', // warning ! table prefix can change from one system to another 
    'usernamecol' => 'login',
    'passwordcol' => 'password',
    'db_fields'   => array(' first_name', ' last_name', 'email'),
    'cryptType'   => 'none'
);


// Link additionnal external authentication attributes to the Claroline 
// user attribute.
//
// array KEYS   are the Claroline attributes and
// array VALUES are the authentication external attributes.

$extAuthAttribNameList = array (
    'lastname'  => 'last_name',
    'firstname' => 'first_name',
    'email'     => 'email',
);

// Array setting optionnal preliminary treatment to the data retrieved from the 
// exernal authentication source. Array KEYS are the concernend claroline 
// user table fields, and Array VALUES are either the name of a function which 
// makes the treatment or simply a default value to insert
// Note. Treatments doesn't necessary previously require data from the external 
// authentication system. They're able to be trigged from NULL value ...

$extAuthAttribTreatmentList = array ('status' => 5);



// PROCESS AUTHENTICATION

security fix : protect scripts for remote inclusion

?>