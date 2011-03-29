<?php output('_header.html.php',$output); ?>

    <!-- custom javascript -->
    <?=includeJS("jquery-ui-1.8.11.custom.min.js");?>
    <?=includeJS("fileuploader.js");?>
    <?=includeJS("admin_project_edit.js");?>

    <script type="text/javascript" >
        var project_name = '<?=$project_name?>';
    </script>

    <!-- custom css -->
    <?=includeCSS("fileuploader.css");?>

<?output('_menu.html.php',$output);?>
<h1>Project</h1>
<form id="projectform" name="projectform" >
    <a href="#" id="saveproject" >save page</a>
    <input type="text" id="project_title" value="<?=$title?>" >
    <textarea id="project_text" ><?=$text?></textarea>      
    <div id="file-uploader"></div> 
    <?php print $gallery ?>
</form>
<?php output('_footer.html.php',$output); ?>