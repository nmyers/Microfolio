<?php

set_thumbnails($project,1,200);
set_images($project,0);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <!-- metas -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- css -->
    <?=includeCSS("style.css")?>
    <?=includeCSS("jquery.fancybox-1.3.4.css")?>

    
    <?=includeJS("jquery-1.5.1.min.js")?>
    <?=includeJS("jquery.fancybox-1.3.4.pack.js")?>
    
    <script>
        $(function() {
            $("#gallery a").attr('rel','gallery');
            $("#gallery a").fancybox({
				'titlePosition'	: 'inside'
            });
        });
    </script>


    <title><?=$project->title?></title>
</head>
    <body>
    <div id="menu">
        <?=$menu?>
    </div>
    <div id="project" class="" >

        <h1 id="title" ><?=$project->title?></h1>

        <div id="presentation" >
            <?=$project->presentation?>
        </div><!-- end of presentation text -->

        <div id="gallery" >
            <?=$project->gallery?>
        </div><!-- end of gallery -->
              

    </div><!-- end of project -->

    </body>
</html>