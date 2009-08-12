// $Id$
// vim: expandtab sw=4 ts=4 sts=4:

/** 
 * MYSQL Datetime YYYY-MM-DD hh:mm:ss to Javascript Date conversion library
 *
 * @version     1.0 $Revision$
 * @copyright   2001-2009 Universite catholique de Louvain (UCL)
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license      http://www.gnu.org/licenses/lgpl-3.0.txt
 *              GNU LESSER GENERAL PUBLIC LICENSE Version 3.0 or later
 * @package     core.js
 *
 */

Date.fromDatetime = function( datetime ) {
    
    var _datetimeParts = datetime.split(' ');
    var _dateParts = _datetimeParts[0].split('-');
    var _timeParts = _datetimeParts[1].split(':');
    
    return new Date(
        _dateParts[0], // year
        _dateParts[1].replace(/^0/,'') - 1, // month 0 - 11 !!!!
        _dateParts[2].replace(/^0/,''), // days
        _timeParts[0].replace(/^0/,''), // hours
        _timeParts[1].replace(/^0/,''), // minutes
        _timeParts[2].replace(/^0/,'')  // seconds
    );
}