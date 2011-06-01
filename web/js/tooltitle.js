$(document).ready(function(){
    // show/hide tooltitle's commands
    $('.commandList li.hidden').hide();
    
    $('.commandList a.more').click(function() {
        if ($('.commandList a.more').hasClass('clicked'))
        {
            $('.commandList li.hidden').hide();
            $('.commandList a.more').removeClass('clicked').text('»');
        }
        else
        {
            $('.commandList li.hidden').show();
            $('.commandList a.more').addClass('clicked').text('«');
        }
    });
    
    // tooltips on the tools' titles
    $(".commandList li a").each(function() {
        if ($(this).attr("title")) {
            $(this).qtip({
                content: $(this).attr("title"),
                
                show: "mouseover",
                hide: "mouseout",
                position: {
                    corner: {
                        target: "topRight",
                        tooltip: "bottomRight"
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
                    tip: 'bottomMiddle'
               },
               
               position: {
                  corner: {
                     target: "topMiddle",
                     tooltip: "bottomMiddle"
                  }
               }
            });
        }
    });
});