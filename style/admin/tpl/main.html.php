<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <!-- metas -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <!-- css -->
    <link rel="stylesheet" type="text/css" href="<?=$cfg['base_url'].$cfg['style_dir']?>/admin/css/style.css" media="screen" />
    <link href="<?=$cfg['base_url'].$cfg['lib_dir']?>/ajaxupload/fileuploader.css" type="text/css" rel="stylesheet" />
    <link type="text/css" href="<?=$cfg['base_url'].$cfg['style_dir']?>/admin/css/smoothness/jquery-ui-1.8.11.custom.css" rel="stylesheet" />
    <!-- javascript -->
    
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js" ></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js" ></script>
    <script type="text/javascript" src="<?=$cfg['base_url'].$cfg['lib_dir']?>/jquery.form.js"></script>
    
    <link href="fileuploader.css" rel="stylesheet" type="text/css">	
    
    <title></title>
  </head>
  <body>
<!-- start div page -->
<div id="page" >

    <div id="left" >
    <h3>Prj</h3>
    <br />
    <br />
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
