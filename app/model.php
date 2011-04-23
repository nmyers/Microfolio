<?php

/**
 * 
 * $prj = new Project('test');  // if 'test' exists : loads the project, if not create a new one
 * $prj->create('test'); //creates a new project
 * $prj->load('test');  //loads an existing project
 * $prj->save();   //saves the current dom in file
 * $prj->delete(); //deletes the whole project (folder and files)
 * $prj->sync();   //syncs the project data with actual content of folder
 * $prj->rename(); //renames a project (rename folder)
 *
 * $prj->name         //read only
 * $prj->title        //text
 * $prj->description  //html content
 * $prj->settings     //assoc array
 * eg: $prj->settings['template'] = 'new-template';
 *
 * $prj->gallery      //gallery dom or assoc array
 *
 */

class Project {
    
    private $name;
    private $folder;
    private $html_file;

    private $dom;
    private $gallery;
    
    function __construct($name,$forceCreate=false){

        //set main properties
        $this->name = normalize_str($name);
        $this->folder = cfg('projects_dir') . $this->name;
        $this->html_file = $this->folder . '/project.html';

        //dom parser
        include_lib('simple_html_dom/simple_html_dom.php');

        //loads project if name exists, creates otherwise
        if ($forceCreate){
            $this->create();
        } else {
            if ($this->exists()) {
                $this->load();
            } else {
                throw new Exception('Project doesn\'t exist');
            }
        }
    }

    public function create() {
        if (empty($this->name))
            throw new Exception('Project name is empty, can\'t create.');
        if ($this->exists())
            throw new Exception('This project already exists.');
        if (!mkdir($this->folder))
            throw new Exception('Error creating the folder.');
        $html = output("empty_project.html.php", array('project_name' => $this->name), true);
        if (!file_put_contents($this->html_file, $html))
            throw new Exception('Error writing "project.html".');
        return true;
    }

    public function load() {
        if (!$this->exists())
            throw new Exception('Project does not exist.');
        $this->dom = file_get_html($this->html_file);
    }

    public function save() {
        include_lib('htmlindent/htmlindent.php');
        $html = clean_html_code($this->dom->save());
        if (!file_put_contents($this->html_file, $html))
            throw new Exception("Error writing '{$this->html_file}'.");
        return true;
    }

    public function delete() {
        if (!$this->exists())
            throw new Exception('Project does not exist.');
        foreach (getFiles($this->folder) as $file)
            if (!unlink($this->folder.'/'.$file))
                throw new Exception("Could not delete the file: '$file'");
        if (!rmdir($this->folder)) {
            throw new Exception("Could not delete the folder: '{$this->folder}$'");
        }
        return true;
    }

    public function sync() {
        $dir_imgs = getFiles($this->folder.'/', '/\.(jpg|jpeg)/i');
        $dom_imgs = array();
        foreach ($this->dom->find('#gallery a.image') as $img) {
            if (!in_array($img->href, $dir_imgs)) {
                $img->parent()->outertext = "";
                continue;
            }
            $dom_imgs[] = $img->href;
        }
        $new_imgs = array_diff($dir_imgs, $dom_imgs);
        foreach ($new_imgs as $new_img)
            $this->addMedia($new_img, $new_img, '', $new_img);
        $this->save();
        return true;
    }
    
    public function getMedia($key) {
        $key = normalize_str($key);
        $node = $this->dom->find("#gallery a[href=$key]",0);
        if (empty($node)) return false;
        $media = new stdClass();
        $media->title = $node->title;
        $media->caption = $node->parent()->find('.caption',0)->innertext;
        $media->filename = $node->href;
        return $media;
    }

    public function setMedia($key,$title=null,$caption=null,$file=null) {
        $key = normalize_str($key);
        $node = $dom->find("#gallery a[href=$key]",0);
        if (empty($node)) return false;
        if ($title!=null) $node->title = $title;
        if ($caption!=null) $node->parent()->find('.caption',0)->innertext = $caption;
        if ($file!=null) {
            $node->href = $file;
            if ($img=$node->find('img',0)) $img->src = $file;
        }
        return true;
    }

    public function addMedia($key,$title,$caption,$file) {
        if (!$this->getMedia($key)) {
            if (!file_exists($this->folder.'/'.$file))
                throw new Exception("File doesn't exist, can't add media '$file'");
            $div = "<div class=\"media image\" >";
            $div.= "   <a href=\"$file\" title=\"$title\" class=\"image\" >";
            $div.= "     <img src=\"$file\" alt=\"$title\" />";
            $div.= "   </a>";
            $div.= "   <div class=\"caption\" >$caption</div>"; //space left on purpose!
            $div.= "</div>";
            $this->dom->find('#gallery',0)->innertext .= $div;
        }
    }

    public function delMedia($key) {
        if (!unlink($this->folder.'/'.$key))
            throw new Exception("Can't delete '$key'");
        $key = normalize_str($key);
        $node = $this->dom->find("#gallery a[href=$key]",0);
        if (empty($node)) return false;
        $node->parent()->outertext = '';
    }

    public function getSetting($key) {
        $settings = $this->getSettings();
        return $settings[$key];
    }

    //this should update the projects settings as well
    public function setSetting($key,$value) {
        $settings = $this->getSettings();
        $settings[$key] = $value;
        $this->setSettings($settings);
        //should update the projects list file
    }

    private function getSettings() {
        $class = $dom->find('#project',0)->class;
        $classes = explode(" ", $class);
        if (empty($classes))
            return null;
        $settings = array();
        foreach ($classes as $class) {
            if (stripos($class, '-') !== false) {
                list($key, $val) = explode('-', $class);
                $settings[$key] = $val;
            } else {
                $settings[$class] = "";
            }
        }
        ksort($settings);
        return $settings;
    }

    private function setSettings($array) {
        $class = "";
        $classes = array();
        ksort($array);
        foreach ($array as $key => $val)
            $classes[] = empty($val) ? $key : $key . '-' . $val;
        $dom->find('#project',0)->class = implode(' ', $classes);
    }


    //@todo implement
    public function rename($new_name) {
        $new_name = normalize_str($new_name);
        if ($this->name != $new_name) {
            if (!rename($this->name,  cfg('projects_dir') . $new_name))
                throw new Exception("Could not rename the folder: '$project_name'");
            $this->name = $new_name;
        }
        return true;
    }

    /**
     *
     */

    public function __get($key) {
        switch ($key) {
            case 'title': return $this->dom->find('#title',0)->innertext;
            case 'presentation': return $this->dom->find('#presentation',0)->innertext;
            case 'gallery': return $this->dom->find('#gallery',0)->innertext;
            case 'dom': return $this->dom;
            case 'name' : return $this->name;
        }
    }

    public function __set($key,$value) {
        switch ($key) {
            case 'title':
                $this->dom->find('#title',0)->innertext = $value;
                break;
            case 'presentation':
                $this->dom->find('#presentation',0)->innertext = $value;
                break;
            case 'gallery':
                $this->dom->find('#gallery',0)->innertext = $value;
                break;
        }
    }

    //private

    /**
     * test for folder and project.html file
     */
    private function exists() {
        return (file_exists($this->folder) && (file_exists($this->html_file)));
    }

}

class ProjectsList {

    private $dom;
    private $folder;
    private $html_file;

    function __construct(){
        //set main properties
        $this->folder = cfg('projects_dir');
        $this->html_file = $this->folder . '/index.html';

        //dom parser
        include_lib('simple_html_dom/simple_html_dom.php');
        $this->load();
    }

    function load() {
        if (!file_exists(cfg('projects_dir').'index.html'))
            throw new Exception("Project list not found.");
        $this->dom = file_get_html($this->html_file);
    }

    function save() {
        include_lib('htmlindent/htmlindent.php');
        $html = clean_html_code($this->dom->save());
        if(!file_put_contents($this->html_file, $html))
            throw new Exception("Error writing '{$this->html_file}'.");
        return true;
    }

    function sync() {
        $dir_prjs = getDirs($this->folder);
        $dom_prjs = array();
        foreach($this->dom->find('#projects .project') as $project) {
            if(!in_array($project->id, $dir_prjs)) {
                if ($children = $project->parent()->find('ol',0)) {
                    $project->parent()->outertext = $children->innertext;
                } else {
                    $project->parent()->outertext = '';
                }
            } else {
                $dom_prjs[] = $project->id;
            }
        }
        $new_prjs = array_diff($dir_prjs,$dom_prjs);
        foreach ($new_prjs as $proj) {
            $li = "<li><div class=\"project status-offline\" id=\"$proj\" ><a href=\"$proj/project.html\" >$proj</a></div></li>";
            $this->dom->find("#projects",0)->innertext .= $li;
        }
        $this->save();
    }

    public function getProject($project_name) {
        return $dom->find('#'.$project_name,0);
    }

    public function setProject($project_name,$title=null,$uri=null) {
        if ($title!=null) {
            $dom->find('#'.$project_name.' a',0)->innertext = htmlentities($title);
        }
        if ($uri!=null) {
            $uri = normalize_str($uri);
            $project = new Project($project_name);
            $project->rename($uri);
            $dom->find('#'.$project_name.' a',0)->href = $uri.'/project.html';
            $dom->find('#'.$project_name,0)->id = $uri;
        }
    }

    public function getSetting($project_name,$key) {
        $settings = $this->getSettings($project_name);
        return $settings[$key];
    }

    //this should update the projects settings as well
    public function setSetting($project_name,$key,$value) {
        $settings = $this->getSettings($project_name);
        $settings[$key] = $value;
        $this->setSettings($project_name,$settings);
        //should update the projects list file
    }

    private function getSettings($project_name) {
        $class = $dom->find('#'.$project_name,0)->class;
        $classes = explode(" ", $class);
        if (empty($classes))
            return null;
        $settings = array();
        foreach ($classes as $class) {
            if (stripos($class, '-') !== false) {
                list($key, $val) = explode('-', $class);
                $settings[$key] = $val;
            } else {
                $settings[$class] = "";
            }
        }
        ksort($settings);
        return $settings;
    }

    private function setSettings($project_name,$array) {
        $class = "";
        $classes = array();
        ksort($array);
        foreach ($array as $key => $val)
            $classes[] = empty($val) ? $key : $key . '-' . $val;
        $dom->find('#'.$project_name,0)->class = implode(' ', $classes);
    }

    private function getMenu() {
        $tempdom = $this->dom;
        foreach($tempdom->find('.status-offline, .status-hidden') as $node)
            $node->parent()->outertext = '';
        return $tempdom->save();
    }

    public function __get($key) {
        switch ($key) {
            case 'list':
                return $this->dom->find('#projects',0)->innertext;
            case 'menu':
                return $this->getMenu();
        }
    }

    public function __set($key,$value) {
        switch ($key) {
            case 'list':
                $this->dom->find('#projects',0)->innertext = $value;
                break;
        }
    }

}
