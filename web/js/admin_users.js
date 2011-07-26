/*
 * $Id$
 */

$(document).ready(function(){
    $(".delete").each(function( i )
    {
        var _id = $(this).attr("id");
        var id = _id.substr(_id.lastIndexOf("_") + 1 );
        var firstname = _id.substr(0,_id.indexOf("_"));
        var lastname = _id.substr(_id.indexOf("_") + 1 );
        lastname = lastname.substr(0, lastname.lastIndexOf("_"));
        
        $(this).click(function()
        {
            return confirmation(" " + firstname + " " + lastname);
        });
        $(this).attr("href","admin_users.php?cmd=exDelete&user_id=" + id + "&offset=' . $offset . $addToURL . '");
    });
    
    $("a.showUserCourses").each(function()
    {
        $(this).qtip({
            content: {
                url: "./ajax/ajax_requests.php",
                data: { action: "getUserCourseList", userId: $(this).find("span").attr("class") },
                method: "get"
            },
            
            show: "mouseover",
            hide: "mouseout",
            position: {
                corner: {
                    target: "topRight",
                    tooltip: "bottomRight"
                }
            },
            
            style: {
                width: 200,
                padding: 5,
                background: "#CCDDEE",
                color: "black",
                fontSize: "1em",
                textAlign: "center",
                border: {
                    width: 7,
                    radius: 5,
                    color: "#CCDDEE"
                }
            }
        });
    });
    
    $("a.showUserCategory").each(function()
    {
        $(this).qtip({
            content: {
                url: "./ajax/ajax_requests.php",
                data: { action: "getUserCategoryList", userId: $(this).find("span").attr("class") },
                method: "get"
            },
            
            show: "mouseover",
            hide: "mouseout",
            position: {
                corner: {
                    target: "topRight",
                    tooltip: "bottomRight"
                }
            },
            
            style: {
                width: 200,
                padding: 5,
                background: "#CCDDEE",
                color: "black",
                fontSize: "1em",
                textAlign: "center",
                border: {
                    width: 7,
                    radius: 5,
                    color: "#CCDDEE"
                }
            }
        });
    });
});