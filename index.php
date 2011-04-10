<?php
/**
 * --------------------------------------------------------------------
 * Microfolio
 * --------------------------------------------------------------------
 *
 * An open source portfolio CMS for PHP 5
 * 
 *
 * @author		Nicolas Myers
 * @version             0.1
 */

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

function ctrl_project($project_name) {

    $project_dir = cfg('projects_dir') . $project_name . '/';
    $project_file = $project_dir . 'project.html';

    if (!file_exists($project_file)) redirect ();

    //load html parser
    include_lib('simple_html_dom/simple_html_dom.php');
    $menu_html    = file_get_html(cfg('projects_dir').'projects.html');

    //remove unpublished project from the list
    foreach($menu_html->find('div[class*=prj-unpublished]') as $e) $e->parent()->outertext = '';
    $menu_html = str_get_html($menu_html->save());

    //redirect if the project is not found (= not published)
    if(!$prj=$menu_html->find("a[href^=$project_name/]",0)) redirect();

    //parse the project classes = project settings
    $prj_classes = explode(" ",$prj->parent()->class);
    $prj_settings = array();
    foreach ($prj_classes as $class) {
        if (stripos($class,'-')!==false) {
            list($key,$val) = explode('-',$class);
            $prj_settings[$key]=$val;
        }
    }

    $project_html = file_get_html($project_file);

    //rewrite images urls
    //foreach($project_html->find('div.image img') as $e) $e->src = cfg('base_url').$project_dir.$e->src;

    //Check for a project template
    $project_template = "project_default.html.php";
    if (isset($prj_settings['template'])) {
        $template_file = "project_".$prj_settings['template'].".html.php";
        if (file_exists(cfg('style_dir') . cfg('theme') . cfg('tpl_dir') . $template_file)) {
            $project_template = $template_file;
        }
    }
    $output['project_name'] = $project_name;
    $output['menu'] = $menu_html->find("#menu-projects",0)->outertext;
    $output['project'] = array (
        'title'        => $project_html->find("#title",0)->innertext,
        'presentation' => $project_html->find("#presentation",0),
        'gallery'      => $project_html->find("#gallery",0),
        'name'         => $project_name,
        'dir'          => $project_dir,
        'settings'     => $prj_settings
    );

    if (file_exists(cfg('style_dir') . cfg('theme') . 'functions.php')) {
            include cfg('style_dir') . cfg('theme') . 'functions.php';
        }

    //show the project
    output($project_template,$output);
}

/**
 * Public Controller - Login page
 * @global array $cfg
 */
function ctrl_login() {
    global $cfg;
    $cfg['in_admin'] = true;
    output("login.html.php");
}

/**
 * Public Controller - DoLogin
 * Test the login and redirects
 *
 * @global array $cfg
 */
function ctrl_dologin() {
    if (miniLog($_POST['username'], $_POST['password'])) {
        //@todo: change to var in config
        redirect("admin_projects_menu");
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

/**
 * Dynamic image resizing using jit_image_manipulation class
 * @see https://github.com/pointybeard/jit_image_manipulation
 */
function ctrl_image() {
    include_lib('jit_image_manipulation/image.php');
    die();
}


/*
 * --------------------------------------------------------------------
 * ADMIN CONTROLLERS
 * --------------------------------------------------------------------
 *
 */

/**
 * Admin Controller - Projects menu
 * 
 * This parses the 'projects.html' file and sync it with the projects folders
 * The view takes care of reordering projects and adding sections (jquery)
 *
 * @global  $cfg
 */
function ctrl_admin_projects_menu() {
    // loads and parse 'projects.html'
    include_lib('simple_html_dom/simple_html_dom.php');
    $html = file_get_html(cfg('projects_dir').'projects.html');

    // gets an array of all the project folders
    $projects_dir = getDirs(cfg('projects_dir'));

    /** Renoves all the project DIVs with no matching folders (deleted)
     */
    $projects_found = array();
    foreach($html->find('#menu-projects .project a') as $project_link) {
        $project_name = str_replace("/project.html","",$project_link->href);
        if(!in_array($project_name, $projects_dir)) {
            $project_link->parent()->parent()->outertext = "";
        } else {
            $projects_found[] = $project_name;
        }
    }

    /** Adds new DIVs for new project folders
     */
    $projects_new = array_diff($projects_dir,$projects_found);
    $menu_dom = $html->find("#menu-projects",0);
    foreach ($projects_new as $project_new) {
        $new_project_link = "<li><div class=\"project\" ><a href=\"$project_new/project.html\" >$project_new</a></div></li>";
        $menu_dom->innertext = $menu_dom->innertext .$new_project_link;
    }

    /**
     * adds the class sortable for the output
     * @todo makes more sense in js
     */
    $html->find("#menu-projects",0)->class="sortable";
    $output['menu'] = $html->find("#menu-projects",0);
    
    output("projects_menu.html.php", $output);
}

/**
 * Admin Controller - Projects menu save
 * Ajax call only. Saves the menu in 'projects.html'
 */
function ctrl_admin_projects_menu_save() {
    checkAjax();

    include_lib('simple_html_dom/simple_html_dom.php');
    include_lib('htmlindent/htmlindent.php');

    /**
     * Cleans up the html: removes the controls and inline styles
     */
    $menuhtml = str_get_html(stripslashes($_POST['menuhtml']));
    foreach($menuhtml->find('.controls') as $e) $e->outertext = '';
    foreach($menuhtml->find('li[style]') as $e) $e->style = null;

    $output['menuhtml'] = clean_html_code($menuhtml->save());
    $html = output("empty_projects_menu.html.php", $output, true);
    if(!file_put_contents(cfg('projects_dir')."projects.html", $html))
        die('Error writing "projects.html".');
    echo '1';
}

/**
 * Admin Controller - Project Edit
 *
 * @global  $cfg
 * @param string $project_name
 */
function ctrl_admin_project_edit($project_name) {
    $project_dir = cfg('projects_dir') . $project_name . '/';
    $project_file = $project_dir . 'project.html';

    //load html parser
    include_lib('simple_html_dom/simple_html_dom.php');

    $html = file_get_html($project_file);
    $dir_imgs = getFiles($project_dir, '/\.(jpg|jpeg)/i');

    $found_imgs = array();
    foreach($html->find('#gallery a.image') as $img) {
        if(!in_array($img->href, $dir_imgs)) {
            $img->parent()->outertext = ""; //not in dir? > remove div
        } else {
            $found_imgs[] = $img->href;
        }
    }

    //new images
    $new_imgs = array_diff($dir_imgs, $found_imgs);

    //add links for new project folders
    $gallery_dom = $html->find("#gallery",0);
    foreach ($new_imgs as $new_img) {
        $div = "<div class=\"media image\" >";
        $div.= "   <a href=\"$new_img\" title=\"$new_img\" class=\"image\" >";
        $div.= "     <img src=\"$new_img\" alt=\"$new_img\" />";
        $div.= "   </a>";
        $div.= "   <div class=\"caption\" > </div>"; //spaces left in div on purpose!
        $div.= "</div>";
        $gallery_dom->innertext = $gallery_dom->innertext .$div;
    }

    //prepare vars for template
    $gallery_html = $html->find("#gallery",0)->outertext;
    $output['gallery'] = str_replace('src="', 'src="'.cfg('base_url').'image/2/72/72/5/'.$project_name.'/', $gallery_html);
    $output['title'] = $html->find("h1",0)->innertext;
    $output['text'] = $html->find("#presentation",0)->innertext;
    $output['project_name'] = $project_name;

    output("project_edit.html.php", $output);
}

/**
 * Admin Controller - Project Save
 * Ajax only!
 *
 * @global  $cfg
 * @param string $project_name
 */
function ctrl_admin_project_save($project_name) {
    checkAjax();

    $project_dir = cfg('projects_dir') . $project_name . '/';
    $project_file = $project_dir . 'project.html';

    include_lib('simple_html_dom/simple_html_dom.php');
    include_lib('htmlindent/htmlindent.php');

    $html = file_get_html($project_file);

    //cleans and adds the gallery
    $gallery = str_get_html(stripslashes($_POST['gallery']));
    foreach($gallery->find('.controls') as $e) $e->outertext = '';
    foreach($gallery->find('div[style]') as $e) $e->style = NULL;
    foreach($gallery->find('img') as $img) {
        $img->src = substr($img->src, strrpos($img->src,"/")+1);
    }
    $html->find("#gallery",0)->innertext = "\n\n".clean_html_code($gallery->innertext)."\n\n";

    //process and adds the text
    //@todo use a strip tag to cleanup the code received
    $html_text = clean_html_code(stripslashes($_POST['text']));

    $html->find("#presentation",0)->innertext = "\n\n".$html_text."\n\n";

    //adds the text
    $html->find("#title",0)->innertext = stripslashes($_POST["title"]);

    if(!file_put_contents($project_file, $html->save()))
        die('Error writing "project.html".');
    echo '1';
}

function ctrl_admin_project_delete($project_name) {
    checkAjax();
    $projects = getDirs(cfg('projects_dir'));
    if (!in_array($project_name, $projects)) die("This project does not exist.");
    foreach(getFiles(cfg('projects_dir').$project_name) as $file)
        if(!unlink(cfg('projects_dir').$project_name.'/'.$file)) die("Could not delete the file: ".$project_name);;
    if(!rmdir(cfg('projects_dir').$project_name)) die("Could not delete the folder: ".$project_name);
    echo '1';
}

function ctrl_admin_project_create($project_name) {
    checkAjax();
    $projects = getDirs(cfg('projects_dir'));
    if (in_array($project_name, $projects))
            die("This project already exists.");
    if(!mkdir(cfg('projects_dir').$project_name))
        die('Error creating the folder.');
    $output['project_name'] = $project_name;
    $html = output("empty_project.html.php", $output, true);
    if(!file_put_contents(cfg('projects_dir').$project_name."/project.html", $html))
        die('Error writing "project.html".');
    echo '1';
}

function ctrl_admin_project_media_delete($project_name) {
    checkAjax();
    $file = cfg('projects_dir').$project_name.'/'.htmlentities($_POST['media_file']);
    if (file_exists($file)) {
        unlink($file);
        echo '1';
    } else {
        echo '0';
    }
}

function ctrl_admin_project_media_upload($project_name,$filename) {
    include_lib('ajaxupload/upload.php');

    //we need to clean the url and reinject the filename
    //in a get to make the lib work.. dodgy
    $filename = htmlentities(str_replace('?qqfile=', '', $filename));
    $_GET['qqfile']=$filename;

    $allowedExtensions = array('jpg','jpeg');
    $sizeLimit = 10 * 1024 * 1024; //in bytes
    $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
    $result = $uploader->handleUpload(cfg('projects_dir').$project_name.'/');
    // to pass data through iframe you will need to encode all html tags
    echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
}

/*
 * -------------------------------------------------------------------
 *  HELPER FUNCTIONS
 * -------------------------------------------------------------------
 */

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

/**
 * Returns an array of all the directories within $dir, excluding . and ..
 *
 * @param  string   $dir
 * @return array
 */
function getDirs($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && is_dir($dir . '/' . $file))
                $result[] = $file;
    }
    return $result;
}

/**
 * Includes an external library
 *
 * @global array $cfg
 * @param  string $filename
 */
function include_lib($filename) {
    require_once cfg('lib_dir').$filename;
}

/**
 * Quit the app if it's not an ajax call
 */
function checkAjax() {
    if (!isset($_POST['ajax'])) die("No direct call allowed.");
}

function cfg($key) {
    global $cfg;
    return isset($cfg[$key]) ? $cfg[$key] : NULL;
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

/**
 * Returns a full URL from a given URI
 *
 * @global array  $cfg     The global configuration
 * @param  string $action  The URI
 * @return string          The full URL
 */
function makeUrl($action) {
    return cfg('base_url') . cfg('base_index') . $action;
}

/**
 * Forces to redirect to a new URI
 *
 * @param string $action The URI
 */
function redirect($action="") {
    header("Location: " . makeUrl($action));
    die();
}

function includeJS($filename) {
    $js_filename = cfg('in_admin') ? cfg('admin_style_dir') : cfg('style_dir').cfg('theme');
    $js_filename .= cfg('js_dir').$filename;
    
    if (file_exists($js_filename)) {
        $js_url = cfg('base_url').$js_filename;
        return "<script type=\"text/javascript\" src=\"$js_url\"></script>";
    }
}

function includeCSS($filename,$media="all") {
    $css_filename = cfg('in_admin') ? cfg('admin_style_dir') : cfg('style_dir').cfg('theme');
    $css_filename .= cfg('css_dir').$filename;

    if (file_exists($css_filename)) {
        $css_url = cfg('base_url').$css_filename;
        return "<link href=\"$css_url\" rel=\"stylesheet\" type=\"text/css\" media=\"$media\" />";
    }
}

/*
 * -------------------------------------------------------------------
 *  USER LOGIN FUNCTIONS
 * -------------------------------------------------------------------
 */

/**
 * Basic login system
 *
 * @param  string $username
 * @param  string $password
 * @return boolean
 */
function miniLog($username, $password) {
    if (cfg('username') == $username && md5($username . $password) == cfg('password')) {
        $_SESSION['islogged'] = true;
        return true;
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
    return isset($_SESSION['islogged']) ? $_SESSION['islogged'] : false;
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

    loadConfig();

    //first thing to do (to make the login system work)
    session_start();

    //Read the PATH INFO to divide the request in segments
    //The first one will be used as the controller
    //we need to use the request_uri instead of pathinfo when mod_rewrite is on
    if (isset($_SERVER['PATH_INFO'])) {
        $uri = $_SERVER['PATH_INFO'];
    } else if (isset($_SERVER['REQUEST_URI'])) {
        $uri = str_replace(dirname($_SERVER['SCRIPT_NAME']),'',$_SERVER['REQUEST_URI']);
    }


    $args = @explode("/", ltrim($uri,'/'));
    if (!$args) $args = array($cfg['default_ctrl']);
    $cfg['controller'] = 'ctrl_' . array_shift($args);
    $cfg['args'] = implode('/', $args);


    //Defines the controller if it exists
    if (!function_exists($cfg['controller']))
        $cfg['controller'] = 'ctrl_' . $cfg['default_ctrl'];

    //Checks the login if it's an admin controller
    $cfg['in_admin'] = false;
    if (stripos($cfg['controller'], "_admin_") !== false) {
        if (!is_logged()) redirect("/login");
        $cfg['in_admin'] = true;
    }

    //Calls the controller function
    call_user_func_array($cfg['controller'], $args);
}



 //this is maybe a bit too much.. why not leave it to the user to edit the file
//to change the username and password?

        //check if php version ok
        //check if folder are writable
function loadConfig() {
    global $cfg;
    /**
     * These are core settings, merged with config.php
     */
    $cfg = array (
        'cache_images'    => true,
        //default controller
        'default_ctrl'    => "index",
        'image_quality'   => 90,

        //dir names
        'cache_dir'       => 'system/cache/',
        'admin_dir'       => 'system/',
        'admin_style_dir' => 'system/style/',
        'lib_dir'         => "system/lib/",
        'projects_dir'    => "projects/",
        'style_dir'       => "style/",
        'tpl_dir'         => "tpl/",
        'js_dir'          => "js/",
        'css_dir'         => "css/",
        'theme'           => "default/"
    );

    if (file_exists('system/config/config.php')) {
        include 'system/config/config.php';

        //cleanup theme
        $cfg['theme'] = rtrim($cfg['theme'],'/').'/';

        /**
         * Automatic Base URL
         * @see: http://codeigniter.com/forums/viewthread/81424/
         */
        if (!isset($cfg['base_url'])) {
            $cfg['base_url']  = "http://" . $_SERVER['HTTP_HOST'];
            $cfg['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
        }
        
        /**
         * Automatic Base dir
         */
        if (!isset($cfg['base_dir'])) {
            $cfg['base_dir'] = realpath('.').'/';
            $cfg['base_dir'] = str_replace("\\","/",rtrim($cfg['base_dir'], '/').'/');
        }

        if (!isset($cfg['base_index'])) {
            //@todo test if function available? if not apache?
            if (in_array("mod_rewrite", apache_get_modules()) && file_exists('.htaccess')) {
                $cfg['base_index'] = "";
            } else {
                $cfg['base_index'] = "index.php/";
            }
        }
    } else {
        //lets install
        $cfg['in_admin'] = true;
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $error = array();
            if (empty($_POST['username'])) $error[] = "Enter a username";
            if (strlen($_POST['username'])<4 && !empty($_POST['username'])) $error[] = "Username too short (min 4 char)";
            if (empty($_POST['password'])) $error[] = "Enter a password";
            if (strlen($_POST['password'])<5 && !empty($_POST['password'])) $error[] = "Password too short (min 5 char)";
            if ($_POST['password']!=$_POST['password2']) $error[] = "Passwords don't match";

            if (empty($error)) {
                $output['password'] = md5($_POST['username'].$_POST['password']);
                $output['username'] = $_POST['username'];
                $php = "<?php \n".output("empty_config.php", $output, true);
                if (!file_put_contents("system/config/config.php", $php))
                    die('Error writing the config! check auth.');
                output("install_welcome.html.php",$output);
                die();
            }
            
        }

        $output['error'] = empty($error) ? '' : '<ul><li>'.implode('</li><li>',$error).'</li></ul>';
        output("install_form.html.php",$output);
        die();
    }
}

//Launches the front controlller if this file is not an include
// = broken (doesn't resolve docroot properly)
if (!defined('ALLOWINCLUDE')) front_ctrl();