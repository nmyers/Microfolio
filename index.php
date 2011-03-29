<?php
//------------------------------------------------------------------------------
//
//  CONFIG
//
//------------------------------------------------------------------------------

$cfg['default_controller']="index";

$cfg['base_url']="http://localhost/microwiki/";
                   
$cfg['tpl_dir']   = "tpl";
$cfg['style_dir'] = "style";
$cfg['theme']     = "default";

$cfg['lib_dir'] = "lib";
$cfg['projects_dir'] = "projects";
// users/passwords
$cfg['users'] = array ( 'admin'=>'122442');


if (function_exists('realpath') AND @realpath(dirname(__FILE__)) !== FALSE)
	$cfg['base_dir'] = str_replace("\\", "/", realpath(dirname(__FILE__))).'/';

//------------------------------------------------------------------------------
//
//  FONCTIONS CONTROLLERS - micro MVC
//
//------------------------------------------------------------------------------

    //controller par défaut
    function ctrl_index()
    {
        redirect ('pagemgr');
    }
    
    function ctrl_admin_project_list() {
        global $cfg;
        $output['projects'] = directoryToArray("./".$cfg['projects_dir'], true,'/project\.xml$/');
        $output['content']=output("project_list.html.php",$output,true);
        output("main.html.php",$output);
    }
    
    function ctrl_admin_project_edit() {
        global $cfg;
        $project_file = $cfg['args'];
        $project = getProjectData($cfg['args']);  
        
        //Loop through all the images in project dir and add new ones to xml
        $images = directoryToArray("./".dirname($project_file), true,'/\.jpg$/',false);
        foreach ($images as $image) { 
            $found = false;
            foreach ($project->medialist->media as $media) {
                if ($media->filename==$image) { 
                    $found = true;
                    break;
                }   
            }
            if (!$found) {
                $media = $project->medialist->addChild('media');
                $media->addChild('filename', $image);
                $media->addChild('title', '');
                $media->addChild('caption', '');
            }
        }
        
        //prepare the view
        $output['project'] = $project; 
        $output['project_file'] = $project_file;
        $output['project_url'] = $cfg['base_url'].dirname($cfg['args']).'/';
        $output['content']=output("project_edit.html.php",$output,true);
        output("main.html.php",$output);
    }
    
    function ctrl_admin_project_save() {
        global $cfg;
        $project_file = $cfg['args'];
        $medialist = array_filter(explode(",",$_POST['project_media'] ));
        $meta = "media[]='".implode("'\nmedia[]='",$medialist)."'";
        file_put_contents($project_file, $_POST['project_text']."\n>>>>\n".$meta);
        redirect("admin_project_edit/".$project_file);
    }
    
    function ctrl_login() {
        global $cfg;
        $cfg['theme']='admin';
        $output['content']=output("login.html.php",array(),true);
        output("main.html.php",$output);
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
    
//------------------------------------------------------------------------------
//
//  HELPERS
//
//------------------------------------------------------------------------------
    
    function getProjectData($project_file)
    {
        if (file_exists($project_file)) {
            $xml = simplexml_load_file($project_file,'SimpleXMLElement', LIBXML_NOCDATA);
            return $xml;
        } else {
            exit('Failed to open '.$project_file);
        } 
    }
    
    function directoryToArray($directory, $recursive,$pattern='/.*/',$includepath=true) {
    	$array_items = array();
    	if ($handle = opendir($directory)) {
    		while (false !== ($file = readdir($handle))) {
    			if ($file != "." && $file != ".." ) {
    				if (is_dir($directory. "/" . $file)) {
    					if($recursive) {
    						$array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive,$pattern,$includepath));
    					}
    					if ($includepath) $file = $directory . "/" . $file;
    					if (preg_match($pattern, $file)==false) continue;
    					$array_items[] = preg_replace("/\/\//si", "/", $file);
    				} else {
    					if ($includepath) $file = $directory . "/" . $file;
    					if (preg_match($pattern, $file)==false) continue;
    					$array_items[] = preg_replace("/\/\//si", "/", $file);
    				}
    			} 
    		}
    		closedir($handle);
    	}
    	return $array_items;
    }
    
    function normalizeStr($rawStr) {
        $rawStr=trim($rawStr);
        $accent     = utf8_decode(" .ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ");
        $noaccent   = utf8_decode("__aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby");
        $normStr    = strtr(trim(html_entity_decode(utf8_decode($rawStr), ENT_NOQUOTES)), $accent, $noaccent);
        $validChars = 'a-zA-Z0-9-_';
        $normStr    = preg_replace("/[^$validChars]/", "", $normStr);
        return strtolower($normStr);
    }

//------------------------------------------------------------------------------
//
//  CORE - micro MVC
//
//------------------------------------------------------------------------------
        
    function output($tpl,$contentArray=array(),$returnToString=FALSE) {
        global $cfg;
        $tpl=$cfg['base_dir'].$cfg['style_dir'].'/'.$cfg['theme'].'/'.$cfg['tpl_dir'].'/'.$tpl;
        extract($contentArray, EXTR_OVERWRITE);
        if ($returnToString) {
            ob_start();
            include($tpl);
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        } else {
            include($tpl);
        }
    }
        
    function makeUrl($action) {
        global $cfg;
        return $cfg['base_url'].'index.php/'.$action;
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
        
    function frt_ctrl() {
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
            $cfg['theme']='admin';
        }
        
        call_user_func_array($controller,$args);
    }

if (!defined('ALLOWINCLUDE')) frt_ctrl();