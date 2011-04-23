<?php output('_header.html.php',$output); ?>

    <?=includeCSS("uEditor.css");?>
    <?=includeCSS("fileuploader.css");?>

    <!-- custom javascript -->
    <?=includeJS("fileuploader.js");?>
    <?=includeJS("uEditor.js");?>
    <?=includeJS("admin_project_edit.js");?>

    <script type="text/javascript" >
        var project_name = '<?=$project->name?>';
    </script>

<?output('_menu.html.php',$output);?>
    <div class="top-fixed" >
    <div class="pad" >
    
    <input type="text" id="project_title" value="<?=$project->title?>" >



    <div class="buttons" >
    <a href="#" class="button status-" id="status" ></a>
    <a href="#" class="button" id="saveproject" >save</a>
    </div>
    
    </div>
    </div>

    <div class="pad" >

    <textarea id="project_text" name="project_text" ><?=$project->presentation?></textarea>
   
    
    
    <div id="gallery_container" >
        
        <ul class="toolbar" >
            <li id="file-uploader" ></li>
            <li><a href="#" id="addembed" >add embed</a></li>
        </ul>

        <div id="gallery_content" >
            <div id="gallery" >
                <?=$project->gallery?>
            </div>
        </div>

        <div id="edit_media_dialog" >
            <div id="edit_image_form" >
                <div class="preview" >
                    <a href="#" target="_blank" >
                    <img src=""/>
                    </a>
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
                <a href="#" class="button cancel" >cancel</a>
                <a href="#" class="button save" >save</a>
            </div>
        </div>

    </div>

    </div>


<?php output('_footer.html.php',$output); ?>