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
 */

/* -------------------------------------------------------------------
 *  CONFIG FUNCTIONS
 * -------------------------------------------------------------------
 */

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

    if (file_exists($cfg['admin_dir'].'config/config.php')) {
        include $cfg['admin_dir'].'config/config.php';
        //cleanup theme
        $cfg['theme'] = rtrim($cfg['theme'],'/').'/';
        // @see: http://codeigniter.com/forums/viewthread/81424/
        if (!isset($cfg['base_url'])) {
            $cfg['base_url']  = "http://" . $_SERVER['HTTP_HOST'];
            $cfg['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
        }
        if (!isset($cfg['base_dir'])) {
            $cfg['base_dir'] = realpath('.').'/';
            $cfg['base_dir'] = str_replace("\\","/",rtrim($cfg['base_dir'], '/').'/');
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
        $pattern = rtrim($pattern,'/');
        $pattern = '/^'.str_replace('/','\/',$pattern).'$/i';
        $pattern = str_replace('**', '([\w\/]+)', $pattern);
        $pattern = str_replace('*', '([^\/]+)', $pattern);
    }
    $routes[$pattern] = $function;
}

function param($i) {
    global $params;
    return isset($params[$i]) ? $params[$i] : null;
}

function router() {
    global $routes,$params;
    $query = '';
    $first = true;
    foreach ($_GET as $key => $item) {
        if (!empty($item)) $query .= $first ? '?' : '&';
        $query .= empty($item) ? $key : $key.'='.$item;
        if (!empty($item)) $first = false;
    }
    $query = rtrim($query,'/');
    print_r($routes);
    $call_function = 'default';
    foreach ($routes as $pattern => $function) {
        if (preg_match($pattern, $query, $matches)) {
            $call_function = $function;
            $params = $matches;
        }
    }
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

    if (!file_exists($tpl)) redirect();

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
    return cfg('base_url') . cfg('base_index') . $action;
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

/* -------------------------------------------------------------------
 *  HELPERS
 * -------------------------------------------------------------------
 */

function projects() {
    global $projects;
    if (!isset($projects)) {
        include 'app/model-n.php';
        $projects = Projects::singleton();
    }
    return $projects;
}


/* -------------------------------------------------------------------
 *  CONTROLLERS
 * -------------------------------------------------------------------
 */

dispatch('/showproject/*/','ctrl_project_view');
function ctrl_project_view() {
    print_r(projects()->getProject(param(1)));
}


/* -------------------------------------------------------------------
 *  START
 * -------------------------------------------------------------------
 */

function run() {
    session_start();

    include 'app/lib/phpconsole/PhpConsole.php';
    PhpConsole::start();
    
    config();
    router();
}

run();
