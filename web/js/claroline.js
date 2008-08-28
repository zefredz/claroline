/*
    $Id$
    
    Main Claroline javascript library
 */

// Claroline namespace
var Claroline = {};

Claroline.version = '1.9 rev. $Revision$';

Claroline.json = {
    isResponse: function( response ) {
        return (typeof response.responseType != 'undefined') && (typeof response.responseBody != 'undefined');
    },
    isError: function( response ) {
        return Claroline.json.isResponse(response) && (response.responseType == 'error');
    },
    isSuccess: function( response ) {
        return Claroline.json.isResponse(response) && (response.responseType == 'success');
    }
};

$(document).ready( function (){
    // this is the core function of Claroline's jQuery implementation

    // ajax activity shower
    $("#loading").hide();

    $("#loading").ajaxStart(function(){
        $(this).show();
    });

    $("#loading").ajaxStop(function(){
        $(this).hide();
    });

});


// here should also come :

// - a kind of get_lang function
// - a standard confirmation box function
// - some object to set up standard environment vars ? (base url (module,...) courseId, userId, groupId, ...)
// - get_icon

function array_indexOf(arr,val)
{
    for ( var i = 0; i < arr.length; i++ )
    {
        if ( arr[i] == val )
        {
            return i;
        }
    }
    return -1;
}

function isDefined(a)
{
    return typeof a != 'undefined';
}

function isNull(a)
{
    return typeof a == 'object' && !a;
}

function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;

    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";

    if(typeof(arr) == 'object') { //Array/Hashes/Objects
        for(var item in arr) {
            var value = arr[item];

            if(typeof(value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}