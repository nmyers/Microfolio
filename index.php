<?php
/**
 *  microFolio
 *
 *  A light portfolio CMS for PHP 5.
 *
 *  For more informations:
 *
 *  @author Nicolas Myers
 *  @copyright Copyright (c) 2011 Nicolas Myers
 *  @license http://opensource.org/licenses/mit-license.php The MIT License
 *
 */

/* -------------------------------------------------------------------
 *  CONFIG FUNCTIONS
 * -------------------------------------------------------------------
 */

define("MESSAGE_ERROR",    0);
define("MESSAGE_SUCCESS",  1);
define("MESSAGE_INFO",     2);
define("MESSAGE_LOADING",  3);

function config() {
    global $cfg;
    
    $cfg['admin_dir']       = 'app/';
    $cfg['cache_dir']       = 'app/cache/';
    $cfg['admin_style_dir'] = 'app/style/';
    $cfg['lib_dir']         = "app/lib/";
    $cfg['projects_dir']    = "content/";
    $cfg['style_dir']       = "style/";
    $cfg['tpl_dir']         = "tpl/";
    $cfg['js_dir']          = "js/";
    $cfg['css_dir']         = "css/";
    $cfg['theme']           = "default/";

    if (file_exists($cfg['admin_dir'].'config/config.php')) {
        include $cfg['admin_dir'].'config/config.php';
        $cfg = array_merge($cfg,$config);
        $cfg['theme'] = rtrim($cfg['theme'],'/').'/';
        // @see: http://codeigniter.com/forums/viewthread/81424/
        if (empty($cfg['base_url'])) {
            $cfg['base_url']  = "http://" . $_SERVER['HTTP_HOST'];
            $cfg['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
        }
    } else {
        die('no config found! please install.');
    }
}

function cfg($key) {
    global $cfg;
    return isset($cfg[$key]) ? $cfg[$key] : NULL;
}

/* -------------------------------------------------------------------
 *  ROUTING FUNCTIONS
 * -------------------------------------------------------------------
 */

$routes = array();

function dispatch($pattern,$function,$regex=false) {
    global $routes;
    if (!$regex) {
        $pattern = trim($pattern,'/');
        $pattern = '/^'.str_replace('/','\/',$pattern).'$/i';
        $pattern = str_replace('?','\?',$pattern);
        $pattern = str_replace('**', '(.+)', $pattern);
        $pattern = str_replace('*', '([^\/\?]+)', $pattern);
    }
    $routes[$pattern] = $function;
}

function param($i) {
    global $params;
    return isset($params[$i]) ? $params[$i] : null;
}

function router() {
    global $routes,$params;
    $query = trim(str_replace('q=','', $_SERVER['QUERY_STRING']),'/');
    //QUERY STRING replace the first ? by &, this corrects it:
    if (strpos($_SERVER['REQUEST_URI'], '?q=')===false && strpos($query,'&')!==false) $query{strpos($query,'&')}='?';
    foreach ($routes as $pattern => $function) {
        if (preg_match($pattern, $query, $matches)) {
            $call_function = $function;
            $params = $matches;
        }
    }
    if (!isset($call_function)) die('<br>not found '.$query);
    call_user_func($call_function);
}

/*
 * -------------------------------------------------------------------
 *  USER LOGIN FUNCTIONS
 * -------------------------------------------------------------------
 */

function logging($username, $password) {
    if (cfg('username') == $username && md5($username . $password) == cfg('password')) {
        $_SESSION['islogged'] = true;
        return true;
    }
    return false;
}

function logout() {
    $_SESSION['islogged'] = false;
}

function is_logged() {
    return isset($_SESSION['islogged']) ? $_SESSION['islogged'] : false;
}

//Check credentials
function in_admin() {
    if (!is_logged()) redirect('login');
    global $cfg;
    $cfg['in_admin'] = true;
}

/*
 * -------------------------------------------------------------------
 *  TEMPLATE FUNCTIONS
 * -------------------------------------------------------------------
 */

/**
 * Includes a template
 * This function will return the content to a string instead of printing it out
 * if the param $returnToString is set to true.
 *
 * @global array   $cfg
 * @param  string  $tpl
 * @param  array   $contentArray
 * @param  boolean $returnToString
 * @return string
 */
function output($tpl, $contentArray=array(), $returnToString=FALSE) {

    if(cfg('in_admin')) {
        $tpl = cfg('admin_style_dir') . cfg('tpl_dir') . $tpl;
    } else {
        $tpl = cfg('style_dir') . cfg('theme') . cfg('tpl_dir') . $tpl;
    }

    if (!file_exists($tpl))
        throw new Exception("Can't find template '$tpl'.");

    extract($contentArray, EXTR_OVERWRITE);
    $output = $contentArray;
    if ($returnToString) {
        ob_start();
        include($tpl);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    } else {
        include($tpl);
    }
}

function makeUrl($action) {
    return cfg('base_url') . cfg('base_index') . trim($action,'/');
}

function redirect($action="") {
    header("Location: " . makeUrl($action));
    die();
}

function includeJS($filename,$tag=true) {
    $js_filename = cfg('in_admin') ? cfg('admin_style_dir') : cfg('style_dir').cfg('theme');
    $js_filename .= cfg('js_dir').$filename;

    if (file_exists($js_filename)) {
        $js_url = cfg('base_url').$js_filename;
        if (!$tag) return $js_url;
        return "<script type=\"text/javascript\" src=\"$js_url\"></script>";
    }
}

function includeCSS($filename,$tag=true) {
    $css_filename = cfg('in_admin') ? cfg('admin_style_dir') : cfg('style_dir').cfg('theme');
    $css_filename .= cfg('css_dir').$filename;

    if (file_exists($css_filename)) {
        $css_url = cfg('base_url').$css_filename;
        if (!$tag) return $css_url;
        return "<link href=\"$css_url\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
    }
}

function warnings() {
    $html = '';
    if (!is_writable(cfg('admin_dir').'config/config.php'))
        $html.= "<div class='warning' ><span>Warning: your config file is not writable!</span></div>\n";
    if (!is_writable(cfg('projects_dir')))
        $html.= "<div class='warning' ><span>Warning: your content folder is not writable!</span></div>\n";
    return $html;
}

/* -------------------------------------------------------------------
 *  HELPERS
 * -------------------------------------------------------------------
 */

function projects() {
    global $projects;
    if (!isset($projects)) {
        include 'app/models.php';
        $projects = Projects::singleton();
    }
    return $projects;
}

function include_lib($filename) {
    require_once cfg('lib_dir').$filename;
}

function getPost($key,$sanitize=true) {
    if (!isset($_POST[$key])) return null;
    $val = stripslashes($_POST[$key]);
    if ($sanitize)  $val = filter_var($val, FILTER_SANITIZE_STRING);
    return $val;
}

/**
 * Returns an array of all the files matching the pattern
 *
 * @param  string   $dir
 * @param  string   $pattern  Regex pattern to filter the files
 * @return array              Array of files
 */
function getFiles($dir, $pattern='/.*/') {
    $files = scandir($dir);
    $result = array();
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && preg_match($pattern, $file))
                $result[] = $file;
    }
    return $result;
}

/* -------------------------------------------------------------------
 *  START
 * -------------------------------------------------------------------
 */

function run() {
    session_start();
    
    config();
    
    include cfg('admin_dir').'controllers.php';
    include cfg('lib_dir').'/phpconsole/PhpConsole.php';

    PhpConsole::start();


    router();
}

run();