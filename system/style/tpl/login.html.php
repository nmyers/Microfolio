<?output('_header.html.php',$output);?>
<?output('_menu.html.php',$output);?>
<div class="loginform" >
<form name="logform" method=post action="<?=makeUrl("/dologin/");?>">
    <label>Username</label><input type="text" name="username" value="" /><br />
    <label>Password</label><input type="password" name="password" value="" /><br />
    <div class="buttons" >
    <a class="button" href="#" onclick="document.logform.submit();" >login</a>
    <a class="button" href="<?=makeUrl("index");?>" >cancel</a>
    </div>
</form>
</div>
<?output('_footer.html.php',$output);?>