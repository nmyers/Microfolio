<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

Class ImageProcessing {

    const PROJECT_OFFLINE = 'offline';
    const PROJECT_HIDDEN  = 'hidden';
    const PROJECT_ONLINE  = 'online';

    private $isLoaded = false;

    private $file;

    public  $presets;
    public  $width,$height,$ratio;

    private $img;

    private $external = false;

    private $cache_folder = 'app/cache/';

    public function __construct() {
        $this->presets = array();
        $this->addPreset('full', '');
    }

    public function load($file) {
        if (strpos($file,'://')!==false)
            $this->external = true;
        elseif (!file_exists($file))
            throw new Exception("Cannot load file $file");

        $this->file = $file;
        list($this->width, $this->height) = getimagesize($file);
        $this->ratio = $this->width/$this->height;
        $this->img = imagecreatefromjpeg($file);
        $this->isLoaded = true;
        return true;
    }

    // original '', thumbnail "r,100,100,1|c,100,100,cc"
    public function addPreset($name,$preset_string) {
        $this->presets[$name] = $preset_string;
    }

    public function show($file,$preset=null) {
        
        //Shorthands for common combinations
        if (preg_match('/(r|c|t|f)(\d+)x(\d+)/i', $preset,$matches)) {
            $preset = '_tmp2';
            switch($matches[1]) {
                case 'r':
                    $this->addPreset('_tmp2', 'r,'.$matches[2].','.$matches[3].',0');
                    break;
                case 'f':
                    $this->addPreset('_tmp2', 'r,'.$matches[2].','.$matches[3].',1');
                    break;
                case 'c':
                    $this->addPreset('_tmp2', 'c,'.$matches[2].','.$matches[3].',cc');
                    break;
                case 't':
                    $this->addPreset('_tmp2', 'r,'.$matches[2].','.$matches[3].',1|c,'.$matches[2].','.$matches[3].',cc');
                    break;
            }
        }

        if (strpos($file,'://')!==false)
            $this->external = true;

        //serve original file
        if ($preset==null || $this->presets[$preset]=='') {
            $this->load($file);
            $this->render($file);
        }

        //check if original exists and preset defined
        if (!$this->external) {
            if ((!file_exists($file)) || (!isset($this->presets[$preset]))) {
                header('HTTP/1.0 404 Not Found');
                die();
            }
        }

        $cache_file = md5($this->presets[$preset].$file).'_'.basename($file);

        //Delete cache file if older than original
        if (!$this->external) {
            if (file_exists($this->cache_folder.$cache_file)) {
                if(filemtime($this->cache_folder.$cache_file) < filemtime($file))
                    unlink($this->cache_folder.$cache_file);
            }
        }

        //Creates a cache file it it doesn't exist
        if (!file_exists($this->cache_folder.$cache_file)) {
            $this->load($file);
            $this->processImage($preset);
            $this->saveProcessed($this->cache_folder.$cache_file);
        } else {
            $this->load($this->cache_folder.$cache_file);
        }

        //we should have a cache file to serve at this point
        $this->render($this->cache_folder.$cache_file);
    }

    private function render($file) {
        if (!$this->external) {
            $last_modified = filemtime($file);
            $last_modified_gmt = gmdate('D, d M Y H:i:s', $last_modified) . ' GMT';
            $etag = md5(filemtime($file) . $file);
            header(sprintf('ETag: "%s"', $etag));
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
                if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $last_modified_gmt || str_replace('"', NULL, stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $etag) {
                    header('HTTP/1.1 304 Not Modified');
                    exit();
                }
            }
            header('Last-Modified: ' . $last_modified_gmt);
            header('Cache-Control: public');
            header('Content-type: image/jpeg');
            header("Pragma: public");
        } else {
            header('Cache-Control: public');
            header('Content-type: image/jpeg');
            header("Pragma: public");
        }
        imagejpeg($this->img);
        exit();
    }

    private function processImage($preset) {
        if (!isset($this->presets[$preset]))
                throw new Exception("Cannot find preset $preset");
        $commands = explode('|',  $this->presets[$preset]);
        foreach ($commands as $command) {
            $p = explode(',',$command);
            switch($p[0]) {
                case 'r':
                    $this->resize($p[1], $p[2], $p[3]);
                    break;
                case 'c':
                    $this->crop($p[1], $p[2], $p[3]);
                    break;
                default:
                    throw new Exception("Cannot find action ".$p[0]);
                    break;
            }
        }
        return true;
    }

    private function saveProcessed($file) {
        imagejpeg($this->img, $file, 99);
    }

    //RESIZE W,H,M mode: inside/outside (inside will resize the image to fit the box, outside will resize to fill the box)
    //if W or H is set to 0, the image is resized using the other value while keeping the aspect ration
    //truth table:
        // mode | r1>r2 | h | w
        //  0   |   0   | 1 | 0
        //  0   |   1   | 0 | 1
        //  1   |   0   | 0 | 1
        //  1   |   1   | 1 | 0
    public function resize($w,$h,$mode) {
        $r = $w/$h;
        $h = ($mode == ($this->ratio>$r))*$h;
        $w = ($mode != ($this->ratio>$r))*$w;
                
        if ($w==0) {
            $nh = $h;
            $nw = $h*$this->ratio;
        } elseif ($h==0) {
            $nw = $w;
            $nh = $w/$this->ratio;
        }

        $tmp_img = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($tmp_img, $this->img, 0,0,0,0, $nw, $nh, $this->width, $this->height);
        $this->img = $tmp_img;
        $this->width = $nw;
        $this->height = $nh;
    }

    //CROP W,H,C  Center: TL,TC,TR,CL,CC,CR,BL,BC,BR define the crop center
    public function crop($w,$h,$pos) {
        $val = array('t'=>0,'c'=>0.5,'b'=>1,'l'=>0,'r'=>1);
        $dy = $val[$pos{0}]*($this->height-$h);
        $dx = $val[$pos{1}]*($this->width-$w);

        $tmp_img = imagecreatetruecolor($w, $h);
        imagecopy($tmp_img, $this->img, 0, 0, $dx, $dy, $w, $h);
        $this->img = $tmp_img;
        $this->width = $w;
        $this->height = $h;
    }

}






