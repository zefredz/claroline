/*
    $Id$
 */

$(document).ready(function(){
    $("a.qtip").each(function()
    {
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
                tip: "bottomLeft"
            },
           position: {
              corner: {
                 target: "topMiddle",
                 tooltip: "bottomLeft"
              }
           }
        });
    });
});