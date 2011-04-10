<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <!-- metas -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <!-- css -->
    <?=includeCSS("style.css")?>
    
    <!-- global javascript -->
    <script type="text/javascript" >
        var base_url   = '<?=cfg('base_url')?>';
        var base_dir   = '<?=cfg('base_dir')?>';
        var base_index = '<?=cfg('base_index')?>';
        var lib_dir    = '<?=cfg('lib_dir')?>';
        //var theme_dir  = '<?=cfg('style_dir').cfg('theme')?>';
    </script>
    <?=includeJS("jquery-1.5.2.min.js")?>
    <?=includeJS("jquery-ui-1.8.11.custom.min.js");?>
    <?=includeJS("jquery.layout.js")?>
    
    <script type="text/javascript">

	var myLayout; // a var is required because this page utilizes: myLayout.allowOverflow() method

	$(document).ready(function () {

		myLayout = $('body').layout({
			west__size:			500
		,	west__spacing_closed:		20
		,	west__togglerLength_closed:	100
		,	west__togglerAlign_closed:	"top"
		,	west__togglerContent_closed:"M<BR>E<BR>N<BR>U"
		,	west__togglerTip_closed:	"Open & Pin Menu"
		,	west__sliderTip:			"Slide Open Menu"
		,	west__slideTrigger_open:	"mouseover"
		});

 	});

	</script>

          
