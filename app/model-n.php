<?php


global $projects;
$projects = Projects::singleton();

class Projects implements Iterator {

    private static $instance;
    private $position = 0;
    private $settings;
    private $projects;

    private function __construct() {
        $this->position = 0;
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

    }

    /**
     * Converts the projects back to json and saves the file
     */
    public function save() {

    }

    /**
     * Synchronizes the content folder with the json file
     */
    public function sync() {

    }

    /**
     * Returns an HTML representation of the menu
     *
     * @param boolean $filter Removes hidden and offline projects if set to true
     */
    public function getHTMLMenu($filter=true) {

    }

    /**
     * Reorders the projects according to the array
     * @see jquery nested sortable
     *
     * @param array $array
     */
    public function reorderProjects($array) {

    }

    /**
     * Returns the project matching the slug
     *
     * @param string $projectSlug
     */
    public function getProject($projectSlug) {

    }

    /**
     * Adds a new project
     *
     * @param string $projectTitle
     */
    public function addProject($projectTitle) {

    }

    /**
     * Deletes the project matching the slug
     * 
     * @param string $projectSlug
     */
    public function deleteProject($projectSlug) {

    }

    /**
     * Converts this object to JSON
     */
    public function toJSON() {
        
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

    private $position = 0;
    private $title;
    private $status;
    private $parent;
    private $style;
    private $text;
    private $gallery;

    public function __construct() {
        $this->position = 0;
    }

    public function load() {
        
    }

    public function reorder($array) {
        
    }

    public function getItem($uid) {
        
    }

    public function addItem() {
        
    }

    public function deleteItem($uid) {
        
    }

    public function toJSON() {
        
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

    private $uid;
    private $type;
    private $title;
    private $caption;
    private $url;

    public function __construct($uid, $type, $url, $title, $caption) {
        $this->uid = $uid;
        $this->type = $type;
        $this->url = $url;
        $this->title = $title;
        $this->caption = $caption;
    }

    public function toJSON() {

    }

}
