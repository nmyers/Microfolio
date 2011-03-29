/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(function() {
        $('#newproject').click(function() {
            newProject();
            return false;
        })

        $('#serialize').click(function() {
            alert($("#menu-projects").html());
        })


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
                
        addControls();

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

function newProject() {
    var project_name = prompt("New project name:");
    $.post(base_url+"index.php/admin_project_create/"+project_name,{
           ajax: true
        },function(data) {
           if (data=='1') {
               //reload
               alert("project created!");
           } else {
               alert(data);
           }
    })
}

function saveMenu() {
    $.post(base_url+"index.php/admin_projects_menu_save/",{
           ajax: true,
           menuhtml: $("#menu-projects").html()
        },function(data) {
           if (data=='1') {
               //reload
               alert("project created!");
           } else {
               alert(data);
           }
    })
}

function deleteProject(project_name) {
    $.post(base_url+"index.php/admin_project_delete/"+project_name,{
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

function addControls() {
    $("#menu-projects .controls").remove();
    controls  = "<a href='#' class='bt-rename' >rename</a>";
    controls += "<a href='#' class='bt-edit' >edit</a>";
    controls += "<a href='#' class='bt-delete' >delete</a>";
    controls += "<a href='#' class='bt-publish' ><span>un</span>publish</a>";
    controls += "<a href='#' class='bt-hide' ><span>un</span>hide</a>";

    $("#menu-projects div").append("<div class='controls' >"+controls+"</div>");
    $("#menu-projects div.section .bt-publish").remove();
    $("#menu-projects div.section .bt-edit").remove();

    $(".controls .bt-rename").click(function(){
        firstElem = $(this).parent().prev();
        if (newTitle = prompt("New menu title:",firstElem.text()))
            firstElem.text(newTitle);
        return false;
    })

    /**
     * Delete a section or a project
     */
    $(".controls .bt-delete").click(function(){
        parentDiv = $(this).parent().parent();
        if (parentDiv.hasClass("section")) {
            //this drops one level of hierarchy
            //so we get rid of the section title but not it's content
            parentLi = parentDiv.parent();
            $("div.section",parentLi).eq(0).remove();
            $("ol",parentLi).eq(0).replaceWith($("ol",parentLi).eq(0).html());
            parentLi = parentLi.replaceWith(parentLi.html());
            return false;
        }
        if (parentDiv.hasClass("project")) {
            href = $(this).parent().prev().attr("href");
            href = href.substring(0,href.indexOf('/'));
            deleteProject(href);
        }
    })
    $(".controls .bt-edit").click(function(){
        parentDiv = $(this).parent().parent();
        if (parentDiv.hasClass("project")) {
            href = $(this).parent().prev().attr("href");
            project_name = href.substring(0,href.indexOf('/'));
            window.location = base_url+"index.php/admin_project_edit/"+project_name;
        }
        return false;
    })
    $(".controls .bt-publish").click(function(){
        $(this).parent().parent().toggleClass("prj-publish");
        return false;
    })
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
