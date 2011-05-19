<?php

/* -------------------------------------------------------------------
 *  CONTROLLERS
 * -------------------------------------------------------------------
 */

dispatch('/project/*','ctrl_project_view');
function ctrl_project_view() {
    print_r(projects()->getProject(param(1)));
}

dispatch('project/*?edit','ctrl_project_editurl');
function ctrl_project_editurl() {
    echo 'edit > '.param(1);
}

dispatch('image/*/*/**', 'ctrl_process_image');
function ctrl_process_image() {
    include_lib('image_processing/image_processing.php');
    $imageP = new ImageProcessing();
    $preset = param(1);

    if (param(2)=='ext') {
        $file = 'http://'.param(3);
    } else {
        $file = cfg('projects_dir').param(2).'/'.param(3);
    }
    
    $imageP->show($file,$preset);
}

dispatch('login','ctrl_login');
function ctrl_login() {
    global $cfg;
    $cfg['in_admin'] = true;
    $output['admin_title']='login';
    if (logging(getPost('username'), getPost('password'))) {
        redirect("admin/projects/");
    } else {
        output("login.html.php",$output);
    }
}

dispatch('logout','ctrl_logout');
function ctrl_logout() {
    logout();
    redirect("login");
}

/* -------------------------------------------------------------------
 *  PROJECTS LIST CONTROLLERS
 * -------------------------------------------------------------------
 */

dispatch('admin/projects', 'ctrl_admin_projects');
function ctrl_admin_projects() {
    in_admin();
    $output['admin_title'] = 'Projects list';
    output("projects_list.html.php", $output);
}

dispatch('admin/projects/*', 'ctrl_admin_project_edit');
function ctrl_admin_project_edit() {
    in_admin();
    if (projects()->get(param(1))->sync())
        projects()->save();
    $output['project'] = projects()->get(param(1));
    $output['templates'] = array('template a','template b','longerlongerlonger'); //$templates;
    $output['admin_title']  ='&laquo; back to list';
    output("project_edit.html.php", $output);
}

dispatch('admin/projects/reorder', 'ctrl_admin_projects_reorder');
function ctrl_admin_projects_reorder() {
    in_admin();
    parse_str(getPost('neworder'), $list);
    try {
        projects()->reorder($list['menu']);
        projects()->save();
        $json['message'] = "Projects reordered.";
        $json['message_type'] = MESSAGE_SUCCESS;
    } catch (Exception $e) {
        $json['message'] = $e->getMessage();
        $json['message_type'] = MESSAGE_ERROR;
    }
    echo json_encode($json);
}

dispatch('admin/projects/status', 'ctrl_admin_project_status');
function ctrl_admin_project_status() {
    in_admin();
    try {
        $allowed_status = array ('offline','online','hidden');
        if (!in_array(getPost('new_status'), $allowed_status))
                throw new Exception("Status '".getPost('new_status')."' is not allowed.");
        projects()->get(getPost('project_slug'))->status = getPost('new_status');
        projects()->save();
        $json['message'] = "Project's status udpated.";
        $json['message_type'] = MESSAGE_SUCCESS;
    } catch (Exception $e) {
        $json['message'] = $e->getMessage();
        $json['message_type'] = MESSAGE_ERROR;
    }
    echo json_encode($json);
}

dispatch('admin/projects/delete/*', 'ctrl_admin_project_delete');
function ctrl_admin_project_delete() {
    in_admin();
    try {
        projects()->delete(param(1));
        projects()->save();
        $json['message'] = "Project deleted.";
        $json['message_type'] = MESSAGE_SUCCESS;
    } catch (Exception $e) {
        $json['message'] = $e->getMessage();
        $json['message_type'] = MESSAGE_ERROR;
    }
    echo json_encode($json);
}

dispatch('admin/projects/create', 'ctrl_admin_project_create');
function ctrl_admin_project_create() {
    in_admin();
    try {
        projects()->add(getPost('project_title',false));
        projects()->save();
        $json['message'] = "Project created.";
        $json['message_type'] = MESSAGE_SUCCESS;
    } catch (Exception $e) {
        $json['message'] = $e->getMessage();
        $json['message_type'] = MESSAGE_ERROR;
    }
    echo json_encode($json);
}

dispatch('admin/projects/*/reorder', 'ctrl_admin_project_reorder');
function ctrl_admin_project_reorder() {
    in_admin();
    parse_str(getPost('neworder'), $list);
    try {
        projects()->get(param(1))->reorder($list['item']);
        projects()->save();
        $json['message'] = "Gallery reordered.";
        $json['message_type'] = MESSAGE_SUCCESS;
    } catch (Exception $e) {
        $json['message'] = $e->getMessage();
        $json['message_type'] = MESSAGE_ERROR;
    }
    echo json_encode($json);
}

dispatch('admin/projects/*/uploaditem', 'ctrl_admin_project_uploaditem');
function ctrl_admin_project_uploaditem() {
    in_admin();

    //filename
    if ((!empty($_FILES["user_file"])) && ($_FILES['user_file']['error'][0] != 0)) {
        $json['message'] = "No file uploaded.";
        $json['message_type'] = MESSAGE_ERROR;
        die(json_encode($json));
    }

    //Check if the file is JPEG image and it's size is less than 350Kb
    $filename = basename($_FILES['user_file']['name'][0]);
    $ext = substr($filename, strrpos($filename, '.') + 1);

    if (($ext != "jpg") && ($_FILES["user_file"]["type"][0] != "image/jpeg")) {
        $json['message'] = "Filetype not allowed.";
        $json['message_type'] = MESSAGE_ERROR;
        die(json_encode($json));
    }

    if ($_FILES["user_file"]["size"][0] > 350000) {
        $json['message'] = "File too big.";
        $json['message_type'] = MESSAGE_ERROR;
        die(json_encode($json));
    }

    $i=0;
    $newname = cfg('projects_dir') . param(1).'/'.$filename;
    while(file_exists($newname)) {
        if ($i++>100) die('error');
        $newname = cfg('projects_dir') . param(1).'/'.str_replace('.'.$ext, '_'.$i.'.'.$ext, $filename);
    }

    if (!move_uploaded_file($_FILES['user_file']['tmp_name'][0], $newname)) {
        $json['message'] = "Filetype not allowed.";
        $json['message_type'] = MESSAGE_ERROR;
        die(json_encode($json));
    }

    //success

    $json['message'] = "File '$newname' uploaded.";
    $json['message_type'] = MESSAGE_SUCCESS;
    die(json_encode($json));
}

dispatch('admin/projects/*/deleteitem', 'ctrl_admin_project_deleteitem');
function ctrl_admin_project_deleteitem() {
    in_admin();
    try {
        projects()->get(param(1))->deleteItem(getPost('itemid'));
        projects()->save();
        $json['message'] = "Item deleted.";
        $json['message_type'] = MESSAGE_SUCCESS;
    } catch (Exception $e) {
        $json['message'] = $e->getMessage();
        $json['message_type'] = MESSAGE_ERROR;
    }
    echo json_encode($json);
}

dispatch('admin/projects/*/updateitem', 'ctrl_admin_project_updateitem');
function ctrl_admin_project_updateitem() {
    in_admin();
    try {
        if (getPost('uid')=='new') {
            $data = new stdClass();
            $data->type = getPost('type');
            $item = projects()->get(param(1))->addItem($data);
        } else {
            $item = projects()->get(param(1))->getItem(getPost('uid'));
        }
        $item->title = strip_tags(getPost('title'));
        $item->caption = strip_tags(getPost('caption',false),cfg('allowed_tags'));
        
        if ((getPost('src')!=null) && ($item->type==Project::ITEM_TYPE_EMBED)) {
            if(filter_var(getPost('src'), FILTER_VALIDATE_URL)) {
                $item->src = getPost('src');
            } else {
                $json['message'] = "The url you provided is not valid.";
                $json['message_type'] = MESSAGE_ERROR;
                die(json_encode($json));
            }
        }

        projects()->save();
        $json['message'] = "Item updated.";
        $json['message_type'] = MESSAGE_SUCCESS;
    } catch (Exception $e) {
        $json['message'] = $e->getMessage();
        $json['message_type'] = MESSAGE_ERROR;
    }
    echo json_encode($json);
}



