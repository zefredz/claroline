$(document).ready(function(){
    $(".collapsed .fieldset-wrapper").hide();
    
    $(".collapsible legend a").click(function(){
        var fieldset = $(this).parent().parent();
        if ($(fieldset).is('.collapsed')) {
            $(fieldset).removeClass('collapsed');
            
            $(".fieldset-wrapper",fieldset).slideDown({
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
            $(fieldset).addClass('collapsed');
            $(".fieldset-wrapper",fieldset).slideUp("fast");
        }
        
        return false;
    });

});

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
