<form name="sendmessage" method=post action="<?=makeUrl("doeditpage/".$pagename);?>">
<a href="#" onclick="document.sendmessage.submit();" >update page</a>&nbsp;&nbsp;
<a href="#" onclick="history.go(-1)" >cancel</a><br /><br />
<textarea name="wikicontent" style="font-family:courier;font-size:9px;line-height:15px;width:700px;height:600px;" ><?=$wikicontent?></textarea><br /><br />
</form>
