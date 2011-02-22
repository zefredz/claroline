<!-- $Id$ -->

<?php if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) ); ?>

<script type="text/javascript">
    $(document).ready(function(){
        $("img.qtip").each(function()
        {
            $(this).qtip({
                content: $(this).attr("alt"),
                
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
                    fontSize: "1em",
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
                     target: "topRight",
                     tooltip: "bottomLeft"
                  }
               }
            });
        });
    });
</script>

<div id="myCourseList">
<?php
//Display activated courses list
echo claro_html_tool_title(get_lang('My course list'));

if( !empty( $this->userCourseList ) ) :
    echo $this->userCourseList; // Comes from render_user_course_list();

elseif( empty( $this->userCourseListDesactivated ) ) :
    echo get_lang('You are not enrolled to any course on this platform or all your courses are deactivated');

else :
    echo get_lang( 'All your courses are deactivated (see list below)' );

endif;

//Display deactivated courses list
if ( !empty( $this->userCourseListDesactivated ) ) :
    echo claro_html_tool_title(get_lang('Deactivated course list'));
    echo $this->userCourseListDesactivated; // Comes from render_user_course_list_desactivated();
endif;
?>
</div>