<?php output('_header.html.php',$output); ?>

    <?=includeCSS("uEditor.css");?>

    <!-- custom javascript -->
    <?=includeJS("jquery-ui-1.8.11.custom.min.js");?>
    <?=includeJS("fileuploader.js");?>

    <?=includeJS("uEditor.js");?>

    <?=includeJS("admin_project_edit.js");?>

    <script type="text/javascript" >
        var project_name = '<?=$project_name?>';
    </script>

    <!-- custom css -->
    <?=includeCSS("fileuploadera.css");?>

<?output('_menu.html.php',$output);?>
<h1>Project</h1>

    <a href="#" id="saveproject" >save page</a>
    <input type="text" id="project_title" value="<?=$title?>" >
    <textarea id="project_text" name="project_text" ><?=$text?></textarea>
    
    <div id="file-uploader"></div>

    <div id="gallery_container" >
    <?php print $gallery ?>
    </div>

    <div id="edit_media_dialog" >
        <div id="edit_image_form" >
            <div class="preview" >
                <img src="" width="200" />
            </div>
            <div class="form" >
                <label for="image_title" >Image title</label>
                <input id="image_title" type="text" value="title..." >
                <label for="image_caption" >Image Caption</label>
                <textarea id="image_caption" >caption...</textarea>
            </div>
        </div>
        <div id="edit_embed_form" >
            <div class="preview" >
                EMBED
            </div>
            <div class="form" >
                <label for="embed_url" >Embed url (http://vimeo.com/21429986)</label>
                <input id="embed_url" type="text" value="http://vimeo.com/" >
                <label for="embed_title" >Image title</label>
                <input id="embed_title" type="text" value="title..." >
                <label for="embed_caption" >Image Caption</label>
                <textarea id="embed_caption" >caption...</textarea>
            </div>
        </div>
        <div id="edit_media_controls" >
            <a href="#" class="save" >save</a>
            <a href="#" class="cancel" >cancel</a>
        </div>
    </div>



<?php output('_footer.html.php',$output); ?>