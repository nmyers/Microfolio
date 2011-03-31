/**
 * Admin Project Edit
 * 
 */

$(function() {
    createSortable();
    createUploader();
    addControls();

    /**
     * Buttons
     */
    $('#saveproject').click(function() {
        saveProject();
        return false;
    })
})

/**
 * Adds buttons to thumbnails
 * edit / delete
 */
function addControls() {
    $( "#gallery .media .controls" ).remove();
    $( "#gallery .media" ).prepend("<div class='controls' ><a href='#' class='edit' >edit</a><a href='#' class= 'delete' >delete</a></div>");

    /**
     * Edit caption
     */
    $( "#gallery .media a.edit" ).click(function(){
        alert("edit "+$(this).text());
    })

    /**
     * Delete media
     */
    $( "#gallery .media a.delete" ).click(function(){
        var src = $('img',$(this).parent().parent()).attr('src');
        var filename = src.substring(src.lastIndexOf('/')+1);
        deleteMedia(filename);
    })
}

/**
 * Initialize ajax uploader
 * @see http://github.com/valums/file-uploader
 * @todo wrap the call to the main index.php for consistency
 */
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

/**
 * Initialise the sortable thumbnails
 */
function createSortable() {
    $( "#gallery" ).sortable();
}

/**
 * Reloads the gallery/thumbnails using an ajax call
 */
function reloadGallery() {
    $('#gallery').load(base_url+base_index+'admin_project_edit/'+project_name+' #gallery',
        function(){
            createSortable();
            addControls();
        });
}

/**
 * Saves the project -> ajax
 */
function saveProject() {
    $.post(base_url+base_index+"admin_project_save/"+project_name,{
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

/**
 * Deletes a media -> ajax
 */
function deleteMedia(media_file) {
    $.post(base_url+base_index+"admin_project_media_delete/"+project_name+'/'+media_file,{
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