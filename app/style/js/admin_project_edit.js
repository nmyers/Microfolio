/**
 * Admin Project Edit
 * 
 */

$(function() {
    
    createSortable();
    createUploader();
    addControls();
    initWysiwyg();

    $("#edit_media_dialog").hide();

    $('a[href=#saveproject]').click(function() {
        saveProject();
        return false;
    })

     $('a[href=#addembed]').click(function() {
        addEmbed();
        return false;
    })

    $('a[href=#editslug]').click(function() {
        if(new_project_slug = prompt("New project uri:",project_slug)) {
            changeProjectSlug(new_project_slug);
        }
        return false;
    })

    $("#status").click(function(){
        status = $(this).data('status');
        list_status = new Array('offline','online','hidden');
        new_status = list_status[(list_status.indexOf(status)+1) % list_status.length];
        var scope = $(this);
        saveStatus(new_status, function() {
            scope.data('status',new_status);
            scope.removeClass('status-'+status).addClass('status-'+new_status).text(new_status);
            return false;
        })
    })

    $('#template').dropp();

})

function initWysiwyg() {
    $('#project_text').uEditor({
            toolbarItems : ['bold','italic','link','h1','h2','h3','p','htmlsource'],
            containerClass : 'uEditor',
            stylesheet : editorCSSUrl
    });
}

function addControls() {
    $( "#gallery .item .controls" ).remove();
    $( "#gallery .item" ).prepend("<div class='controls' ><a href='#edit' >edit</a><a href='#delete' >delete</a></div>");

    $( "#gallery .item a[href=#edit]" ).click(function(){
        editMedia($(this).parent().parent());
        return false;
    })

    $( "#gallery .item a[href=#delete]" ).click(function(){
        if(window.confirm('Are you sure?')) {
            var scope = $(this).parent().parent();
            deleteItem($(this).parent().parent().attr('id'),function() {
                scope.remove();
            });
            return false;
        }
    })
}

function createUploader() {
    //"admin/projects/"+project_slug+"/uploaditem",
    //$("a[href=#fileupload]")
    $("#upload_field").html5_upload({
        url: makeUrl("admin/projects/"+project_slug+"/uploaditem"),
        sendBoundary: window.FormData || $.browser.mozilla,
        onStart: function(event, total, files) {
            $('#gallery_container .toolbar').after('<ol id="uploadqueue" ></ol>');
            for (i=0;i<total;i++) {
                $('#uploadqueue').append('<li id="queue_'+i+'" >'+files[i].fileName+'<div class="progress_holder" ><div class="progress"></div></div></li>');
                $("#queue_"+i).fadeTo(0,0.6);
            }
            return true;
        },
        onStartOne: function(event, name, number, total) {
            $("#queue_"+number).fadeTo(0,1);
            return true;
        },
        onProgress: function(event, progress, name, number, total) {
            $("#queue_"+number+" .progress_holder .progress").css('width', Math.ceil(progress*100)+"%");
	},
        onFinish: function(event, response, name, number, total) {
            $('#uploadqueue').fadeTo(500,0.1).delay(500).remove();
            reloadGallery();
        }
    });
}

function createSortable() {
    $( "#gallery" ).sortable({
       update: function(event, ui) {
            saveOrder();
        }
    });
}

function reloadGallery() {
    $('#gallery_content').load(makeUrl('admin/projects/'+project_slug+' #gallery_content'),
        function(){
            createSortable();
            addControls();
        });
        //updateIframe(base_url+base_index+'project/'+project_slug)
}

function saveOrder(successCallback) {
    showMessage('Saving gallery order...',MESSAGE_LOADING);
    $.post(makeUrl('admin/projects/'+project_slug+'/reorder'),{
        ajax: true,
        neworder: $( "#gallery" ).sortable('serialize')
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (typeof successCallback === 'function') {
            successCallback();
        }
    })
}

function saveProject(successCallback) {
    //saves textarea content, title and template
    showMessage('Saving project...',MESSAGE_LOADING);
    var editor = $('#project_text').data('editor');
    editor.updateuEditorInput();
    $.post(makeUrl('admin/projects/'+project_slug+'/update'),{
        ajax: true,
        title: $("#project_title").val(),
        text: $("#project_text").val(),
        template: $('#template option:selected').val()
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (typeof successCallback === 'function') {
            successCallback();
        }
    })
}

function changeProjectSlug(new_project_slug,successCallback) {
    showMessage('Saving project...',MESSAGE_LOADING);
    var editor = $('#project_text').data('editor');
    editor.updateuEditorInput();
    $.post(makeUrl('admin/projects/'+project_slug+'/rename/'+new_project_slug),{
        ajax: true
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (data.message_type!=MESSAGE_ERROR) {
            window.location = makeUrl('admin/projects/'+data.new_project_slug);
        }
    })
}

function saveStatus(new_status,successCallback) {
    showMessage('Saving project\'s status...',MESSAGE_LOADING);
    $.post(makeUrl('admin/projects/'+project_slug+'/status'),{
        ajax: true,
        new_status: new_status
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (typeof successCallback === 'function') {
            successCallback();
        }
    })
}


function editMedia(media_div) {
    //hide the gallery and show the dialog
    $("#gallery_content").hide();
    $("#edit_media_dialog").show();

    //unbind click events
    $("#edit_media_controls a[href=#save]").unbind('click');
    $("#edit_media_controls a[href=#cancel]").unbind('click');

    //populate the form
    type = media_div.data('type');
    src = media_div.data('src');
    item_uid = media_div.attr('id');
    
    //if it's an image
    if (type=='image') {
        $("#edit_image_form").show();
        $("#edit_embed_form").hide();

        $("#edit_image_form img").attr("src",makeUrl('image/full/'+project_slug+'/'+src));
        $("#image_title").val($("img",media_div).attr("alt"));
        $("#image_caption").val($(".caption",media_div).html());
    }

    if (type=='embed') {
        $("#edit_image_form").hide();
        $("#edit_embed_form").show();

        $("#edit_embed_form .preview").oembed(media_div.data("src"),{embedMethod: "fill",afterEmbed:function(){
                $("#edit_embed_form .preview *").attr('width', '100%').attr('height','100%');
        }} );

        $('#embed_url').blur(function() {
            $("#edit_embed_form .preview").oembed($("#embed_url").val(),{embedMethod: "fill",afterEmbed:function(){
                $("#edit_embed_form .preview *").attr('width', '100%').attr('height','100%');
            }} );
        });
        $("#embed_url").val(media_div.data("src"));
        $("#embed_title").val($("img",media_div).attr("alt"));
        $("#embed_caption").val($(".caption",media_div).html());
    }

    $("#edit_media_controls a[href=#save]").click(function(){
        $("#gallery_content").show();
        $("#edit_media_dialog").hide();
        var data = new Object();
        data.uid = item_uid;
        data.src = type=='embed' ? $("#embed_url").val() : '';
        data.title = $("#"+type+"_title").val();
        data.caption = $("#"+type+"_caption").val();
        data.type = type;
        saveItemDetails(data,function() {
                reloadGallery();
            });
    })

    $("#edit_media_controls a[href=#cancel]").click(function(){
        $("#gallery_content").show();
        $("#edit_media_dialog").hide();
    })
}

function addEmbed() {

        $("#gallery_content").hide();
        $("#edit_media_dialog").show();
        $("#edit_image_form").hide();
        $("#edit_embed_form").show();

        //unbind click events
        $("#edit_media_controls a[href=#save]").unbind('click');
        $("#edit_media_controls a[href=#cancel]").unbind('click');

        $("#edit_embed_form .preview").html('');

        $('#embed_url').blur(function() {
            $("#edit_embed_form .preview").oembed($("#embed_url").val(),{embedMethod: "fill",afterEmbed:function(){
                $("#edit_embed_form .preview *").attr('width', '100%').attr('height','100%');
            }} );
        });
        $("#embed_url").val("http://");
        $("#embed_title").val("");
        $("#embed_caption").val("");

        $("#edit_media_controls a[href=#save]").click(function(){
            $("#gallery_content").show();
            $("#edit_media_dialog").hide();
            var data = new Object();
            data.uid = 'new';
            data.src = $("#embed_url").val();
            data.title = $("#embed_title").val();
            data.caption = $("#embed_caption").val();
            data.type = 'embed';
            saveItemDetails(data,function() {
                reloadGallery();
            });
        });

        $("#edit_media_controls a[href=#cancel]").click(function(){
            $("#gallery_content").show();
            $("#edit_media_dialog").hide();
        })
}

function saveItemDetails(data,successCallback) {
    showMessage('Updating item...',MESSAGE_LOADING);
    $.post(makeUrl('admin/projects/'+project_slug+'/updateitem'),{
        ajax: true,
        uid: data.uid,
        title: data.title,
        src: data.src,
        caption: data.caption,
        type: data.type
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (typeof successCallback === 'function') {
            successCallback();
        }
    })
}

function deleteItem(item_id,successCallback) {
    showMessage('Deleting item...',MESSAGE_LOADING);
    $.post(makeUrl('admin/projects/'+project_slug+'/deleteitem'),{
        ajax: true,
        itemid: item_id
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (typeof successCallback === 'function') {
            successCallback();
        }
    })
}