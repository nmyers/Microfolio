<?php



/**
 *
 * @param <type> $filter
 *                  filter = 0  everything
 *                  filter = 1  removes offline
 *                  filter = 2  removes offline and hidden
 * @param <type> $sync
 */
function model_projects_list_read($filter=0,$sync=false) {
    if (!file_exists(cfg('projects_dir').'index.html'))
        throw new Exception("Project list not found.");

    include_lib('simple_html_dom/simple_html_dom.php');
    $list_dom = file_get_html(cfg('projects_dir').'index.html');

    if ($sync) {
        $dir_prjs = getDirs(cfg('projects_dir'));
        $dom_prjs = array();
        foreach($list_dom->find('.project') as $project) {
            if(!in_array($project->id, $dir_prjs)) {
                //replace lost project with its children hierarchy
                $project->parent()->outertext = $project->parent()->find('ol',0)->innertext;
            } else {
                $dom_prjs[] = $project->id;
            }
        }

        // Adds new DIVs for new project folders
        $new_prjs = array_diff($dir_prjs,$dom_prjs);
        foreach ($new_prjs as $proj) {
            $li = "<li><div class=\"project status-offline\" id=\"$proj\" ><a href=\"$proj/project.html\" >$proj</a></div></li>";
            $list_dom->find("#list-projects",0)->innertext .= $li;
        }
    }

    if ($filter>0) {
        $elem_to_remove = 'div[class*=status-offline]';
        if ($filter>1) $elem_to_remove .= ', div[class*=status-hidden]';
        foreach($list_dom->find($elem_to_remove) as $elem)
                $elem->parent()->outertext = '';
        $list_dom = str_get_html($list_dom->save());
    }

    return $list_dom;
}

function model_projects_list_update($update_dom) {
    $list_dom = model_projects_list_read();

    //project list and settings
    if ($update_dom->find("#list-projects", 0)) {
        $list_dom->find("#list-projects", 0)->innertext = $update_dom->find("#list-projects", 0)->innertext;
    }
    include_lib('htmlindent/htmlindent.php');
    $html = clean_html_code($list_dom->save());
    file_put_contents(cfg('projects_dir').'index.html', $html);
    return true;
}

/**
 * ------
 * Project
 * ------
 */

/**
 * Creates a new project
 *
 * @param string $project_name
 */
function model_project_create($project_name) {
    $project_name_norm = normalize_str($project_name);
    $projects = getDirs(cfg('projects_dir'));
    if (in_array($project_name_norm, $projects)) {
        throw new Exception('This project already exists.');
    }
    if (!mkdir(cfg('projects_dir') . $project_name_norm)) {
        throw new Exception('Error creating the folder.');
    }
    $html = output("empty_project.html.php", array('project_name' => $project_name), true);
    if (!file_put_contents(getProjectPath($project_name) . "/project.html", $html)) {
        throw new Exception('Error writing "project.html".');
    }
    return true;
}

/**
 * Reads and returns a project's dom
 * @todo if sync is set to true, new images will be added to the returned dom
 *
 * @param <type> $project_name
 * @return <type>
 */
function model_project_read($project_name, $sync=false) {
    if (!file_exists(getProjectPath($project_name)))
        throw new Exception("Project '$project_name' not found.");

    include_lib('simple_html_dom/simple_html_dom.php');
    $project_dom = file_get_html(getProjectPath($project_name).'/project.html');
    if ($sync) {

        $dir_imgs = getFiles(getProjectPath($project_name).'/', '/\.(jpg|jpeg)/i');
        $dom_imgs = array();

        //remove dom elements with no file
        foreach ($project_dom->find('#gallery a.image') as $img) {
            if (!in_array($img->href, $dir_imgs)) {
                $img->parent()->outertext = ""; //not in dir? > remove div
            } else {
                $dom_imgs[] = $img->href;
            }
        }

        //add dom elements for new files
        $new_imgs = array_diff($dir_imgs, $dom_imgs);
        foreach ($new_imgs as $new_img) {
            $div = "<div class=\"media image\" >";
            $div.= "   <a href=\"$new_img\" title=\"$new_img\" class=\"image\" >";
            $div.= "     <img src=\"$new_img\" alt=\"$new_img\" />";
            $div.= "   </a>";
            $div.= "   <div class=\"caption\" > </div>"; //space left on purpose!
            $div.= "</div>";
            $project_dom->find('#gallery',0)->innertext .= $div;
        }
    }
    return $project_dom;
}

/**
 *
 * @param <type> $project_name
 * @param <type> $project_dom
 */
function model_project_update($project_name, $update_dom) {

    //loads current project dom
    //changes will be made to this dom and saved at the end
    $project_dom = model_project_read($project_name);

    //Project status
    
    if ($update_dom->find("#project", 0)->class) {
        $settings_old = classToArray($project_dom->find("#project", 0)->class);
        $settings_new = classToArray($update_dom->find("#project", 0)->class);
        $settings_new = array_merge($settings_old, $settings_new);
        $project_dom->find("#project", 0)->class = arrayToClass($settings_new);
        //update the projects list
    }

    //Project title / name
    if ($update_dom->find("#title", 0)) {
        $new_title = $update_dom->find("#title", 0)->innertext;
        $project_dom->find("#title", 0)->innertext = $new_title;
        /*
         *
         *
         * 
        if (normalize_str($new_title) !== normalize_str($project_name)) {
            if (!rename(getProjectPath($project_name), getProjectPath($new_title)))
                throw new Exception("Could not rename the folder: '$project_name'");
            //@todo update the projects list
            $project_name = normalize_str($new_title);
        }*/
    }

    //Project presentation
    if ($update_dom->find("#presentation", 0)) {
        $new_presentation = strip_tags($update_dom->find("#presentation", 0)->innertext, cfg('allowed_tags'));
        $new_presentation = $new_presentation;
        $project_dom->find("#presentation", 0)->innertext = $new_presentation;
    }

    //Project gallery
    if ($update_dom->find("#gallery", 0)) {
        //check for images that are in the folder but not in the new dom
        $dir_imgs = getFiles(getProjectPath($project_name) . '/', '/\.(jpg|jpeg)/i');
        $upd_imgs = array();
        foreach ($update_dom->find('#gallery a.image') as $img)
            $upd_imgs[] = $img->href;
        //delete media that are not in the new dom
        $diff_imgs = array_diff($dir_imgs, $upd_imgs);
        foreach ($diff_imgs as $file) {
            if (!unlink(getProjectPath($project_name) . '/' . $file))
                throw new Exception("Could not delete the file: '$file'");
        }
        $project_dom->find("#gallery", 0)->innertext = $update_dom->find("#gallery", 0)->innertext;
    }

    //saves the updated dom
    include_lib('htmlindent/htmlindent.php');
    $html = clean_html_code($project_dom->save());
    file_put_contents(getProjectPath($project_name).'/project.html', $html);
    return true;
}

/**
 * Delete project
 *
 * @param <type> $project_name
 * @return <type>
 */
function model_project_delete($project_name) {
    $project_name_norm = normalize_str($project_name);
    if (!file_exists(getProjectPath($project_name))) {
        throw new Exception("Project '$project_name' not found.");
    }
    foreach (getFiles(getProjectPath($project_name)) as $file) {
        if (!unlink(getProjectPath($project_name) . '/' . $file))
            throw new Exception("Could not delete the file: '$file'");
    }
    if (!rmdir(getProjectPath($project_name))) {
        throw new Exception("Could not delete the folder: '$project_name'");
    }
    return true;
}

//--------------- helpers


function getProjectPath($project_name) {
    return cfg('projects_dir') . normalize_str($project_name);
}

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

