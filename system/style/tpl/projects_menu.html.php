<?output('_header.html.php',$output);?>

    <!-- custom javascript -->
    <?=includeJS("jquery-ui-1.8.11.custom.min.js");?>
    <?=includeJS("jquery.ui.nestedSortable.js");?>
    <?=includeJS("admin_projects_menu.js");?>
    
<?output('_menu.html.php',$output);?>
    <div class="pad" >
        <a href="#" id="newproject"  class="button">new project</a>
        <a href="#" id="addsection"  class="button">add section</a>
        <a href="#" id="savechanges" class="button">save changes</a>
        <div id="menu-holder" >
            <?=$menu?>
        </div>
    </div>
<?output('_footer.html.php',$output);?>