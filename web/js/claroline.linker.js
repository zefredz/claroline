$(document).ready(function(){
    
    // load list
    linkerFrontend.loadList();
    
    // output list to page
    
    // bind event on each added icon
    // - on category : min max display || select resource ?
    // - on resources : select resources
    
    // listen event so that added resources create a hidden field or added in js object that will add hidden field in form submission
    // listen to close events (min/max display)
    // listen to remove events
    

});

var linkerFrontend = {

    // vars
    selected : {}, 

    base_url : '../../backends/linker.php',
    
    // methods
   
    loadList : function(crl) {
        var url = this.base_url;
        if( typeof crl != 'undefined' )
        {
            url = url + '?crl=' + escape(crl);
        }
        
        // get data from php backend throught JSON and store it in this.available
        $.getJSON( url,
	        function(data){
	          $("#lnk_location")
	           .text(data.name)
                      .append("<br />");
	           
	          $("<a />")
                      .text("Remonter")
	                  .attr("onclick", "linkerFrontend.loadList('"+data.parent+"');return false;")
                      .appendTo("#lnk_location")
                      ;
	          
	          $("#lnk_resources").empty();
	          
	          var currentResource;
	          for ( var x in data.resources ) {
	               currentResource = data.resources[x];
	               /* 
	                  "name":"Course description"
	                  "icon":"\/~fragile\/claroline\/claroline\/course_description\/icon.png"
	                  "crl":"crl:\/\/claroline.net\/ca801b57eca5b49e077071709f42c924\/EXAMPLE_003\/CLDSC"
	                  "parent":"crl:\/\/claroline.net\/ca801b57eca5b49e077071709f42c924\/EXAMPLE_003"
	                  "isVisible":true
	                  "isLinkable":true
	                  "isNavigable":false
	               */

                   // style for !isVisible to add on a and span
	               if( currentResource.isNavigable )
	               {
    	               $("<a />")
	                   .text("*"+currentResource.name)
	                   .attr("title",currentResource.name)
	                   .attr("onclick", "linkerFrontend.loadList('"+currentResource.crl+"');return false;")
	                   .appendTo("#lnk_resources")
	                   ;
	               
	               }
	               else
	               {
	                   // !isNavigable
	                   $("<span />")
	                   .text(currentResource.name)
	                   .appendTo("#lnk_resources");
	               }
	               
	               if( currentResource.isLinkable )
	               {
	                   $("<a />")
                       .text(' [Attach]')
                       .attr("title",currentResource.name)
                       .attr("onclick", "linkerFrontend.select('"+currentResource.crl+"','"+currentResource.name+"');return false;")
                       .appendTo("#lnk_resources")
                       ;
	               }
	               $("<br />").appendTo("#lnk_resources"); 
	               
		        }
	        });
    },
    
    submit : function() {
        // add each selected resource to form before submitting it
    },
    
    select : function( crl, name ) {
        // mark a resource as selected
        // - add it to selected array
        this.selected[crl] = name;
        // - repaint list of selected resources
        this.renderSelected();
    },
    
    unselect : function() {
        // mark a resource as not selected
        // - remove it from selected array
        // - repaint list of selected resources
    },
    
    unselectAll : function() {
        // - remove all resources from selected array
        // - repaint list of selected resources
    },
    
    
    // rendering methods
    
    renderSelected : function() {
        $("#lnk_selected_resources").empty();
        for ( var x in this.selected ) {
        // ajouter chemin complet
             $("<span />")
             .text(this.selected[x])
             .attr("id", x)
             .appendTo("#lnk_selected_resources");
        }
    },
    
    renderAddSelectedItem : function() {
        // create a new dom node and show it
    },

    renderRemoveSelectedItem : function() {
        // find dom node and remove it
    },
}