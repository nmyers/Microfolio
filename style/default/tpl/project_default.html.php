<?output('_header.html.php',$output);?>

<h1 id="title" ><?=$project->title?></h1>
<div id="presentation" >
    <?=$project->text?>
</div><!-- end of presentation text -->

<?=$project->render()?>

<?output('_footer.html.php',$output);?>
