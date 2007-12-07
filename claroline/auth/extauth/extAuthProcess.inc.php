<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLAUTH
 *
 * @author Claro Team <cvs@claroline.net>
 */

require_once(dirname(__FILE__) . '/../../inc/lib/extauth.lib.php');

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
} // if ( $extAuth->isAuth() )
else
{
    $extAuthId = false;
} // if ( $extAuth->isAuth() ) else

return $extAuthId;
?>