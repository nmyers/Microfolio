
    <title>Microfolio :: <?=$admin_title?></title>
</head>


<iframe id="mainFrame" name="mainFrame" class="ui-layout-center"
	width="800" height="600" frameborder="0" scrolling="auto"
	src=""></iframe>

<div class="ui-layout-west">
<div id="design" >&nbsp;</div>
<div id="header" >
    <div class="in" >
        <div class="middle" >
            <a href="#" >Microfolio</a>
        </div>
        <div class="left" >
            <a href="<?=makeUrl('admin/projects');?>" ><?=$admin_title?></a>
        </div>
        
        <div class="right" >
            <ul id="menu">
                
                <li><a href="<?=makeUrl('admin/settings');?>" >Settings</a></li>
                <li><a href="<?=makeUrl('admin/help');?>" >Help</a></li>
                <li class="last" ><a href="<?=makeUrl('logout');?>" >Logout</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="warnings" >
    <?=warnings();?>
</div>
<div id="main" >