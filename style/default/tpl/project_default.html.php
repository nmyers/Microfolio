<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <!-- metas -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- css -->
    <?=includeCSS("style.css")?>

    <title><?=$project['title']?></title>
</head>
    <body>
    <div id="menu">
        <?php output('_menu.html.php',$output); ?>
    </div>
    <div id="project" class="" >

        <h1 id="title" ><?=$project['title']?></h1>

        <div id="presentation" >
            <?=$project['presentation']?>
        </div><!-- end of presentation text -->

        <div id="gallery" >
            <?=$project['gallery']?>
        </div><!-- end of gallery -->

    </div><!-- end of project -->

    </body>
</html>