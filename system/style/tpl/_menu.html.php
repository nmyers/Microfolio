

    <title>Microfolio :: <?=$admin_title?></title>
</head>


<iframe id="mainFrame" name="mainFrame" class="ui-layout-center"
	width="800" height="600" frameborder="0" scrolling="auto"
	src="<?= makeUrl("project/test")?>"></iframe>

<div class="ui-layout-west">
<div id="design" >&nbsp;</div>
<div id="header" >
    <div class="in" >
        <div class="left" >
            <a href="<?=makeUrl('admin_projects_menu');?>" ><?=$admin_title?></a>
        </div>
        <div class="right" >
            <ul id="menu">
                <li><a href="#" >Microfolio</a></li>
                <li><a href="<?=makeUrl('admin_settings');?>" >Settings</a></li>
                <li class="last" ><a href="<?=makeUrl('logout');?>" >Logout</a></li>
            </ul>
        </div>
    </div>
</div>
<div id="main" >