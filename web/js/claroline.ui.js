$(document).ready(function(){
    registerCollapseBehavior();
});


/*
 * Markup should be something like 
 * <div ... class="collapsible"><a ... class="doCollapse" /><div ... class="collapsible-wrapped" /></div> 
 */
registerCollapseBehavior = function() {
    $(".collapsed .collapsible-wrapper").hide();
    
    $(".collapsible a.doCollapse").click(function(){
        var collapsible = $(this).parents('.collapsible:first')[0];

        if ($(collapsible).is('.collapsed')) {
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
        } else {
            $(collapsible).addClass('collapsed');
            $(".collapsible-wrapper",collapsible).slideUp("fast");
        }
        
        return false;
    });
};
/**
 * Scroll a given fieldset into view as much as possible.
 * This function is part of the Drupal js library
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
