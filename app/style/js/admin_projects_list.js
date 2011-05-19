/* 
 * admin_project_list.js
 *
 */

$(function() {

    createSortable();
    addControls();

    $('a[href=#newproject]').click(function() {
        newProject();
        return false;
    })
    
})

/**
 * Makes the list sortable
 * using nested sortable
 *
 * @see http://mjsarfatti.com/sandbox/nestedSortable/
 */
function createSortable() {
    $('#projects_root').addClass('sortable');
    $('ol.sortable').nestedSortable({
        forcePlaceholderSize: true,
        handle: 'div',
        helper:	'clone',
        items: 'li',
        opacity: .6,
        placeholder: 'placeholder',
        tabSize: 25,
        tolerance: 'pointer',
        toleranceElement: '> div',
        update: saveOrder
    });
}

function reloadList(successCallback) {
    showMessage('Reloading list...',MESSAGE_LOADING);
    $('#list-holder').load(makeUrl('admin/projects')+' #projects_root',
        function(){
            createSortable();
            addControls();
            if (typeof successCallback === 'function') {
                successCallback();
            }
        })
}

/**
 * Saves the list
 * -> ajax call
 */
function saveOrder(successCallback) {
    showMessage('Saving projects list...',MESSAGE_LOADING);
    alert($('ol.sortable').nestedSortable('serialize'));
    $.post(makeUrl('admin/projects/reorder'),{
        ajax: true,
        neworder: $('ol.sortable').nestedSortable('serialize')
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (typeof successCallback === 'function') {
            successCallback();
        }
    })
}

function saveStatus(project_slug,new_status,successCallback) {
    showMessage('Saving project\'s status...',MESSAGE_LOADING);
    $.post(makeUrl('admin/projects/status'),{
        ajax: true,
        project_slug: project_slug,
        new_status: new_status
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (typeof successCallback === 'function') {
            successCallback();
        }
    })
}

/**
 * Adds a new project to the list
 * -> Ajax call
 */
function newProject() {
    if(project_title = prompt("New project title:")) {
        saveOrder(function () {
            showMessage('Adding new project...',MESSAGE_LOADING);
            $.post(makeUrl("admin/projects/create/"),{
                ajax: true,
                project_title: project_title
            },function(json){
                var data = jQuery.parseJSON(json);
                showMessage(data.message,data.message_type);
                if (data.message_type!=MESSAGE_ERROR)
                    reloadList();
            })
        })
    }
}

/**
 * Deletes a project
 * @todo needs a proper confirm box!
 * -> ajax call
 */
function deleteProject(project_slug,successCallback) {
    showMessage('Deleting project...',MESSAGE_LOADING);
    $.post(makeUrl('admin/projects/delete/'+project_slug),{
        ajax: true
    },function(json){
        var data = jQuery.parseJSON(json);
        showMessage(data.message,data.message_type);
        if (typeof successCallback === 'function') {
            successCallback();
        }
    })
}

/**
 * Adds a div with control links for each element of the list
 * rename / edit / delete / publish / hide
 */
function addControls() {

    $("#projects_root .controls").remove();
    controls  = "<a href='#rename' class='button2' >rename</a>";
    controls += "<a href='#edit'   class='button2' >edit</a>";
    controls += "<a href='#delete' class='button2' >delete</a>";
    controls += "<a href='#status' class='button bt-status' >offline</a>";
    $("#projects_root div").append("<div class='controls' >"+controls+"</div>");

    /**
     * Rename an element
     * @todo
     */    
    $(".controls .bt-rename").click(function(){
        return false;
    })

    /**
     * Delete a section or a project
     * this removes a level of hierarchy if needed
     */
    $(".controls a[href=#delete]").click(function(){
        parentDiv = $(this).parent().parent();
        href = $(this).parent().prev().attr("href");
        href = href.substring(0,href.indexOf('/'));
        deleteProject(parentDiv.data('slug'),function(){
            //drops a level
            parentLi = parentDiv.parent();
            $("div",parentLi).eq(0).remove();
            $("ol",parentLi).eq(0).replaceWith($("ol",parentLi).eq(0).html());
            parentLi = parentLi.replaceWith(parentLi.html());
            saveOrder(function(){
                showMessage("Project deleted.",MESSAGE_SUCCESS);
            });
        });
        return false;
    })

    /**
     * Edit
     */
    $(".controls a[href=#edit]").click(function(){
        parentDiv = $(this).parent().parent().children('a').first();
        window.location = parentDiv.attr('href');
        return false;
    })

    /**
     * Status
     */
    $(".controls a[href=#status]").each(function(){
        status = $(this).parent().parent().data('status');
        $(this).addClass('status-'+status).text(status);
    })

    $(".controls a[href=#status]").click(function(){
        status = $(this).parent().parent().data('status');
        list_status = new Array('offline','online','hidden');
        new_status = list_status[(list_status.indexOf(status)+1) % list_status.length];
        project_slug = $(this).parent().parent().data('slug');
        var scope = $(this);
        saveStatus(project_slug, new_status, function() {
            scope.parent().parent().data('status',new_status);
            scope.removeClass('status-'+status).addClass('status-'+new_status).text(new_status);
            return false;
        })
    })
}
