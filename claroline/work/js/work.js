/*
 * $Id$
 */

var WORK = {};

WORK.confirmationDel = function (name)
{
    var arr = {"%name" : name};
    
    if (confirm(Claroline.getLang('Are you sure to delete %name ?', arr)))
    {
        return true;
    }
    else
    {
        return false;
    }
}