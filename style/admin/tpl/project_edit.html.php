<?php output('_header.html.php',$output); ?>
<script type="text/javascript" >
    var project_name = '<?=$project_name?>';
</script> 
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