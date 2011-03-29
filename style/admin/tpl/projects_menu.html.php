<?output('_header.html.php',$output);?>

    <!-- custom javascript -->
    <?=includeJS("jquery-ui-1.8.11.custom.min.js");?>
    <?=includeJS("jquery.ui.nestedSortable.js");?>
    <?=includeJS("admin_projects_menu.js");?>
    
<?output('_menu.html.php',$output);?>
<h2>Page Manager</h2>
<a href="#" id="newproject" >new project</a>
<a href="#" id="addsection" >add section</a>
<a href="#" id="savechanges" >save changes</a>
<?=$menu?>
<?output('_footer.html.php',$output);?>