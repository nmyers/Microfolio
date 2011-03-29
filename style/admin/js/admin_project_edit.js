/* 
 * Admin Project Edit
 * --
 * 
 */

$(function() {
        createSortable();
        createUploader();
        addControls();
        $('#saveproject').click(function() {
            saveProject();
            return false;
        })
})

   function createUploader() {
            var uploader = new qq.FileUploader({
                element: document.getElementById('file-uploader'),
                action: base_url+'lib/ajaxupload/upload.php',
                allowedExtensions: ['jpg'],
                onComplete: function(id, fileName, responseJSON){
                    reloadGallery();
                },
                params: {
                    folder: project_name
                }
            });
    }

    function createSortable() {
        $( "#gallery" ).sortable();
    }

    function addControls() {
        $( "#gallery .media .controls" ).remove();
        $( "#gallery .media" ).prepend("<div class='controls' ><a href='#' class='edit' >edit</a><a href='#' class= 'delete' >delete</a></div>");
        $( "#gallery .media a.edit" ).click(function(){
           alert("edit "+$(this).text());
        })
        $( "#gallery .media a.delete" ).click(function(){
           var src = $('img',$(this).parent().parent()).attr('src');
           var filename = src.substring(src.lastIndexOf('/')+1);
           deleteMedia(filename);
        })
    }

    function reloadGallery() {
        $('#gallery').load(base_url+'index.php/admin_project_edit/'+project_name+' #gallery',
        function(){
            createSortable();
            addControls();
        });
    }

    function saveProject() {

        $.post(base_url+"index.php/admin_project_save/"+project_name,{
           ajax: true,
           title: $("#project_title").attr("value"),
           text:  $("#project_text").html(),
           gallery: $("#gallery").html()
        },function(data) {
           if (data=='1') {
               alert("saved");
           } else {
               alert(data);
           }
       })
    }

    function deleteMedia(media_file) {
        $.post(base_url+"index.php/admin_project_media_delete/"+project_name+'/'+media_file,{
           ajax: true,
           project_name: project_name,
           media_file: media_file
        },function(data) {
           if (data=='1') {
               reloadGallery();
           } else {
               alert("error deleting media");
           }
       })
    }