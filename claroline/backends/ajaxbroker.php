<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Ajax Broker script
 *
 * Usage:
 *  1. Register Ajax remote service in module functions.php
 *      Claroline::ajaxServiceBroker()->register( .... );
 *  2. Execute AJAX requests on get_path('url').'/claroline/backends/ajaxbroker.php'
 *
 * @version     1.10 $Revision$
 * @copyright   2001-2010 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     core.linker
 */

try
{
    require_once dirname(__FILE__) . '/../inc/claro_init_global.inc.php';

    $broker = Claroline::ajaxServiceBroker();

    $ajaxRequest = Ajax_Request::getRequest(Claro_UserInput::getInstance());

    $response = $broker->handle($ajaxRequest);
}
catch (Exception $e )
{
    $response = new Json_Exception( $e );
}

echo $response->toJson();
exit;


