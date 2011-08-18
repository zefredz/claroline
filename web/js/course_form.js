/*
 * $Id$
 */

$(document).ready(function() {
    var courseRegistrationEnable = function(){
        $("#registration_validation").attr("disabled", false);
        $("#registration_key").attr("disabled", false);
        $("#course_registrationKey").attr("disabled", false);
    };
    
    var courseRegistrationDisable = function(){
        $("#registration_validation").attr("disabled", true);
        $("#registration_key").attr("disabled", true);
        $("#course_registrationKey").attr("disabled", true);
    };
    
    $("#registration_true").click(courseRegistrationEnable);
    
    $("#registration_false").click(courseRegistrationDisable);
    
    if ( $("#registration_true").attr("checked") ) {
        courseRegistrationEnable();
    }
    else if ( $("#registration_false").attr("checked") ) {
        courseRegistrationDisable();
    }
    else {
        courseRegistrationEnable();
    }
    
    var courseStatusEnabled = function(){
        $("#status_pending").attr("disabled", true);
        $("#status_disable").attr("disabled", true);
        $("#status_trash").attr("disabled", true);
        
        $("#course_expirationDay").attr("disabled", true);
        $("#course_expirationMonth").attr("disabled", true);
        $("#course_expirationYear").attr("disabled", true);
        
        $("#course_publicationDay").attr("disabled", true);
        $("#course_publicationMonth").attr("disabled", true);
        $("#course_publicationYear").attr("disabled", true);
        
        $("#useExpirationDate").attr("disabled", true);
    };
    
    var courseStatusDate = function(){
        $("#status_trash").attr("disabled", true);
        $("#status_pending").attr("disabled", true);
        $("#status_disable").attr("disabled", true);
        
        $("#course_publicationDay").removeAttr("disabled");
        $("#course_publicationMonth").removeAttr("disabled");
        $("#course_publicationYear").removeAttr("disabled");
        
        $("#useExpirationDate").removeAttr("disabled");
        
        if ( $("#useExpirationDate").attr("checked") ) {
            $("#course_expirationDay").removeAttr("disabled");
            $("#course_expirationMonth").removeAttr("disabled");
            $("#course_expirationYear").removeAttr("disabled");
        }
        else {
            $("#course_expirationDay").attr("disabled", true);
            $("#course_expirationMonth").attr("disabled", true);
            $("#course_expirationYear").attr("disabled", true);
        }
    };
    
    var courseStatusDisabled = function(){
        $("#status_trash").removeAttr("disabled");
        $("#status_pending").removeAttr("disabled");
        $("#status_disable").removeAttr("disabled");
        
        $("#course_expirationDay").attr("disabled", true);
        $("#course_expirationMonth").attr("disabled", true);
        $("#course_expirationYear").attr("disabled", true);
        
        $("#course_publicationDay").attr("disabled", true);
        $("#course_publicationMonth").attr("disabled", true);
        $("#course_publicationYear").attr("disabled", true);
        
        $("#useExpirationDate").attr("disabled", true);
    };
    
    $("#course_status_enable").click(courseStatusEnabled);
    
    $("#course_status_date").click(courseStatusDate);
    
    $("#course_status_disabled").click(courseStatusDisabled);
    
    $("#useExpirationDate").click(function(){
        if ( $("#useExpirationDate").attr("checked") ) {
            $("#course_expirationDay").removeAttr("disabled");
            $("#course_expirationMonth").removeAttr("disabled");
            $("#course_expirationYear").removeAttr("disabled");
        }
        else {
            $("#course_expirationDay").attr("disabled", true);
            $("#course_expirationMonth").attr("disabled", true);
            $("#course_expirationYear").attr("disabled", true);
        }
    });
    
    if ( $("#course_status_enable").attr("checked") ) {
        courseStatusEnabled();
    }
    else if ( $("#course_status_date").attr("checked") ) {
        courseStatusDate();
    }
    else {
        courseStatusDisabled();
    }
    
    $("#registration_key").click(function(){
        if ( $("#registration_key").attr("checked") ) 
        {
            $("#course_registrationKey").attr("disabled", false);
        }
        else {
            $("#course_registrationKey").attr("disabled", true);
        }
    });
});