<?php output('_header.html.php',$output); ?>

    <?=includeCSS("uEditor.css");?>
    <?=includeCSS("fileuploader.css");?>

    <!-- custom javascript -->
    <?=includeJS("fileuploader.js");?>
    <?=includeJS("uEditor.js");?>
    <?=includeJS("admin_project_edit.js");?>

    <script type="text/javascript" >
        var project_name = '<?=$project_name?>';
    </script>

<?output('_menu.html.php',$output);?>
    <div class="top-fixed" >
    <div class="pad" >
    
    <input type="text" id="project_title" value="<?=$title?>" >

    <select id="template">
    <?php foreach($templates as $template): ?>
        <option value="<?=$template?>" <?php if($settings['template']==$template) echo 'selected' ?> ><?=$template?></option>
    <?php endforeach; ?>
    </select>

    <div class="buttons" >
    <a href="#" class="button status-<?= $prj_settings['status']; ?>" id="status" ><?= $prj_settings['status']; ?></a>
    <a href="#" class="button" id="saveproject" >save</a>
    </div>
    
    </div>
    </div>

    <div class="pad" >

    <textarea id="project_text" name="project_text" ><?=$text?></textarea>
   
    
    
    <div id="gallery_container" >
        
        <ul class="toolbar" >
            <li id="file-uploader" ></li>
            <li><a href="#" id="addembed" >add embed</a></li>
        </ul>


    <?php print $gallery ?>
    

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

    </div>

    </div>


<?php output('_footer.html.php',$output); ?>