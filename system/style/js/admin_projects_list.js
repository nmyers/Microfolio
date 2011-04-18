/* 
 * admin_project_list.js
 *
 *
 *
 */

$(function() {

    createSortable();
    addControls();

    /*
     * --------------------------------------------------------------------
     * BUTTONS
     * --------------------------------------------------------------------
     */

    $('#newproject').click(function() {
        newProject();
        return false;
    })

    $("#addsection").click(function(){
        if (newTitle = prompt("Section title:")) {
            newSection = '<li><div class="section status-offline" ><a>'+newTitle+"</a></div></li>\n";
            $("#list-projects").append(newSection);
            addControls();
        }
        return false;
    })

    $("#savechanges").click(function(){
        saveList();
        return false;
    })

})


/**
 * Makes the list sortable
 * using nested sortable
 *
 * @see http://mjsarfatti.com/sandbox/nestedSortable/
 *
 */
function createSortable() {
    $('#list-projects').addClass('sortable');
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
        update: saveList
    });
}

/**
 * Adds a new project to the list
 * -> Ajax call
 *
 */
function newProject() {
    if(project_name = prompt("New project name:")) {
        showMessage('3#Adding new project...');
        $.post(base_url+base_index+"admin_projects_list_save/",{
            ajax: true,
            listhtml: $("#list-projects").html()
        },function(message) {
            if (message.charAt(1)=='#' && message.charAt(0)=='1') {
                $.post(base_url+base_index+"admin_project_create/"+project_name,{
                    ajax: true
                },function(message){
                    showMessage(message);
                    //reload list
                    $('#list-holder').load(base_url+base_index+'admin_projects_list #list-projects',
                    function(){
                        createSortable();
                        addControls();
                    });
                })
            }
        })
    }
}

/**
 * Saves the list
 * -> ajax call
 *
 */
function saveList() {
    //show message
    showMessage('3#Saving projects list...');
    $.post(base_url+base_index+"admin_projects_list_save/",{
        ajax: true,
        listhtml: $("#list-projects").html()
    },function(message){
        showMessage(message);
        if (message.charAt(1)=='#' && message.charAt(0)=='1') {
            addControls();
        }
    })
}

/**
 * Deletes a project
 * @todo needs a proper confirm box!
 * -> ajax call
 *
 */
function deleteProject(project_name) {
    showMessage('3#Deleting project...');
    $.post(base_url+base_index+"admin_project_delete/"+project_name,{
        ajax: true
    },showMessage)
}

/**
 * Adds a div with control links for each element of the list
 * rename / edit / delete / publish / hide
 */
function addControls() {

    /**
     *  Adds controls to each project and section
     *  rename / edit / delete / publish / hide
     */
    $("#list-projects .controls").remove();
    controls  = "<a href='#' class='button2 bt-rename' >rename</a>";
    controls += "<a href='#' class='button2 bt-edit' >edit</a>";
    controls += "<a href='#' class='button2 bt-delete' >delete</a>";
    controls += "<a href='#' class='button bt-status' >offline</a>";
    $("#list-projects div").append("<div class='controls' >"+controls+"</div>");

    // Removes publish and edit if it's a section
    $("#list-projects div.section .bt-publish").remove();
    $("#list-projects div.section .bt-edit").remove();


    /**
     * Rename an element
     * !!! > this will not rename the project but just the link's text.
     *
     */
    $(".controls .bt-rename").click(function(){
        firstElem = $(this).parent().prev();
        if (newTitle = prompt("New item title:",firstElem.text())) {
            firstElem.text(newTitle);
            saveList();
        }
        return false;
    })


    /**
     * Delete a section or a project
     * this removes a level of hierarchy if needed
     * @todo needs to be tested properly
     */
    $(".controls .bt-delete").click(function(){
        parentDiv = $(this).parent().parent();
        if (parentDiv.hasClass("project")) {
            href = $(this).parent().prev().attr("href");
            href = href.substring(0,href.indexOf('/'));
            deleteProject(href);
        }
        //drops a level
        parentLi = parentDiv.parent();
        $("div",parentLi).eq(0).remove();
        $("ol",parentLi).eq(0).replaceWith($("ol",parentLi).eq(0).html());
        parentLi = parentLi.replaceWith(parentLi.html());
        saveList();
        return false;
    })

    /**
     * Go and edit the project
     * Rewrites the link
     */
    $(".controls .bt-edit").click(function(){
        parentDiv = $(this).parent().parent();
        if (parentDiv.hasClass("project")) {
            href = $(this).parent().prev().attr("href");
            project_name = href.substring(0,href.indexOf('/'));
            window.location = base_url+base_index+"admin_project_edit/"+project_name;
        }
        return false;
    })

    /**
     * Toggle status
     */
    $(".controls .bt-status").each(function(){
        parentDiv = $(this).parent().parent();
        if (parentDiv.hasClass('status-hidden')) $(this).addClass('status-hidden').text('hidden');
        if (parentDiv.hasClass('status-offline')) $(this).addClass('status-offline').text('offline');
        if (parentDiv.hasClass('status-online')) $(this).addClass('status-online').text('online');
    })

    $(".controls .bt-status").click(function(){
        parentDiv = $(this).parent().parent();
        if (parentDiv.hasClass('status-offline')) {
            oldClass = 'status-offline';
            newClass = 'status-online';
            newText  = 'online';
        } else if(parentDiv.hasClass('status-online')) {
            oldClass = 'status-online';
            newClass = 'status-hidden';
            newText  = 'hidden';
        } else if(parentDiv.hasClass('status-hidden')) {
            oldClass = 'status-hidden';
            newClass = 'status-offline';
            newText  = 'offline';
        }
        parentDiv.removeClass(oldClass).addClass(newClass);
        $(this).removeClass(oldClass).addClass(newClass).text(newText);
        saveList();
        return false;
    })
}
