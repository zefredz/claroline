/*
 * $Id$
 */

var ADMIN = {};

ADMIN.confirmationDel = function (name)
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

ADMIN.confirmationUnReg = function (name)
{
    var arr = {"%name" : name};
    
    if (confirm(Claroline.getLang('Are you sure you want to unregister %name ?', arr)))
    {
        return true;
    }
    else
    {
        return false;
    }
}

ADMIN.confirmationUninstall = function (name)
{
    var arr = {"%name" : name};
    
    if (confirm(Claroline.getLang('Are you sure you want to uninstall the module %name ?', arr)))
    {
        return true;
    }
    else
    {
        return false;
    }
}