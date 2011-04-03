<?output('_header.html.php',$output);?>
<?output('_menu.html.php',$output);?>
    <form name="logform" method=post action="<?=makeUrl("/dologin/");?>">
        <h1>Login</h1><br />
        <label>Username</label><input type="text" name="username" value="" /><br />
        <label>Password</label><input type="password" name="password" value="" /><br />
        <br />
        <a href="#" onclick="document.logform.submit();" >login</a>&nbsp;&nbsp;
        <a href="<?=makeUrl("index");?>" >cancel</a><br /><br />
    </form>
<?output('_footer.html.php',$output);?>