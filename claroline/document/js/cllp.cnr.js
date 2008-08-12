$(document).ready(function() {    

    doInitialize();
    // flag to know i
    var isTerminated = false;

    // start timer for session_time
    var d = new Date();
    var startTime = d.getTime(); 

	doSetValue("cmi.score.min","0");
	doSetValue("cmi.score.max","100");
	doSetValue("cmi.session_time","PT0H0S");

    doSetValue("cmi.score.raw","0");
    doSetValue("cmi.completion_status","incomplete");

    $(".progressRadio").click( function() {
        if( isTerminated ) return false;
        // score
        var currentProgress = $(this).val();
        
        doSetValue("cmi.score.raw",currentProgress);
        
        // completion_status
        if( currentProgress > 50 )
        {    
	        doSetValue("cmi.completion_status","completed");
	    }
	    else
	    {
	       doSetValue("cmi.completion_status","incomplete");
	    }
	    
	    // session_time
        var d = new Date();
        var partTime = d.getTime(); 
    
        var time = partTime - startTime; // time in milliseconds
        doSetValue("cmi.session_time", centisecsToISODuration(time/10));
        
        // save
	    doCommit();
    });
    
    $("#progressDone").click( function() {
        
        // session_time
        var d = new Date();
        var partTime = d.getTime(); 
    
        var time = partTime - startTime; // time in milliseconds
        doSetValue("cmi.session_time", centisecsToISODuration(time/10));
        
        // save
        doCommit();
        doTerminate();
        
        isTerminated = true;
        
        return false;
    });
        
});

