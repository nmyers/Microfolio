<?output('_header.html.php',$output);?>

    <!-- custom javascript -->
    <script type="text/javascript" >
        var default_url = '<?= makeUrl(cfg('default_uri')) ?>';
    </script>
    <?=includeJS("jquery-ui-1.8.11.custom.min.js");?>
    <?=includeJS("jquery.ui.nestedSortable.js");?>
    <?=includeJS("admin_projects_list.js");?>

    
<?output('_header2.html.php',$output);?>
    <div class="pad" >
        <a href="#newproject" class="button">add new project</a>
        <div id="list-holder" >
            <?=projects()->getMenu(false)?>
        </div>
    </div>
<?output('_footer.html.php',$output);?>