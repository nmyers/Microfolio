<style>
	#media_container { list-style-type: none; margin: 0; padding: 0; }
	#media_container li { margin: 3px 3px 3px 0; padding: 1px; float: left; width: 100px; height: 100px; font-size: 4em; text-align: center;background:#ccc }
</style>
<script src="http://localhost/microwiki/lib/ajaxupload/fileuploader.js" type="text/javascript"></script>
<script>
    function createUploader() {            
            var uploader = new qq.FileUploader({
                element: document.getElementById('file-uploader-demo1'),
                action: '/microwiki/lib/ajaxupload/upload.php',
                allowedExtensions: ['jpg'],
                onComplete: function(id, fileName, responseJSON){
                    $('#ajaxmedia').load('<?=makeUrl('admin_project_edit/'.str_replace(' ','%20',$project_file));?> #ajaxmedia');
                    createSortable();
                },
                params: {
                    folder: '<?=dirname($project).'/'?>'
                }
            });           
    }     
    
    function createSortable() {
        $( "#media_container" ).sortable({
               update: function(event, ui) {
                 }
        });
    }        
    
    function saveProject() {
        $.post("<?=makeUrl("admin_project_save/".$project_file);?>",{
           title: 'title', 
           text:  'text',
           media: 'media'
        })
    }
    
    $('#saveproject').click(function() {
        alert($("#media_container").html());
        return false; 
    })  
    
    $(function() { 
        createUploader();
        createSortable()
    
    
    $( "#dialog-form" ).dialog({
			autoOpen: false,
			height: 300,
			width: 350,
			modal: true,
			buttons: {
				"Save": function() { } ,
				Cancel: function() {
					$( this ).dialog( "close" );
				}
				
			}
			});
			
			$( "#create-user" )
			.button()
			.click(function() {
				$( "#dialog-form" ).dialog( "open" );
			});
			
			
			
    }); 
</script> 

<h1>Project</h1>
<form id="projectform" name="projectform" >
    <a href="#" id="saveproject" >save page</a>
    <fieldset>
        <label for="project_title">Title</label>
        <input type="text" name="project_title" value="<?=$project->title?>" >
        <label for="project_text">Text</label>
        <textarea name="project_text" id="project_text" ><?=$project->text?></textarea>      
    </fieldset>
    <div id="file-uploader-demo1"></div>
    <div id='ajaxmedia' >
    <ul id='media_container' >
        <?foreach($project->medialist->media as $key=>$media):?>
        <li>
            <img src="<?=$project_url.$media->filename?>" width=50 />
             <div class="title" ></div>
             <div class="caption" ></div>
        </li>
        <?endforeach;?>
    </ul>
    </div>
</form>

<div id="dialog-form" title="Create new user">
	<p class="validateTips">All form fields are required.</p>

	<form>
	<img src="" />
	<fieldset>
		<label for="title">Title</label>
		<input type="text" name="title" id="title" class="text ui-widget-content ui-corner-all" value="" />
		<label for="caption">Caption</label>
		<textarea name="caption" id="caption" class="text ui-widget-content ui-corner-all" ></textarea>
	</fieldset>
	</form>
</div>
<button id="create-user">Create new user</button>


        