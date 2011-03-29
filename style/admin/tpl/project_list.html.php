<?output('_header.html.php',$output);?>
<?output('_menu.html.php',$output);?>
<h2>Page Manager</h2>
<a href="<?=makeUrl('admin_project_edit/new');?>" >new</a>
<?foreach($projects as $project):?>
    <div>
    <a href="<?=makeUrl('admin_project_edit/'.$project);?>" >edit</a>
    <?=$project?>
    </div>
<?endforeach;?>
<?output('_footer.html.php',$output);?>