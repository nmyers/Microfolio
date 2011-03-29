<h1>Page Manager</h1>
<a href="<?=makeUrl('admin_project_edit/new');?>" >new</a>
<?foreach($projects as $project):?>
<div>
<a href="<?=makeUrl('admin_project_edit/'.$project);?>" >edit</a>
<?=$project?>
</div>
<?endforeach;?>

