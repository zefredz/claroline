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


/**
 * This class is mainly a bridge between the claroline system 
 * and the PEAR Auth library. It allows to use external authentication system 
 * for claroline login process
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 */

class ExternalAuthentication
{
    var $auth; // auth container

    /**
     * constructor.
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @param string $extAuthType
     * @param string $authOptionList
     */



    function ExternalAuthentication($extAuthType, $authOptionList,
                                    $formFieldList = array('username' => 'login',
                                                           'password' => 'password'))
    {
        // Auth library expects HTTP POST request with 'password' and 'username' 
        // keys. The Claroline authentication form uses 'login' and 'password'. 
        // The following line joins 'login' and 'password' enabling Auth to work 
        // properly

        $_POST['username'] = $GLOBALS[ $formFieldList['username'] ];
        $_POST['password'] = $GLOBALS[ $formFieldList['password'] ];

        require_once('Auth/Auth.php');

        $this->auth = new Auth($extAuthType, $authOptionList,'', false);

        $this->auth->start();
    }

    function setAuthSourceName($authSourceName)
    {
    	$this->authSourceName = $authSourceName;
    }



    /**
     * check if user is authenticated
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @return 
     */

    function isAuth()
    {
        return $this->auth->getAuth();
    }
    
    /**
     * record user data into the claroline system
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @param array $extAuthAttribNameList - list that make correspondance 
     *        between claroline attribute names and the external authentication 
     *        system attribute name
     * @param array $extAttribTreatmentList list of preliminary treatment before 
     *        submitting the attribute values to the claroline system. Each 
     *        claroline attributes destination can have its own preliminary 
     *        treatment
     * @param int $uid (optional) user id if the user is already registered to 
     *        claroline
     * @return 
     */

    function recordUserData($extAuthAttribNameList, $extAuthAttribTreatmentList, $uid = false)
    {
        /* Default initialisation of user attributes
         * It will be progressively filled by the foreach loop below
         */

        $userAttrList = array('lastname'     => NULL,
                              'firstname'    => NULL,
                              'loginName'    => NULL,
                              'email'        => NULL,
                              'officialCode' => NULL,
                              'phoneNumber'  => NULL,
                              'status'       => NULL,
                              'authSource'   => NULL);

        foreach($extAuthAttribNameList as $claroAttribName => $extAuthAttribName)
        {
            if ( ! is_null($extAuthAttribName) )
            {
                $userAttrList[$claroAttribName] = $this->auth->getAuthData($extAuthAttribName);
            }
        }

        /* Possible preliminary treatment before recording */

        foreach($userAttrList as $claroAttribName => $claroAttribValue)
        {
            if ( array_key_exists($claroAttribName, $extAuthAttribTreatmentList ) )
            {
                $treatmentName = $extAuthAttribTreatmentList[$claroAttribName];

                if ( function_exists( (string)$treatmentName ) )
                {
                    $claroAttribValue = $treatmentName($claroAttribValue);
                }
                else
                {
                    $claroAttribValue = $treatmentName;
                }
            }

            $userAttrList[$claroAttribName] = $claroAttribValue;
        } // end foreach

        /* Two fields retrieving info from another source ... */

        $userAttrList['loginName' ] = $this->auth->getUsername();
        $userAttrList['authSource'] = $this->authSourceName;

        /* Data record */

        $userTbl = claro_sql_get_main_tbl();

        $dbFieldToClaroMap = array('nom'          => 'lastname', 
                                   'prenom'       => 'firstname', 
                                   'username'     => 'loginName', 
                                   'email'        => 'email', 
                                   'officialCode' => 'officialCode', 
                                   'phoneNumber'  => 'phoneNumber', 
                                   'statut'       => 'status', 
                                   'authSource'   => 'authSource');
        $sqlPrepareList = array();

        foreach($dbFieldToClaroMap as $dbFieldName => $claroAttribName)
        {
            if ( ! is_null($userAttrList[$claroAttribName]) )
            {
                $sqlPrepareList[] = $dbFieldName. ' = "'.addslashes($userAttrList[$claroAttribName]).'"';
            }
        }


        $sql = ($uid ? 'UPDATE' : 'INSERT INTO') . " `".$userTbl['user']."` "
              ."SET ".implode(', ', $sqlPrepareList)
              .($uid ? 'WHERE user_id = '.(int)$uid : '');

        $res  = mysql_query($sql) 
                or die('<center>UPDATE QUERY FAILED LINE '.__LINE__.'<center>');   

        if ($uid) $this->uid = $uid;
        else      $this->uid = mysql_insert_id();
    }

    /**
     * get the current uid of the logged usser
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @return int
     */
    
    function getUid()
    {
        return $this->uid;
    }
}


?>
