<?php


/**
 * 
 * 
 * $prj = new Project('test');  // if 'test' exists : loads the project, if not create a new one
 * $prj->create('test'); //creates a new project
 * $prj->load('test');  //loads an existing project
 * $prj->save();   //saves the current dom in file
 * $prj->delete(); //deletes the whole project (folder and files)
 * $prj->sync();   //syncs the project data with actual content of folder
 * $prj->rename(); //renames a project (rename folder)
 *
 *
 * $prj->name         //read only
 * $prj->title        //text
 * $prj->description  //html content
 * $prj->settings     //assoc array
 * eg: $prj->settings['template'] = 'new-template';
 *
 * $prj->gallery      //gallery dom or assoc array
 *
 *
 */
class Project {
    
    private $name;
    private $folder;
    private $html_file;

    private $dom;
    private $gallery;
    
    function __construct($name){

        //set main properties
        $this->name = normalize_str($name);
        $this->folder = cfg('projects_dir') . $this->name;
        $this->html_file = $this->folder . '/project.html';

        //dom parser
        include_lib('simple_html_dom/simple_html_dom.php');

        //loads project if name exists, creates otherwise
        if ($this->exists()) {
            $this->load();
        } else {
            $this->create();
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

    /**
     *
     */

    public function __get($key) {
        switch ($key) {
            case 'title': return $this->dom->find('#title',0)->innertext;
            case 'presentation': return $this->dom->find('#presentation',0)->innertext;
            case 'gallery': return $this->dom->find('#gallery',0)->innertext;
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

    public function __get($key) {
        switch ($key) {
            case 'list':
                return $this->dom->find('#projects',0)->innertext;
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

//
///**
// *
// * @param <type> $filter
// *                filter = 0  everything
// *                filter = 1  removes offline
// *                filter = 2  removes offline and hidden
// * @param <type> $sync
// */
//function model_projects_list_read($filter=0,$sync=false) {
//    if (!file_exists(cfg('projects_dir').'index.html'))
//        throw new Exception("Project list not found.");
//
//    include_lib('simple_html_dom/simple_html_dom.php');
//    $list_dom = file_get_html(cfg('projects_dir').'index.html');
//
//    if ($sync) {
//        $dir_prjs = getDirs(cfg('projects_dir'));
//        $dom_prjs = array();
//        foreach($list_dom->find('.project') as $project) {
//            if(!in_array($project->id, $dir_prjs)) {
//                //replace lost project with its children hierarchy
//                if ($children = $project->parent()->find('ol',0)) {
//                    $project->parent()->outertext = $children->innertext;
//                } else {
//                    $project->parent()->outertext = '';
//                }
//            } else {
//                $dom_prjs[] = $project->id;
//            }
//        }
//
//        // Adds new DIVs for new project folders
//        $new_prjs = array_diff($dir_prjs,$dom_prjs);
//        foreach ($new_prjs as $proj) {
//            $li = "<li><div class=\"project status-offline\" id=\"$proj\" ><a href=\"$proj/project.html\" >$proj</a></div></li>";
//            $list_dom->find("#list-projects",0)->innertext .= $li;
//        }
//
//        //should be saved at this point
//        model_projects_list_update($list_dom);
//    }
//
//    if ($filter>0) {
//        $elem_to_remove = 'div[class*=status-offline]';
//        if ($filter>1) $elem_to_remove .= ', div[class*=status-hidden]';
//        foreach($list_dom->find($elem_to_remove) as $elem)
//                $elem->parent()->outertext = '';
//        $list_dom = str_get_html($list_dom->save());
//    }
//
//    return $list_dom;
//}
//
//function model_projects_list_update($update_dom) {
//    $list_dom = model_projects_list_read();
//
//    //project list and settings
//    if ($update_dom->find("#list-projects", 0)) {
//        $list_dom->find("#list-projects", 0)->innertext = $update_dom->find("#list-projects", 0)->innertext;
//    }
//    include_lib('htmlindent/htmlindent.php');
//    $html = clean_html_code($list_dom->save());
//    file_put_contents(cfg('projects_dir').'index.html', $html);
//    return true;
//}

/**
 * ------
 * Project
 * ------
 */
//
///**
// * Creates a new project
// *
// * @param string $project_name
// */
//function model_project_create($project_name) {
//    $project_name_norm = normalize_str($project_name);
//    $projects = getDirs(cfg('projects_dir'));
//    if (in_array($project_name_norm, $projects)) {
//        throw new Exception('This project already exists.');
//    }
//    if (!mkdir(cfg('projects_dir') . $project_name_norm)) {
//        throw new Exception('Error creating the folder.');
//    }
//    $html = output("empty_project.html.php", array('project_name' => $project_name), true);
//    if (!file_put_contents(getProjectPath($project_name) . "/project.html", $html)) {
//        throw new Exception('Error writing "project.html".');
//    }
//    return true;
//}
//
///**
// * Reads and returns a project's dom
// * @todo if sync is set to true, new images will be added to the returned dom
// *
// * @param <type> $project_name
// * @return <type>
// */
//function model_project_read($project_name, $sync=false) {
//    if (!file_exists(getProjectPath($project_name)))
//        throw new Exception("Project '$project_name' not found.");
//
//    include_lib('simple_html_dom/simple_html_dom.php');
//    $project_dom = file_get_html(getProjectPath($project_name).'/project.html');
//    if ($sync) {
//
//        $dir_imgs = getFiles(getProjectPath($project_name).'/', '/\.(jpg|jpeg)/i');
//        $dom_imgs = array();
//
//        //remove dom elements with no file
//        foreach ($project_dom->find('#gallery a.image') as $img) {
//            if (!in_array($img->href, $dir_imgs)) {
//                $img->parent()->outertext = ""; //not in dir? > remove div
//            } else {
//                $dom_imgs[] = $img->href;
//            }
//        }
//
//        //add dom elements for new files
//        $new_imgs = array_diff($dir_imgs, $dom_imgs);
//        foreach ($new_imgs as $new_img) {
//            $div = "<div class=\"media image\" >";
//            $div.= "   <a href=\"$new_img\" title=\"$new_img\" class=\"image\" >";
//            $div.= "     <img src=\"$new_img\" alt=\"$new_img\" />";
//            $div.= "   </a>";
//            $div.= "   <div class=\"caption\" > </div>"; //space left on purpose!
//            $div.= "</div>";
//            $project_dom->find('#gallery',0)->innertext .= $div;
//        }
//
//        //save the changes
//        model_project_update($project_name, $project_dom);
//    }
//    return $project_dom;
//}
//
///**
// *
// * @param <type> $project_name
// * @param <type> $project_dom
// */
//function model_project_update($project_name, $update_dom) {
//
//    //loads current project dom
//    //changes will be made to this dom and saved at the end
//    $project_dom = model_project_read($project_name);
//
//    //Project status
//
//    if ($update_dom->find("#project", 0)->class) {
//        $settings_old = classToArray($project_dom->find("#project", 0)->class);
//        $settings_new = classToArray($update_dom->find("#project", 0)->class);
//        $settings_new = array_merge($settings_old, $settings_new);
//        $project_dom->find("#project", 0)->class = arrayToClass($settings_new);
//        //update the projects list
//    }
//
//    //Project title / name
//    if ($update_dom->find("#title", 0)) {
//        $new_title = $update_dom->find("#title", 0)->innertext;
//        $project_dom->find("#title", 0)->innertext = $new_title;
//        /*
//         * Renaming the folder should be done somewhere else...
//         *
//         *
//        if (normalize_str($new_title) !== normalize_str($project_name)) {
//            if (!rename(getProjectPath($project_name), getProjectPath($new_title)))
//                throw new Exception("Could not rename the folder: '$project_name'");
//            //@todo update the projects list
//            $project_name = normalize_str($new_title);
//        }*/
//    }
//
//    //Project presentation
//    if ($update_dom->find("#presentation", 0)) {
//        $new_presentation = strip_tags($update_dom->find("#presentation", 0)->innertext, cfg('allowed_tags'));
//        $new_presentation = $new_presentation;
//        $project_dom->find("#presentation", 0)->innertext = $new_presentation;
//    }
//
//    //Project gallery
//    if ($update_dom->find("#gallery", 0)) {
//        //check for images that are in the folder but not in the new dom
//        $dir_imgs = getFiles(getProjectPath($project_name) . '/', '/\.(jpg|jpeg)/i');
//        $upd_imgs = array();
//        foreach ($update_dom->find('#gallery a.image') as $img)
//            $upd_imgs[] = $img->href;
//        //delete media that are not in the new dom
//        $diff_imgs = array_diff($dir_imgs, $upd_imgs);
//        foreach ($diff_imgs as $file) {
//            if (!unlink(getProjectPath($project_name) . '/' . $file))
//                throw new Exception("Could not delete the file: '$file'");
//        }
//        $project_dom->find("#gallery", 0)->innertext = $update_dom->find("#gallery", 0)->innertext;
//    }
//
//    //saves the updated dom
//    include_lib('htmlindent/htmlindent.php');
//    $html = clean_html_code($project_dom->save());
//    file_put_contents(getProjectPath($project_name).'/project.html', $html);
//    return true;
//}
//
///**
// * Delete project
// *
// * @param <type> $project_name
// * @return <type>
// */
//function model_project_delete($project_name) {
//    $project_name_norm = normalize_str($project_name);
//    if (!file_exists(getProjectPath($project_name))) {
//        throw new Exception("Project '$project_name' not found.");
//    }
//    foreach (getFiles(getProjectPath($project_name)) as $file) {
//        if (!unlink(getProjectPath($project_name) . '/' . $file))
//            throw new Exception("Could not delete the file: '$file'");
//    }
//    if (!rmdir(getProjectPath($project_name))) {
//        throw new Exception("Could not delete the folder: '$project_name'");
//    }
//    return true;
//}
//
//
///**
// * Renaming a project means renaming the folder but not changing the project title
// *
// * @param <type> $old_project_name
// * @param <type> $new_project_name
// */
//function model_project_rename($old_project_name,$new_project_name) {
//    //check if project exists
//    if (!file_exists(getProjectPath($project_name)))
//        throw new Exception("Project '$project_name' not found.");
//
//    if (normalize_str($old_project_name) !== normalize_str($new_project_name))
//        throw new Exception("Projects have the same name.");
//
//    if (!rename(getProjectPath($old_project_name), getProjectPath($new_project_name)))
//        throw new Exception("Could not rename the folder: '$old_project_name'");
//
//    //rename project in the list
//    $list_dom = model_projects_list_read();
//    $item = $list_dom->find('#'.normalize_str($old_project_name),0);
//    if ($item) {
//       $item->id = normalize_str($new_project_name);
//       $item->find('a',0)->href = normalize_str($new_project_name).'/project.html';
//    }
//
//    return true;
//}



//--------------- helpers

//
//function getProjectPath($project_name) {
//    return cfg('projects_dir') . normalize_str($project_name);
//}

function classToArray($class) {
    $prj_classes = explode(" ", $class);
    if (empty($prj_classes))
        return array();
    $prj_settings = array();
    foreach ($prj_classes as $class) {
        if (stripos($class, '-') !== false) {
            list($key, $val) = explode('-', $class);
            $prj_settings[$key] = $val;
        } else {
            $prj_settings[$class] = "";
        }
    }
    ksort($prj_settings);
    return $prj_settings;
}

function arrayToClass($array) {
    $class = "";
    $classes = array();
    foreach ($array as $key => $val)
        $classes[] = empty($val) ? $key : $key . '-' . $val;
    return implode(' ', $classes);
}

