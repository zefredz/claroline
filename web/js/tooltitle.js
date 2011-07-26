/*
 * $Id$
 */

$(document).ready(function(){
    // show/hide tooltitle's commands
    $('.commandList li.hidden').hide();
    
    $('.commandList a.more').click(function() {
        if ($('.commandList a.more').hasClass('clicked'))
        {
            $('.commandList li.hidden').hide();
            $('.commandList a.more').removeClass('clicked').html('&raquo;');
        }
        else
        {
            $('.commandList li.hidden').show();
            $('.commandList a.more').addClass('clicked').html('&laquo;');
        }
    });
});