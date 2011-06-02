<?php



class Projects implements Iterator {

    private static $instance;
    private $position = 0;

    public static $folder = 'content/';
    private $jsonFile = 'content.json';

    public $settings;
    public $projects;
    public $theme;

    private function __construct() {
        $this->position = 0;
        $this->load();
    }

    public static function singleton() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    // Prevent users to clone the instance
    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Loads the JSON file and creates Project objects,
     * stored in $this->projects (assoc array with project slugs as key)
     *
     */
    public function load() {
        $data = json_decode(file_get_contents(Projects::$folder.$this->jsonFile));
        foreach ($data as $key => $value) {
            if (is_object($value)) continue;
            if (property_exists($this,$key)) $this->$key = $value;
        }
        foreach ($data->projects as $project_slug => $project) {
            if (file_exists(Projects::$folder.$project_slug))
                $this->projects[$project_slug] = new Project($project);
        }
    }

    /**
     * Converts the projects back to json and saves the file
     */
    public function save() {
        if (!rename(Projects::$folder.$this->jsonFile,Projects::$folder.$this->jsonFile.'.bak'))
                throw new Exception("Can't rename json file.");
        if (!file_put_contents(Projects::$folder.$this->jsonFile, $this->toJSON()))
                throw new Exception("Can't write json file.");
        return true;
    }

    /**
     * Synchronizes the object with the actual content of the projects folder
     *
     */
    public function sync() {
        $files = scandir(Projects::$folder);
        $project_slugs = array_keys($this->projects);
        foreach ($files as $file) {
            $isNew = $file != '.' && $file != '..';
            $isNew = $isNew && is_dir(Projects::$folder . '/' . $file);
            $isNew = $isNew && !in_array($file, $project_slugs);
            if ($isNew) {
                $this->projects[$file] = new Project(array(
                    "timestamp" => time(),
                    "title"     => $file,
                    "slug"      => $file,
                    "status"    => Project::PROJECT_OFFLINE,
                    "parent"    => "root",
                ));
            }
        }
        //sync projects ?
        foreach ($this->projects as $project)
                $project->sync();
    }

    /**
     * Returns an HTML representation of the menu
     *
     * @param boolean $filter Removes hidden and offline projects if set to true
     */
    public function getMenu($filter=true,$level='root') {
        if ($level=='root') {
           $html = "<ol id='menu' >\n";
        } else {
           $html = "<ol id='projects_$level'>\n";
        }
        foreach ($this->projects as $key => $project) {
            $is_visible = ($project->status==Project::PROJECT_ONLINE) || (!$filter);
            if (($project->parent == $level) && ($is_visible)) {
                $html .= "<li id=\"menu_".$project->slug."\" ><div data-status=\"".$project->status."\" data-slug=\"".$project->slug."\" ><a href=\"".  makeUrl('project/'.$project->slug)."\" >".$project->title."</a></div>\n";
                $html .= $this->getMenu($filter,$key);
                $html .= "</li>\n";
            }
        }
        $html .= "</ol>\n";
        return $html;
    }

    /**
     * Reorders the projects according to the array
     * @see jquery nested sortable
     *
     * @param array $array
     */
    public function reorder($new_order) {
        $tmp_projects = array();
        if (count($new_order)!=count($this->projects))
               throw new Exception("Error reordering projects, counts don't match.");
        foreach ($new_order as $slug => $parent) {
            if (isset ($this->projects[$slug])) {
                $tmp_projects[$slug] = $this->projects[$slug];
                $this->projects[$slug]->parent = $parent;
            } else {
                throw new Exception("Error reordering projects, unknown: '$slug'.");
            }
        }
        $this->projects = $tmp_projects;
        return true;
    }

    /**
     * Returns the project matching the slug
     *
     * @param string $projectSlug
     */
    public function get($projectSlug) {
        if (isset ($this->projects[$projectSlug]))
            return $this->projects[$projectSlug];
        throw new Exception("Cannot find project '$projectSlug'."); 
    }

    /**
     * Adds a new project
     *
     * @param string $projectTitle
     */
    public function add($projectTitle) {
        $projectSlug = normalize_str($projectTitle);
        if (!isset ($this->projects[$projectSlug])) {
            $this->projects[$projectSlug] = new Project();
            $this->projects[$projectSlug]->create($projectTitle);
            return $this->projects[$projectSlug];
        }
        throw new Exception("Cannot add '$projectTitle', project already exists.");
    }

    /**
     * Deletes the project matching the slug
     * 
     * @param string $projectSlug
     */
    public function delete($projectSlug) {
        if (isset ($this->projects[$projectSlug])) {
            //delete
            $this->projects[$projectSlug]->delete();
            unset($this->projects[$projectSlug]);
            return true;
        }
        throw new Exception("Cannot delete '$projectTitle', project does not exist.");
    }

    public function rename($oldProjectSlug,$newProjectSlug) {
        $newProjectSlug = normalize_str($newProjectSlug);

        //check if it's a valid new slug
        if (isset ($this->projects[$newProjectSlug])) {
            throw new Exception("Cannot rename, '$newProjectSlug' already exists.");
        }

        if (!rename(Projects::$folder.$oldProjectSlug,Projects::$folder.$newProjectSlug))
                throw new Exception("Can't rename folder '$oldProjectSlug' in '$newProjectSlug'");
        $this->projects[$oldProjectSlug]->slug = $newProjectSlug;

        //replace with new key at the same position
        $offset = array_search($oldProjectSlug,array_keys($this->projects));
        if ($offset!==false) {
            $this->projects = array_slice($this->projects, 0, $offset, true) +
                      array($newProjectSlug => $this->projects[$oldProjectSlug]) +
                      array_slice($this->projects, $offset+1, NULL, true);
        }

        //rename all the "parents" value
        foreach($this->projects as $key => $project) {
            if ($project->parent==$oldProjectSlug)
                    $project->parent = $newProjectSlug;
        }

        return $this->projects[$newProjectSlug];
    }

    
    /**
     * Converts this object to JSON
     */
    public function toJSON() {
        return indentJSON(json_encode($this));
    }

    //------------------------
    // ITERATOR METHODS
    //------------------------ 

    function rewind() {
        $this->position = 0;
    }

    function current() {
        $keys = array_keys($this->projects);
        $key = $keys[$this->position];
        return $this->projects[$key];
    }

    function key() {
        $keys = array_keys($this->projects);
        return $keys[$this->position];
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        $keys = array_keys($this->projects);
        return isset($keys[$this->position]);
    }

}

class Project implements Iterator {

    const PROJECT_OFFLINE = 'offline';
    const PROJECT_HIDDEN  = 'hidden';
    const PROJECT_ONLINE  = 'online';

    const ITEM_TYPE_EMBED = 'embed';
    const ITEM_TYPE_IMAGE = 'image';

    private $position = 0;
    private $loaded = false;

    public $slug;

    public $title;
    public $status;
    public $timestamp;
    public $parent;
    public $style;
    public $text;

    public $thumbnail_uid;

    public $gallery;

    public function __construct($data=null) {
        $this->position = 0;
        if ($data!=null) $this->load($data);
    }

    public function load($data) {
        foreach ($data as $key => $value) {
            if (is_object($value)) continue;
            if (property_exists($this,$key)) $this->$key = $value;
        }
        if (isset($data->gallery)) {
            foreach ($data->gallery as $uid => $item) {
                $item->slug = $this->slug;
                $item->uid = $uid;
                if ($item->type!=Project::ITEM_TYPE_EMBED) {
                   if (file_exists(Projects::$folder.  $this->slug.'/'.$item->src))
                       $this->gallery[$uid] = new GalleryItem($item);
                } else {
                   $this->gallery[$uid] = new GalleryItem($item);
                }
            }
        }
        $this->loaded = true;
        return $this;
    }

    public function create($title) {
        if ($this->loaded)
                throw new Exception("Project already loaded, cannot create '$title'.");
        $this->title = $title;
        $this->slug = normalize_str($title);
        $this->timestamp = time();
        $this->parent = 'root';
        $this->style = 'default';
        $this->status = 'offline';
        if (file_exists(Projects::$folder.$this->slug))
                throw new Exception("Cannot create new folder: '".Projects::$folder.$this->slug."' .");
        mkdir(Projects::$folder.$this->slug);
        return $this;
    }

    public function reorder($new_order) {
        $new_gallery = array();
        if (count($new_order)!=count($this->gallery))
                throw new Exception("Cannot reorder. Item counts dont match.");
        foreach ($new_order as $uid) {
            $uid = 'item_'.str_replace('item_', '', $uid);
            if (isset($this->gallery[$uid]))
                $new_gallery[$uid] = $this->gallery[$uid];
            else
                throw new Exception("Cannot reorder. Item uid does not exist: $uid.");
        }
        $this->gallery = $new_gallery;
        return $this;
    }

    public function delete() {
        try {
            recursiveDelete(Projects::$folder.$this->slug);
        } catch (Exception $e) {
            throw new Exception("Cannot delete project ".$this->slug);
        }
    }
    
    public function sync() {
        //add new image files
        $folder = Projects::$folder.$this->slug.'/';
        $files = scandir($folder);
        $items = array();
        if (isset($this->gallery)) {
            foreach ($this->gallery as $item){
                if ($item->type!=Project::ITEM_TYPE_EMBED) {
                    $items[] = $item->src;
                }
            }
        }
        $has_changed = false;
        foreach ($files as $file) {
            $isNew = is_file($folder. $file);
            $isNew = $isNew && stripos($file, '.jpg');
            $isNew = $isNew && !in_array($file, $items);
            if ($isNew) {
                $item = new stdClass();
                $item->type = Project::ITEM_TYPE_IMAGE;
                $item->src  = $file;
                $item->slug = $this->slug;
                $this->addItem($item);
                $has_changed = true;
            }
        }
        return $has_changed;
    }

    public function render() {
        $html = '<div class="gallery project-'.$this->slug.'" >'."\n\n";
        if (!empty($this->gallery)) {
            foreach ($this->gallery as $key => $item){
                $html .= sprintf('<div class="item type-%s" title="%s" >',$item->type,$item->title);
                $html .= "\n".$item->render()."\n";
                $html .= sprintf('<div class="title" >%s</div>',$item->title)."\n";
                $html .= sprintf('<div class="caption" >%s</div>',$item->caption)."\n";
                $html .= "</div>\n\n";
            }
        }
        $html .= "</div><!-- end of gallery -->\n";
        return $html;
    }


    public function getItem($uid) {
        if (isset($this->gallery[$uid]))
            return $this->gallery[$uid];
        throw new Exception("Cannot find item '$uid'.");
    }

    public function addItem($data) {
        //the url/file need to exists.
        $data->slug = $this->slug;
        if ($data->type!=Project::ITEM_TYPE_EMBED)
            if (!file_exists(Projects::$folder.$this->slug.'/'.$data->src))
                throw new Exception("Cannot add item, file does not exist: ".$data->src.".");
        $uid = uniqid('item_');
        $data->uid = $uid;
        $this->gallery[$uid] = new GalleryItem($data);
        return $this->gallery[$uid];
    }

    public function deleteItem($uid) {
        if (isset($this->gallery[$uid])) {
            //delete file if needed
            if ($this->gallery[$uid]->type!=Project::ITEM_TYPE_EMBED)
                unlink(Projects::$folder.$this->slug.'/'.$this->gallery[$uid]->src);
            unset($this->gallery[$uid]);
            return true;
        }
        throw new Exception("Cannot find item '$uid'.");
    }


    //------------------------
    // ITERATOR METHODS
    //------------------------ 

    function rewind() {
        $this->position = 0;
    }

    function current() {
        $keys = array_keys($this->gallery);
        $key = $keys[$this->position];
        return $this->gallery[$key];
    }

    function key() {
        $keys = array_keys($this->gallery);
        return $keys[$this->position];
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        if (empty ($this->gallery)) return false;
        $keys = array_keys($this->gallery);
        return isset($keys[$this->position]);
    }

}

class GalleryItem {

    private $uid;
    private $slug;

    public $type;
    public $title;
    public $caption;
    public $src;


    public function __construct($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this,$key)) $this->$key = $value;
        }
    }

    public function  __get($name) {
        switch ($name) {
            case 'uid':
                return $this->uid;
                break;
        };
    }
    
    public function render($resize_code='full', $force_thumbnail=false) {
        if (preg_match('/(r|c|t|f)(\d+)x(\d+)/i', $resize_code, $matches)) {

            $r = $matches[1];
            $w = $matches[2];
            $h = $matches[3];

            if ($this->type == 'embed') {
                if ($w < 250 || $h < 250 || $force_thumbnail) {
                    $url = str_replace('http://', '', getEmbedCode($this->src, true));
                    return sprintf('<img src="%s" alt="%s" width="%s" height="%s" class="embed %s" />',
                            makeUrl('image/' . $resize_code . '/ext/' . $url), $this->title,
                            $w, $h, $resize_code);
                } else {
                    return getEmbedCode($this->src);
                }
            } elseif ($this->type == 'image') {
                return sprintf('<img src="%s" alt="%s" width="" height="" class="image %s" />',
                        makeUrl('image/' . $resize_code . '/' . $this->slug . '/' . $this->src), $this->title,
                        $w, $h, $resize_code);
            }
        } elseif ($resize_code == 'full') {
            if ($this->type == 'embed') {
                return getEmbedCode($this->src);
            } elseif ($this->type == 'image') {
                return sprintf('<img src="%s" alt="%s" class="image full" />',
                        makeUrl('image/' . $resize_code . '/' . $this->slug . '/' . $this->src), $this->title);
            }
        }
    }
}


function getEmbedCode($url,$preview=false) {

    //youtube
    $pattern = '/^http:\/\/(?:www\.)?youtube.com\/watch\?(?=.*v=([\w-]+))(?:\S+)?$/i';
    if (preg_match($pattern, $url, $matches))
    if ($preview) {
        return 'http://img.youtube.com/vi/'.$matches[1].'/0.jpg';
    } else {
        return '<div class="embed" ><iframe title="YouTube video player" src="http://www.youtube.com/embed/'.$matches[1].'?rel=0" frameborder="0" allowfullscreen></iframe></div>';
    }

    //vimeo
    $pattern = '/^http:\/\/(www\.)?vimeo\.com\/(clip\:)?(\d+).*$/i';
    if (preg_match($pattern, $url, $matches))
    if ($preview) {
        $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$matches[3].".php"));
        return $hash[0]['thumbnail_large'];
    } else {
        return'<div class="embed" ><iframe src="http://player.vimeo.com/video/'.$matches[3].'?title=0&amp;byline=0&amp;portrait=0&amp;color=00adef" frameborder="0"></iframe></div>';
    }

    return false;
}


/////--------- Helpers

setlocale(LC_ALL, 'en_US.UTF8');
function normalize_str($str, $replace=array(), $delimiter='-') {
    if( !empty($replace) ) {
        $str = str_replace((array)$replace, ' ', $str);
    }
    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
    return $clean;
}

function indentJSON($json) {
    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = '    ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;
    for ($i = 0; $i <= $strLen; $i++) {
        $char = substr($json, $i, 1);
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        }
        else if (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos--;
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        $result .= $char;
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos++;
            }
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        $prevChar = $char;
    }
    return $result;
}

function recursiveDelete($str){
    if(is_file($str)){
        return @unlink($str);
    }
    elseif(is_dir($str)){
        $scan = glob(rtrim($str,'/').'/*');
        foreach($scan as $index=>$path){
            recursiveDelete($path);
        }
        return @rmdir($str);
    }
}