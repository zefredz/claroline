<?php

require_once($includePath.'/lib/extauth.lib.php');


$extAuth = new ExternalAuthentication($authSourceType, $extAuthOptionList);
$extAuth->setAuthSourceName($authSourceName);

if ( $extAuth->isAuth() )
{
    if ( isset($uData['user_id']) )
    {
       // update the user data in the claroline user table

       $extAuth->recordUserData($extAuthAttribNameList, 
                                $extAuthAttribTreatmentList, 
                                $uData['user_id']);
    }
    else
    {
        // create a new rank in the claroline user table for this user
    
        $extAuth->recordUserData($extAuthAttribNameList, 
                                 $extAuthAttribTreatmentList);
    }

    $extAuthId = $extAuth->getUid();
}
else
{
    $extAuthId = false;
}

return $extAuthId;

?>