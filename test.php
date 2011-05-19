<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


include 'app/lib/phpconsole/PhpConsole.php';
PhpConsole::start();
/*
include 'app/model-n.php';

global $projects;
$projects = Projects::singleton();

$str = <<<DEMO
<div class="header_text">
  		<h1>PHP F1</h1>
    	<h3>Help in PHP, get PHP code, scripts, tutorials</h3>
</div>
DEMO;

$projects->sync();

$projects->getProject('tete')->text = $str;

$projects->save();

echo $projects->toJSON();

echo $projects->getHTMLMenu();
*/
/*
include 'app/lib/image_processing/image_processing.php';

$imageP = new ImageProcessing();
$imageP->addPreset('thumbnail', 'r,150,150,1|c,100,100,bc');
//$imageP->show('content/tete/stargazerscboldt04.jpg');
//$imageP->show('content/tete/stargazerscboldt10.jpg','thumbnail');
//$imageP->show('content/tete/stargazerscboldt10.jpg','r,150,150,1|c,100,100,bc',true);

$url = 'http://marketing-for-smallbusinesses.co.uk/wp-content/uploads/2009/11/google-chrome-logo.jpg';

$imageP->show($url,'r,180,180,1|c,100,100,tl',true);
*/
/*
$urlval = 'list[ab]=root&list[cd]=root&list[ef]=cd&list[gh]=ef&list[kl]=cd&list[ij]=root';
parse_str($urlval,$data);
print_r($data);

toHtml($data['list']);

function toHtml($array,$level='root') {
    echo '<ol>';
    foreach ($array as $item => $parent) {
        if ($parent==$level) {
            echo "<li><div>item $item</div>\n";
            toHtml($array,$item);
            echo "</li>\n";
        }
    }
    echo '</ol>';
}*/

print_r($_SERVER);