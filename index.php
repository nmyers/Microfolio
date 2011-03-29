<?php
/**
 * Microfolio
 *
 * An open source portfolio CMS for PHP 5.3 or newer! (see todo)
 *
 * @author		Nicolas Myers
 * @version             0.1
 */

/*
 * --------------------------------------------------------------------
 * Configuration
 * --------------------------------------------------------------------
 */

$cfg = array (

    //default controller
    'default_ctrl' => "index",

    //dir names
    'lib_dir'      => "lib/",
    'projects_dir' => "projects/",
    'style_dir'    => "style/",
    'tpl_dir'      => "tpl/",
    'theme'        => "default/",

    //users
    'users'        => array('admin' => '122442'),
);

/**
 * Automatic Base URL
 * from: http://codeigniter.com/forums/viewthread/81424/
 */
$cfg['base_url']  = "http://" . $_SERVER['HTTP_HOST'];
$cfg['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

//Uncomment the following line to override
//$cfg['base_url']  = "http://localhost/microfolio";


/*
 * --------------------------------------------------------------------
 * PUBLIC CONTROLLERS
 * --------------------------------------------------------------------
 */

/**
 * Public Controller - Index
 * Default controller
 */
function ctrl_index() {
    echo "Hello micro";
}

/**
 * Public Controller - Login page
 * @global array $cfg
 */
function ctrl_login() {
    global $cfg;
    $cfg['theme'] = 'admin/';
    output("login.html.php");
}

/**
 * Public Controller - DoLogin
 * Test the login and redirects
 *
 * @global array $cfg
 */
function ctrl_dologin() {
    if (miniLog($_REQUEST['username'], $_REQUEST['password'])) {
        //@todo: change to var in config
        redirect("admin_project_list");
    } else {
        redirect("login");
    }
}

/**
 * Public controller - Logout
 * Forces a logout and redirects to the login page.
 */
function ctrl_logout() {
    logout();
    redirect("/login");
}

/*
 * --------------------------------------------------------------------
 * ADMIN CONTROLLERS
 * --------------------------------------------------------------------
 *
 */

/**
 * Admin Controller - Project Edit
 * @todo ...a lot
 * @global  $cfg
 */
function ctrl_admin_project_list() {
    global $cfg;
    $output['projects'] = getDirs($cfg['projects_dir']);
    output("project_list.html.php", $output);
}

/**
 * Admin Controller - Project Edit
 * This is where the action is
 *
 * @global  $cfg
 * @param <type> $project_name
 */
function ctrl_admin_project_edit($project_name) {
    global $cfg;

    $project_dir = $cfg['projects_dir'] . $project_name . '/';
    $project_file = $project_dir . 'project.html';
    $html_dom = getDOM($project_file);

    //get images from directory
    $dir_imgs = getFiles($cfg['projects_dir'] . $project_name, '/\.(jpg|jpeg)/i');

    //Removes nodes (divs) referencing missing images
    $xpath = new DOMXpath($html_dom);
    foreach ($xpath->query('//div/img') as $node) {
        if (!in_array($src = $node->getAttribute('src'), $dir_imgs)) {
            $parent_node = $node->parentNode;
            $parent_node->parentNode->removeChild($parent_node);
        } else {
            $found_imgs[] = $src;
        }
    }

    //new images
    $new_imgs = array_diff($dir_imgs, $found_imgs);
    $dom_gallery_node = $xpath->query('//div[@id="gallery"]')->item(0);
    foreach ($new_imgs as $new_img) {
        $xml = "   <div class=\"media\" >\n";
        $xml.= "      <img src=\"$new_img\" />\n";
        $xml.= "      <div class=\"caption\" > </div>\n"; //spaces left in div on purpose!
        $xml.= "   </div>\n";
        $new_img = $html_dom->createDocumentFragment();
        $new_img->appendXML($xml);
        $dom_gallery_node->appendChild($new_img);
    }

    $html_sxml = simplexml_import_dom($html_dom);

    //extract the html for the gallery and add the full url
    $output['gallery'] = array_pop($html_sxml->xpath('//div[@id="gallery"]'))->asXML();
    $output['gallery'] = str_replace('src="', 'src="' . $cfg['base_url'] . $project_dir, $output['gallery']);

    $output['title'] = array_pop($html_sxml->xpath('//h1[@id="title"]'));
    $output['text'] = array_pop($html_sxml->xpath('//div[@id="presentation"]/pre'));

    $output['project_name'] = $project_name;

    output("project_edit.html.php", $output);
}

/**
 * Admin Controller - Project Save
 * Ajax only!
 *
 * @global  $cfg
 * @param <type> $project_name
 */
function ctrl_admin_project_save($project_name) {
    global $cfg;
    checkAjax();
    //file_put_contents('test', var_export($_POST,true));
}

function ctrl_admin_project_delete($project_name) {

}

function ctrl_admin_project_create() {

}

function ctrl_admin_project_media_delete() {

}

/*
 * -------------------------------------------------------------------
 *  HELPER FUNCTIONS
 * -------------------------------------------------------------------
 */

/**
 * Loads an HTML file and returns the DOM document
 * @param string $file The HTML file
 * @return DOMDocument
 */
function getDOM($file) {
    if (file_exists($file)) {
        $raw = str_replace("\r", "", file_get_contents($file));
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->strictErrorChecking = FALSE;
        $dom->loadHTML($raw);
        return $dom;
    } else {
        exit('Failed to open ' . $project_file);
    }
}

/**
 * Returns an array of all the files matching the pattern
 * @todo This function forces the use of php 5.3! a while loop might be better
 *
 * @param  string   $dir
 * @param  string   $pattern  Regex pattern to filter the files
 * @return array              Array of files
 */
function getFiles($dir, $pattern='/.*/') {
    $files = scandir($dir);
    return array_filter($files, function($elem) use ($pattern) {
                return ($elem != '.' && $elem != '..' && preg_match($pattern, $elem));
            });
}

/**
 * Returns an array of all the directories within $dir, excluding . and ..
 * @todo This function forces the use of php 5.3! a while loop might be better
 *
 * @param  string   $dir
 * @return array
 */
function getDirs($dir) {
    $files = scandir($dir);
    return array_filter($files, function($elem) use ($dir) {
                return ($elem != '.' && $elem != '..' && is_dir($dir . '/' . $elem));
            });
}

/**
 * Quit the app if it's not an ajax call
 */
function checkAjax() {
    if (!isset($_POST['ajax'])) die("No direct call allowed.");
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
 * @global array $cfg
 * @param string $tpl
 * @param <mixed> $contentArray
 * @param boolean $returnToString
 * @return string Ret
 */
function output($tpl, $contentArray=array(), $returnToString=FALSE) {
    global $cfg;
    $tpl = $cfg['style_dir'] . $cfg['theme'] . $cfg['tpl_dir'] . $tpl;
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

/**
 * Returns a full URL from a given URI
 *
 * @global array  $cfg     The global configuration
 * @param  string $action  The URI
 * @return string          The full URL
 */
function makeUrl($action) {
    global $cfg;
    return $cfg['base_url'] . 'index.php/' . $action;
}

/**
 * Forces to redirect to a new URI
 *
 * @param string $action The URI
 */
function redirect($action) {
    header("Location: " . makeUrl($action));
    die();
}

/*
 * -------------------------------------------------------------------
 *  USER LOGIN FUNCTIONS
 * -------------------------------------------------------------------
 */

/**
 * Crude login system
 *
 * @global array $cfg
 * @param  string $username
 * @param  string $password
 * @return boolean
 */
function miniLog($username, $password) {
    global $cfg;
    if (isset($cfg['users'][$username])) {
        if ($password == $cfg['users'][$username]) {
            $_SESSION['islogged'] = true;
            return true;
        }
    }
    return false;
}

/**
 * Forces a logout by changing the session
 */
function logout() {
    $_SESSION['islogged'] = false;
}

/**
 * Checks if the user is logged in
 *
 * @return boolean TRUE if logged in
 */
function is_logged() {
    if (isset($_SESSION['islogged']))
        return $_SESSION['islogged'];
    else
        return false;
}

/*
 * -------------------------------------------------------------------
 *  FRONT CONTROLLER
 * -------------------------------------------------------------------
 */

/**
 * Front controller
 * This is the first function called.
 * It resolves the controller function and arguments to use.
 * It will also test if the user is logged in for admin controllers.
 *
 * @global array $cfg Configuration
 */
function front_ctrl() {
    global $cfg;

    //first thing to do (to make the login system work)
    session_start();

    //Read the PATH INFO to divide the request in segments
    //The first one will be used as the controller

    $args = explode("/", substr($_SERVER['PATH_INFO'], 1));
    if (!$args) $args = array($cfg['default_controller']);
    $controller = 'ctrl_' . array_shift($args);
    $cfg['args'] = implode('/', $args);

    //Defines the controller if it exists
    if (!function_exists($controller))
        $controller = 'ctrl_' . $cfg['default_controller'];

    //Checks the login if it's an admin controller
    if (stripos($controller, "_admin_") !== false) {
        if (!is_logged())
            redirect("/login");
        $cfg['theme'] = 'admin/';
    }

    //Calls the controller function
    call_user_func_array($controller, $args);
}

//Launches the front controlller if this file is not an include
if (!defined('ALLOWINCLUDE')) front_ctrl();