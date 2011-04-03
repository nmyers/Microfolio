/* 
 * admin_project_menu.js
 *
 *
 *
 */

$(function() {

    sortable();
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
            newSection = '<li><div class="section" ><a>'+newTitle+"</a></div></li>\n";
            $("#menu-projects").append(newSection);
            addControls();
        }
        return false;
    })

    $("#savechanges").click(function(){
        saveMenu();
        return false;
    })

})


/**
 * Makes the menu sortable
 * using nested sortable
 *
 * @see http://mjsarfatti.com/sandbox/nestedSortable/
 *
 */
function sortable() {
    $('ol.sortable').nestedSortable({
        forcePlaceholderSize: true,
        handle: 'div',
        helper:	'clone',
        items: 'li',
        opacity: .6,
        placeholder: 'placeholder',
        tabSize: 25,
        tolerance: 'pointer',
        toleranceElement: '> div'
    });
}

/**
 * Adds a new project to the list
 * -> Ajax call
 *
 */
function newProject() {
    var project_name = prompt("New project name:");
    $.post(base_url+base_index+"admin_project_create/"+project_name,{
        ajax: true
    },function(data) {
        if (data=='1') {
            //@todo reload the list
            alert("project created!");
        } else {
            alert(data);
        }
    })
}

/**
 * Saves the menu
 * -> ajax call
 *
 */
function saveMenu() {
    $.post(base_url+base_index+"admin_projects_menu_save/",{
        ajax: true,
        menuhtml: $("#menu-projects").html()
    },function(data) {
        if (data=='1') {
            //reload
            alert("project saved!");
        } else {
            alert(data);
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
    $.post(base_url+base_index+"admin_project_delete/"+project_name,{
        ajax: true
    },function(data) {
        if (data=='1') {
            //reload
            alert("project deleted!");
        } else {
            alert(data);
        }
    })
}

/**
 * Adds a div with control links for each element of the menu
 * rename / edit / delete / publish / hide
 */
function addControls() {

    /**
     *  Adds controls to each project and section
     *  rename / edit / delete / publish / hide
     */
    $("#menu-projects .controls").remove();
    controls  = "<a href='#' class='bt-rename' >rename</a>";
    controls += "<a href='#' class='bt-edit' >edit</a>";
    controls += "<a href='#' class='bt-delete' >delete</a>";
    controls += "<a href='#' class='bt-publish' ><span>un</span>publish</a>";
    controls += "<a href='#' class='bt-hide' ><span>un</span>hide</a>";
    $("#menu-projects div").append("<div class='controls' >"+controls+"</div>");

    // Removes publish and edit if it's a section
    $("#menu-projects div.section .bt-publish").remove();
    $("#menu-projects div.section .bt-edit").remove();


    /**
     * Rename an element
     * @todo !!! > this will not rename the project but just the link..
     *
     */
    $(".controls .bt-rename").click(function(){
        firstElem = $(this).parent().prev();
        if (newTitle = prompt("New menu title:",firstElem.text()))
            firstElem.text(newTitle);
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
     * Publish or unpublish a project by toggling a class
     */
    $(".controls .bt-publish").click(function(){
        $(this).parent().parent().toggleClass("prj-unpublished");
        return false;
    })

    /**
     * Hide/unhide a project or a section by toggling a class
     * @todo might be better to remove the option of hiding a section?
     */
    $(".controls .bt-hide").click(function(){
        parentDiv = $(this).parent().parent();
        parentDiv.toggleClass("prj-hide");
        if (parentDiv.hasClass("prj-hide")) {
            $(".section, .project",parentDiv.parent()).addClass("prj-hide");
        } else {
            $(".section, .project",parentDiv.parent()).removeClass("prj-hide");
        }
        return false;
    })
}
