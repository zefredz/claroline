/*
 * $Id$
 */

$(document).ready(function(){
    registerCollapseBehavior();
});


/*
 * Markup should be something like 
 * <div ... class="collapsible">
 *     <a ... class="doCollapse" />
 *     <div ... class="collapsible-wrapped" /></div> 
 */
expand = function(collapsible) {
    $(collapsible).removeClass('collapsed');
    
    $(".collapsible-wrapper",collapsible).slideDown({
          duration: 'fast',
          easing: 'linear',
          complete: function() {
            collapseScrollIntoView(this.parentNode);
            this.parentNode.animating = false;
          },
          step: function() {
            // Scroll the fieldset into view
            collapseScrollIntoView(this.parentNode);
          }
        });
}

collapse = function(collapsible) {
    $(collapsible).addClass('collapsed');
    $(".collapsible-wrapper",collapsible).slideUp("fast");
}

registerCollapseBehavior = function() {
    $(".collapsed .collapsible-wrapper").hide();
    
    $(".collapsible a.doCollapse").click(function(){
        var collapsible = $(this).parents('.collapsible:first')[0];
        
        if ($(collapsible).is('.collapsed')) {
        
            expand(collapsible);
        
        }
        else {
        
            collapse(collapsible);
        
        }
        
        return false;
    });
    
    $(".expand-all").click(function(){
        $(".collapsible").each(function(){
            
            expand($(this));
            
        });
        
        return false;
    });
    
    $(".collapse-all").click(function(){
        $(".collapsible").each(function(){
            
            collapse($(this));
            
        });
        
        return false;
    });
};


/**
 * Scroll a given fieldset into view as much as possible.
 * This function is part of the Drupal js library.
 */
collapseScrollIntoView = function (node) {
  var h = self.innerHeight || document.documentElement.clientHeight || $('body')[0].clientHeight || 0;
  var offset = self.pageYOffset || document.documentElement.scrollTop || $('body')[0].scrollTop || 0;
  var posY = $(node).offset().top;
  var fudge = 55;
  
  if (posY + node.offsetHeight + fudge > h + offset) {

    if (node.offsetHeight > h) {
      window.scrollTo(0, posY);
    } else {
      window.scrollTo(0, posY + node.offsetHeight - h + fudge);
    }
  }
};


/**
 * Manage the qtips.  Simply add a CSS class "qtip" to an <img> or a 
 * <a> tag to add a qtip on it, displaying the "title" or "alt" (in that order)
 * value on mouseover.
 * If you deserve other renders for specifi uses of qtips, write another 
 * js cript dedicated to this use, and use a class like "qtip-custom" to 
 * refer to it.
 */
$(document).ready(function(){
    $(".qtip").each(function()
    {
        var qtipContent = '';
        
        if ($(this).attr("title") != '')
        {
            qtipContent = $(this).attr("title");
        }
        else if ($(this).attr("alt") != '')
        {
            qtipContent = $(this).attr("alt");
        }
        
        if ( $(this).qtip && qtipContent != '')
        {
            $(this).qtip({
                content: qtipContent,
                
                show: "mouseover",
                hide: "mouseout",
                position: {
                    corner: {
                     target: "topMiddle",
                     tooltip: "bottomLeft"
                    }
                },
                
                style: {
                    width: "auto",
                    padding: 5,
                    background: "#CCDDEE",
                    color: "black",
                    fontSize: "0.9em",
                    textAlign: "center",
                    border: {
                        width: 7,
                        radius: 5,
                        color: "#CCDDEE"
                    },
                    tip: "bottomLeft"
               }
            });
        }
    });
});
