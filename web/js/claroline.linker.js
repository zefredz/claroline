/*
This code will work with html like this
<div id="lnk_panel">
 <div id="lnk_ajax_loading">load</div>
 <div id="lnk_selected_resources"></div>
 <h4 id="lnk_location"></h4>
 <div id="lnk_resources"></div>
 <div id="lnk_hidden_fields"></div>
</div>

*/


$(document).ready(function(){
    
    // load list
    linkerFrontend.loadList();
    
    // output list to page
    
    // bind event on each added icon
    // - on category : min max display || select resource ?
    // - on resources : select resources
    
    // listen to browse events
    $("#lnk_resources a.navigable").livequery( 'click', function(){
        linkerFrontend.loadList($(this).attr("rel"));
        return false;
    });
    
    // listen to attach events
    $("#lnk_resources a.linkable").livequery( 'click', function(){
        linkerFrontend.select($(this).attr("rel"), $(this).text());
        return false;
    });
    // listen to detach events
    $("#lnk_selected_resources div a").livequery( 'click', function(){
        linkerFrontend.unselect($(this).attr("rel"));
        return false;
    });
    // listen to close events (min/max display)
    
    
    // show activity mechanism
    $("#lnk_ajax_loading").hide();
    
    $("#lnk_ajax_loading").ajaxStart(function(){
        $(this).show();
    });
        
    $("#lnk_ajax_loading").ajaxStop(function(){
        $(this).hide();
    });
    

});

var linkerFrontend = {

    // vars
    selected : {}, 

    base_url : claro_linkerBackend,
    
    // methods
   
    loadList : function(crl) {
        var url = this.base_url;
        if( typeof crl != 'undefined' )
        {
            url = url + '?crl=' + escape(crl);
        }
        

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
                        $("#lnk_resources")
                        .append('<a class="navigable" rel="'+currentResource.crl+'">'+currentResource.name+'</a>');
                   }
                   else
                   {
                       // !isNavigable
                       $("#lnk_resources")
                        .append('<span>'+currentResource.name+'</span>');
                   }
                   
                   if( currentResource.isLinkable )
                   {/*
                       $("<a />")
                       .text(' [Attach]')
                       .attr("title",currentResource.name)
                       .attr("onclick", "linkerFrontend.select('"+currentResource.crl+"','"+currentResource.name+"');return false;")
                       .appendTo("#lnk_resources")
                       ;*/
                        $("#lnk_resources")
                        .append('<a class="linkable" rel="'+currentResource.crl+'">'+currentResource.name+'</a>');
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
    
    unselect : function(crl) {
        // mark a resource as not selected
        // - remove it from selected array
        delete this.selected[crl];
        // - repaint list of selected resources
        this.renderSelected();
    },
    
    unselectAll : function() {
        // - remove all resources from selected array
        // - repaint list of selected resources
    },
    
    
    // rendering methods
    
    renderSelected : function() {
        $("#lnk_selected_resources").empty();
        var i=0;
        for ( var x in this.selected ) {
            // ajouter chemin complet
             // add element in displayed list
             $("#lnk_selected_resources")
             .append('<div id="'+x+'">'+this.selected[x]+'<a href="#" rel="'+x+'">delete</a></div>');
             
             // add a form element
             $("#lnk_hidden_fields")
             .append('<input name="servAdd['+i+']" value="'+x+'" type="hidden">');
             
             i++;
        }
    },
    
    renderAddSelectedItem : function() {
        // create a new dom node and show it
    },

    renderRemoveSelectedItem : function() {
        // find dom node and remove it
    },
}