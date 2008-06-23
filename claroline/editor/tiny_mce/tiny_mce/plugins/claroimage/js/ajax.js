    $(document).ready( function ()
    {
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
    
    function selectImage(imageUrl, elem)
    {
        $('#src').val(imageUrl);
        ImageDialog.showPreviewImage(imageUrl, 1);
    }