<?output('_header.html.php',$output);?>

    <!-- custom javascript -->
    <?=includeJS("jquery-ui-1.8.11.custom.min.js");?>
    <?=includeJS("jquery.ui.nestedSortable.js");?>
    <?=includeJS("admin_projects_list.js");?>
    
<?output('_menu.html.php',$output);?>
    <div class="pad" >
        <a href="#" id="newproject"  class="button">new project</a>
        <a href="#" id="addsection"  class="button">add section</a>
        <div id="list-holder" >
            <ol id="projects" >
            <?=$projects->list?>
            </ol>
        </div>
    </div>
<?output('_footer.html.php',$output);?>