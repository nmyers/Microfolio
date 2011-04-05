<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <!-- metas -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Install microfolio</title>
</head>
<body>
    <?=$error?>
    <form name="logform" method=post action="<?=htmlentities($_SERVER['SCRIPT_NAME'])?>">
        <h1>Create a new username and password</h1><br />
        <label>Username</label><input type="text" name="username" value="" /><br />
        <label>Password</label><input type="password" name="password" value="" /><br />
        <label>Password (again)</label><input type="password" name="password2" value="" /><br />
        <br />
        <a href="#" onclick="document.logform.submit();" >save</a>
    </form>
</body>
</html>