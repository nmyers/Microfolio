<h1>Filemanager</h1>
<br />
<?=$msg?>
<br />
<h2>Pages</h2>
<br />
<table class="list" >
<?foreach($files as $file):?>
    <tr>
        <td><a href="<?=makeUrl('filemgr/delete/'.$file);?>" >delete</a></td>
        <td><?=$file?></td>
    </tr>
<?endforeach;?>
<table>
<br /><br /><br /><br />
<h2>Images</h2>
<br />
<?=uploadFile()?><br /><br />
<table class="list">
<?foreach($images as $image):?>
<tr>
    <td><a href="<?=makeUrl('filemgr/delete/'.$image);?>" >delete</a></td>
    <td><img src='<?=$cfg['base_url'].'media/'.$image?>' width=50 height=50 /></td>
    <td><?=$image?></td>
</tr>
<?endforeach;?>
</table>
