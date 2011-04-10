/**
 * Admin Project Edit
 * 
 */

$.fn.outer = function(val){
    if(val){
        $(val).insertBefore(this);
        $(this).remove();
    }
    else{return $("<div>").append($(this).clone()).html();}
}

$(function() {
    createSortable();
    createUploader();
    addControls();
    initWysiwyg();

    $("#edit_media_dialog").hide();
    /**
     * Buttons
     */
    $('#saveproject').click(function() {
        saveProject();
        return false;
    })

     $('#addembed').click(function() {
        addEmbed();
        return false;
    })
})

function showMessage(message) {
    $('#message').remove();
    $('body').prepend('<div id="message" >'+message+'</div>');
    $('#message').show().delay(1000).fadeOut(500,function(){$('#message').remove();});
}


function initWysiwyg() {

    $('#project_text').uEditor({
            toolbarItems : ['bold','italic','link','htmlsource'],
            containerClass : 'uEditor'
    });
    
}

function addEmbed() {
    //add html and go to edit
    html  = '<div class="media embed">';
    html += '   <a href="" class="embed" title="" >';
    html += '      EMBED';
    html += '   </a>';
    html += '   <div class="caption">';
    html += '  </div>';
    html += '</div>';
    $("#gallery").prepend(html);
    addControls();
    editMedia($("#gallery .media:eq(0)"));
}

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
        editMedia($(this).parent().parent());
        return false;
    })

    /**
     * Delete media
     */
    $( "#gallery .media a.delete" ).click(function(){
        var src = $('a.image',$(this).parent().parent()).attr('href');
        alert(src);
        var filename = src.substring(src.lastIndexOf('/')+1);
        deleteMedia(filename);
        return false;
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
        action: base_url+base_index+"admin_project_media_upload/"+project_name+"/",
        allowedExtensions: ['jpg'],
        onComplete: function(id, fileName, responseJSON){
            //alert(fileName);
            reloadGallery();
            //$(".qq-upload-list").delay(1000).text("");
        }
    });
}

/**
 * Initialise the sortable thumbnails
 */
function createSortable() {
    $( "#gallery" ).sortable({
       update: function(event, ui) {
            saveProject();
        }
    });
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
    //fetch the wysiwyg editor object to update the textarea
    var editor = $('#project_text').data('editor');
    editor.updateuEditorInput();
    
    $.post(base_url+base_index+"admin_project_save/"+project_name,{
        ajax: true,
        title: $("#project_title").attr("value"),
        text:  $("#project_text").val(),
        gallery: $("#gallery").html()
    },function(data) {
        if (data=='1') {
            showMessage("saved");
        } else {
            showMessage(data);
        }
    })
}

function editMedia(media_div) {
    //hide the gallery and show the dialog
    $("#gallery_container").hide();
    $("#edit_media_dialog").show();

    //unbind click events
    $("#edit_media_controls .save").unbind('click');
    $("#edit_media_controls .cancel").unbind('click');

    //populate the form

    //if it's an image
    if (media_div.hasClass('image')) {
        $("#edit_image_form").show();
        $("#edit_embed_form").hide();

        //populate the form
        $("#edit_image_form img").attr("src",$("img",media_div).attr("src"));
        $("#image_title").val($("a",media_div).attr("title"));
        $("#image_caption").val($(".caption",media_div).text());

        //buttons
        $("#edit_media_controls .save").click(function(){
            $("img",media_div).attr("alt",$("#image_title").val());
            $("a",media_div).attr("title",$("#image_title").val());
            $(".caption",media_div).text($("#image_caption").val());
            $("#gallery_container").show();
            $("#edit_media_dialog").hide();
            saveProject();
        })
    }

    if (media_div.hasClass('embed')) {
        $("#edit_image_form").hide();
        $("#edit_embed_form").show();

        //populate the form
        $("#edit_embed_form .preview").html($("a.embed",media_div).outer());
        $("#embed_url").val($("a.embed",media_div).attr("href"));
        $("#embed_title").val($("a.embed",media_div).attr("title"));
        $("#embed_caption").val($(".caption",media_div).text());

        //buttons
        $("#edit_media_controls .save").click(function(){
            $("a.embed",media_div).attr("href",$("#embed_url").val());
            $("a.embed",media_div).attr("title",$("#embed_title").val());
            $(".caption",media_div).text($("#embed_caption").val());

            $("#gallery_container").show();
            $("#edit_media_dialog").hide();
            saveProject();
        })
    }

    //cancel button
    $("#edit_media_controls .cancel").click(function(){
        $("#gallery_container").show();
        $("#edit_media_dialog").hide();
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
            showMessage("error deleting media");
        }
    })
}