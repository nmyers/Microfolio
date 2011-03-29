<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <!-- metas -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <!-- css -->
    <link rel="stylesheet" type="text/css" href="<?=$cfg['base_url'].$cfg['style_dir']?>css/reset.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?=$cfg['base_url'].$cfg['style_dir']?>css/style.css" media="screen" />
    
    <!-- javascript -->
    <script type="text/javascript" src="<?=$cfg['base_url'].$cfg['style_dir']?>js/jquery.js" ></script>
    
    <script type="text/javascript">
    </script>
    
    <title></title>
  </head>
  <body>
<!-- start div page -->
<div id="page" >

    <div id="left" >
    <h3>Min_wiki</h3>
    <br />
    <br />
    <?=renderPage('menu');?>
    <br /><br /><br />
    <?if (is_logged()):?><a href="<?=makeUrl("editpage/".$pagename);?>" >edit page</a><?endif;?>
    <?if (is_logged()):?><a href="<?=makeUrl("logout");?>" >disconnect</a><?endif;?>
    <?if (!is_logged()):?><a href="<?=makeUrl("login");?>" >connect</a><?endif;?>
    </div>

    <div id="content" >
        <?=$content?>
    </div>
</div>
<!-- end div page -->
</body>
</html>
