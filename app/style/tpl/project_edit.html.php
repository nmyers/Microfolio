<? output('_header.html.php', $output) ?>

<?= includeCSS("uEditor.css"); ?>

<!-- custom javascript -->
<?= includeJS("jquery.oembed.min.js"); ?>
<?= includeJS("jquery.dropp.js"); ?>
<?= includeJS("jquery.html5_upload.js"); ?>
<?= includeJS("uEditor.js"); ?>
<?= includeJS("admin_project_edit.js"); ?>

<script type="text/javascript" >
    var project_slug = '<?= $project->slug ?>';
    var editorCSSUrl = '<?= includeCSS('editor_style.css', false) ?>';
</script>

<? output('_header2.html.php', $output) ?>

<div class="top-fixed" >
    <div class="pad" >
        <div id="slug" ><?= makeUrl('project/' . $project->slug) ?>
            <a href="#editslug" >[ edit ]</a>
        </div>
        <div id="title_section">
            <div class="pad" >
        <input type="text" id="project_title" value="<?= $project->title ?>" >
        
            </div>
        </div>

        

        <div class="template-select button" >
            <select id="template">
                <?php foreach ($templates as $template): ?>
                    <option value="<?= $template ?>" <?php if ($project->style == $template) echo 'selected' ?> ><?= $template ?></option>
                <?php endforeach; ?>
             </select>
        </div>
        <div class="buttons" >
            <a href="#status" class="button status-<?= $project->status ?>" data-status="<?=$project->status?>" id="status" ><?= $project->status ?></a>
            <a href="#saveproject" class="button" >save</a>
        </div>
    </div>
</div>

        <div class="pad" >

            <textarea id="project_text" name="project_text" ><?= $project->text ?></textarea>

            <div id="gallery_container" >

                <ul class="toolbar" >
                    <li>
                        <div class="uploadbutton" >
                            <input type="file" multiple="multiple" id="upload_field" />
                            <span>upload a file</span>
                        </div>
                    </li>
                    <li><a href="#addembed" >add embed</a></li>
                </ul>

                <div id="gallery_content" >
                    <div id="gallery" >
<? foreach ($project as $key => $item): ?>
                                <div class="item" id="<?= $key ?>" data-src="<?= $item->src ?>" data-type="<?= $item->type ?>" >
<?= $item->render('t72x72') ?>
                                <div class="caption" ><?= $item->caption ?></div>
                            </div>
<? endforeach; ?>
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
                                <a href="#cancel" class="button" >cancel</a>
                                <a href="#save" class="button" >save</a>
                            </div>
                        </div>

                    </div>

                </div>

<? output('_footer.html.php', $output) ?>