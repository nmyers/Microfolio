<?php



class Projects implements Iterator {

    private static $instance;
    private $position = 0;

    public static $folder = 'content/';
    private $jsonFile = 'content.json';

    public $settings;
    public $projects;

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
        //sync projects
        foreach ($this->projects as $project)
                $project->sync();
    }

    /**
     * Returns an HTML representation of the menu
     *
     * @param boolean $filter Removes hidden and offline projects if set to true
     */
    public function getHTMLMenu($filter=true,$level='root') {
        $html = "<ol>\n";
        foreach ($this->projects as $key => $project) {
            if ($project->parent == $level) {
                $html .= "<li><div>".$project->title."</div>\n";
                $html .= $this->getHTMLMenu($filter,$key);
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
    public function reorderProjects($new_order) {
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
    }

    /**
     * Returns the project matching the slug
     *
     * @param string $projectSlug
     */
    public function getProject($projectSlug) {
        if (isset ($this->projects[$projectSlug]))
            return $this->projects[$projectSlug];
        throw new Exception("Cannot find project '$projectSlug'."); 
    }

    /**
     * Adds a new project
     *
     * @param string $projectTitle
     */
    public function addProject($projectTitle) {
        $projectSlug = normalize_str($projectTitle);
        if (!isset ($this->projects[$projectSlug])) {
            $this->projects[$projectSlug] = new Project();
            $this->projects[$projectSlug]->create($projectTitle);
        }
        throw new Exception("Cannot add '$projectTitle', project already exists.");
    }

    /**
     * Deletes the project matching the slug
     * 
     * @param string $projectSlug
     */
    public function deleteProject($projectSlug) {
        if (isset ($this->projects[$projectSlug])) {
            unset($this->projects[$projectSlug]);
            return true;
        }
        throw new Exception("Cannot delete '$projectTitle', project does not exist.");
    }

    public function renameProject($oldProjectSlug,$newProjectSlug) {
        //todo
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
                if ($item->type!=Project::ITEM_TYPE_EMBED) {
                   if (file_exists(Projects::$folder.  $this->slug.'/'.$item->url))
                       $this->gallery[$uid] = new GalleryItem($item);
                } else {
                   $this->gallery[$uid] = new GalleryItem($item);
                }
            }
        }
        $this->loaded = true;
    }

    public function create($title) {
        if (!$this->loaded)
                throw new Exception("Project already loaded, cannot create '$title'.");
        $this->title = $title;
        $this->slug = normalize_str($title);
        $this->timestamp = time();
        if (!mkdir(Projects::$folder.$this->slug))
                throw new Exception("Cannot create new folder: '".Projects::$folder.$this->slug."' .");
    }

    public function reorder($new_order) {
        $new_gallery = array();
        if (count($new_gallery)!=count($this->gallery))
                throw new Exception("Cannot reorder. Item counts dont match.");
        foreach ($new_order as $uid) {
            if (isset($this->gallery[$uid]))
                $new_gallery[$uid] = $this->gallery[$uid];
            else
                throw new Exception("Cannot reorder. Item uid does not exist: $uid.");
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
                    $items[] = $item->url;
                }
            }
        }
        foreach ($files as $file) {
            $isNew = is_file($folder. $file);
            $isNew = $isNew && stripos($file, '.jpg');
            $isNew = $isNew && !in_array($file, $items);
            if ($isNew) {
                $this->addItem(array(
                    "type" => Project::ITEM_TYPE_IMAGE,
                    "url" => $file
                ));
            }
        }
    }

    public function getItem($uid) {
        if (isset($this->gallery[$uid]))
            return $this->gallery[$uid];
        throw new Exception("Cannot find item '$uid'.");
    }

    public function addItem($data) {
        //the url/file need to exists.
        if ($data['type']!=Project::ITEM_TYPE_EMBED)
            if (!file_exists(Projects::$folder.$this->slug.'/'.$data['url']))
                throw new Exception("Cannot add item, file does not exist: ".$data['url'].".");
        $uid = uniqid('item_');
        $this->gallery[$uid] = new GalleryItem($data);
        return $this->gallery[$uid];
    }

    public function deleteItem($uid) {
        if (isset($this->gallery[$uid])) {
            unset($this->gallery[$uid]);
            return true;
        }
        throw new Exception("Cannot find item '$uid'.");
    }

    //------------------------
    // ITERATOR METHODS
    //------------------------ 

    function rewind() {
        var_dump(__METHOD__);
        $this->position = 0;
    }

    function current() {
        var_dump(__METHOD__);
        return $this->array[$this->position];
    }

    function key() {
        var_dump(__METHOD__);
        return $this->position;
    }

    function next() {
        var_dump(__METHOD__);
        ++$this->position;
    }

    function valid() {
        var_dump(__METHOD__);
        return isset($this->array[$this->position]);
    }

}

class GalleryItem {

    public $type;
    public $title;
    public $caption;
    public $url;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this,$key)) $this->$key = $value;
        }
    }

}



function normalize_str($rawTag) {
     $accent  =". ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ";
     $noaccent="--aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby";
     $tag=strtr(trim($rawTag),$accent,$noaccent);
     $normalized_valid_chars = 'a-zA-Z0-9-';
     $normalized_tag = preg_replace("/[^$normalized_valid_chars]/", "", $tag);
     return strtolower($normalized_tag);
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

// Grab the next character in the string
        $char = substr($json, $i, 1);

// Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        }
// If this character is the end of an element,
// output a new line and indent the next line
        else if (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos--;
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
// Add the character to the result string
        $result .= $char;

// If the last character was the beginning of an element,
// output a new line and indent the next line
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