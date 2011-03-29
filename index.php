<?php
//------------------------------------------------------------------------------
//
//  CONFIG
//
//------------------------------------------------------------------------------

$cfg['default_controller']="index";

//from: http://codeigniter.com/forums/viewthread/81424/
$cfg['base_url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$cfg['base_url'] .= "://".$_SERVER['HTTP_HOST'];
$cfg['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
//$cfg['base_url']    = "http://localhost/microfolio/";

//directories                   
$cfg['tpl_dir']     = "tpl/";
$cfg['style_dir']   = "style/";
$cfg['theme']       = "default/";

$cfg['lib_dir']      = "lib/";
$cfg['projects_dir'] = "projects/";

// users/passwords
$cfg['users'] = array ( 'admin'=>'122442');

/*
 * --------------------------------------------------------------------
 * Public Controllers
 * --------------------------------------------------------------------
 */
 
    function ctrl_index() {
        echo "Hello world";
    }
    
    function ctrl_login() {
        global $cfg;
        $cfg['theme']='admin/';             
        output("login.html.php");
    }
    
    function ctrl_dologin() {
        if (miniLog($_REQUEST['username'],$_REQUEST['password'])) {
            redirect("admin_project_list");
        } else {
            redirect("login");
        }
    }
    
    function ctrl_logout() {
        logout();
        redirect("/login");   
    }
    
/*
 * --------------------------------------------------------------------
 * Admin Controllers
 * --------------------------------------------------------------------
 *
 */
    function ctrl_admin_project_list() {
        global $cfg;
        $output['projects'] = getDirs($cfg['projects_dir']);
        output("project_list.html.php",$output);
    }   
    
    function ctrl_admin_project_edit($project_name) {
        global $cfg;
        
        $project_dir = $cfg['projects_dir'].$project_name.'/';
        $project_file = $project_dir.'project.html';
        $html_dom  = getDOM($project_file);

        //get images from directory
        $dir_imgs  = getFiles($cfg['projects_dir'].$project_name,'/\.(jpg|jpeg)/i');

        //Removes nodes (divs) referencing missing images
        $xpath = new DOMXpath($html_dom);
        foreach($xpath->query('//div/img') as $node) {   
            if (!in_array($src = $node->getAttribute('src'),$dir_imgs)) {
                $parent_node = $node->parentNode;
                $parent_node->parentNode->removeChild($parent_node);
            } else {
                $found_imgs[] = $src;
            }
        }
        
        //new images
        $new_imgs = array_diff($dir_imgs,$found_imgs);
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
        $output['gallery'] = str_replace('src="','src="'.$cfg['base_url'].$project_dir,$output['gallery']);
        
        $output['title'] = array_pop($html_sxml->xpath('//h1[@id="title"]'));
        $output['text'] = array_pop($html_sxml->xpath('//div[@id="presentation"]/pre')); 
        
        $output['project_name']=$project_name;
        
        output("project_edit.html.php",$output);
    }
    
    function ctrl_admin_project_save($project_name) {
        global $cfg;
        //file_put_contents('test', var_export($_POST,true));
        /*
        $project_file = $cfg['args'];
        $medialist = array_filter(explode(",",$_POST['project_media'] ));
        $meta = "media[]='".implode("'\nmedia[]='",$medialist)."'";
        file_put_contents($project_file, $_POST['project_text']."\n>>>>\n".$meta);
        redirect("admin_project_edit/".$project_file);
        */
    }
    
    function ctrl_admin_project_delete($project_name) {
    
    }
    
    function ctrl_admin_project_create() {
    
    }

    function ctrl_admin_project_media_delete() {
    
    }
    
//------------------------------------------------------------------------------
//
//  HELPERS
//
//------------------------------------------------------------------------------

    function getDOM($file) {
        if (file_exists($file)) {
            $raw = str_replace("\r","",file_get_contents($file));
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->strictErrorChecking = FALSE;
            $dom->loadHTML($raw);
            return $dom;
        } else {
            exit('Failed to open '.$project_file);
        }
    }
        
    function getFiles($dir,$pattern='/.*/') {
        $files = scandir($dir);
        return array_filter( $files, function($elem) use ($pattern) { 
            return ($elem!='.' && $elem!='..' && preg_match($pattern,$elem)); 
        });
    }
    
    /**
     *  Returns an array of all the directories within $dir, excluding . and ..
     **/              
    function getDirs($dir) {
        $files = scandir($dir);
        return array_filter( $files, function($elem) use ($dir) { 
            return ($elem!='.' && $elem!='..' && is_dir($dir.'/'.$elem)); 
        });
    }

//------------------------------------------------------------------------------
//
//  CORE
//
//------------------------------------------------------------------------------
        
    function output($tpl,$contentArray=array(),$returnToString=FALSE) {
        global $cfg;
        $tpl=$cfg['style_dir'].$cfg['theme'].$cfg['tpl_dir'].$tpl;
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
        global $cfg;
        return $cfg['base_url'].'index.php/'.$action;
    }
    
    function addScript($src) {
        global $cfg;
        if (strpos($src,"://") === false) $src = $cfg['base_url'].$cfg['style_dir'].$cfg['theme']."js/".$src;
        $cfg['js_files'][]="<script type='text/javascript' src='$src' ></script>";
    }
    
    function getScripts() {
        global $cfg;
        if (!isset($cfg['js_files'])) return "";
        return implode("\n      ",$cfg['js_files']);
    }
   
    function redirect($action) {
        header("Location: ".makeUrl($action));
        die();
    }
    
    //return true/false and set the login in session
    function miniLog($username,$password) {
        global $cfg;
        if (isset($cfg['users'][$username])) {
            if ($password==$cfg['users'][$username]) {
                $_SESSION['islogged']=true;
                return true;
            }
        }
        return false;
    }
    
    function logout() {
        $_SESSION['islogged']=false;
    }
    
    //islogged
    function is_logged() {
        if (isset($_SESSION['islogged']))
            return $_SESSION['islogged'];
        else
            return false;
    }
        
    function front_ctrl() {
        global $cfg;
        session_start();        
        //read url
        $args=explode("/",substr($_SERVER['PATH_INFO'],1));
        if (!$args) $args = array ($cfg['default_controller']);
        $controller = 'ctrl_'.array_shift($args);
        $cfg['args'] = implode('/',$args);
        if (!function_exists($controller)) $controller='ctrl_'.$cfg['default_controller'];
        //if controller is admin then check login
        if  (stripos($controller,"_admin_")!==false) {
            if (!is_logged()) redirect("/login");
            $cfg['theme']='admin/';
        }
        call_user_func_array($controller,$args);
    }

if (!defined('ALLOWINCLUDE')) front_ctrl();