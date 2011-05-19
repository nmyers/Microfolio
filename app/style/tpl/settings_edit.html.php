<?output('_header.html.php',$output);?>
<?= includeJS("jquery.form.js"); ?>
<script>
$(document).ready(function() {
                $('a[href=#savesettings]').click(function() {
                    $('#settingsform').ajaxSubmit({
			success: function (json) {
                            var data = jQuery.parseJSON(json);
                            showMessage(data.message,data.message_type);
                        }
                    });
                    return false;
                })
	});
</script>

<?output('_header2.html.php',$output);?>
<? if(is_writable(cfg('admin_dir').'config/config.php')): ?>
<div class="settings-top" >
<a href="#savesettings" class="button savesettings">save settings</a>
</div>
        <div id="list-settings" >
            <form id="settingsform" name="settingsform" method=post action="<?=makeUrl("admin/settings");?>">
            <input type="hidden" name="posted" value="1" />
            <? foreach($config as $key => $value): ?>
            <div class="setting" >
                <label><?=ucfirst(str_replace('_', ' ', $key))?></label>
            <input type="<?=stripos($key,'password')!==FALSE?'password':'text'?>" name="<?=$key?>" value="<?=$value?>" />
            </div>
            <? endforeach; ?>
            </form>
        </div>
<? else: ?>
<div>
    Your configuration file is not writable. <br />
    You have to change the permission of this file before proceeding:<br />
    <ol>
        <li>Connect to your FTP</li>
        <li>Go to the folder app/config</li>
        <li>Change the permissions of the file 'config.php </li>
    </ol>
</div>
<? endif; ?>
<?output('_footer.html.php',$output);?>