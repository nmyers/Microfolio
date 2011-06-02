<?output('_header.html.php',$output);?>
<table>
    <tr>
        <td>
            <div class="intro" >
                <h1 id="title" ><?=$project->title?></h1>
                <div id="presentation" >
                    <?=$project->text?>
                </div>
            </div>
        </td>
        <? foreach($project as $item): ?>
        <td>
            <?=$item->render()?>
            <div class="caption" ><?=$item->caption?></div>
        </td>
        <? endforeach; ?>
    </tr>
</table>
<?output('_footer.html.php',$output);?>