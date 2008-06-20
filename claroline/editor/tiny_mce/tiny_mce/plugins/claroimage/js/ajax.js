    $(document).ready( function ()
    {
        // bind events
        // use livequery so that binding is automatically added on new DOM elements (list is build dynamically)
        //$('a.selectImage').livequery('click', selectImage);
        //$('a.selectFolder').livequery('click', setFileList);

        // init file list.  After an upload we have to display the correct list so pass it directly 
        setFileList(relativePath);
    });
    
    
    function setFileList(relPath)
    {
        $.ajax({
            url: "./backend.php",
            data: "cmd=getFileList&relPath=" + relPath,
            success: function(response){
                    if( response != 'undefined' && response != '' )
                    {
                        $("#image_list").empty();
                        $("#image_list").append(response);
                        $("#relativePath").val(relPath);
                    }
                },
            error: function(response){
            },
            dataType: 'html'
        });
    }
    
    function selectImage(imageUrl)
    {
        $('.selectedImage').removeClass('selectedImage');
        $(this).addClass('selectedImage');
        $('#src').val(imageUrl);
        ImageDialog.showPreviewImage(imageUrl);
    }